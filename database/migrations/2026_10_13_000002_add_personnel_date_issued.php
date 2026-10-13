<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::table('personnel',fn(Blueprint $table)=>$table->date('date_issued')->nullable());}
 public function down(): void {Schema::table('personnel',fn(Blueprint $table)=>$table->dropColumn('date_issued'));}
};
