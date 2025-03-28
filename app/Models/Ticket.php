<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;

class Ticket extends Model
{
   use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table='ticket';

    public function user()
    {
       return $this->belongsTo(User::class,'user_id');
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class,'category_id');
    }



}
