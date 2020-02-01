<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\SeraXMLParser;
use App\Upload;
use DB;
use Storage;

class UploadsController extends Controller
{

    const UPLOADS_DISK = 'uploads';
    protected $xmlParser;

    public function __construct(SeraXMLParser $xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    public function index(Request $request)

    {
        $uploads = $request->user()->uploads()->get();

        return view('uploads.index', [
            'uploads' => $uploads
        ]);
    }

    public function create()
    {
        return view('uploads.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'screenings' => 'required|file|mimetypes:application/xml',
        ]);

        $user = $request->user();
        $file = $request->file('screenings');

        $upload = new Upload();
        DB::transaction(function () use ($user, $file, $upload) {
            $upload->fill([
                'original_name' => $file->getClientOriginalName(),
            ]);
            $user->uploads()->save($upload);

            $path = $file->storeAs('', $upload->generateStorageName(), self::UPLOADS_DISK);

            $upload->path = $path;
            $upload->save();
        });

        $this->xmlParser->processScreenings($upload->id, $upload->path, self::UPLOADS_DISK);

        return redirect()->action('UploadsController@index')->with('status', 'Fișierul a fost încarcat.');
    }

    public function show(Upload $upload)
    {

        return '';
    }

    public function download(Request $request, Upload $upload)
    {
        if ($request->user()->cant('download', $upload)) {
            return abort(403);
        }

        // Get path to file
        $localDisk = Storage::disk(self::UPLOADS_DISK);
        $pathPrefix = $localDisk->getAdapter()->getPathPrefix();

        // Send file to browser
        return response()->download(
            $pathPrefix . $upload->path,
            $upload->original_name
        );
    }
}
