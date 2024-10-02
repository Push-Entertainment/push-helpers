<?php

namespace Push\Shopify;

use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options;
use Gnikyt\BasicShopifyAPI\Session;
use Push\Functions\Traits\Error;


/**
 * Class Base
 *
 * @package Push\Shopify
 */
class Base
{

    use Error;

    /**
     * API VERSION URL SLUG
     */
    private const DEFAULT_VERSION = '2024-01';


    /**
     * @var string $api_version ;
     */
    private static string $api_version;


    /**
     * API SETTINGS
     */
    private const API_SETTINGS = [
        'timeout'            => 120.0,
        'max_retry_attempts' => 5,
        'retry_on_status'    => [
            429,
            504,
            503,
            502,
            520,
            500,
            // 400 Bad Request. This is returned when the request is invalid or cannot be served
        ]
    ];


    /**
     * Api call packet limit
     */
    private const LIMIT_REQUEST = 250;

    /**
     * @var callable|null
     */
    private $callback_function_error;

    /**
     * @var callable|null
     */
    private $callback_function_success;

    /**
     * @var string $api_endpoint
     */
    private string $api_endpoint = '';

    /**
     * @var string $access_token
     */
    private string $access_token = '';

    /**
     * @var string $api_user
     */
    private string $api_user = '';

    /**
     * @var string $api_password
     */
    private string $api_password = '';

    /**
     * @var string $api_shared
     */
    private string $api_shared = '';

    /**
     * @var string $last_error
     */
    private string $last_error = '';

    /**
     * @var bool $failed_request
     */
    private bool $failed_request = false;

    /**
     * @var string $called_endpoint
     */
    private string $called_endpoint = '';

    /**
     * @var array $posted_data
     */
    private array $posted_data = [];

    /**
     * @var string|array|object|null $response
     */
    private null|string|array|object $response;

    /**
     * @var string $request_method
     */
    private string $request_method;

    /**
     * @var \Gnikyt\BasicShopifyAPI\BasicShopifyAPI $api_session
     */
    private BasicShopifyAPI $api_session;


    /**
     * ShopifyModel constructor.
     *
     * @param string|null $domain
     * @param string|null $access_token
     * @param array       $options
     */
    public function __construct(
        ?string $domain = '',
        ?string $access_token = '',
        array $options = ['custom_api_version' => '']
    ) {
        try
        {
            if (!empty($domain))
            {
                $this->setApiEndpoint($domain);
            }
            if (!empty($access_token))
            {
                $this->setAccessToken($access_token);
            }

            $custom_api_version = $options['custom_api_version'] ?? '';

            // Set Shopify Api version used
            $custom_api_version = !empty($custom_api_version) ? $custom_api_version : self::DEFAULT_VERSION;

            // Check compare version || WARN
            if (version_compare(self::DEFAULT_VERSION, $custom_api_version) > 0)
            {
                self::throwErrorSentry(
                    new \Exception(
                        "Older Shopify Api version being used {$custom_api_version} on {$domain}, min version suggested " . self::DEFAULT_VERSION
                    )
                );
            }

            // SET API version being used
            self::setApiVersion($custom_api_version);


            // Create options for the API
            $options = new Options();
            $options->setVersion(self::getApiVersion());
            $options->setGuzzleOptions(self::API_SETTINGS);

            $this->api_session = new BasicShopifyAPI($options);
        } catch (\Exception | \ReflectionException $e)
        {
            self::throwErrorSentry($e);
        }
    }

