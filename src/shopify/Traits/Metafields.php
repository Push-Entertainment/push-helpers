<?php

namespace Push\Shopify\Traits;


/**
 * Class Metafields
 *
 * @package Push\Shopify\Traits
 * Types: https://shopify.dev/apps/metafields/types
 */
trait Metafields
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
        ?callable $function_between_pages = null
    ): bool;


    /**
     * @param array $data
     *
     * @return void
     * @throws \Exception
     */
    private function sanityCheckData(array $data): void
    {

        if ( str_replace( "-", "", \Push\Shopify\Base::getApiVersion() ) >= '202204' && ( !isset($data['type']) || isset($data['value_type']) ) )
        {
            \Push\Functions\Traits\Error::throwErrorSentry(new \Exception("Metafield call using old Deprecated value_type field."));
        }
    }


    /**
     * Api call to list metafields for a specific variant
     *
     * @param int   $parent_id
     * @param int   $variant_id
     * @param array $query_params
     *
     * @return bool
     */
    public function getVariantMetafields(int $parent_id, int $variant_id, array $query_params = []): bool
    {
        return $this->apiCall(
            'products/' . $parent_id . '/variants/' . $variant_id . '/metafields.json',
            [
                'query' => $query_params
            ],
            'GET',
            'metafields'
        );
    }


    /**
     * Api call to delete metafield
     *
     * @param int $metafield_id
     *
     * @return bool
     */
    public function deleteMetafield(int $metafield_id): bool
    {
        return $this->apiCall("metafields/{$metafield_id}.json", [], 'DELETE');
    }


    /**
     * Api call to list metafields for a specific variant
     *
     * @param int   $parent_id
     * @param int   $variant_id
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function addVariantMetafield(int $parent_id, int $variant_id, array $data = []): bool
    {
        $this->sanityCheckData($data);

        return $this->apiCall(
            'products/' . $parent_id . '/variants/' . $variant_id . '/metafields.json',
            [
                'metafield' => $data
            ],
            'POST',
            'metafield'
        );
    }


    /**
     * List all metafields against a product
     *
     * @param int   $product_id
     * @param array $query_params
     *
     * @return bool
     */
    public function getProductMetafields(int $product_id, array $query_params = []): bool
    {
        return $this->apiCall(
            "products/{$product_id}/metafields.json",
            [
                'query' => $query_params
            ],
            'GET',
            'metafields'
        );
    }

    /**
     * Create new metafield against a product
     *
     * @param int   $product_id
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function addProductMetafield(int $product_id, array $data): bool
    {
        $this->sanityCheckData($data);

        return $this->apiCall(
            "products/{$product_id}/metafields.json",
            [
                'metafield' => $data
            ],
            'POST',
            'metafield'
        );
    }


    /**
     * Create new metafield against a product
     *
     * @param int $product_id
     * @param int $meta_id
     *
     * @return bool
     */
    public function deleteProductMetafield(int $product_id, int $meta_id): bool
    {
        return $this->apiCall("products/{$product_id}/metafields/{$meta_id}.json", [], 'DELETE');
    }






}