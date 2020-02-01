<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['original_name'];
    protected $path;

    /**
     * Generate file name to store upload on local filesystem.
     *
     * @return string
     */
    public function generateStorageName()
    {
        return $this->id . '.xml';
    }

    /**
     * Defines the relationship between User and Upload.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Defines the relationship between Upload and Screening.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function screening()
    {
        return $this->hasMany('App\Screening');
    }

    /**
     * Defines the relationship between Upload and ScreeningTest.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function screeningTest()
    {
        $this->hasMany('App\ScreeningTest');
    }
}
