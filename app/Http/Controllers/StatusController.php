<?php

namespace App\Http\Controllers;

use App\Status;
use App\Traits\Validators\StatusValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatusController extends Controller
{
    use StatusValidator;

    private String $validatorMessage = "Name is required";
    private String $notFoundMessage = "The requested status does not exist";

    public function index()
    {
        return Status::paginate();
    }

    public function show(int $id) {
        $status = Status::where('id', $id)->first();
        if(!$status) {
            return $this->responseError($this->notFoundMessage, 404);
        }
        return $this->responseSuccess($status);
    }

    public function create(Request $request)
    {
        $validator = $this->validateStatus($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $status = new Status;
        $status->name = $request->get('name');
        $status->description = $request->get('description');

        try {
            $status->save();
        } catch (\Exception $e) {
            Log::error("Error creating status. ".$e);
            return $this->responseError("There was an error creating the status", 500);
        }

        return $this->responseSuccess($status, 201);
    }

    public function update(int $id, Request $request) {

        $status = Status::where('id', $id)->first();

        if(!$status) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateStatus($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $status->name = $request->get('name');

        if($request->get('description')) {
            $status->description = $request->get('description');
        }

        try {
            $status->update();
        } catch (\Exception $e) {
            Log::error("Error updating status ".$id." ".$e);
            return $this->responseError("There was an error updating the status", 500);
        }

        return $this->responseSuccess($status);
    }
}
