<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PadcastCategory extends Model
{
  use SoftDeletes;

  protected $table= 'padcast_category';

}
