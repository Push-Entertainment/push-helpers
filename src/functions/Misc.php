<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Misc
 *
 * @package Push\Functions
 */
class Misc
{

    use Error;


    /**
     * @param string $email_address
     * @param int    $size
     *
     * @return string
     */
    public static function getGravatarUrl(  string $email_address, int $size = 160 ): string
    {
        return '//www.gravatar.com/avatar/'.md5( strtolower( $email_address ) ) .'?s=' . $size . '&d=mm';
    }
}