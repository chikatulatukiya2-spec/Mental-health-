<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('journal_entries',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('title');$t->longText('content');$t->string('mood')->nullable();$t->date('entry_date');$t->timestamps();});}public function down():void{Schema::dropIfExists('journal_entries');}};
