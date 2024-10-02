<?php

namespace Push\Shopify\Traits;

/**
 * Class Fulfillments
 *
 * @package Push\Shopify\Traits
 */
trait Fulfillments
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
     * Fulfill order
     *
     * @param array $order_data
     *
     * @return bool
     */
    public function fulfillmentCreate(array $order_data): bool
    {
        return $this->apiCall(
            "fulfillments.json",
            [
                'fulfillment' => $order_data,
            ],
            'POST',
            'fulfillment'
        );
    }


    /**
     * Fulfill order
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function fulfillmentsGet(int $order_id): bool
    {
        return $this->apiCall(
            "orders/{$order_id}/fulfillments.json",
            [],
            'GET',
            'fulfillments'
        );
    }


    /**
     * Get existing fulfilment services
     *
     *
     * {
     * "fulfillment_services": [
     * {
     * "id": 62429430082,
     * "name": "CIRKAY",
     * "email": "xxx@pushentertainment.com",
     * "service_name": "CIRKAY",
     * "handle": "cirkay",
     * "fulfillment_orders_opt_in": false,
     * "include_pending_stock": false,
     * "provider_id": 4,
     * "location_id": 73186083138,
     * "callback_url": null,
     * "tracking_support": false,
     * "inventory_management": false,
     * "admin_graphql_api_id": "gid://shopify/CustomFulfillmentService/62429430082",
     * "permits_sku_sharing": false
     * }
     * ]
     * }
     *
     *
     * @return bool
     */
    public function fulfillmentServicesGet(): bool
    {
        return $this->apiCall(
            "fulfillment_services.json",
            [
                "scope" => "all"
            ],
            'GET',
            'fulfillment_services'
        );
    }


    /**
     * Get back the fulfillment order id packets
     *
     * @param int $order_id
     *
     *
     *                     {
     * "fulfillment_orders": [
     * {
     * "id": 6176060375362,
     * "shop_id": 68114448706,
     * "order_id": 5237452833090,
     * "assigned_location_id": 73186083138,
     * "request_status": "unsubmitted",
     * "status": "open",
     * "supported_actions": [
     * "create_fulfillment"
     * ],
     * "destination": {
     * "id": 5805255688514,
     * "address1": "Str.",
     * "address2": null,
     * "city": "Targu",
     * "company": "Push",
     * "country": "United Kingdom",
     * "email": "xx@yahoo.com",
     * "first_name": "Gabriel",
     * "last_name": "xx",
     * "phone": null,
     * "province": "British Forces",
     * "zip": "341542"
     * },
     * "line_items": [
     * {
     * "id": 13840195780930,
     * "shop_id": 68114448706,
     * "fulfillment_order_id": 6176060375362,
     * "quantity": 1,
     * "line_item_id": 13705219113282,
     * "inventory_item_id": 46111015076162,
     * "fulfillable_quantity": 1,
     * "variant_id": 44062961631554
     * }
     * ],
     * "fulfill_at": "2023-01-27T10:00:00+00:00",
     * "fulfill_by": null,
     * "international_duties": null,
     * "fulfillment_holds": [],
     * "delivery_method": {
     * "id": 428407849282,
     * "method_type": "none",
     * "min_delivery_date_time": null,
     * "max_delivery_date_time": null
     * },
     * "created_at": "2023-01-27T10:02:22+00:00",
     * "updated_at": "2023-01-27T10:02:22+00:00",
     * "assigned_location": {
     * "address1": null,
     * "address2": null,
     * "city": null,
     * "country_code": "GB",
     * "location_id": 73186083138,
     * "name": "CIRKAY",
     * "phone": null,
     * "province": "England",
     * "zip": null
     * },
     * "merchant_requests": []
     * }
     * ]
     * }
     *
     *
     * @return bool
     */
    public function fulfilmentOrderIdGet(int $order_id): bool
    {
        return $this->apiCall(
            "orders/{$order_id}/fulfillment_orders.json",
            [],
            'GET',
            'fulfillment_orders'
        );
    }


    /**
     * Fulfill order before version 2022-07
     *
     * @param int   $order_id
     * @param array $data
     * @param bool  $notify_customer
     *
     * @return bool
     */
    public function fulfillOrderLegacy(int $order_id, array $data, bool $notify_customer = true ): bool
    {
        return $this->apiCall(
            "orders/{$order_id}/fulfillments.json",
            [
                'fulfillment' => array_merge(
                    [
                        'notify_customer' => $notify_customer
                    ],
                    $data
                ),
            ],
            'POST',
            'fulfillment'
        );
    }





    /**
     * Fulfill order
     *
     * @param int    $order_id
     * @param array  $request_line_items [ [ item_id, qty ] ]
     * @param array  $tracking_info [ number, url, company ]
     * @param bool   $notify_customer true|false
     * @param string $message "Package shipped."
     *
     * @return bool
     */
    public function fulfillOrder(
        int $order_id,
        array $request_line_items,
        array $tracking_info,
        bool $notify_customer = true,
        string $message = ""
    ): bool {
        if ($this->fulfilmentOrderIdGet($order_id))
        {
            $fulfillment_order_ids = $this->getResultsObject();

            if (!empty($fulfillment_order_ids))
            {
                $line_request = [];

                // Loop trough the generated fulfillment orders data @from Shopify
                foreach ($fulfillment_order_ids as $fulfillment_order_id)
                {
                    $f_order_id = $fulfillment_order_id->id;

                    // Loop trough each item to build mapping data
                    foreach ($fulfillment_order_id->line_items as $item)
                    {
                        // No specific items, fulfill all
                        if (empty($request_line_items))
                        {
                            self::throwErrorSentry( new \Exception("No requested line items to be fulfilled for order id {$order_id} on fulf order data ". \Push\Functions\Json::encode($fulfillment_order_ids) ));
                        }
                        else
                        {
                            foreach ($request_line_items as $line_item_request)
                            {
                                $line_item_request = (object)$line_item_request;

                                // Fulfillment qty is not zero and the line item id matches the one from the request
                                if ( (int)$item->line_item_id > 0 && (int)$line_item_request->id === (int)$item->line_item_id)
                                {
                                    $fulfilled_quantity = (int) min(
                                        $line_item_request->qty,
                                        $item->fulfillable_quantity
                                    );

                                    // Qty > 0
                                    if( $fulfilled_quantity > 0 )
                                    {
                                        $line_request[$f_order_id]["fulfillment_order_id"]                                = $f_order_id;
                                        $line_request[$f_order_id]["fulfillment_order_line_items"][$item->id]["id"]       = $item->id;
                                        $line_request[$f_order_id]["fulfillment_order_line_items"][$item->id]["quantity"] = $fulfilled_quantity;
                                    }

                                }
                            }
                        }
                    }

                    // Contains fulf order request by other provided and does not contain any of our items.
                    if( empty( $line_request[$f_order_id] ) )
                    {
                        continue;
                    }

                    $line_request[$f_order_id]["fulfillment_order_line_items"] = array_values(
                        $line_request[$f_order_id]["fulfillment_order_line_items"]
                    );
                }

                $line_request = array_values($line_request);

                if (empty($line_request))
                {
                    // No fulfill-able quantities found || manually mark
                    $this->setLastError( "No fulfill-able quantities found for order id {$order_id} on fulf order data ". \Push\Functions\Json::encode($fulfillment_order_ids));
                    self::throwErrorSentry( new \Exception( "No fulfill-able quantities found for order id {$order_id} on fulf order data ". \Push\Functions\Json::encode($fulfillment_order_ids) ));

                    return false;
                }

                return $this->apiCall(
                    "fulfillments.json",
                    [
                        "fulfillment" => [
                            "message"                         => $message,
                            "notify_customer"                 => $notify_customer,
                            "tracking_info"                   => $tracking_info,
                            "line_items_by_fulfillment_order" => $line_request
                        ]
                    ],
                    'POST',
                    'fulfillment'
                );

            }
        }

        return false;
    }
}