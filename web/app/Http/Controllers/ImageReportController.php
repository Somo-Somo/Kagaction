<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Condition;
use App\Models\Feeling;
use App\Models\ImageReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ImageReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Carbon::setWeekStartsAt(Carbon::SUNDAY); // 週の最初を日曜日に設定
        Carbon::setWeekEndsAt(Carbon::SATURDAY); // 週の最後を土曜日に設定
        $today = Carbon::today();
        $eightDays = Carbon::today()->subDay(8);
        $thisWeekConditions = Condition::whereDate('date', '>=', $eightDays)
            ->whereDate('date', '<', $today)
            ->get();
        $userConditions = $thisWeekConditions->groupBy('user_uuid')->toArray();
        $thisWeekFeelings = Feeling::whereDate('date', '>=', $eightDays)
            ->whereDate('date', '<', $today)
            ->get();
        $userFeelings = $thisWeekFeelings->groupBy('user_uuid')->toArray();

        $weekStartDay = $today->startOfWeek()->toDateString();
        $weekEndDay = $today->endOfWeek()->toDateString();

        $data = [
            'conditions' => $userConditions,
            'feelings' => $userFeelings,
            'period' => [
                'start' => $weekStartDay,
                'end' => $weekEndDay,
            ]
        ];
        return response()->json($data, Response::HTTP_OK);
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
