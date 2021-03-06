<?php

namespace App\Http\Controllers;

use App\User;
use App\Enums\UserEnum;
use App\Helpers\CustomHash;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\Validators\UserValidator;

class UserController extends Controller
{
    use UserValidator;

    private String $validatorMessage = "Name, lastname, role_id and institute_id are required.";
    private String $validatorCreateMessage = "Name, lastname, email, password, role_id and institute_id are required.";
    private String $notFoundMessage = "The requested user does not exist.";
    private String $notPermissions = "This user does not have the permissions to perform the requested action.";
    private String $notLogout = "There was an error while trying to logging the user out";

    public function index(Request $request)
    {
        $user = Auth::user();
        $onlyMine = $request->get('onlyMine');
        
        if($onlyMine) {
            if(!$user->tokenCan('user:mine:list')) {
                return $this->responseError($this->notPermissions, 403);
            }
            return User::where('created_by', $user->id)->paginate();
        }

        if ($user->tokenCan('user:others:list')) {
            return User::paginate();
        }
        return $this->responseError($this->notPermissions, 403);
    }

    public function show(int $id) {

        $authUser = Auth::user();
        $user = User::where('id', $id)->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        if($user->created_by === $authUser->id) {
            if(!$authUser->tokenCan('user:mine:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } elseif($user->id === $authUser->id) {
            if(!$authUser->tokenCan('user:mine:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } else {
            if(!$authUser->tokenCan('user:others:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
        }

        return $this->responseSuccess($user);

    }

    public function create(Request $request)
    {
        $authUser = Auth::user();

        if($authUser->tokenCan('user:create')) {

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
            $user->created_by = $authUser->id;

            try {
                $user->save();
            } catch (\Exception $e) {
                Log::error("Error creating user. ".$e);
                return $this->responseError("There was an error creating the user", 500);
            }

            return $this->responseSuccess($user, 201);
        }

        return $this->responseError($this->notPermissions, 403);

    }

    public function update(int $id, Request $request) {

        $user = User::where('id', $id)->first();
        $authUser = Auth::user();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        if($user->created_by === $authUser->id) {
            if(!$authUser->tokenCan('user:mine:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } elseif($user->id === $authUser->id) {
            if(!$authUser->tokenCan('user:mine:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } else {
            if(!$authUser->tokenCan('user:others:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
        }

        $validator = $this->validateUser($request->all(), $user->id);

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

    public function changePassword(Request $request) {
        $user = Auth::user();
        $password = $request->get('password');
        $newPassword = $request->get('newPassword');
        $repeatNewPassword = $request->get('repeatNewPassword');

        if(!CustomHash::check($password, $user->password)) {
            return $this->responseError("Credentials do not match.", 401);
        }

        if($newPassword !== $repeatNewPassword) {
            return $this->responseError("New password do not match", 401);   
        }

        try {
            $user->password = $password = CustomHash::make($newPassword);
            $user->save();
        } catch(\Exception $e) {
            Log::error("Error changing password ".$e);
            return $this->responseError("Error changing password", 500);
        }

        return $this->responseSuccess('', 200);

    }

    public function login(Request $request) {

        $email = $request->get('email');
        $password = $request->get('password');
        $tokenAbilities = UserEnum::STUDENT_ABILITIES();

        $user = User::where('email', $email)->first();

        if(!$user) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        if(!CustomHash::check($password, $user->password)) {
            return $this->responseError("Credentials do not match.", 401);
        }

        if($user->role->id === UserEnum::ADMIN()) {
            $tokenAbilities = UserEnum::ADMIN_ABILITIES();
        }

        if($user->role->id === UserEnum::TEACHER()) {
            $tokenAbilities = UserEnum::TEACHER_ABILITIES();
        }

        $token = $user->createToken('auth_token', $tokenAbilities)->plainTextToken;

        $data = ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'];

        return $this->responseSuccess($data, 200);

    }

    public function logout() {
        try {
            $user = Auth::user();
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            return $this->responseSuccess('', 200);
        } catch(\Exception $e) {
            Log::error("Error logging user out ".$e);
            return $this->responseError($this->notLogout, 500);
        }
    }

}
