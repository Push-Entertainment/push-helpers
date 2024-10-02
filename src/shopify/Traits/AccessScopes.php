<?php

namespace Push\Shopify\Traits;


/**
 * Class Collections
 *
 * @package Push\Shopify\Traits
 */
trait AccessScopes
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
     * @return bool
     */
    public function getAccessScopes(): bool
    {
        return $this->apiCall(
            'admin/oauth/access_scopes.json',
            [],
            'GET',
            'access_scopes'
        );
    }

}