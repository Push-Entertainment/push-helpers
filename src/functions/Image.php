<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Image
 *
 * @package Push\Functions
 */
class Image
{

    use Error;


    /**
     * @var string|null
     */
    public static ?string $last_image_error = null;

    /**
     * Check if remote image exits if its valid?!
     *
     * @param string $url remote image url
     * @param bool   $follow_redirects
     * @param string $redirect_image_path_check
     *
     * @return bool
     * @noinspection MultipleReturnStatementsInspection
     */
    public static function checkRemoteImageExists( string $url, bool $follow_redirects = true, string $redirect_image_path_check = "p=defaultimage&frombiblio=1&tree" ): bool
    {
        self::$last_image_error = null;

        try
        {
            $url = trim( $url );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_NOBODY, 1 );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, $follow_redirects );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_HEADER, TRUE );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 2 );

            $result = curl_exec( $ch );
            // Successfully called
            if ( $result === FALSE )
            {
                throw new \RuntimeException( curl_error( $ch ) );
            }

            $status_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            $redirect_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
            curl_close( $ch );

            // Check if valid status code
            if ( ! is_numeric( $status_code ) || $status_code < 200 )
            {
                throw new \RuntimeException( "invalid status code {$status_code}" );
            }

            // Check if being redirected to default non existing image
            if ( preg_match( "~{$redirect_image_path_check}~i", $redirect_url ) )
            {
                return false;
            }

            return TRUE;
        }
        catch ( \RuntimeException $e )
        {
            self::$last_image_error = "Image fetch error for {$url} :" . $e->getMessage();

            if ( stripos( $e->getMessage(), "OpenSSL" ) !== FALSE )
            {
                self::throwErrorSentry($e);
                return TRUE;
            }

            self::throwErrorSentry($e);
            return FALSE;
        }
    }
}