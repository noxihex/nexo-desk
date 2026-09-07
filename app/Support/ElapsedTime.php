<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class ElapsedTime
{
    public static function wholeMinutes(DateTimeInterface $from, DateTimeInterface $to): int
    {
        return (int) floor(abs(Carbon::instance($from)->diffInSeconds($to, false)) / 60);
    }

    public static function wholeHours(DateTimeInterface $from, DateTimeInterface $to): int
    {
        return (int) floor(abs(Carbon::instance($from)->diffInSeconds($to, false)) / 3600);
    }
}
