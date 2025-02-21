<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermSection extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title', 'code',
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
            'code' => trans('message.code'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getRules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'code' => 'required|string|max:50',
        ];
    }

    /**
     * @return HasMany
     */
    public function action(): HasMany
    {
        return $this->hasMany(PermAction::class, 'perm_section_id', 'id');
    }
}
