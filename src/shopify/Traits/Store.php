<?php

namespace Push\Shopify\Traits;


/**
 * Class Store
 *
 * @package Push\Shopify\Traits
 */
trait Store
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
     * List all webhooks for the channel
     *
     * @return bool
     */
    public function getWebhooks(): bool
    {
        return $this->apiCall('webhooks.json', [], 'GET', 'webhooks');
    }


    /**
     * Delete a webhook
     *
     * @param int $webhook_id
     *
     * @return bool
     */
    public function deleteWebhook(int $webhook_id): bool
    {
        return $this->apiCall("webhooks/{$webhook_id}.json", [], 'DELETE');
    }


    /**
     * Delete a webhook
     *
     * @param array $data
     *
     * @return bool
     */
    public function addWebhook(array $data): bool
    {
        return $this->apiCall('webhooks.json', ["webhook" => $data], 'POST', 'webhook');
    }


    /**
     * Get all redirects for give store in place
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getStoreRedirects(array $query_params = []): bool
    {
        return $this->apiCall("redirects.json", ['query' => $query_params], 'GET', 'redirects');
    }


    /**
     * Create redirect rules
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function addStoreRedirectUrl(string $from, string $to): bool
    {
        return $this->apiCall(
            'redirects.json',
            [
                'redirect' => [
                    "path"   => $from,
                    "target" => $to,
                ],
            ],
            'POST',
            'redirect'
        );
    }


    /**
     * Delete existing redirect rule
     *
     * @param int $redirect_id
     *
     * @return bool
     */
    public function deleteStoreRedirect(int $redirect_id): bool
    {
        return $this->apiCall("redirects/{$redirect_id}.json", [], 'DELETE');
    }


    /**
     * Api call get single location
     *
     * @param int $location_id
     *
     * @return bool
     */
    public function getLocation(int $location_id): bool
    {
        return $this->apiCall('locations/' . $location_id . '.json', [], 'GET', 'location');
    }


    /**
     * Api call to list store fulfilment locations
     *
     * @return bool
     */
    public function getLocations(): bool
    {
        return $this->apiCall('locations.json', [], 'GET', 'locations');
    }

    /**
     * Api call to list store fulfilment locations
     *
     * @return bool
     */
    public function getFulfilmentServices(): bool
    {
        return $this->apiCall('fulfillment_services.json', ['query' => ['scope' => 'all']], 'GET', 'fulfillment_services');
    }

    /**
     * Api call to list store fulfilment locations
     *
     * @return bool
     */
    public function getStoreData(): bool
    {
        return $this->apiCall('shop.json', [], 'GET', 'shop');
    }


}