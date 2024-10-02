<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Crons
 *
 * @package Push\Functions
 */
class Crons
{


    /**
     * Cron looper to maximize polling
     *
     * @param callable $function
     * @param int      $max_interval_seconds
     * @param int      $elapsed_time_minimum_sleep
     *
     * @return array
     */
    public static function tickTick( callable $function, int $max_interval_seconds = 60, int $elapsed_time_minimum_sleep = 3 ): array
    {
        $spent    = 0;
        $response = [];

        while( $max_interval_seconds - $spent > 0 )
        {
            $start_time = time();

            $response[] = $function();

            // Calculate total spent time
            $elapsed_time = time() - $start_time;
            sleep( ( $elapsed_time < $elapsed_time_minimum_sleep ? ( $elapsed_time_minimum_sleep - $elapsed_time ) : 0 ) );
            $spent += (max($elapsed_time, $elapsed_time_minimum_sleep));
        }

        return ( array_merge( [], ...$response ) );
    }
}