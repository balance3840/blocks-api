<?php

namespace App\Http\Controllers;

use App\Role;
use App\Traits\Validators\RoleValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    use RoleValidator;

    private String $validatorMessage = "Name is required";
    private String $notFoundMessage = "The requested role does not exist";

    public function index()
    {
        return Role::paginate();
    }

    public function show(int $id) {
        $role = Role::where('id', $id)->first();
        if(!$role) {
            return $this->responseError($this->notFoundMessage, 404);
        }
        return $this->responseSuccess($role);
    }

    public function create(Request $request)
    {
        $validator = $this->validateRole($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $role = new Role;
        $role->name = $request->get('name');
        $role->description = $request->get('description');

        try {
            $role->save();
        } catch (\Exception $e) {
            Log::error("Error creating role. ".$e);
            return $this->responseError("There was an error creating the role", 500);
        }

        return $this->responseSuccess($role, 201);
    }

    public function update(int $id, Request $request) {

        $role = Role::where('id', $id)->first();

        if(!$role) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateRole($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $role->name = $request->get('name');

        if($request->get('description')) {
            $role->description = $request->get('description');
        }

        try {
            $role->update();
        } catch (\Exception $e) {
            Log::error("Error updating role ".$id." ".$e);
            return $this->responseError("There was an error updating the role", 500);
        }

        return $this->responseSuccess($role);
    }
}
