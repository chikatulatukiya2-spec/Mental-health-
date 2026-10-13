<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function moods() { return $this->hasMany(MoodEntry::class); }
    public function journals() { return $this->hasMany(JournalEntry::class); }
    public function screenings() { return $this->hasMany(Screening::class); }
    public function goals() { return $this->hasMany(Goal::class); }
    public function chatMessages() { return $this->hasMany(AiMessage::class); }
}
