<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Screening;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public const QUESTIONS = [
        'Little interest or pleasure in doing things?',
        'Feeling down, depressed or hopeless?',
        'Trouble falling or staying asleep, or sleeping too much?',
        'Feeling tired or having little energy?',
        'Poor appetite or overeating?',
        'Feeling bad about yourself — or that you are a failure?',
        'Trouble concentrating on things?',
        'Moving or speaking slowly, or being unusually restless?',
        'Thoughts that you would be better off dead or hurting yourself?',
    ];
    public const ANSWERS=['Not at all','Several days','More than half the days','Nearly every day'];

    public function questions(){return response()->json(['type'=>'phq9','title'=>'Depression Screening','instruction'=>'Answer based on how you have felt during the last 2 weeks.','questions'=>collect(self::QUESTIONS)->map(fn($q,$i)=>['id'=>$i,'text'=>$q])->values(),'answers'=>collect(self::ANSWERS)->map(fn($a,$i)=>['value'=>$i,'label'=>$a])->values(),'disclaimer'=>'This screening is not a diagnosis. If you feel unsafe or have thoughts of self-harm, seek immediate help from a trusted person, local emergency service, or qualified professional.']);}

    public function index(Request $request){return response()->json(['data'=>$request->user()->screenings()->latest('completed_at')->get()]);}

    public function store(Request $request){
        $data=$request->validate(['answers'=>'required|array|size:9','answers.*'=>'required|integer|between:0,3']);
        $score=array_sum($data['answers']);
        $label=match(true){$score<=4=>'Minimal symptoms',$score<=9=>'Mild symptoms',$score<=14=>'Moderate symptoms',$score<=19=>'Moderately severe symptoms',default=>'Severe symptoms'};
        $screening=$request->user()->screenings()->create(['type'=>'PHQ-9','answers'=>$data['answers'],'score'=>$score,'result_label'=>$label,'completed_at'=>now()]);
        return response()->json(['success'=>true,'message'=>'Screening completed.','screening'=>$screening,'score'=>$score,'result_label'=>$label,'self_harm_flag'=>(($data['answers'][8]??0)>0),'disclaimer'=>'A screening score is not a diagnosis. Consider discussing concerns with a qualified mental health professional.'],201);
    }
    public function show(Request $request,Screening $screening){abort_unless($screening->user_id===$request->user()->id,404);return response()->json(['data'=>$screening]);}
}
