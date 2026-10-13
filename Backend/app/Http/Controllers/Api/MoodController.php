<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\MoodEntry;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    public function index(Request $request){
        $limit=min((int)$request->query('limit',30),100);
        return response()->json(['data'=>$request->user()->moods()->latest('recorded_at')->latest('id')->limit($limit)->get()]);
    }
    public function store(Request $request){
        $data=$request->validate([
            'mood'=>'required|string|in:Happy,Calm,Okay,Sad,Anxious,Overwhelmed',
            'emoji'=>'nullable|string|max:16','note'=>'nullable|string|max:2000','recorded_at'=>'nullable|date',
        ]);
        $data['recorded_at']=$data['recorded_at'] ?? now()->toDateString();
        $entry=$request->user()->moods()->create($data);
        return response()->json(['success'=>true,'message'=>'Mood saved.','data'=>$entry],201);
    }
    public function show(Request $request,MoodEntry $mood){
        abort_unless($mood->user_id===$request->user()->id,404); return response()->json(['data'=>$mood]);
    }
    public function destroy(Request $request,MoodEntry $mood){
        abort_unless($mood->user_id===$request->user()->id,404); $mood->delete(); return response()->json(['success'=>true]);
    }
}
