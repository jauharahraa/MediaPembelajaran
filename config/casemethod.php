<?php

return [
    'teacher_invite_code' => env('TEACHER_INVITE_CODE'),
    'max_attempts' => 2, // 1 pengiriman awal + 1 revisi

    'gemini' => [
        'timeout'     => (int) env('GEMINI_TIMEOUT', 30),     // detik per permintaan ke Gemini
        'time_budget' => (int) env('GEMINI_TIME_BUDGET', 70), // detik total pembuatan feedback tiap pengiriman kuis
    ],
];