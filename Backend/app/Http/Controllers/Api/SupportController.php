<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SupportResource;
use Illuminate\Http\Request;
class SupportController extends Controller
{
    public function index(Request $request){$q=SupportResource::where('active',true);if($request->boolean('emergency'))$q->where('is_emergency',true);return response()->json(['data'=>$q->orderByDesc('is_emergency')->get()]);}
}
