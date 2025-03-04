<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use App\Http\Utilities\Request as UtilityRequest;
use App\Models\PermAction;
use App\Models\PermRole;
use App\Models\PermRolePermission;
use App\Models\PermSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    use Response, StatusCode, UtilityRequest;

    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllRoles(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $searches = $request->get('search', false);
        $query = PermRole::query();
        if ($searches) {
            foreach ($searches as $column => $value) {
                $query->where($column, PermRole::$searchable[$column]['operator'], sprintf(PermRole::$searchable[$column]['value'], $value));
            }
        }

        if ($request->has('per_page')) {
            $roles = $query->OrderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
            $items = $roles->items();
        } else {
            $roles = $query->OrderBy('id', 'desc')->get();
            $items = $roles;
        }

        // Add details of permissions
        foreach ($items as $data) {
            $result = PermRolePermission::getPermissionsByRoleId($data->id)->select('perm_sections.title as sectionTitle',
                'perm_actions.title as actionTitle', 'perm_role_permissions.*')->get();
            $data->permissions = $result;
        }
        return $this->sendJsonResponse($roles, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $sections = PermSection::with('action')->get();
        return $this->sendJsonResponse($sections, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addRole(Request $request)
    {
        // Check validation
        $this->rules = [
            'role_title' => 'required|string|max:100|unique:perm_roles,title',
        ];
        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        try {
            DB::beginTransaction();
            $role = PermRole::create(['title' => $request->role_title]);
            DB::commit();

            return $this->sendJsonResponse($role, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editRole(Request $request, $id)
    {
        // Check validation
        $this->rules = [
            'role_title' => 'required|string|max:100|unique:perm_roles,title',
        ];
        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        try {
            $role = PermRole::where('id',$id)->update(['title'=>$request->role_title]);
            return $this->sendJsonResponse([], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        }
        catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            PermRole::id($id)->delete();
            DB::commit();
            return $this->sendJsonResponse([], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function addRolePerm(Request $request){

        // Check validation
        $this->rules = [
            'role_id' => 'required|string|max:100|exists:perm_roles,id',
            'actions.*' => 'required|exists:perm_actions,id'
        ];

        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        // After validation, insert data
        try {
            DB::beginTransaction();

            foreach ($request->actions as $action)
            {
                PermRolePermission::create(['perm_role_id' => $request->role_id , 'perm_action_id' => $action]);
            }
            // Commit changed
            DB::commit();
            return $this->sendJsonResponse([], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }
    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check validation
        $this->rules = [
            'role_title' => 'required|string|max:100|unique:perm_roles,title,' . $id . ',id',
            'account_type_id' => 'nullable|integer|exists:account_types,id',
            'actions.*' => 'required|exists:perm_actions,id'
        ];
        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        // After validation
        try {
            DB::beginTransaction();
            // Create role
            $role = PermRole::id($id)->update(['title' => $request->role_title, 'account_type_id' => $request->get('account_type_id', null)]);
            // Delete old permissions
            PermRolePermission::roleId($id)->delete();
            // Insert permissions
            foreach ($request->actions as $action) {
                PermRolePermission::create(['perm_role_id' => $id, 'perm_action_id' => $action]);
            }
            // Commit changed
            DB::commit();
            // Return response
            return $this->sendJsonResponse([], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */

    public function getOneRole(Request $request, $id)
    {
        $role = PermRole::find($id);
        return $this->sendJsonResponse(['role' => $role], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
    }

    public function getPermAction()
    {
        $permAction = PermAction::get();
        return $this->sendJsonResponse(['permAction' => $permAction], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
    }


}
