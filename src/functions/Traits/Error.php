<?php

namespace Push\Functions\Traits;

/**
 * Trigger error
 */
trait Error
{
    /**
     * @var mixed|null
     */
    private static mixed $error_function = null;

    /**
     * Custom error handling for throwing errors
     * @param callable $ft
     *
     * @return void
     */
    public static function callableLogError( callable $ft ): void
    {
        if( is_callable($ft) )
        {
            self::$error_function = $ft;
        }
    }


    /**
     * Die on error
     *
     * @param \RuntimeException|\Exception|\JsonException $e
     */
    public static function throwErrorSentry( \RuntimeException|\Exception|\JsonException $e ):void
    {
        if ( function_exists('\Sentry\captureException'))
        {
            \Sentry\captureException($e);
        }

        // Log as custom errors if passed.
        if( isset(self::$error_function) && is_callable( self::$error_function ) )
        {
            $fct = self::$error_function;
            $fct( $e->getMessage() );
        }
    }
}