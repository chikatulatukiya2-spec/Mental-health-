<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProgressController extends Controller
{
    public function index(Request $request){
        $user=$request->user();
        $since=Carbon::now()->subDays(6)->startOfDay();
        $moods=$user->moods()->whereDate('recorded_at','>=',$since->toDateString())->orderBy('recorded_at')->get();
        $moodMap=['Happy'=>5,'Calm'=>4,'Okay'=>3,'Sad'=>2,'Anxious'=>2,'Overwhelmed'=>1];
        $series=$moods->groupBy(fn($m)=>$m->recorded_at->toDateString())->map(fn($day)=>round($day->avg(fn($m)=>$moodMap[$m->mood]??3),1))->values();
        $latest=$user->screenings()->latest('completed_at')->first();
        $score=$series->count()?round(($series->avg()/5)*100):null;
        return response()->json(['data'=>[
            'period'=>'Last 7 days','mood_entries'=>$moods,'mood_series'=>$series,'wellbeing_score'=>$score,
            'latest_screening'=>$latest,
            'goals_total'=>$user->goals()->count(),'goals_completed'=>$user->goals()->where('completed',true)->count(),
        ]]);
    }
}
