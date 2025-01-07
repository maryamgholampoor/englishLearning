<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WordUser extends Model
{
    use SoftDeletes;

    protected $table = 'word_user';

}
