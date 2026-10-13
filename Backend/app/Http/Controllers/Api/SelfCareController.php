<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SelfCareResource;
use Illuminate\Http\Request;
class SelfCareController extends Controller
{
    public function index(Request $request){$q=SelfCareResource::where('active',true);if($request->filled('type'))$q->where('type',$request->type);return response()->json(['data'=>$q->get()]);}
    public function show(SelfCareResource $selfCare){abort_unless($selfCare->active,404);return response()->json(['data'=>$selfCare]);}
}
