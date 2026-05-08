<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Resources\ProjectResource; 

class ProjectApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $projects=Project::all();
        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $project=Project::create([
        'title' =>$request->title,
        'description'=>$request->decription,
        'deadline'=>$request->deadline,
        'user_id'=>1,

       ]);

   return response()->json([
            'message' => 'creation done',
            'data' => new ProjectResource($project)
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project=Project::with('task')->findOrFail($id);
        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());

        return response()->json([
            'message'=>'done',
            'data'=> new ProjectResource($project)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project=Project::findOrFail($id);
        $project->delete();

      return response()->json(['message' => 'mcha']);
    
            }
}
