<?php

namespace Tests\Unit;

use App\Services\DashboardService;
use App\Services\PersonnelService;
use PHPUnit\Framework\TestCase;

class PersonnelWorkflowStatusTest extends TestCase
{
    public function test_dashboard_counts_follow_the_ics_stage(): void
    {
        $cases = [
            ['within', 'inspection', 'new'],
            ['renewed', 'inspection', 'new'],
            ['new', 'inspection', 'new'],
            ['within', 'under', 'pending'],
            ['renewed', 'under', 'pending'],
            ['renewed', 'active', 'renewed'],
            ['renewed', 'ready', 'renewed'],
            ['expired', 'expired', 'expired'],
        ];
        $rows = [];
        foreach ($cases as [$renewal, $ics, $expected]) {
            $actual = PersonnelService::workflow_status($renewal, $ics);
            $this->assertSame($expected, $actual);
            $rows[] = ['approvedStatus' => $actual];
        }
        $this->assertSame([
            'totalNew' => 3, 'totalRenewed' => 2, 'withinRenewal' => 0,
            'expired' => 1, 'pending' => 2,
        ], DashboardService::dashboard_metrics($rows));
    }
}
