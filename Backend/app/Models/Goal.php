<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = ['user_id','title','description','category','target_date','completed','completed_at'];
    protected $casts = ['completed'=>'boolean','target_date'=>'date:Y-m-d','completed_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
}
