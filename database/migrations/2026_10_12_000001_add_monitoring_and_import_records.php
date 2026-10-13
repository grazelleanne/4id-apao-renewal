<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('audit_logs',function(Blueprint $t){$t->string('module',64)->nullable()->index();});
  Schema::table('personnel',function(Blueprint $t){$t->string('atp_serial_number',100)->nullable()->unique();});
  Schema::create('firearms',function(Blueprint $t){
   $t->id();$t->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete();
   $t->string('atp_serial_number',100)->unique();$t->string('serial_number',100)->unique();
   $t->string('nomenclature');$t->string('type',100);$t->date('expiration_date')->nullable();$t->timestamps();
  });
  Schema::create('ammunition',function(Blueprint $t){
   $t->id();$t->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete();
   $t->string('lot_number',100);$t->string('caliber',100);$t->unsignedInteger('quantity');$t->timestamps();
   $t->unique(['personnel_id','lot_number','caliber']);
  });
  Schema::create('notification_logs',function(Blueprint $t){
   $t->id();$t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
   $t->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete();
   $t->foreignId('firearm_id')->nullable()->constrained('firearms')->nullOnDelete();
   $t->string('record_key',100);$t->string('recipient');$t->string('subject');$t->text('message');
   $t->date('expiration_date')->nullable();$t->unsignedTinyInteger('days_before')->nullable();$t->string('status',16);
   $t->timestamp('date_sent')->nullable();$t->text('error')->nullable();$t->timestamps();
   $t->unique(['record_key','expiration_date','days_before'],'renewal_milestone_unique');
  });
  Schema::create('data_import_logs',function(Blueprint $t){
   $t->id();$t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
   $t->string('filename');$t->string('module',32);$t->unsignedInteger('total_records');
   $t->unsignedInteger('imported_records');$t->unsignedInteger('failed_records');
   $t->unsignedInteger('duplicate_records');$t->json('errors')->nullable();$t->timestamp('date_uploaded');
  });
 }
 public function down(): void {
  Schema::dropIfExists('data_import_logs');Schema::dropIfExists('notification_logs');
  Schema::dropIfExists('ammunition');Schema::dropIfExists('firearms');
  Schema::table('personnel',fn(Blueprint $t)=>$t->dropColumn('atp_serial_number'));
  Schema::table('audit_logs',fn(Blueprint $t)=>$t->dropColumn('module'));
 }
};
