<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Padcast extends Model
{
  use SoftDeletes;

  protected $table= 'padcast';

    public function padcastCategory()
    {
        return $this->belongsTo(PadcastCategory::class,'padcastCategory_id');
    }

}
