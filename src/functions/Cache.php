<?php

namespace Push\Functions;



use JsonException;
use RuntimeException;

/**
 * Class Cache
 *
 * @package Push\Functions
 */
class Cache
{

    use Traits\Error;


    /**
     * @var array $cache_data
     */
    private static array $cache_data = [];


    /**
     * Simple inline cache data block, ex. reduce expensive queries
     *
     * @param string   $uid
     * @param callable $function
     *
     * @return mixed
     */
    public static function cacheInline( string $uid, callable $function ): mixed
    {
        return self::getSetInlineCache( $uid, $function );
    }

    /**
     * @var \Predis\Client|null
     */
    private static ?\Predis\Client $client = null;


    /**
     * Variable cache per active session run
     *
     * @var array $session_cache_data
     */
    private static array $session_cache_data = [];


    /**
     * @var string
     */
    private static string $prefix = 'ck:';


    /**
     * @return string
     */
    private static function getHost(): string
    {
        $redis_host = getenv('redis.host');

        if (empty($redis_host))
        {
            $redis_host = $_ENV['redis.host'] ?? '';

            if (empty($redis_host))
            {
                $redis_host = $_SERVER['redis.host'] ?? '';

                if (empty($redis_host))
                {
                    $redis_host = '127.0.0.1';
                }
            }
        }

        return trim($redis_host);
    }

    /**
     * @return int
     */
    private static function getPort(): int
    {
        $redis_port = getenv('redis.port');

        if (empty($redis_port))
        {
            $redis_port = $_ENV['redis.port'] ?? '';

            if (empty($redis_port))
            {
                $redis_port = $_SERVER['redis.port'] ?? '';

                if (empty($redis_port))
                {
                    $redis_port = 6379;
                }
            }
        }

        return $redis_port;
    }


    /**
     * @return \Predis\Client
     */
    public static function init(): \Predis\Client
    {
        self::setPrefix();

        if( self::$client === null )
        {
            $connection_string = 'tcp://' . self::getHost() . ':' . self::getPort() . '?prefix=' . self::$prefix . '&timeout=10.0';
            self::$client = new \Predis\Client( $connection_string,  [
                "prefix" => self::$prefix
            ] );
            self::$client->connect();
        }

        return self::$client;
    }


    /**
     * Get set cache
     *
     * @param string   $key
     * @param callable $fct
     * @param int      $timeout
     * @param bool     $force_reset
     * @param bool     $must_return_value
     *
     * @return array
     * @throws \Exception
     */
    public static function cacheIt( string $key, callable $fct, int $timeout = 60, bool $force_reset = false, bool $must_return_value = false ) : array
    {
        $return_object = [];
        $i             = 1;


        while( $i < 5 && ( empty( $return_object ) ) )
        {
            // Inline session cache
            if ( $timeout === 0 )
            {
                $return_object = self::getSetInlineCache( $key, $fct, $force_reset );
            }
            else
            {
                // Return cached value but also return from inline memory cache if so
                $return_object = self::getSetInlineCache(
                    $key,
                    static function () use ($key, $fct, $timeout, $force_reset)
                    {
                        return self::getSetAdapterCache($key, $fct, $timeout, $force_reset);
                    },
                    $force_reset
                );
            }

            // Cache must return a value
            if( $must_return_value && empty( $return_object ) )
            {
                $i++;
                sleep(10);

                // Unable to fetch back non-empty data
                if( $i === 5 )
                {
                    throw new \Exception('Unable to fetch a value for cache layer ' . $key );
                }
            }
            // Exist while if response can be empty OR there is content returned
            else
            {
                break;
            }
        }

        return $return_object;
    }







