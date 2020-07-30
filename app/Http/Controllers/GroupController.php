<?php

namespace App\Http\Controllers;

use App\Group;
use App\UserGroup;
use App\Traits\Validators\GroupValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    use GroupValidator;

    private String $validatorMessage = "Name, grade, level and stage_id are required.";
    private String $notFoundMessage = "The requested group does not exist";
    private String $notPermissions = "This user does not have the permissions to perform the requested action.";

    public function index(Request $request)
    {
        $user = Auth::user();
        $onlyMine = $request->get('onlyMine');

        if($onlyMine) {
            if(!$user->tokenCan('group:mine:list')) {
                return $this->responseError($this->notPermissions, 403);
            }
            return Group::with('stage')
            ->whereHas('members', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('id', 'desc')
            ->paginate();
        }

        if(!$user->tokenCan('group:others:list')) {
            return $this->responseError($this->notPermissions, 403);
        }

        return Group::with('stage')
            ->orderBy('id', 'desc')
            ->paginate();
    }

    public function show(Request $request, int $id) {

        $user = Auth::user();
        $onlyMine = $request->get('onlyMine');
        $group = null;

        if($onlyMine) {
            if(!$user->tokenCan('group:mine:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
            $group = Group::where('id', $id)
                ->with('stage')
                ->whereHas('members', function (Builder $query) use($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();
        } else {
            if(!$user->tokenCan('group:others:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
            $group = Group::where('id', $id)
                ->with('stage')
                ->first();
        }

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        return $this->responseSuccess($group);
    }

    public function showMembers(int $id) {

        $user = Auth::user();

        $group = Group::find($id);

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $members = $group->members()->get();

        $userBelong = $members->where('id', $user->id);

        if(count($userBelong) > 0) {
            if($user->tokenCan('group:members:list')) {
                return $this->responseSuccess($members);
            }
        } else {
            if($user->tokenCan('group:others:members:list')) {
                return $this->responseSuccess($members);
            }
        }

        return $this->responseError($this->notPermissions, 403);

    }

    public function showTasks(int $id) {

        $user = Auth::user();

        $group = Group::find($id);

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $members = $group->members()->get();

        $userBelong = $members->where('id', $user->id);

        $tasks = Group::find($id)->tasks()->orderBy('id', 'desc')->get();

        if(count($userBelong) > 0) {
            if($user->tokenCan('group:tasks:list')) {
                return $this->responseSuccess($tasks);
            }
        } else {
            if($user->tokenCan('group:others:tasks:list')) {
                return $this->responseSuccess($tasks);
            }
        }

        return $this->responseError($this->notPermissions, 403);

    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if(!$user->tokenCan('group:create')) {
            return $this->responseError($this->notPermissions, 403);
        }

        $validator = $this->validateGroup($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $group = new Group;
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->grade = $request->get('grade');
        $group->level = $request->get('level');
        $group->stage_id = $request->get('stage_id');

        try {
            $group->save();
            $userGroup = new UserGroup;
            $userGroup->user_id = $user->id;
            $userGroup->group_id = $group->id;
            $userGroup->save();
        } catch (\Exception $e) {
            Log::error("Error creating group. ".$e);
            return $this->responseError("There was an error creating the group", 500);
        }

        return $this->responseSuccess($group, 201);
    }

    public function update(int $id, Request $request) {

        $user = Auth::user();

        $group = Group::where('id', $id)->first();

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $userBelong = Group::where('id', $id)->whereHas('members', function(Builder $query) use($user) {
            $query->where('user_id', $user->id);
        })->get();

        if(count($userBelong) > 0) {
            if(!$user->tokenCan('group:mine:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } else {
            if(!$user->tokenCan('group:others:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
        }

        $validator = $this->validateGroup($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $group->name = $request->get('name');

        if($request->get('description')) {
            $group->description = $request->get('description');
        };

        $group->grade = $request->get('grade');
        $group->level = $request->get('level');
        $group->stage_id = $request->get('stage_id');

        try {
            $group->update();
        } catch (\Exception $e) {
            Log::error("Error updating group ".$id." ".$e);
            return $this->responseError("There was an error updating the group", 500);
        }

        return $this->responseSuccess($group);
    }

    public function addMembers(int $groupId, Request $request) {

        $user = Auth::user();

        $userBelong = Group::where('id', $groupId)->whereHas('members', function(Builder $query) use($user) {
            $query->where('user_id', $user->id);
        })->get();

        if(count($userBelong) > 0) {
            if(!$user->tokenCan('group:members:add')) {
                return $this->responseError($this->notPermissions, 403);
            }
        } else {
            if(!$user->tokenCan('group:others:members:add')) {
                return $this->responseError($this->notPermissions, 403);
            }
        }

        $users = $request->input('users');
        foreach ($users as $user) {
            UserGroup::where('user_id', $user)
                ->where('group_id', $groupId)
                ->delete();
            $users_groups[] = [
                'user_id' => $user,
                'group_id' => $groupId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        try {
            UserGroup::insert($users_groups);
            return $this->responseSuccess([$users_groups], 200);
        } catch(\Exception $e) {
            Log::error($e);
            return $this->responseError('There was a problem adding the users', 500);
        }
    }
}
