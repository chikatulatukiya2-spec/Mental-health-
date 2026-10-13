<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('screenings',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('type');$t->json('answers');$t->unsignedTinyInteger('score');$t->string('result_label');$t->timestamp('completed_at');$t->timestamps();});}public function down():void{Schema::dropIfExists('screenings');}};
