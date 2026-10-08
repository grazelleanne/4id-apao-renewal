<?php
return ['environment'=>[
    'APP_SESSION_LIFETIME'=>env('APP_SESSION_LIFETIME','7200'),
    'BREVO_API_KEY'=>env('BREVO_API_KEY',''),
    'BREVO_SENDER_EMAIL'=>env('BREVO_SENDER_EMAIL',''),
    'BREVO_SENDER_NAME'=>env('BREVO_SENDER_NAME','APAO Renewal System'),
    'BREVO_REPLY_TO_EMAIL'=>env('BREVO_REPLY_TO_EMAIL',''),
    'APP_ENV'=>env('APP_ENV','production'),'RENDER'=>env('RENDER',''),
]];
