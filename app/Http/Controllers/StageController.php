<?php

namespace App\Http\Controllers;

use App\Stage;
use App\Traits\Validators\StageValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StageController extends Controller
{
    use StageValidator;

    private String $validatorMessage = "Name is required";
    private String $notFoundMessage = "The requested stage does not exist";

    public function index()
    {
        return Stage::paginate();
    }

    public function show(int $id) {
        $stage = Stage::where('id', $id)->first();
        if(!$stage) {
            return $this->responseError($this->notFoundMessage, 404);
        }
        return $this->responseSuccess($stage);
    }

    public function create(Request $request)
    {
        $validator = $this->validateStage($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $stage = new Stage;
        $stage->name = $request->get('name');
        $stage->description = $request->get('description');

        try {
            $stage->save();
        } catch (\Exception $e) {
            Log::error("Error creating stage. ".$e);
            return $this->responseError("There was an error creating the stage", 500);
        }

        return $this->responseSuccess($stage, 201);
    }

    public function update(int $id, Request $request) {

        $stage = Stage::where('id', $id)->first();

        if(!$stage) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateStage($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $stage->name = $request->get('name');

        if($request->get('description')) {
            $stage->description = $request->get('description');
        }

        try {
            $stage->update();
        } catch (\Exception $e) {
            Log::error("Error updating stage ".$id." ".$e);
            return $this->responseError("There was an error updating the stage", 500);
        }

        return $this->responseSuccess($stage);
    }
}
