<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request){return response()->json(['data'=>$request->user()->journals()->latest('entry_date')->latest('id')->paginate(20)]);}
    public function store(Request $request){
        $data=$request->validate(['title'=>'required|string|max:150','content'=>'required|string|max:10000','mood'=>'nullable|string|max:50','entry_date'=>'nullable|date']);
        $data['entry_date']=$data['entry_date']??now()->toDateString();
        $entry=$request->user()->journals()->create($data);
        return response()->json(['success'=>true,'message'=>'Journal entry saved.','data'=>$entry],201);
    }
    public function show(Request $request,JournalEntry $journal){abort_unless($journal->user_id===$request->user()->id,404);return response()->json(['data'=>$journal]);}
    public function update(Request $request,JournalEntry $journal){
        abort_unless($journal->user_id===$request->user()->id,404);
        $data=$request->validate(['title'=>'sometimes|required|string|max:150','content'=>'sometimes|required|string|max:10000','mood'=>'nullable|string|max:50','entry_date'=>'nullable|date']);
        $journal->update($data); return response()->json(['success'=>true,'data'=>$journal]);
    }
    public function destroy(Request $request,JournalEntry $journal){abort_unless($journal->user_id===$request->user()->id,404);$journal->delete();return response()->json(['success'=>true]);}
}
