<?php

namespace Push\Shopify\Traits;


/**
 * Class Transactions
 *
 * @package Push\Shopify\Traits
 */
trait Transactions
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
     * Api call to get all products
     *
     * @param array         $query_params
     * @param callable|null $custom_function_pager
     *
     * @return bool
     */
    public function getPayouts(array $query_params = [], ?callable $custom_function_pager = NULL): bool
    {
        return $this->apiCall(
            "shopify_payments/payouts.json",
            ['query' => $query_params],
            'GET',
            'payouts',
            $custom_function_pager
        );
    }


    /**
     * Api call to get all products
     *
     * @param array         $query_params
     * @param callable|null $custom_function_pager
     *
     * @return bool
     */
    public function getPayoutsTransactions(array $query_params = [], ?callable $custom_function_pager = NULL): bool
    {
        return $this->apiCall(
            "shopify_payments/balance/transactions.json",
            ['query' => $query_params],
            'GET',
            'transactions',
            $custom_function_pager
        );
    }


    /**
     * Get order transactions
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function getTransactions(int $order_id): bool
    {
        return $this->apiCall('orders/' . $order_id . '/transactions.json', [], 'GET', 'transactions');
    }


}