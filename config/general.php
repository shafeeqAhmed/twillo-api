<?php

return [

    'twilio_sid' => env('TWILIO_SID'),
    'twilio_token' => env('TWILIO_TOKEN'),
    'front_app_url' => env('FRONT_END_APP', 'http://localhost:3000'),
    'web_hook' => env('WEB_HOOK', 'https://colony.rocks/twillo-api/api/twilio_webhook'),

];
