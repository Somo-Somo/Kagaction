<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Condition;
use App\Models\Feeling;
use App\Models\ImageReport;
use Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ImageReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  int  $id
     * @param string $uuid
     * @return \Illuminate\Http\Response
     */
    public function index(int $id, string $uuid)
    {
        $condition = Condition::where('user_uuid', $uuid)->get();
        Log::debug((array)$condition);
        $data = ['id' => $id];
        // [
        //     'user' => [
        //         'id' => $id,
        //     ],
        //     'condition' => [
        //         'total' => 'aa',
        //         'type' => [],
        //     ],
        //     'feeling' => [
        //         'total' => 'aa',
        //         'type' => [],
        //     ]
        // ];
        return response()->json($data, Response::HTTP_OK);
        // return view('index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
     * @param  \App\Models\ImageReport  $imageReport
     * @return \Illuminate\Http\Response
     */
    public function show(ImageReport $imageReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ImageReport  $imageReport
     * @return \Illuminate\Http\Response
     */
    public function edit(ImageReport $imageReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ImageReport  $imageReport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImageReport $imageReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageReport  $imageReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageReport $imageReport)
    {
        //
    }
}
