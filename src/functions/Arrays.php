<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Array
 *
 * @package Push\Functions
 */
class Arrays
{

    use Error;


    /**
     * Check whether an array has duplicate values and return them
     *
     * @param $array
     *
     * @return array
     */
    public static function getDuplicateValues($array): array
    {
        $dupes = [];

        foreach (array_count_values($array) as $val => $c) {
            if ($c > 1) {
                $dupes[] = $val;
            }
        }

        return $dupes;
    }


    /**
     * Get combinations, variations of multidimensional arrays
     *
     * @param $arrays
     *
     * @return array
     */
    public static function getCombinations($arrays): array
    {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }


    /**
     * Safe unserialize method
     *
     * @param string $string
     *
     * @return array
     */
    public static function safeUnserialize(string $string): array
    {
        return (array)unserialize(
            preg_replace_callback(
                '!s:(\d+):"(.*?)";!',
                static function ($match) {
                    return ((int)$match[1] === strlen($match[2])) ? $match[0] : 's:' . strlen(
                            $match[2]
                        ) . ':"' . $match[2] . '";';
                },
                $string
            )
            ,
            ['allowed_classes' => FALSE]
        );
    }


    /**
     * Array combiner + array column
     *
     * @param object|array $object
     * @param string       $key_index
     * @param string       $key_value
     *
     * @return array
     */
    public static function combine( object|array $object, string $key_index, string $key_value): array
    {
        return array_combine( array_column($object, $key_index), array_column($object, $key_value));
    }



    /**
     * Via custom function map and create array
     *
     * @param array    $array
     * @param callable $function
     *
     * @return array
     */
    public static function mapCreate( array $array, callable $function ): array
    {
        $result = [];

        foreach ( $array as $item )
        {
            $result[] = $function( $item );
        }

        return array_replace_recursive( [], ...$result );
    }


    /**
     * Reduces multi-dimensional array to single, by combining/merging each existing element
     *
     * @param array $data [ [1,2], [3,4] ]
     *
     * @return array [ 1,2,3,4 ]
     */
    public static function reduce( array $data ): array
    {
        return self::unique( array_reduce( $data, 'array_merge', [] ) );
    }


    /**
     * Array uniques / filters / resets index
     *
     * @param array $data [ 1, 2, 3, 4, 4, '' ]
     *
     * @return array [ 1,2,3,4 ]
     */
    public static function unique( array $data ): array
    {
        return self::filter(  array_unique( $data ) );
    }


    /**
     * Array uniques / filters / resets index
     *
     * @param array $data [ 1, 2, 3, ' gabe ', null, '' ]
     *
     * @return array [ 1, 2, 3, 'gabe' ]
     */
    public static function filter( array $data ): array
    {
        return array_values( array_filter( array_map( 'trim', array_filter($data) ) ) );
    }


    /**
     * string explode into array / filters / resets index
     *
     * @param string $separator
     * @param string $data '  gabe simply the    best'
     *
     * @return array [ 'gabe', 'simply', 'the', 'best' ]
     */
    public static function explode( string $separator, string $data ): array
    {
        return self::filter(  explode( $separator, $data ) );
    }


    /**
     * Order multidimensional array by key value
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    public static function sortMultidimensionalByValue( array $array, string $key ):array
    {
        usort($array, static function( $a, $b) use($key) {
            return $a[$key] <=> $b[$key];
        });

        return $array;
    }
}