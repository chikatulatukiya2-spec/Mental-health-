<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('support_resources',function(Blueprint $t){$t->id();$t->string('title');$t->text('description')->nullable();$t->string('phone')->nullable();$t->string('url')->nullable();$t->string('type')->default('general');$t->boolean('is_emergency')->default(false);$t->boolean('active')->default(true);$t->timestamps();});}public function down():void{Schema::dropIfExists('support_resources');}};
