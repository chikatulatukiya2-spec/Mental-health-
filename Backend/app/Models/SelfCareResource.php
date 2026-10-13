<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SelfCareResource extends Model
{
    protected $fillable = ['title','description','type','content','active'];
    protected $casts = ['active'=>'boolean'];
}
