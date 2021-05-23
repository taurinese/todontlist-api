<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Auth::user()->tasks()->get();
        return response()->json([
            'tasks' => $tasks
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /* dd($request->user()->id); */
        $request->validate([
            'done' => 'required|boolean',
            'body' => 'required|string'
        ]);
        $task = Task::create([
            'done' => $request->done,
            'body' => $request->body,
            'user_id' => $request->user()->id
        ]);
        $task->save();
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::find($id);
        if(!$task){
            return response()->json([
                'errors' =>'Not found'
            ], 404);
        }
        if($task->user->id !== Auth::id()){
            return response()->json([
                'errors' => 'Forbidden'
            ], 403);
        }
        return response()->json([
            $task
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string'
        ]);
        $task = Task::find($id);
        if(!$task){
            return response()->json([
                'errors' =>'Not found'
            ], 404);
        }
        if($task->user->id !== Auth::id()){
            return response()->json([
                'errors' => 'Forbidden'
            ], 403);
        }
        $task->body = $request->content;
        $task->save();
        return response()->json([
            $task
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::find($id);
        if(!$task){
            return response()->json([
                'errors' =>'Not found'
            ], 404);
        }
        if($task->user->id !== Auth::id()){
            return response()->json([
                'errors' => 'Forbidden'
            ], 403);
        }
        $task->delete();
        return response()->json([
            $task
        ], 200);
    }
}