    /**
     * Check if Shopify $_SERVER variables are set
     *
     * @return bool
     */
    public static function isRequestFromShopify(): bool
    {
        if (!isset($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'], $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
        {
            return false;
        }

        return true;
    }

    /**
     * Get Shopify headers
     *
     * @return array
     */
    public static function getShopifyHeaders(): array
    {
        $server = [];
        foreach (array_change_key_case($_SERVER, CASE_UPPER) as $key => $value)
        {
            if (false !== stripos($key, "SHOPIFY"))
            {
                $server[$key] = $value;
            }
        }

        return $server;
    }

    /**
     * Get Shopify header HMAC code
     *
     * @return string
     */
    public static function getWebhookHMAC(): string
    {
        return $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
    }

    /**
     * Get Shopify header HMAC code
     *
     * @return string
     */
    public static function getWebhookTopic(): string
    {
        return $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '';
    }

    /**
     * Get Shopify header domain set
     *
     * @return string
     */
    public static function getWebhookDomain(): string
    {
        return $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '';
    }

    /**
     * Get the php://input
     *
     * @return string
     */
    public static function getPostedContents(): string
    {
        return trim(file_get_contents('php://input'));
    }

    /**
     * Verify webhook integrity
     *
     * @param string $data_packet
     * @param string $shopify_hmac
     * @param string $webhooks_key
     *
     * @return bool
     */
    public static function verifyHMAC(string $data_packet, string $shopify_hmac, string $webhooks_key): bool
    {
        return hash_equals($shopify_hmac, base64_encode(hash_hmac('sha256', $data_packet, $webhooks_key, true)));
    }

    /**
     * Check if POST method provided
     * @return bool
     *
     */
    public static function isPost(): bool
    {
        return ( strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' );
    }

    /**
     * Check if in allowed list of topics
     * @param array $allowed_topics
     *
     * @return bool
     */
    public static function isAllowedTopic( array $allowed_topics ): bool
    {
        return in_array( self::getWebhookTopic(), $allowed_topics, true );
    }

    /**
     * Check data integrity, every webhook should have an id
     * @param object $data
     *
     * @return bool
     */
    public static function checkWebhookDataIntegrity( object $data ): bool
    {
        return ( ! empty( $data->id ?? null ) );
    }

    /**
     * @return string
     */
    public static function getApiVersion(): string
    {
        return self::$api_version;
    }

    /**
     * @param string $api_version
     */
    public static function setApiVersion(string $api_version): void
    {
        self::$api_version = $api_version;
    }

    /**
     * @return string
     */
    public function getApiShared(): string
    {
        return $this->api_shared;
    }

    /**
     * @param string $api_shared
     */
    public function setApiShared(string $api_shared): void
    {
        $this->api_shared = $api_shared;
    }

    /**
     * @return string
     */
    public function getLastError(): string
    {
        return $this->last_error;
    }

    /**
     * @param string $last_error
     */
    public function setLastError(string $last_error): void
    {
        $this->last_error = $last_error;
    }

    /**
     * Shopify rest api call
     *
     * @param string        $endpoint
     * @param array         $posted_data
     * @param string        $method
     * @param string        $response_property_check
     * @param callable|null $function_between_pages
     *
     * @return bool
     */
    public function apiCall(
        string $endpoint,
        array $posted_data = [],
        string $method = 'GET',
        string $response_property_check = '',
        ?callable $function_between_pages = null
    ): bool {
        $endpoint = preg_match('~^admin/~i', $endpoint) ? $endpoint : ('/admin/api/' . self::getApiVersion(
            ) . '/' . $endpoint);
        $this->setCalledEndpoint($endpoint);


        $this->setFailedRequest(false);

        $this->setPostedData($posted_data);
        $this->setRequestMethod($method);

        $results = [];
        $method  = strtoupper(trim($method));

        try
        {
            if (empty($this->getApiEndpoint()))
            {
                throw new \Exception('Api Endpoint url is missing.');
            }

            if (empty($this->getAccessToken()) && (empty($this->getApiPassword()) || empty($this->getApiUser())))
            {
                throw new \Exception('Api User/Password OR Access Token is missing.');
            }

            // Always max capacity :)
            if ($method === 'GET')
            {
                $posted_data['query']['limit'] = self::LIMIT_REQUEST;
            }

            $this->api_session->setSession(new Session($this->getApiEndpoint(), $this->getAccessToken()));


            /**
             * A request that includes the page_info parameter can't include any other parameters except for limit and fields (if it applies to the endpoint).
             * If you want your results to be filtered by other parameters, then you need to include those parameters in the first request you make.
             */
            $paging_required = true;
            $attempts        = 5;


            // Loop over and cope with paging and errors
            while ($paging_required)
            {
                $api_response    = $this->api_session->rest($method, $endpoint, $posted_data);
                $body            = $api_response['body']->container ?? $api_response['body'];
                $paging_required = ($method === 'GET' && isset($api_response['link']['next']));

                // Give some time and threshold for the 520 error
                if (\in_array((int)$api_response['status'], self::API_SETTINGS['retry_on_status'], true))
                {
                    $attempts--;
                    sleep(15);

                    // Retry the call if threshold not hit
                    if ($attempts > 0)
                    {
                        continue;
                    }
                }

                // If there are errors exit.
                if ($api_response['errors'])
                {
                    throw new \Exception(
                        'Error ' . $api_response['status'] . ' occurred on [' . $method . ']' . $endpoint . ' api request: ' . \Push\Functions\Json::encode(
                            $api_response
                        ), $api_response['status']
                    );
                }

                // Methods that return response
                if (in_array($method, ['PUT', 'GET', 'POST'], true))
                {
                    // Paging required
                    if ($paging_required)
                    {
                        $posted_data['query']              = array_diff_key(
                            $posted_data['query'],
                            array_diff_key(
                                $posted_data['query'],
                                array_flip(['page_info', 'limit', 'fields'])
                            )
                        );
                        $posted_data['query']['page_info'] = ($api_response['link']['next'] ?? '');
                    }

                    // Empty property detected from response
                    if (!empty($response_property_check))
                    {
                        if (!isset($body[$response_property_check]))
                        {
                            throw new \Exception(
                                'Error ' . $api_response['status'] . ' occurred on ' . $endpoint . ' api call, unable to detect shopify response object: ' . \Push\Functions\Json::encode(
                                    $body
                                ), $api_response['status']
                            );
                        }

                        // Parser function
                        if (null !== $function_between_pages && is_callable($function_between_pages))
                        {
                            $fct = $function_between_pages($body[$response_property_check]);
                            if (!empty($fct))
                            {
                                $results[] = $fct;
                            }
                        }
                        else
                        {
                            $results[] = $body[$response_property_check];
                        }
                    }
                    elseif (null !== $function_between_pages && is_callable($function_between_pages))
                    {
                        $fct = $function_between_pages($body);
                        if (!empty($fct))
                        {
                            $results[] = $fct;
                        }
                    }
                    else
                    {
                        $results[] = $body;
                    }
                }
            }

            $this->setResponse(array_merge([], ...$results));
            $this->_callbackFctSuccess();

            return true;
        } catch (\Exception $e)
        {
            $this->setFailedRequest(true);
            $this->setLastError($e->getMessage() . "[ {$method} ]" . trim( $this->getApiEndpoint(), '/') . '/' . $endpoint);
            $this->setResponse(null);
            $this->_callbackFctError();
            self::throwErrorSentry($e);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->api_endpoint;
    }






    /**
     * Init Shopify API
     *
     * @param string $domain
     * @param string $access_token
     *
     * @return void
     */
    public function apiInit( string $domain, string $access_token ): void
    {
        $this->setApiEndpoint( ( $domain ) );
        $this->setAccessToken( ( $access_token ) );
    }



    /**
     * @param string $api_endpoint
     */
    public function setApiEndpoint(string $api_endpoint): void
    {
        $this->api_endpoint = preg_replace("~(^http(s)?://|/$)~i", "", strtolower(trim($api_endpoint))) . "";
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken(string $access_token): void
    {
        $this->access_token = trim( $access_token );
    }

    /**
     * @return string
     */
    public function getApiPassword(): string
    {
        return $this->api_password;
    }

    /**
     * @param string $api_password
     */
    public function setApiPassword(string $api_password): void
    {
        $this->api_password = $api_password;
    }

    /**
     * @return string
     */
    public function getApiUser(): string
    {
        return $this->api_user;
    }

    /**
     * @param string $api_user
     */
    public function setApiUser(string $api_user): void
    {
        $this->api_user = $api_user;
    }

    /**
     * @param object|array|string|null $response
     */
    public function setResponse( object|array|string|null $response): void
    {
        $this->response = $response;
    }

    /**
     * Callback function to fire on error
     */
    public function _callbackFctSuccess(): void
    {
        if ($this->callback_function_success !== NULL) {
            $function = $this->callback_function_success;
            if (is_callable($function)) {
                $function();
            }
        }
    }

    /**
     * Callback function to fire on error
     */
    public function _callbackFctError(): void
    {
        if ($this->callback_function_error !== NULL) {
            $function = $this->callback_function_error;
            if (is_callable($function)) {
                $function();
            }
        }
    }

    /**
     * @param string $query
     * @param array  $variables
     * @param string $object search inside object $res[$object]['pageInfo']['hasNextPage'] | collections, products
     *
     * @return bool
     */
    public function graphQL( string $query, array $variables = [], string $object = 'collections' ): bool
    {
        try {
            if (empty($this->getApiEndpoint())) {
                throw new \Exception('Api Endpoint url is missing.');
            }

            if (empty($this->getAccessToken()) && (empty($this->getApiPassword()) || empty($this->getApiUser()))) {
                throw new \Exception('Api User/Password OR Access Token is missing.');
            }

            $this->api_session->setSession(new Session($this->getApiEndpoint(), $this->getAccessToken()));

            $paging         = true;
            $all_results    = [];
            $orig_query     = $query;
            $retry_attempts = 5;

            while ($paging === TRUE)
            {
                /** @var array $api_response */
                $api_response = $this->api_session->graph($query, $variables);

                // Failed fetch response of graph
                if( ! is_object( $api_response['body'] ) || ! isset( $api_response['body']->container['data'] ) )
                {
                    if( $retry_attempts <= 0)
                    {
                        throw new \Exception('Unable to fetch graphQL response. ' . \Push\Functions\Json::encode( $api_response ) );
                    }

                    sleep(15 );
                    $retry_attempts--;
                }
                else
                {
                    $res          = $api_response['body']->container['data'];
                    $key          = array_key_first($res);

                    // If there are errors exit.
                    if ($api_response['errors']) {
                        throw new \Exception(
                            'Error ' . $api_response['status'] . ' occurred on query call: ' . json_encode(
                                $api_response['errors'],
                                JSON_THROW_ON_ERROR
                            ), $api_response['status']
                        );
                    }

                    $all_results[] = array_column($res[$key]['edges'], 'node');

                    $paging_info          = $api_response['body']->container['extensions']['cost'];
                    $cost_required        = $paging_info['requestedQueryCost'];
                    $remaining_juice      = $paging_info['throttleStatus']['currentlyAvailable'];
                    $restore_rate_per_sec = $paging_info['throttleStatus']['restoreRate'];
                    $paging               = ((int)$res[$object]['pageInfo']['hasNextPage'] === 1);
                    if ($paging === TRUE) {
                        if ($cost_required > $remaining_juice) {
                            // Rest till we got a response
                            sleep(ceil(($cost_required - $remaining_juice) / $restore_rate_per_sec));
                        }

                        $cursors = (array_column($res[$object]['edges'], 'cursor'));
                        $cursor  = $cursors[count($cursors) - 1];
                        $query   = str_replace('##cursor', ', after: "' . $cursor . '"', $orig_query);
                    }
                }
            }

            $all_results = array_merge([], ...$all_results);
            $this->setResponse( $all_results );

            return TRUE;
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
            $this->setResponse(NULL);

            return FALSE;
        }
    }

    /**
     * @return object|array|string|null
     */
    public function getResults(): object|array|string|null
    {
        return ( $this->response ?? '' );
    }

    /**
     * @return object|array|null
     */
    public function getResultsObject(): object|array|null
    {
        return \Push\Functions\Json::decodeToObject(\Push\Functions\Json::encode( ($this->response ?? []) ) );
    }

    /**
     * @return array|null
     */
    public function getResultsArray(): array|null
    {
        return \Push\Functions\Json::decodeToArray(\Push\Functions\Json::encode( ($this->response ?? []) ));
    }

    /**
     * @return string
     */
    public function getCalledEndpoint(): string
    {
        return $this->called_endpoint;
    }

    /**
     * @param string $called_endpoint
     */
    public function setCalledEndpoint(string $called_endpoint): void
    {
        $this->called_endpoint = $called_endpoint;
    }

    /**
     * @return array
     */
    public function getPostedData(): array
    {
        return $this->posted_data;
    }

    /**
     * @param array $posted_data
     */
    public function setPostedData(array $posted_data): void
    {
        $this->posted_data = $posted_data;
    }

    /**
     * Callback function to fire on API error
     *
     * @param callable|null $callback_function_error
     */
    public function setCallbackFunctionError(?callable $callback_function_error): void
    {
        $this->callback_function_error = $callback_function_error;
    }

    /**
     * Callback function to fire on API error
     *
     * @param callable|null $callback_function_success
     */
    public function setCallbackFunctionSuccess(?callable $callback_function_success): void
    {
        $this->callback_function_success = $callback_function_success;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->request_method;
    }

    /**
     * Set request method
     *
     * @param string $request_method
     */
    public function setRequestMethod(string $request_method): void
    {
        $this->request_method = $request_method;
    }

    /**
     * @return bool
     */
    public function isFailedRequest(): bool
    {
        return $this->failed_request;
    }

    /**
     * @param bool $failed_request
     */
    public function setFailedRequest(bool $failed_request): void
    {
        $this->failed_request = $failed_request;
    }
}