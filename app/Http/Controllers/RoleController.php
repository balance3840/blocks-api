<?php

namespace App\Http\Controllers;

use App\Role;
use App\Traits\Validators\RoleValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use RoleValidator;

    public function index()
    {
        return Role::paginate();
    }

    public function show(int $id) {
        $role = Role::where('id', $id)->first();
        if(!$role) {
            return $this->responseError("The requested role does not exist");
        }
        return $this->responseSuccess($role);
    }

    public function create(Request $request)
    {
        $validator = $this->validateRole($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->roleValidatorMessage);
        }

        $role = new Role;
        $role->name = $request->get('name');
        $role->description = $request->get('description');

        $role->save();

        return $this->responseSuccess($role, 201);
    }

    public function update(int $id, Request $request) {

        $role = Role::where('id', $id)->first();

        if(!$role) {
            return $this->responseError("The requested role does not exist");
        }

        $validator = $this->validateRole($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->roleValidatorMessage);
        }

        $role->name = $request->get('name');

        if($request->get('description')) {
            $role->description = $request->get('description');
        }

        $role->update();

        return $this->responseSuccess($role, 200);
    }
}
