<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WordCategory extends Model
{
   use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table='word_category';

    protected function wordCount()
    {
        return $this->hasMany(WordUser::class,'word_category_id');
    }


}
