<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\Validators\TaskValidator;
use App\UserGroup;
use App\TaskResult;
use App\TaskComment;
use Illuminate\Database\Eloquent\Builder;

class TaskController extends Controller
{
    use TaskValidator;

    private String $validatorMessage = "Name, title, group_id and status_id are required";
    private String $notFoundMessage = "The requested task does not exist";
    private String $notPermissions = "This user does not have the permissions to perform the requested action.";

    public function index(Request $request)
    {
        $user = Auth::user();
        $onlyMine = $request->get('onlyMine');

        if($onlyMine) {
            if(!$user->tokenCan('task:mine:list')) {
                return $this->responseError($this->notPermissions, 403);
            }
            return Task::with(['group', 'status'])
                ->whereHas('group.members', function(Builder $query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('id', 'desc')
                ->paginate();    
        }

        if(!$user->tokenCan('task:others:list')) {
            return $this->responseError($this->notPermissions, 403);
        }

        return Task::with(['group', 'status'])
            ->orderBy('id', 'desc')
            ->paginate();
    }

    public function show(Request $request, int $id) {

        $onlyMine = $request->get('onlyMine');

        /* if($onlyMine) {
            if(!$user->tokenCan('task:mine:view')) {
                return $this->responseError($this->notPermissions, 403);
            }
            $task = Task::with(['group', 'status'])
                ->whereHas('group.members', function(Builder $query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();    

            return $this->responseSuccess($task);
        } */

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

        $user = Auth::user();

        if(!$user->tokenCan('task:create')) {
            return $this->responseError($this->notPermissions, 403);
        }

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
        $task->created_by = $user->id;

        try {
            $task->save();
            $groupUsers = UserGroup::where('group_id', $task->group_id)->get();
            $taskResults = [];
            foreach ($groupUsers as $groupUser) {
                $taskResult = [];
                $taskResult['task_id'] = $task->id;
                $taskResult['user_id'] = $groupUser->user_id;
                $taskResults[] = $taskResult;
            }
            TaskResult::insert($taskResults);
        } catch (\Exception $e) {
            Log::error("Error creating task. ".$e);
            return $this->responseError("There was an error creating the task", 500);
        }

        return $this->responseSuccess($task, 201);
    }

    public function update(int $id, Request $request) {

        $user = Auth::user();
        $task = Task::where('id', $id)->first();
        $mineTask = Task::where('id', $id)->where('created_by', $user->id)->first();
        $onlyMine = $request->get('onlyMine');

        if($onlyMine) {
            if(!$user->tokenCan('task:mine:edit')) {
                return $this->responseError($this->notPermissions, 403);
            }
            if($task && !$mineTask) {
                return $this->responseError($this->notPermissions, 403);
            }
            $task = $mineTask;
        }
        else {
            if(!$user->tokenCan('task:others:list')) {
                return $this->responseError($this->notPermissions, 403);
            }
        }

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

    public function myStudentsTasks() {
        $user = Auth::user();
        $tasks = Task::where('created_by', $user->id)->pluck('id')->toArray();
        $taskResults = TaskResult::whereIn('task_id', $tasks)->get();
        return $this->responseSuccess($taskResults);
    }

    public function getComments(int $taskId) {
        $comments = TaskComment::where('task_id', $taskId)->get();
        return $this->responseSuccess($comments);
    }

    public function saveComment(Request $request, int $taskId) {
        $user = Auth::user();

        $comment = new TaskComment;
        $comment->user_id = $user->id;
        $comment->task_id = $taskId;
        $comment->comment = $request->get('comment');

        try {
            $comment->save();
        } catch(\Exception $e) {
            Log::error("Error creating comment ".$taskId." ".$e);
            return $this->responseError("There was an error creating the comment", 500);
        }

        return $this->responseSuccess($comment, 201);
    }

    public function editComment(Request $request, int $id, int $commentId) {
        $user = Auth::user();
        try {
            $comment = TaskComment::where('id', $commentId)
                ->where('task_id', $id)
                ->where('user_id', $user->id)
                ->first();
            $comment->comment = $request->get('comment');
            $comment->save();
        } catch(\Exception $e) {
            Log::error("Error editing comment ".$id." ".$e);
            return $this->responseError("There was an error editing the comment", 500);
        }

        return $this->responseSuccess($comment, 200);
    }

    public function deleteComment(int $id, int $commentId) {
        try {
            TaskComment::where('id', $commentId)
            ->where('task_id', $id)
            ->delete();
        } catch(\Exception $e) {
            Log::error("Error deleting task comment ".$commentId." ".$e);
            return $this->responseError("There was an error deleting the comment", 500);
        }

        return $this->responseSuccess([]);
    }
}
