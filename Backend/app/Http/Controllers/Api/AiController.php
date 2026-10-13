<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\AiMessage;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function history(Request $request){return response()->json(['data'=>$request->user()->chatMessages()->oldest()->get()]);}
    public function chat(Request $request){
        $data=$request->validate(['message'=>'required|string|max:3000']);
        $text=$data['message']; $lower=strtolower($text);
        $emergency=collect(['kill myself','suicide','self harm','hurt myself','end my life','better off dead'])->contains(fn($x)=>str_contains($lower,$x));
        if($emergency){$reply='I am really sorry you are dealing with this. I cannot provide emergency care. Please move to a safe place, stay with someone you trust, and contact a local emergency service or qualified mental health professional now. If you can, tell someone you trust exactly what you are experiencing.';}
        elseif(str_contains($lower,'anxious')||str_contains($lower,'anxiety')||str_contains($lower,'panic')){$reply='That sounds difficult. Try slowing your breathing for a minute: breathe in gently, pause briefly, and breathe out slowly. If anxiety keeps interfering with daily life, consider speaking with a qualified professional.';}
        elseif(str_contains($lower,'sad')||str_contains($lower,'depress')||str_contains($lower,'down')){$reply='Thank you for sharing that. You do not have to handle everything alone. Consider talking to someone you trust and taking one small, manageable step today. If low mood persists or worsens, a qualified professional can help.';}
        elseif(str_contains($lower,'sleep')){$reply='A consistent sleep and wake time, reducing stimulating activities before bed, and a calm bedtime routine can help. If sleep problems continue, consider discussing them with a healthcare professional.';}
        else{$reply='Thank you for sharing that with me. I am here to listen and provide general wellbeing information. Would you like to tell me more about what has been on your mind?';}
        $request->user()->chatMessages()->createMany([['sender'=>'user','message'=>$text],['sender'=>'ai','message'=>$reply]]);
        return response()->json(['success'=>true,'data'=>['reply'=>$reply,'emergency'=>$emergency],'disclaimer'=>'MindEase AI provides general support and information, not a diagnosis or emergency service.']);
    }
}
