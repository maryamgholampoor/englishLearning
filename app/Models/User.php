<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */



    protected $fillable = [
        'mobile_number', 'name', 'last_name', 'email', 'birth_date', 'sex', 'profile_pic'
    ];

    protected $table= 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    const USER_ACTIVE = 1;
    const USER_DEACTIVE = 0;
    const USER_Registered = 2;




    /**
     * @param $query
     * @param $mobile
     * @return mixed
     */
    public function scopeFindByMobile($query, $mobile)
    {
        return $query->where('mobile_number', '=', $mobile);
    }
}
