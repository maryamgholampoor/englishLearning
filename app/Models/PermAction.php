<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermAction extends Model
{
    use SoftDeletes;

    protected $table= "perm_actions";

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title', 'method_name', 'perm_section_id',
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
            'method_name' => trans('message.method_name'),
            'perm_section_id' => trans('message.perm_section_id'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getRules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'method_name' => 'required|string|max:50',
            'perm_section_id' => 'required|integer|exists:perm_sections,id',
        ];
    }
}
