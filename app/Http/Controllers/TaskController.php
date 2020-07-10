<?php

namespace App\Http\Controllers;

use App\Task;
use App\Traits\Validators\TaskValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use TaskValidator;

    private String $validatorMessage = "Name, title, group_id and status_id are required";
    private String $notFoundMessage = "The requested task does not exist";

    public function index()
    {
        return Task::with(['group', 'status'])
            ->paginate();
    }

    public function show(int $id) {

        $task = Task::where('id', $id)
            ->with(['group', 'status'])
            ->first();

        if(!$task) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        return $this->responseSuccess($task);
    }

    public function create(Request $request)
    {
        $validator = $this->validateTask($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $task = new Task;
        $task->name = $request->get('name');
        $task->title = $request->get('title');
        $task->description = $request->get('description');
        $task->group_id = $request->get('group_id');
        $task->status_id = $request->get('status_id');
        $task->completed_at = $request->get('completed_at');

        try {
            $task->save();
        } catch (\Exception $e) {
            Log::error("Error creating task. ".$e);
            return $this->responseError("There was an error creating the task", 500);
        }

        return $this->responseSuccess($task, 201);
    }

    public function update(int $id, Request $request) {

        $task = Task::where('id', $id)->first();

        if(!$task) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateTask($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $task->name = $request->get('name');
        $task->title = $request->get('title');

        if($request->get('description')) {
            $task->description = $request->get('description');
        }

        $task->group_id = $request->get('group_id');
        $task->status_id = $request->get('status_id');

        if($request->get('completed_at')) {
            $task->completed_at = $request->get('completed_at');
        }

        try {
            $task->update();
        } catch (\Exception $e) {
            Log::error("Error updating task ".$id." ".$e);
            return $this->responseError("There was an error updating the task", 500);
        }

        return $this->responseSuccess($task);
    }
}
