<?php

namespace Push\Shopify\Traits;


/**
 * Class Orders
 *
 * @package Push\Shopify\Traits
 */
trait Orders
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
     * Get back single order
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function getOrder(int $order_id): bool
    {
        return $this->apiCall('orders/' . $order_id . '.json', [], 'GET', 'order');
    }





    /**
     * Cancel an order
     *
     * Cancel and refund an order using the amount property
     * POST /admin/api/2020-07/orders/450789469/cancel.json
     * {
     * "note": "Broke in shipping",
     * "amount": "10.00",
     * "currency": "USD"
     * }
     *
     * @param int   $order_id
     * @param array $data
     *
     * @return bool
     */
    public function cancelOrder(int $order_id, array $data = []): bool
    {
        return $this->apiCall("orders/{$order_id}/cancel.json", $data, 'POST', 'order');
    }


    /**
     * Api call to get all orders
     *
     * @param array         $query_params
     * @param callable|null $custom_function_pager
     *
     * @return bool
     */
    public function getOrders(array $query_params = [], ?callable $custom_function_pager = NULL): bool
    {
        return $this->apiCall(
            "orders.json",
            [
                'query' => array_replace_recursive(
                    [
                        'status' => 'any',
                    ],
                    $query_params
                ),
            ],
            'GET',
            'orders',
            $custom_function_pager
        );
    }



    /**
     * Api call to get all orders
     *
     * @param array         $query_params
     *
     * @return bool
     */
    public function getAbandonedCheckouts(array $query_params = [] ): bool
    {
        return $this->apiCall(
            "checkouts.json",
            [
                'query' => array_replace_recursive(
                    [
                        'status' => 'open',
                    ],
                    $query_params
                ),
            ],
            'GET',
            'checkouts'
        );
    }


    /**
     * Retrieve order risks
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function checkFraud( int $order_id ): bool
    {
        return $this->apiCall(
            "orders/{$order_id}/risks.json",
            [],
            'GET',
            'risks'
        );
    }




    /**
     * Get orders count
     *
     * @param array $query_params
     *
     * @return bool
     */
    public function getOrdersCount(array $query_params = []): bool
    {
        return $this->apiCall(
            "orders/count.json",
            [
                'query' => array_replace_recursive(
                    [
                        'status' => 'any',
                    ],
                    $query_params
                ),
            ]
        );
    }


    /**
     * Update single order
     *
     * @param int   $order_id
     * @param array $data
     *
     * @return bool
     */
    public function updateOrder(int $order_id, array $data): bool
    {
        return $this->apiCall(
            "orders/{$order_id}.json",
            [
                'order' => array_merge(['id' => $order_id], $data),
            ],
            'PUT',
            'order'
        );
    }





    /**
     * Close order
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function closeOrder(int $order_id): bool
    {
        return $this->apiCall("orders/{$order_id}/close.json", [], 'POST', 'order');
    }


    /**
     * Calculate refund for an order
     *
     * @param int   $order_id
     * @param array $data
     *
     * @return bool
     */
    public function calculateRefund(int $order_id, array $data): bool
    {
        return $this->apiCall(
            'orders/' . $order_id . '/refunds/calculate.json',
            [
                'refund' => $data,
            ],
            'POST',
            'refund'
        );
    }


    /**
     * Refunds specific items
     *
     * @param int   $order_id
     * @param array $data
     *
     * @return bool
     */
    public function refundItems(int $order_id, array $data): bool
    {
        return $this->apiCall(
            'orders/' . $order_id . '/refunds.json',
            [
                'refund' => $data,
            ],
            'POST',
            'refund'
        );
    }
}