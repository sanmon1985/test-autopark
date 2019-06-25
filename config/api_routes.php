<?php

return [
    'GET /api/drivers' => 'driver/index',
    'GET /api/travel_times' => 'driver/travel-time',
    'GET /api/travel_time/<id:\\d+>' => 'driver/travel-time',
];