<?php

namespace App\Http\Controllers;

use App\Group;
use App\Traits\Validators\GroupValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    use GroupValidator;

    private String $validatorMessage = "Name, grade, level and stage_id are required.";
    private String $notFoundMessage = "The requested group does not exist";

    public function index()
    {
        return Group::with('stage')
            ->orderBy('id', 'desc')
            ->paginate();
    }

    public function show(int $id) {

        $group = Group::where('id', $id)
            ->with('stage')
            ->first();

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        return $this->responseSuccess($group);
    }

    public function showMembers(int $id) {

        $members = Group::find($id)->members()->get();

        return $this->responseSuccess($members);

    }

    public function create(Request $request)
    {
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
        } catch (\Exception $e) {
            Log::error("Error creating group. ".$e);
            return $this->responseError("There was an error creating the group", 500);
        }

        return $this->responseSuccess($group, 201);
    }

    public function update(int $id, Request $request) {

        $group = Group::where('id', $id)->first();

        if(!$group) {
            return $this->responseError($this->notFoundMessage, 404);
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
}
