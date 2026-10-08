<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['session_version','must_change_password','remember_token'] as $column) {
            if (Schema::hasColumn('users',$column)) continue;
            Schema::table('users',function(Blueprint $table) use($column) {
                match($column) {
                    'session_version'=>$table->unsignedInteger($column)->default(1),
                    'must_change_password'=>$table->boolean($column)->default(false),
                    'remember_token'=>$table->string($column,100)->nullable(),
                };
            });
        }
    }
    public function down(): void {
        // These columns may have existed before Laravel adopted the database.
    }
};
