<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('self_care_resources',function(Blueprint $t){$t->id();$t->string('title');$t->string('description')->nullable();$t->string('type')->nullable();$t->longText('content')->nullable();$t->boolean('active')->default(true);$t->timestamps();});}public function down():void{Schema::dropIfExists('self_care_resources');}};
