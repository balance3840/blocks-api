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

        $user = User::where('email', $email)->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        if(!CustomHash::check($password, $user->password)) {
            return $this->responseError("Credentials do not match.", 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'];

        return $this->responseSuccess($data, 200);

    }

}
