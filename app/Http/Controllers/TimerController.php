<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Timer;
use App\Models\Fish;
use App\Models\Task;

class TimerController extends Controller
{
    function fishStrike() {
        $fishCount = Fish::count();

        return rand(1, $fishCount);
    }

    public function timerFinished(Request $request) {
        $fish_id = $this->fishStrike();
        $fish = Fish::where('id', $fish_id)->get()->first();

        $timer = Timer::create([
            "minutes" => $request->minutes,
            "user_id" => $request->userId,
            "fish_id" => $fish->id,
        ]);

        return response()->json($fish, 200);
    }

    public function getTotalTimerByUserId(Request $request) {
        $completedTaskCount = Task::where('user_id', $request->userId)->where('isFinished', 1)->count();
        $pendingTaskCount = Task::where('user_id', $request->userId)->where('isFinished', 0)->count();
        $totalWeeklyTime = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->selectRaw('SUM(minutes) as total_weekly_time')->get();
        $totalMonthlyTime = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->selectRaw('SUM(minutes) as total_monthly_time')->get();
        $totalYearlyTime = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->selectRaw('SUM(minutes) as total_yearly_time')->get();
        $timersByWeek = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->selectRaw('SUM(minutes) as daily_focus_time, DAYNAME(created_at) as dayname')->groupBy('dayname')->get();
        $timersByMonth = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->selectRaw('SUM(minutes) as monthly_focus_time, DAYOFMONTH(created_at) as date')->groupBy('date')->get();
        $timersByYear = Timer::where('user_id', $request->userId)->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->selectRaw('SUM(minutes) as yearly_focus_time, MONTH(created_at) as month')->groupBy('month')->get();
        $recentCatch = Timer::where('user_id', $request->userId)->join('fish', 'timers.fish_id', '=', 'fish.id')->orderBy('timers.created_at', 'DESC')->limit(3)->get();
        $taskUrgencyList = Task::where('user_id', $request->userId)->selectRaw('urgency, COUNT(urgency) as urgencyCount')->groupBy('urgency')->get();
        $taskTotal = Task::where('user_id', $request->userId)->count();
        $daysInMonth = Timer::selectRaw('DAY(LAST_DAY(created_at)) as days_in_month')->first();

        if ($timersByWeek->count() == 0) {
            $timersByWeek = ['Empty'];
        }
        if ($timersByMonth->count() == 0) {
            $timersByMonth = ['Empty'];
        }
        if ($timersByYear->count() == 0) {
            $timersByYear = ['Empty'];
        }
        if ($recentCatch->count() == 0) {
            $recentCatch = ['Empty'];
        }
        if ($taskTotal == 0) {
            $taskUrgencyList = ['Empty'];
        }
        if ($totalWeeklyTime->count() == 0) {
            $totalWeeklyTime = 0;
        }
        if ($totalMonthlyTime->count() == 0) {
            $totalMonthlyTime = 0;
        }
        if ($totalYearlyTime->count() == 0) {
            $totalYearlyTime = 0;
        }

        // return response()->json(['timersByWeek' => $timersByWeek, 'timersByMonth'], 200, $headers);z
        return response()->json(['timers_by_week' => $timersByWeek, 'timers_by_month' => $timersByMonth, 'timers_by_year' => $timersByYear, 'recent_catch' => $recentCatch, 'task_urgency_list' => $taskUrgencyList, 'total_task' => $taskTotal, 'days_in_month' => $daysInMonth, 'completed_task_count' => $completedTaskCount, 'pending_task_count' => $pendingTaskCount, 'total_weekly_time' => $totalWeeklyTime, 'total_monthly_time' => $totalMonthlyTime, 'total_yearly_time' => $totalYearlyTime], 200);
    }
}
