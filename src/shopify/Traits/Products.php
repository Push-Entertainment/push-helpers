<?php

namespace Push\Shopify\Traits;


/**
 * Class Products
 *
 * @package Push\Shopify\Traits
 */
trait Products
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
     * Api call to add product
     *
     * @param array $data
     *
     * @return bool
     */
    public function addProduct(array $data): bool
    {
        return $this->apiCall('products.json', ['product' => $data], 'POST', 'product');
    }

    /**
     * Api call to delete product
     *
     * @param int $product_id
     *
     * @return bool
     */
    public function deleteProduct(int $product_id): bool
    {
        return $this->apiCall("products/{$product_id}.json", [], 'DELETE');
    }


    /**
     * Api call to delete a variant from parent product
     *
     * @param int $product_id
     * @param int $variant_id
     *
     * @return bool
     */
    public function deleteVariant(int $product_id, int $variant_id): bool
    {
        return $this->apiCall("products/{$product_id}/variants/{$variant_id}.json", [], 'DELETE');
    }


    /**
     * Api call to update product
     *
     * @param int   $product_id
     * @param array $data
     *
     * @return bool
     */
    public function updateProduct(int $product_id, array $data): bool
    {
        return $this->apiCall(
            'products/' . $product_id . '.json',
            ['product' => array_merge(['id' => $product_id], $data)],
            'PUT',
            'product'
        );
    }


    /**
     * Api call to update product images
     *
     * @param int   $product_id
     * @param array $data
     *
     * @return bool
     */
    public function addProductImage(int $product_id, array $data): bool
    {
        return $this->apiCall(
            'products/' . $product_id . '/images.json',
            [ 'image' => $data ],
            'POST',
            'image'
        );
    }


    /**
     * Get shopify parent product by id
     *
     * @param int $id
     *
     * @return bool
     */
    public function getProduct(int $id): bool
    {
        if ($this->getProducts(['ids' => $id])) {
            $this->setResponse($this->getResults()->products[0]);
        }

        return FALSE;
    }

    /**
     * Api call to get all products
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getProducts(array $query_params = []): bool
    {
        return $this->apiCall("products.json", ['query' => $query_params], 'GET', 'products');
    }

    /**
     * Get shopify parent product by id
     *
     * @return bool
     */
    public function getProductsCount(): bool
    {
        return $this->apiCall("products/count.json");
    }


    /**
     * Api call to list variants for a parent product
     *
     * @param int   $parent_id
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getVariants(int $parent_id, array $query_params = []): bool
    {
        return $this->apiCall(
            'products/' . $parent_id . '/variants.json',
            ['query' => $query_params],
            'GET',
            'variants'
        );
    }


    /**
     * Get single variant
     *
     * @param int   $variant_id
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getVariant(int $variant_id, array $query_params = []): bool
    {
        return $this->apiCall('variants/' . $variant_id . '.json', ['query' => $query_params], 'GET', 'variant');
    }


    /**
     * Api call to update variant data
     *
     * @param int   $variant_id
     * @param array $packet
     *
     * @return bool
     */
    public function updateVariant(int $variant_id, array $packet): bool
    {
        return $this->apiCall(
            "variants/{$variant_id}.json",
            ['variant' => array_merge(['id' => $variant_id], $packet)],
            'PUT',
            'variant'
        );
    }
}