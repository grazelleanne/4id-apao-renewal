<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Support\Facades\{DB,Artisan};
final class AuditRetentionTest extends TestCase {
 public function test_cleanup_deletes_only_logs_older_than_90_days(): void {
  config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
  Artisan::call('migrate',['--force'=>true]);$this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-12 12:00:00'));
  $old=DB::table('audit_logs')->insertGetId(['action'=>'test_old','created_at'=>now()->subDays(90)->subSecond()]);
  $boundary=DB::table('audit_logs')->insertGetId(['action'=>'test_boundary','created_at'=>now()->subDays(90)]);
  $recent=DB::table('audit_logs')->insertGetId(['action'=>'test_recent','created_at'=>now()]);
  $this->artisan('apao:prune-audit-logs',['--dry-run'=>true])->expectsOutput('1 audit logs older than 90 days would be deleted.')->assertSuccessful();
  $this->assertSame(3,DB::table('audit_logs')->count());
  $this->artisan('apao:prune-audit-logs')->expectsOutput('1 audit logs older than 90 days deleted.')->assertSuccessful();
  $this->assertDatabaseMissing('audit_logs',['id'=>$old]);$this->assertDatabaseHas('audit_logs',['id'=>$boundary]);$this->assertDatabaseHas('audit_logs',['id'=>$recent]);
  $this->artisan('apao:prune-audit-logs')->expectsOutput('0 audit logs older than 90 days deleted.')->assertSuccessful();$this->travelBack();
 }
}
