<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request){
        return response()->json(['data'=>$request->user()->goals()->orderBy('completed')->latest('id')->get()]);
    }
    public function store(Request $request){
        $data=$request->validate([
            'title'=>'required|string|max:150',
            'description'=>'nullable|string|max:1000',
            'category'=>'nullable|string|max:50',
            'target_date'=>'nullable|date',
        ]);
        $data['category']=$data['category']??'Wellbeing';
        $goal=$request->user()->goals()->create($data);
        return response()->json(['success'=>true,'message'=>'Goal added.','data'=>$goal],201);
    }
    public function update(Request $request,Goal $goal){
        abort_unless($goal->user_id===$request->user()->id,404);
        $data=$request->validate([
            'title'=>'sometimes|required|string|max:150',
            'description'=>'nullable|string|max:1000',
            'category'=>'nullable|string|max:50',
            'target_date'=>'nullable|date',
            'completed'=>'sometimes|boolean',
        ]);
        if(array_key_exists('completed',$data)) $data['completed_at']=$data['completed']?now():null;
        $goal->update($data);
        return response()->json(['success'=>true,'data'=>$goal->fresh()]);
    }
    public function destroy(Request $request,Goal $goal){
        abort_unless($goal->user_id===$request->user()->id,404);
        $goal->delete();
        return response()->json(['success'=>true]);
    }
}
