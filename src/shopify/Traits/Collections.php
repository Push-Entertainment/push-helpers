<?php

namespace Push\Shopify\Traits;


/**
 * Class Collections
 *
 * @package Push\Shopify\Traits
 */
trait Collections
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
     * Api call to add new collection
     *
     * @param array $data
     *
     * @return bool
     */
    public function addCollection(array $data): bool
    {
        return $this->apiCall('custom_collections.json', ['custom_collection' => $data], 'POST', 'custom_collection');
    }

    /**
     * Api call to add new collection
     *
     * @param array $data
     *
     * @return bool
     */
    public function addSmartCollection(array $data): bool
    {
        return $this->apiCall('smart_collections.json', ['smart_collection' => $data], 'POST', 'smart_collection');
    }

    /**
     * Api call to update collection
     *
     * @param int   $collection_id
     * @param array $data
     *
     * @return bool
     */
    public function updateCollection(int $collection_id, array $data): bool
    {
        return $this->apiCall(
            "custom_collections/{$collection_id}.json",
            ['custom_collection' => array_merge(['id' => $collection_id], $data)],
            'PUT',
            'custom_collection'
        );
    }

    /**
     * Api call to delete collection
     *
     * @param int $collection_id
     *
     * @return bool
     */
    public function deleteCollection(int $collection_id): bool
    {
        return $this->apiCall("custom_collections/{$collection_id}.json", [], 'DELETE');
    }


    /**
     * Get all custom collections
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getCollections(array $query_params = []): bool
    {
        return $this->apiCall('custom_collections.json', ['query' => $query_params], 'GET', 'custom_collections');
    }

    /**
     * Get all custom collections
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getSmartCollections(array $query_params = []): bool
    {
        return $this->apiCall('smart_collections.json', ['query' => $query_params], 'GET', 'smart_collections');
    }


    /**
     * Get all custom collections
     *
     * @param int $collection_id
     *
     * @return bool
     */
    public function getCollectionMetafields(int $collection_id): bool
    {
        return $this->apiCall('collections/' . $collection_id . '/metafields.json', [], 'GET', 'metafields');
    }


    /**
     * Get collects -> relation of product and collection
     *
     * @param int $collect_id
     *
     * @return bool
     */
    public function deleteCollect(int $collect_id): bool
    {
        return $this->apiCall('collects/' . $collect_id . '.json', [], 'DELETE');
    }


    /**
     * Get collects -> relation of product and collection
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getCollects(array $query_params = []): bool
    {
        return $this->apiCall('collects.json', ['query' => $query_params], 'GET', 'collects');
    }


    /**
     * Add product to a collection
     *
     * @param int $collection_id
     * @param int $product_id
     *
     * @return bool
     */
    public function addCollect(int $collection_id, int $product_id): bool
    {
        return $this->apiCall(
            'collects.json',
            ['collect' => ['collection_id' => $collection_id, 'product_id' => $product_id]],
            'POST',
            'collect'
        );
    }

}