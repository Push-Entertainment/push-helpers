<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Json
 *
 * @package Push\Functions
 */
class Json
{

    use Error;

    /**
     * Safely json decode string
     *
     * @param string|array|object|null $string |null $string
     *
     * @return array
     */
    public static function decodeToArray( null|string|array|object $string ): array
    {
        if( $string === "null" || empty( $string ) ){
            return [];
        }

        if( is_array( $string ) )
        {
            return $string;
        }

        if( is_object( $string ) )
        {
            return self::castToArray( $string );
        }


        // ONLY NON-EMPTY STRINGS REACHES THESE STATEMENTS
        $array = [];

        try
        {
            $array = json_decode( $string, TRUE, 512, JSON_THROW_ON_ERROR );
            if ( ! is_array( $array ) || json_last_error() !== JSON_ERROR_NONE )
            {
                $array = [];
            }
        }
        catch ( \JsonException | \Exception $e )
        {
            self::throwErrorSentry($e );
            return [];
        }

        return $array;
    }


    /**
     * Safely json decode string | can be array of objects
     *
     * @param string|array|object|null $string |null $string $string
     *
     * @return object|array
     */
    public static function decodeToObject( null|string|array|object $string ): object|array
    {
        if( empty( $string ) || $string === "null" ){
            return new \stdClass();
        }

        if( is_array( $string ) )
        {
            return self::castToObject( $string );
        }

        if( is_object( $string ) )
        {
            return $string;
        }


        // ONLY NON-EMPTY STRINGS REACHES THESE STATEMENTS
        $object = new \stdClass();

        try
        {
            $object = json_decode( $string, FALSE, 512, JSON_THROW_ON_ERROR );

            if ( json_last_error() !== JSON_ERROR_NONE )
            {
                $object = new \stdClass();
            }

        }
        catch ( \JsonException $e )
        {
            self::throwErrorSentry($e);
            return new \stdClass();
        }

        return $object;
    }

    /**
     * Safely json encode array
     *
     * @param string|object|array|null $array $array $array
     *
     * @return string
     */
    public static function encode( null|string|object|array $array ): string
    {
        try
        {
            return empty( $array ) ? "[]" : json_encode($array, JSON_THROW_ON_ERROR );
        }
        catch ( \JsonException $e )
        {
            self::throwErrorSentry($e );
            return '';
        }
    }


    /**
     * Safely json decode string | can be array of objects
     *
     * @param string|object|array|null $data
     *
     * @return object|array
     */
    public static function castToObject( null|string|object|array $data ): object|array
    {
        return self::decodeToObject( self::encode( $data ) );
    }


    /**
     * Safely json decode string | can be array of objects
     *
     * @param string|object|array|null $data
     *
     * @return array
     */
    public static function castToArray( null|string|object|array $data ): array
    {
        return self::decodeToArray( self::encode( $data ) );
    }
}