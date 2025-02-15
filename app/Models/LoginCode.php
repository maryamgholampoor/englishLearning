<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginCode extends Model
{
    use SoftDeletes;

    protected $table = 'login_code';

    protected $fillable = [
        'code', 'user_id', 'expiration_time', 'used_time',
    ];


    public function attributes()
    {
        return [
            'code' => trans('message.code'),
            'user_id' => trans('message.user_id'),
            'expiration_time' => trans('message.expiration_time'),
            'used_time' => trans('message.used_time'),
        ];
    }

    /**
     * @param $query
     * @param $code
     * @param $expiration
     * @return mixed
     */
    public function scopeFindByCode($query, $user_id, $code)
    {
        return $query->where('user_id', '=', $user_id)->where('code', '=', $code);
//        return $query->where('user_id', '=', $user_id)->where('expiration_time', '<=', $expiration);
    }


}
