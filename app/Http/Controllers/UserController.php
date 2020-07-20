<?php

namespace App\Http\Controllers;

use App\User;
use App\Helpers\CustomHash;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;
use App\Traits\Validators\UserValidator;

class UserController extends Controller
{
    use UserValidator;

    private String $validatorMessage = "Name, lastname, role_id and institute_id are required.";
    private String $validatorCreateMessage = "Name, lastname, email, password, role_id and institute_id are required.";
    private String $notFoundMessage = "The requested user does not exist";

    public function index()
    {
        return User::paginate();
    }

    public function show(int $id) {

        $user = User::where('id', $id)
            ->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        return $this->responseSuccess($user);
    }

    public function create(Request $request)
    {
        $validator = $this->validateUserCreate($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorCreateMessage);
        }

        $password = CustomHash::make($request->get('password'));

        $user = new User;
        $user->name = $request->get('name');
        $user->lastname = $request->get('lastname');
        $user->email = $request->get('email');
        $user->password = $password;
        $user->role_id = $request->get('role_id');
        $user->institute_id = $request->get('institute_id');

        try {
            $user->save();
        } catch (\Exception $e) {
            Log::error("Error creating user. ".$e);
            return $this->responseError("There was an error creating the user", 500);
        }

        return $this->responseSuccess($user, 201);
    }

    public function update(int $id, Request $request) {

        $user = User::where('id', $id)->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateUser($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $user->name = $request->get('name');
        $user->lastname = $request->get('lastname');

        if($request->get('email')) {
            $user->email = $request->get('email');
        }

        $user->role_id = $request->get('role_id');
        $user->institute_id = $request->get('institute_id');

        try {
            $user->update();
        } catch (\Exception $e) {
            Log::error("Error updating user ".$id." ".$e);
            return $this->responseError("There was an error updating the user", 500);
        }

        return $this->responseSuccess($user);
    }

    public function login(Request $request) {
        
        $email = $request->get('email');
        $password = $request->get('password');
        $tokenAbilities = [];

        $user = User::where('email', $email)->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        if(!CustomHash::check($password, $user->password)) {
            return $this->responseError("Credentials do not match.", 401);
        }

        if($user->role->id === 1) {
            array_push($tokenAbilities, ...[
                //user
                'user:create', 
                'user:mine:list',
                'user:mine:view', 
                'user:mine:edit', 
                'user:mine:delete',
                'user:others:list',
                'user:others:view', 
                'user:others:edit', 
                'user:others:delete',
                //group
                'group:create',
                'group:mine:list',
                'group:mine:view',
                'group:mine:edit',
                'group:mine:delete',
                'group:others:list',
                'group:others:view',
                'group:others:edit',
                'group:others:delete',
                //task
                'task:create',
                'task:mine:list',
                'task:mine:view',
                'task:mine:edit',
                'task:mine:delete',
                'task:others:list',
                'task:others:view',
                'task:others:edit',
                'task:others:delete'
            ]);
        }

        $token = $user->createToken('auth_token', $tokenAbilities)->plainTextToken;

        $data = ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'];

        return $this->responseSuccess($data, 200);

    }

}
