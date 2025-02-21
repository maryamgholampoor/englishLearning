<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermRolePermission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table= "perm_role_permissions";

    protected $fillable = [
        'perm_role_id', 'perm_action_id',
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
            'perm_action_id' => trans('message.perm_action_id'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getRules(): array
    {
        return [
            'perm_role_id' => 'required|integer|exists:perm_roles,id',
            'perm_action_id' => 'required|integer|exists:perm_action,id',
        ];
    }

    /**
     * @param $query
     * @param $roleId
     * @return mixed
     */
    public function scopeRoleId($query, $roleId)
    {
        return $query->where('perm_role_id', '=', $roleId);
    }

    /**
     * @param $query
     * @param $roleIds
     * @return mixed
     */
    public function scopeRoleIds($query, $roleIds)
    {
        return $query->whereIn('perm_role_id', $roleIds);
    }

    /**
     * @param $query
     * @param $section
     * @param $action
     * @param $userId
     * @return mixed
     */
    public function scopeGetRolesByUser($query, $section, $action, $userId)
    {
        return $query->join('perm_user_roles', 'perm_user_roles.perm_role_id', '=', 'perm_role_permissions.perm_role_id')
            ->join('perm_actions', 'perm_actions.id', '=', 'perm_role_permissions.perm_action_id')
            ->join('perm_sections', 'perm_sections.id', '=', 'perm_actions.perm_section_id')
            ->join('perm_roles', 'perm_roles.id', '=', 'perm_user_roles.perm_role_id')
            ->where('perm_actions.method_name', '=', $action)
            ->where('perm_sections.code', '=', $section)
            ->where('perm_user_roles.user_id', '=', $userId);
    }

    /**
     * @param $query
     * @param $roleId
     * @return mixed
     */
    public function scopeGetPermissionsByRoleId($query, $roleId)
    {
        return $query->join('perm_actions', 'perm_actions.id', '=', 'perm_role_permissions.perm_action_id')
            ->join('perm_sections', 'perm_sections.id', '=', 'perm_actions.perm_section_id')
            ->where('perm_role_permissions.perm_role_id', '=', $roleId);
    }
}
