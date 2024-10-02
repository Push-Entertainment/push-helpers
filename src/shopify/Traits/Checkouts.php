<?php

namespace Push\Shopify\Traits;


/**
 * Class Checkouts
 *
 * @package Push\Shopify\Traits
 */
trait Checkouts
{

    /**
     * Dependent on apiCall base method
     *
     * @param string        $endpoint
     * @param array         $posted_data
     * @param string        $method
     * @param string        $response_property_check
     * @param callable|null $function_between_pages
     *
     * @return bool
     */
    abstract public function apiCall(
        string $endpoint,
        array $posted_data = [],
        string $method = 'GET',
        string $response_property_check = '',
        ?callable $function_between_pages = NULL
    ): bool;


    /**
     * Get app access scopes
     *
     * @param string $token
     *
     * @return bool
     */
    public function getCheckout( string $token ): bool
    {
        return $this->apiCall(
            'checkouts/' . $token . '.json',
            [],
            'GET',
            'checkout'
        );
    }

}