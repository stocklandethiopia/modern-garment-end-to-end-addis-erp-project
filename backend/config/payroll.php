<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ethiopian payroll activation gates
    |--------------------------------------------------------------------------
    |
    | These remain disabled in the canonical baseline. Enabling payroll requires
    | a separately reviewed change after authoritative employee/contract/
    | assignment and statutory classification records, official legal sources,
    | independent Ethiopian tax/pension advice, full calculation regression,
    | and release acceptance are present.
    |
    */
    'ethiopia' => [
        'employee_master_release_ready' => false,
        'statutory_activation_enabled' => false,
    ],
];