    /**
     * Get inline cached values
     *
     * @param string   $key
     * @param callable $fct
     * @param bool     $force_reset
     *
     * @return array
     */
    private static function getSetInlineCache( string $key, callable $fct, bool $force_reset = FALSE ): mixed
    {
        try
        {
            // Set value for key based on parsed function
            if ( $force_reset === TRUE || ! isset( self::$session_cache_data[ $key ] ) )
            {
                self::$session_cache_data[ $key ] = $fct();

                if( self::$session_cache_data[ $key ] === null )
                {
                    throw new RuntimeException('Failure to set cache record for key ' . $key . ', attempting to set a null value' );
                }
            }

            // If retrieved value is null / cache response failure
            if( self::$session_cache_data[ $key ] === null )
            {
                throw new RuntimeException('Failure to get cache record for key ' . $key . ', receiving back a null value.' );
            }

            return self::$session_cache_data[ $key ];
        }
        catch ( RuntimeException $e )
        {
            self::throwErrorSentry( $e );
            die();
        }
    }




    /**
     * Get adapter( redis ) cached values
     *
     * @param string   $key
     * @param callable $fct
     * @param int      $time
     * @param bool     $force_reset
     *
     * @return array
     */
    private static function getSetAdapterCache( string $key, callable $fct, int $time, bool $force_reset ): array
    {
        try
        {
            // Get back array data
            $data_array = self::get($key);

            // Not exists on adapter or needs to be updated
            if ( $force_reset === true || $data_array === null)
            {
                // Parse callable function
                $data_array = $fct();

                // Return null don't save on cache adapter
                if ($data_array === null)
                {
                    throw new RuntimeException('Failure to save cache record for key ' . $key . ' null value found.');
                }

                self::set($key, $data_array, $time);

            }

            // Return cached value
            return $data_array;

        }
        catch ( JsonException | RuntimeException $e )
        {
            self::throwErrorSentry($e);
            die();
        }
    }


    /**
     * @param string $key
     *
     * @return int
     */
    public static function getExpiration( string $key ):int
    {
        $redis = self::init();
        $redis->ttl( $key );

        return $redis->ttl( $key );
    }


    /**
     * Get value directly
     * @param string $key
     *
     * @return array|null
     */
    public static function getValue( string $key ): ?array
    {
        return self::get( $key );
    }


    /**
     * Deletes cache for a key
     * @param array|string $key
     *
     * @return int
     */
    public static function delete( array|string $key ):int
    {
        // Remove inline cache
        if( isset(self::$session_cache_data[ $key ]) )
        {
            unset(self::$session_cache_data[ $key ]);
        }

        return self::init()->del($key );
    }

    /**
     * Deletes all cache
     *
     * @return int
     */
    public static function flushAll():int
    {
        return self::init()->flushall();
    }


    /**
     * @param string $key
     *
     * @return array|null
     */
    private static function get( string $key ): ?array
    {
        $redis = self::init();

        if( ! self::exists($key) )
        {
            return null;
        }

        // Get back array data
        $redis_data = Json::decodeToArray($redis->get($key));

        return ( $redis_data[0] ?? null );
    }


    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $expiration
     *
     */
    private static function set( string $key, mixed $value, int $expiration ): void
    {
        $redis = self::init();

        if ( ! $redis->set($key, Json::encode( [ 0 => $value ] ), 'EX', $expiration))
        {
            throw new RuntimeException('Unable to save cache record for key ' . $key . ' adapter failure.');
        }
    }


    /**
     * Key exists
     * @param string $key
     *
     * @return bool
     */
    public static function exists(string $key) : bool
    {
        $redis = self::init();
        return ( $redis->exists($key) > 0 );
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public static function setPrefix(string $prefix = ''): string
    {
        if( ! empty($prefix ) )
        {
            self::$prefix = $prefix;
        }
        else
        {
            self::$prefix = getenv('redis.prefix');

            if (empty(self::$prefix))
            {
                self::$prefix = $_ENV['redis.prefix'] ?? '';

                if (empty(self::$prefix))
                {
                    self::$prefix = $_SERVER['redis.prefix'] ?? '';

                    if (empty(self::$prefix))
                    {
                        self::$prefix = 'ck';
                    }
                }
            }
        }
        self::$prefix = trim( self::$prefix, ':' ) . ':';

        return self::$prefix;
    }
}