<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermUserRole extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'perm_role_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    public function attributes()
    {
        return [
            'perm_role_id' => trans('message.perm_role_id'),
            'user_id' => trans('message.user_id'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getRules(): array
    {
        return [
            'perm_role_id' => 'required|integer|exists:perm_roles,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeFindByUserId($query, $userId)
    {
        return $query->where('user_id', '=', $userId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function role()
    {
        return $this->hasOne(PermRole::class, 'id', 'perm_role_id');
    }
}
