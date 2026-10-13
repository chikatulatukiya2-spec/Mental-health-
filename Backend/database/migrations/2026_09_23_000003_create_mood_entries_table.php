<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('mood_entries',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('mood');$t->string('emoji',16)->nullable();$t->text('note')->nullable();$t->date('recorded_at');$t->timestamps();$t->index(['user_id','recorded_at']);});}public function down():void{Schema::dropIfExists('mood_entries');}};
