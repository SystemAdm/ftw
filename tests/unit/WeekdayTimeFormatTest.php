<?php

use App\Models\Weekday;

it('formats start_time and end_time as HH:MM on access', function (): void {
    $weekday = new Weekday([
        'weekday' => 1,
        'start_time' => '08:00:00',
        'end_time' => '17:30:59',
        'active' => true,
    ]);

    expect($weekday->start_time)->toBe('08:00');
    expect($weekday->end_time)->toBe('17:30');
});

it('keeps HH:MM input intact', function (): void {
    $weekday = new Weekday([
        'weekday' => 2,
        'start_time' => '09:15',
        'end_time' => '18:45',
        'active' => false,
    ]);

    expect($weekday->start_time)->toBe('09:15');
    expect($weekday->end_time)->toBe('18:45');
});
