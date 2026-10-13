<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Screening extends Model
{
    protected $fillable = ['user_id','type','answers','score','result_label','completed_at'];
    protected $casts = ['answers'=>'array','completed_at'=>'datetime'];
    public function user(){return $this->belongsTo(User::class);}
}
