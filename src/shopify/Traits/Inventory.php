<?php

namespace Push\Shopify\Traits;


/**
 * Class Inventory
 *
 * @package Push\Shopify\Traits
 */
trait Inventory
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


    public function setInventoryQty(int $inventory_item_id, int $location_id, int $qty): bool
    {
        return $this->setInventoryLevel($inventory_item_id, $location_id, $qty);
    }

    /**
     * Set inventory levels for item
     *
     * @param int $inventory_item_id
     * @param int $location_id
     * @param int $qty
     *
     * @return bool
     */
    public function setInventoryLevel(int $inventory_item_id, int $location_id, int $qty): bool
    {
        return $this->apiCall(
            'inventory_levels/set.json',
            [
                "location_id"       => $location_id,
                "inventory_item_id" => $inventory_item_id,
                "available"         => $qty,
            ],
            'POST',
            'inventory_level'
        );
    }


    /**
     * Api call to update product
     *
     * @param int   $inventory_item_id
     * @param array $data
     *
     * @return bool
     */
    public function updateInventoryItem(int $inventory_item_id, array $data): bool
    {
        return $this->apiCall(
            "inventory_items/{$inventory_item_id}.json",
            ['inventory_item' => array_replace(['id' => $inventory_item_id], $data)],
            'PUT',
            'inventory_item'
        );
    }


    /**
     * Get inventory level
     *
     * @param int $inventory_item_id
     * @param int $location_id
     *
     * @return bool
     */
    public function getInventoryLevel(int $inventory_item_id, int $location_id): bool
    {
        return $this->apiCall(
            'inventory_levels.json',
            ['query' => ['inventory_item_ids' => $inventory_item_id, 'location_ids' => $location_id]],
            'GET',
            'inventory_levels'
        );
    }

}