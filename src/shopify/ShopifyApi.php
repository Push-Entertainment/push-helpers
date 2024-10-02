<?php

namespace Push\Shopify;

use Push\Functions\Json;
use Push\Shopify\Traits\AccessScopes;
use Push\Shopify\Traits\Checkouts;
use Push\Shopify\Traits\Collections;
use Push\Shopify\Traits\Fulfillments;
use Push\Shopify\Traits\Inventory;
use Push\Shopify\Traits\Metafields;
use Push\Shopify\Traits\Orders;
use Push\Shopify\Traits\Products;
use Push\Shopify\Traits\Store;
use Push\Shopify\Traits\Transactions;

/**
 * Class ShopifyApi
 *
 * @package Push\Shopify
 */
class ShopifyApi extends Base
{
    /**
     * https://shopify.dev/apps/metafields/types
     */
    public const META_TYPE_BOOLEAN                     = 'boolean';
    public const META_TYPE_COLOR                       = 'color';
    public const META_TYPE_DATE                        = 'date';
    public const META_TYPE_DATE_TIME                   = 'date_time';
    public const META_TYPE_DIMENSION                   = 'dimension';
    public const META_TYPE_FILE_REFERENCE              = 'file_reference';
    public const META_TYPE_JSON                        = 'json';
    public const META_TYPE_MULTI_LINE_TEXT_FIELD       = 'multi_line_text_field';
    public const META_TYPE_NUMBER_DECIMAL              = 'number_decimal';
    public const META_TYPE_NUMBER_INTEGER              = 'number_integer';
    public const META_TYPE_PAGE_REFERENCE              = 'page_reference';
    public const META_TYPE_PRODUCT_REFERENCE           = 'product_reference';
    public const META_TYPE_RATING                      = 'rating';
    public const META_TYPE_SINGLE_LINE_TEXT_FIELD      = 'single_line_text_field';
    public const META_TYPE_URL                         = 'url';
    public const META_TYPE_VARIANT_REFERENCE           = 'variant_reference';
    public const META_TYPE_VOLUME                      = 'volume';
    public const META_TYPE_WEIGHT                      = 'weight';

    public const META_TYPE_LIST_NUMBER_INTEGER         = 'list.number_integer';
    public const META_TYPE_LIST_NUMBER_DECIMAL         = 'list.number_decimal';
    public const META_TYPE_LIST_SINGLE_LINE_TEXT_FIELD = 'list.single_line_text_field';
    public const META_TYPE_LIST_PRODUCT_REFERENCE      = 'list.product_reference';


    /**
     * Order flow required bare minim scopes for fulfillments
     */
    public const ORDER_FULFILLMENT_SCOPES = [
        'read_orders',
        'write_orders',

        'read_fulfillments',
        'write_fulfillments',

        'read_merchant_managed_fulfillment_orders',
        //'write_merchant_managed_fulfillment_orders',

        'read_third_party_fulfillment_orders',
        //'write_third_party_fulfillment_orders',

        'read_assigned_fulfillment_orders',
        // 'write_assigned_fulfillment_orders'
    ];


    use AccessScopes;
    use Checkouts;
    use Collections;
    use Inventory;
    use Metafields;
    use Orders;
    use Fulfillments;
    use Products;
    use Store;
    use Transactions;


    /**
     * Get meta block
     *
     * @param int|null $id
     * @param string   $key
     * @param mixed    $value
     * @param string   $type
     * @param string   $namespace
     *
     * @return array
     * @throws \Exception
     */
    public static function metaPacket(
        ?int $id,
        string $key,
        mixed $value,
        string $type = 'json',
        string $namespace = 'global'
    ): array {

        $type_exists = false;

        $reflector = new \ReflectionClass(__CLASS__);
        foreach($reflector->getConstants() as $constant_name => $constant_value )
        {
            if( str_starts_with($constant_name, 'META_TYPE') && $constant_value === $type )
            {
                $type_exists = true;
                break;
            }
        }

        if( ! $type_exists )
        {
            throw new \Exception("Invalid metafield type provided: {$type}.");
        }

        if( strlen($key ) > 30 )
        {
            throw new \Exception("Metafield {$key}'s length exceeds 30 chars.");
        }

        if( strlen($namespace ) > 20 )
        {
            throw new \Exception("Metafield {$key}'s namespace length exceeds 20 chars.");
        }

        // Sanitize values
        if( $type === self::META_TYPE_NUMBER_INTEGER )
        {
            $value = (int)$value;
        }
        elseif( $type === self::META_TYPE_NUMBER_DECIMAL )
        {
            $value = (float)$value;
        }
        elseif( $type === self::META_TYPE_LIST_NUMBER_DECIMAL )
        {
            $value = array_map('floatval', $value );
        }
        elseif( $type === self::META_TYPE_LIST_NUMBER_INTEGER )
        {
            $value = array_map('intval', $value );
        }
        elseif( $type === self::META_TYPE_BOOLEAN && ! is_bool( $value ) )
        {
            $value = ( strtolower($value) === "true" ? true : $value );
            $value = ( strtolower($value) === "false" ? false : $value );
            $value = (bool) $value;
        }
        elseif( $type === self::META_TYPE_DATE )
        {
            $value = date('Y-m-d', strtotime( $value ) );
        }
        elseif( $type === self::META_TYPE_DATE_TIME )
        {
            $value = date('Y-m-d\TH:i:s', strtotime( $value ) );
        }


        return [
            "id"        => $id,
            "key"       => $key,
            "value"     => ( \is_object($value) || \is_array( $value )? Json::encode($value) : $value ),
            "type"      => $type,
            "namespace" => $namespace,
        ];
    }
}