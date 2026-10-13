<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SupportResource extends Model
{
    protected $fillable = ['title','description','phone','url','type','is_emergency','active'];
    protected $casts = ['is_emergency'=>'boolean','active'=>'boolean'];
}
