<?php

namespace Tests\Unit;

use App\Support\ElapsedTime;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ElapsedTimeTest extends TestCase
{
    public function test_truncates_partial_minutes_in_both_directions(): void
    {
        $start = Carbon::parse('2026-09-07 10:00:00');
        $end = Carbon::parse('2026-09-07 10:01:59');

        $this->assertSame(1, ElapsedTime::wholeMinutes($start, $end));
        $this->assertSame(1, ElapsedTime::wholeMinutes($end, $start));
    }

    public function test_truncates_partial_hours_in_both_directions(): void
    {
        $start = Carbon::parse('2026-09-07 10:00:00');
        $end = Carbon::parse('2026-09-07 11:59:59');

        $this->assertSame(1, ElapsedTime::wholeHours($start, $end));
        $this->assertSame(1, ElapsedTime::wholeHours($end, $start));
    }
}
