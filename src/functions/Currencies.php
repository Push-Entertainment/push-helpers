<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Misc
 *
 * @package Push\Functions
 */
class Currencies
{

    use Error;


    /**
     * @var string $access_key
     */
    private static string $access_key = '6e8e5d67e31330ef141aeafe7a3c74bb';



    /**
     * @return string
     */
    public static function getAccessKey(): string
    {
        return self::$access_key;
    }

    /**
     * @param string $access_key
     */
    public static function setAccessKey(string $access_key): void
    {
        self::$access_key = $access_key;
    }


    /**
     * Get currency rates based on EUR
     *
     * @return array
     * @throws \Exception
     */
    public static function liveRates(): array
    {
        $rates = Cache::cacheIt('currency_rates_api_v2', static function ()
        {
            try
            {
                $response = Api::apiRequest(
                    url: "http://data.fixer.io/api/latest?access_key=" . self::getAccessKey() . "&format=1"
                );

                if (!empty($response->body) && $response->code === 200)
                {
                    $response = $response->body;
                    if (isset($response->success) && $response->success)
                    {
                        return array_merge( [ 'base_currency' => $response->base ], (array)$response->rates);
                    }
                }
            }
            catch (\Exception )
            {
                return null;
            }

            return null;
        }, 60 * 60 * 24 );

        if ( empty($rates) )
        {
            Error::throwErrorSentry( new \Exception('Failed fetching live currency rates | aborting actions.'));
            die('Failed fetching live currency rates | aborting actions.');
        }

        return $rates;
    }
}