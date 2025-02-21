<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermRole extends Model
{
    use SoftDeletes;

    protected $table='perm_roles';

    public static $searchable = [
        'title' => ['operator' => 'like', 'value' => '%%%s%%']
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title'
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
            'title' => trans('message.title'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getRules(): array
    {
        return [
            'title' => 'required|string|max:100',
        ];
    }

    /**
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeId($query, $id)
    {
        return $query->where('id', '=', $id);
    }

}
