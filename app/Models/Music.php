<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Music extends Model
{
    use SoftDeletes;

    protected $table = 'music';

    public function musicCategory()
    {
        return $this->belongsTo(MusicCategory::class, 'music_category_id');
    }

}
