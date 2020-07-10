<?php

namespace App\Http\Controllers;

use App\Institute;
use App\Traits\Validators\InstituteValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstituteController extends Controller
{
    use InstituteValidator;

    private String $validatorMessage = "Name, city, province and address are required.";
    private String $notFoundMessage = "The requested institute does not exist";

    public function index()
    {
        return Institute::paginate();
    }

    public function show(int $id) {
        $institute = Institute::where('id', $id)->first();
        if(!$institute) {
            return $this->responseError($this->notFoundMessage, 404);
        }
        return $this->responseSuccess($institute);
    }

    public function create(Request $request)
    {
        $validator = $this->validateInstitute($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $institute = new Institute;
        $institute->name = $request->get('name');
        $institute->city = $request->get('city');
        $institute->province = $request->get('province');
        $institute->address = $request->get('address');

        try {
            $institute->save();
        } catch (\Exception $e) {
            Log::error("Error creating institute. ".$e);
            return $this->responseError("There was an error creating the institute", 500);
        }

        return $this->responseSuccess($institute, 201);
    }

    public function update(int $id, Request $request) {

        $institute = Institute::where('id', $id)->first();

        if(!$institute) {
            return $this->responseError($this->notFoundMessage, 404);
        }

        $validator = $this->validateInstitute($request->all());

        if ($validator->fails()) {
            return $this->responseError($this->validatorMessage);
        }

        $institute->name = $request->get('name');
        $institute->city = $request->get('city');
        $institute->province = $request->get('province');
        $institute->address = $request->get('address');

        try {
            $institute->update();
        } catch (\Exception $e) {
            Log::error("Error updating institute ".$id." ".$e);
            return $this->responseError("There was an error updating the institute", 500);
        }

        return $this->responseSuccess($institute);
    }
}
