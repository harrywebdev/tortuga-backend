<?php

return [
    'debug_db'            => env('TORTUGA_DEBUG_DB', false),
    'debug_slots'         => env('TORTUGA_DEBUG_SLOTS', false),

    /**
     * Whether to send out notification or just log it
     */
    'debug_notifications' => env('TORTUGA_DEBUG_NOTIFICATIONS', false),

    /**
     * Whether to delay notifications jobs or not, e.g. order rejected, delayed, etc
     */
    'notifications_delay' => env('TORTUGA_NOTIFICATIONS_DELAY', 5),
];