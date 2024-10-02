<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class General
 *
 * @package Push\Functions
 */
class Units
{

    use Error;

    // Default conversion value
    private const ONE_CM_TO_INCH = 2.54;


    /**
     * Convert cm to pixels
     *
     * @param float $cm
     * @param int   $ppi
     *
     * @return int
     */
    public static function cm_to_px( float $cm, int $ppi ): int
    {
        return round( self::cm_to_inch( $cm ) * $ppi );
    }

    /**
     * Cm to inch
     *
     * @param float $cm
     *
     * @return float
     */
    public static function cm_to_inch( float $cm ): float
    {
        return round( ( $cm / self::ONE_CM_TO_INCH ), 3 );
    }

    /**
     * @param float $inch
     * @param int   $ppi
     *
     * @return int
     */
    public static function inch_to_px( float $inch, int $ppi ): int
    {
        return round( $inch * $ppi );
    }

    /**
     * Convert pixels to cm
     *
     * @param int $px
     * @param int $ppi
     *
     * @return float
     */
    public static function px_to_cm( int $px, int $ppi ): float
    {
        return round( self::inch_to_cm( ( $px / $ppi ) ), 3 );
    }

    /**
     * Inch to cm
     *
     * @param float $inch
     *
     * @return float
     */
    public static function inch_to_cm( float $inch ): float
    {
        return round( ( $inch * self::ONE_CM_TO_INCH ), 3 );
    }

    /**
     * Convert pixels to inches
     *
     * @param int $px
     * @param int $ppi
     *
     * @return float
     */
    public static function px_to_inch( int $px, int $ppi ): float
    {
        return round( ( $px / $ppi ), 3 );
    }

}