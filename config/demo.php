<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo data scale
    |--------------------------------------------------------------------------
    |
    | When running the DemoDataSeeder, this value (or DEMO_RECORDS env) sets
    | the target scale. Resulting record counts are derived from this (e.g.
    | users ~25%, pages ~80%, posts ~80%, media ~20% of scale). Clamped
    | between 500 and 5000 for safety.
    |
    */

    'records' => (int) env('DEMO_RECORDS', 1500),

];
