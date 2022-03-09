<?php

namespace App\Http\Controllers;

use App\UseCases\TodaysGoal\UpdateAction;
use App\UseCases\TodaysGoal\DestroyAction;
use Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\Response;

class TodaysGoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  string  $hypothesisUuid
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(string $hypothesisUuid, Request $request, UpdateAction $updateAction)
    {
        $hypothesis = [
            'uuid' => $hypothesisUuid,
            'status' => $request->status,
            'user_email' => $request->user()->email,
        ];

        $updateAction->invoke($hypothesis);

        $json = [
            'message' => '今日の目標を更新しました',
            'error' => '',
        ];

        return response()->json($json, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $hypothesisUuid
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $hypothesisUuid, Request $request, DestroyAction $destroyAction)
    {
        $hypothesis = [
            'uuid' => $hypothesisUuid,
            'user_email' => $request->user()->email,
        ];

        $destroyAction->invoke($hypothesis);

        $json = [
            'message' => '今日の目標を取り消しました',
            'error' => '',
        ];

        return response()->json($json, Response::HTTP_OK);
    }
}
