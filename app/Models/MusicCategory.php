<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MusicCategory extends Model
{
  use SoftDeletes;

  protected $table= 'music_category';

}
