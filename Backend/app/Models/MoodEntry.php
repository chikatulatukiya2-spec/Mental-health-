<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MoodEntry extends Model
{
    protected $fillable = ['user_id','mood','emoji','note','recorded_at'];
    protected $casts = ['recorded_at'=>'date'];
    public function user(){return $this->belongsTo(User::class);}
}
