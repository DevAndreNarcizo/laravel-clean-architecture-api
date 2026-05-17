<?php

return [
    'tasks_per_project' => env('TASKS_PER_PROJECT', 100),
    'rate_limit_api' => env('RATE_LIMIT_API', 120),
    'rate_limit_login' => env('RATE_LIMIT_LOGIN', 5),
];
