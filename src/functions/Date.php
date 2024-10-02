<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Date
 *
 * @package Push\Functions
 */
class Date
{

    use Error;


    /**
     * Display human-readable format of time ago… 3 minutes ago, last week...
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public static function timeAgo( string $date ): string
    {
        return (new \Westsworld\TimeAgo())->inWords(new \DateTime($date ) );
    }




    /**
     * @param string $date datetime or timestamp
     *
     * @return string date Y-m-d H:i:s
     */
    public static function dateYMDHIS( string $date ): string
    {
        if(is_numeric( $date ))
        {
            return date( 'Y-m-d H:i:s', $date );
        }

        return date( 'Y-m-d H:i:s', strtotime( $date ) );
    }


    /**
     * @param string $date datetime or timestamp
     *
     * @return string date Y-m-d H:i:s
     */
    public static function dateGmtYMDHIS( string $date ): string
    {
        if(is_numeric( $date ))
        {
            return gmdate( 'Y-m-d H:i:s', $date );
        }

        return gmdate( 'Y-m-d H:i:s', strtotime( $date ) );
    }




    /**
     * @param string $date datetime or timestamp
     *
     * @return string date Y-m-01 00:00:00
     */
    public static function dateFirstDayOfMonthYMDHIS( string $date ): string
    {
        if(is_numeric( $date ))
        {
            return date( 'Y-m-01', $date ) . ' 00:00:00';
        }

        return date( 'Y-m-01', strtotime( $date ) ) . ' 00:00:00';
    }



    /**
     * @param string $date datetime or timestamp
     *
     * @return string date Y-m-31 23:59:59
     */
    public static function dateLastDayOfMonthYMDHIS( string $date ): string
    {
        if(is_numeric( $date ))
        {
            return date( 'Y-m-t', $date ) . ' 23:59:59';
        }

        return date( 'Y-m-t', strtotime( $date ) ) . ' 23:59:59';
    }




}