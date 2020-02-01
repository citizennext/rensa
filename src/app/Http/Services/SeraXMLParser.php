<?php

namespace App\Http\Services;

use App\Http\Controllers\UploadsController;
use App\Screening;
use App\ScreeningData;
use App\ScreeningTest;
use App\ScreeningTestData;
use Storage;
use DB;

class SeraXMLParser extends XMLParser
{

    protected $FailedScreenings = [];

    /**
     * Process screenings
     *
     * @return array
     */
    public function processScreenings($uploadID, $path, $disk)
    {
        $this->parseToArray($path, $disk);

        $screenings = $this->XMLArray['Patients']['Patient'];

        foreach ($screenings as $index => $screening) {
            try {
                DB::transaction(function () use ($index, $screening, $uploadID) {
                    $screeningID = $this->saveScreening($screening, $uploadID);
                    $this->saveScreeningData($screening, $screeningID);
                    $this->saveScreeningTests($screening['Tests'], $screeningID, $uploadID);

                });
            } catch (Exception $e) {
                $this->FailedScreenings[$index] = $index;
            }

        }

    }

    protected function saveScreening($screeningPatientModel, $uploadID) {
        $screening = new Screening();
//        $screening->cnp = $this->emptyArrayToString($screeningPatientModel['PatientId']);
        $screening->cnp = '111111111'.rand(1, 9999);
        $screening->upload_id = $uploadID;
        $screening->unique_id = $this->emptyArrayToString($screeningPatientModel['Guid']);
        $screening->first_name = $this->emptyArrayToString($screeningPatientModel['FirstName']);
        $screening->last_name = $this->emptyArrayToString($screeningPatientModel['LastName']);
        $screening->birthdate = $this->emptyArrayToString($screeningPatientModel['Birthdate']);
        $screening->gender = strtolower($this->emptyArrayToString($screeningPatientModel['Gender']));
        if ($screening->gender == 'unspecified') {
            $screening->gender = 'unknown';
        }
//        $screening->telephone = $this->emptyArrayToString($screeningPatientModel['Telephone']);
//        $screening->mobile_phone = $this->emptyArrayToString($screeningPatientModel['MobilePhone']);
//        $screening->mothers_first_name = $this->emptyArrayToString($screeningPatientModel['MothersFirstName']);
//        $screening->mothers_last_name = $this->emptyArrayToString($screeningPatientModel['MothersLastName']);
//        $screening->email = $this->emptyArrayToString($screeningPatientModel['Email']);
//        $screening->comment = $this->emptyArrayToString($screeningPatientModel['Comment']);
//        $screening->not_screened_reasons = $this->emptyArrayToString($screeningPatientModel['NotScreenedReasons']);
//        $screening->risk_factors_presence = $this->emptyArrayToString($screeningPatientModel['RiskFactorsPresence']);
//        $screening->le_abr_result = $this->emptyArrayToString($screeningPatientModel['LE_AbrResult']);
//        $screening->re_abr_result = $this->emptyArrayToString($screeningPatientModel['RE_AbrResult']);
//        $screening->le_te_result = $this->emptyArrayToString($screeningPatientModel['LE_TEResult']);
//        $screening->re_te_result = $this->emptyArrayToString($screeningPatientModel['RE_TEResult']);
//        $screening->le_dp_result = $this->emptyArrayToString($screeningPatientModel['LE_DPResult']);
//        $screening->re_dp_result = $this->emptyArrayToString($screeningPatientModel['RE_DPResult']);
        $screening->save();
        return $screening->id;
    }

    protected function saveScreeningData($screeningPatientData, $screeningID) {
        $screeningData = new ScreeningData();
        $screeningData->screening_id = $screeningID;
        $screeningData->data = json_encode($screeningPatientData);
        $screeningData->save();
    }

    protected function saveScreeningTests($screeningTestData, $screeningID, $uploadID) {
        if (!isset($screeningTestData['Test'])) {
            return;
        }
        if (!is_array($screeningTestData['Test'])) {
            $screeningTestData['Test'][0] = $screeningTestData;
        }
        foreach ($screeningTestData['Test'] as $test) {
            $testID = $this->saveScreeningTest($test, $screeningID, $uploadID);
            $this->saveScreeningTestData($testID, $test);
        }
    }

    protected function saveScreeningTest($testModel, $screeningID, $uploadID) {
        $test = new ScreeningTest();
        $test->screening_id = $screeningID;
        $test->upload_id = $uploadID;
        if (!isset($testModel['Guid'])) {
            return 0;
        }
        $test->unique_id = $this->emptyArrayToString($testModel['Guid']);
        $test->test_type = $this->emptyArrayToString($testModel['Method']);
        $test->test_datetime = $this->emptyArrayToString($testModel['CreateDate']);
        $test->ear_type = $this->emptyArrayToString($testModel['Side']);
        switch ($test->ear_type) {
            case 'Left':
                $test->left_result = $this->emptyArrayToString($testModel['Outcome']);
                break;
            case 'Right':
                $test->right_result = $this->emptyArrayToString($testModel['Outcome']);
                break;
        }
        $test->test_facility = $this->emptyArrayToString($testModel['Facility']);
        $test->device_serial_number = $this->emptyArrayToString($testModel['DeviceSerialNumber']);
        $test->probe_serial_number = $this->emptyArrayToString($testModel['ProbeSerialNumber']);
        $test->device = $this->emptyArrayToString($testModel['DeviceName']);
        $test->save();
        return $test->id;
    }

    protected function saveScreeningTestData($testID, $testData) {
        if (!$testID) {
            return;
        }
        $screeningTestData = new ScreeningTestData();
        $screeningTestData->screening_test_id = $testID;
        $screeningTestData->data = json_encode($testData);
        $screeningTestData->save();
    }

    protected function emptyArrayToString($_value) {
        return ($_value) ? $_value : '';
    }
}
