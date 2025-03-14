<?php

return [
    /**
     * Availability settings
     */
    'availability' => [
        // Time slot interval in minutes
        'slot_interval' => env('BARBER_SLOT_INTERVAL', 15),

        // Maximum number of days in advance that appointments can be booked
        'max_days_in_advance' => env('BARBER_MAX_DAYS_IN_ADVANCE', 30),

        // Default appointment duration in minutes (used when no service is specified)
        'default_duration' => env('BARBER_DEFAULT_DURATION', 30),
    ],

    /**
     * Scheduling settings
     */
    'scheduling' => [
        // Buffer time between appointments in minutes
        'buffer_time' => env('BARBER_BUFFER_TIME', 5),

        // Allow overlapping appointments (not recommended)
        'allow_overlapping' => env('BARBER_ALLOW_OVERLAPPING', false),

        // Allow booking outside working hours (not recommended)
        'allow_outside_hours' => env('BARBER_ALLOW_OUTSIDE_HOURS', false),
    ],

    /**
     * Time off settings
     */
    'time_off' => [
        // Minimum time off duration in minutes
        'min_duration' => env('BARBER_TIME_OFF_MIN_DURATION', 30),

        // Maximum time off duration in days
        'max_duration_days' => env('BARBER_TIME_OFF_MAX_DURATION_DAYS', 30),
    ],

    /**
     * Default working hours
     * These will be used when creating a new barber profile
     * Format: ['day_of_week' => 0, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false]
     * Day of week: 0 = Sunday, 6 = Saturday
     */
    'default_working_hours' => [
        ['day_of_week' => 0, 'start_time' => null, 'end_time' => null, 'is_day_off' => true],  // Sunday
        ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false], // Monday
        ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false], // Tuesday
        ['day_of_week' => 3, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false], // Wednesday
        ['day_of_week' => 4, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false], // Thursday
        ['day_of_week' => 5, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false], // Friday
        ['day_of_week' => 6, 'start_time' => '10:00', 'end_time' => '15:00', 'is_day_off' => false], // Saturday
    ],
];
