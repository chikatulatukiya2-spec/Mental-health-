<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class InfoController extends Controller
{
    private array $content=[
      'Depression'=>['title'=>'What is Depression?','text'=>'Depression is a common but serious mental health condition that can affect how you feel, think, and act. Symptoms can persist and may make everyday activities difficult.','symptoms'=>['Persistent sadness or low mood','Loss of interest in activities','Changes in sleep or appetite','Low energy and fatigue','Difficulty concentrating']],
      'Anxiety'=>['title'=>'What is Anxiety?','text'=>'Anxiety can involve persistent worry, fear, or nervousness. Occasional anxiety is common, but ongoing symptoms can interfere with daily life and wellbeing.','symptoms'=>['Excessive worry','Feeling restless or on edge','Difficulty concentrating','Muscle tension','Sleep difficulties']],
      'Wellbeing'=>['title'=>'Mental Wellbeing','text'=>'Mental wellbeing includes how you manage everyday stress, connect with others, and maintain a sense of purpose. Small, consistent habits can support wellbeing.','symptoms'=>['Healthy sleep routine','Regular movement','Supportive relationships','Time for rest','Healthy coping strategies']],
    ];
    public function index(Request $request){$topic=$request->query('topic');if($topic && isset($this->content[$topic]))return response()->json(['data'=>$this->content[$topic]+['topic'=>$topic]]);return response()->json(['data'=>collect($this->content)->map(fn($v,$k)=>$v+['topic'=>$k])->values()]);}
}
