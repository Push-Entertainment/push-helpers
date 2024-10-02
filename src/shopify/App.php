<?php

namespace Push\Shopify;


use Push\Functions\Json;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class App
 */
class App
{

    /**
     * @param string $shop
     * @param array  $params
     * @param string $app_key
     * @param string $app_secret
     * @param string $hmac
     * @param string $scopes
     * @param string $redirect_uri
     *
     * @return void
     * @throws \Exception
     */
    #[NoReturn]
    public static function authRedirectURL(
        string $shop,
        array $params,
        string $app_key,
        string $app_secret,
        string $hmac,
        string $scopes,
        string $redirect_uri
    ): void {
        // Allow framed request
        header_remove('X-Frame-Options');

        if (empty($shop))
        {
            throw new \Exception("Invalid request.");
        }

        if (!self::validateParams($hmac, $params, $app_secret))
        {
            throw new \Exception("Invalid request. Possibly not from shopify");
        }

        // Build install/approval URL to redirect to
        $install_url   = [];
        $install_url[] = "https://{$shop}";
        $install_url[] = "/admin/oauth/authorize?client_id=" . $app_key;
        $install_url[] = "&scope=" . $scopes;
        $install_url[] = "&redirect_uri=" . urlencode($redirect_uri);

        // Redirect
        header("Location: " . implode("", $install_url));
        die();
    }


    /**
     * @param string $hmac
     * @param array  $params
     * @param string $secret
     *
     * @return bool
     */
    public static function validateParams(string $hmac, array $params, string $secret): bool
    {
        ksort($params);

        return hash_equals($hmac, hash_hmac('sha256', http_build_query($params), $secret));
    }


    /**
     * @throws \Exception
     */
    public static function requestAccessToken( string $shop, string $code, string $app_api_key, string $app_secret ): ?string
    {
        // Set variables for our request
        $query = [
            "client_id"     => $app_api_key, // Your API key
            "client_secret" => $app_secret, // Your app credentials (secret key)
            "code"          => $code // Grab the access key from the URL
        ];

        // Generate access token URL
        $access_token_url = "https://" . $shop . "/admin/oauth/access_token";

        // Configure curl client and execute request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $access_token_url);
        curl_setopt($ch, CURLOPT_POST, \count($query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);


        // Store the access token
        $result_array = Json::decodeToArray($result);

        if (!isset($result_array['access_token']))
        {
            throw new \Exception("Unable to retrieve access token: " . JSON::encode($result));
        }

        return $result_array['access_token'];
    }
}