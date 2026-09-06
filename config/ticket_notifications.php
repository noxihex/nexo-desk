<?php

return [
    'outbound_enabled' => env('TICKET_NOTIFICATIONS_OUTBOUND_ENABLED', false),
    'sla_enabled' => env('TICKET_NOTIFICATIONS_SLA_ENABLED', false),
    'inbound_enabled' => env('TICKET_EMAIL_INBOUND_ENABLED', false),
    'mailgun_signing_key' => env('MAILGUN_WEBHOOK_SIGNING_KEY'),
    'mailgun_timestamp_tolerance' => (int) env('MAILGUN_WEBHOOK_TIMESTAMP_TOLERANCE', 900),
    'inbound_domain' => env('MAILGUN_INBOUND_DOMAIN'),
    'reply_local_part' => env('MAILGUN_REPLY_LOCAL_PART', 'reply'),
    'reply_token_lifetime_days' => (int) env('MAILGUN_REPLY_TOKEN_LIFETIME_DAYS', 180),
];
