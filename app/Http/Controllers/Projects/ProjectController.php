<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;

        $projects = Project::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'    => $projects
        ], 200);
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();

        $project = Project::create($data);

        return response()->json([
            'success'   => true,
            'data'      => $project
        ], 201);
    }
}
