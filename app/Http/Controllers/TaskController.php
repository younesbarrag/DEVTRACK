<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $task = Task::all();
        return view("task.index", compact("task"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("task.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Task::create([
            'project_id'  => $request->project_id,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => 'todo',
            'priority'    => $request->priority,
            'assigned_to' => $request->assigned_to,
            'deadline'    => $request->deadline,
        ]);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $task = Task::find($id);
        return view('task.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        return view('task.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        Task::find($id)->update([
            'project_id'=> $request->project_id,
            'title'=> $request->title,
            'description'=> $request->description,
            'status'=> '',
            'priority'=> $request->priority,
            'assigned_to'=> $request->assigned_to,
            'deadline'=> $request->deadline
            ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        Task::find($id)->delete();
        return back();
    }
}
