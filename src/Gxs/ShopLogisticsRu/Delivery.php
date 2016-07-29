<?php


namespace Gxs\ShopLogisticsRu;


use Gxs\ShopLogisticsRu\Exception\NotImplementedException;
use Yalesov\ArgValidator\ArgValidator;

/**
 * Class Delivery
 * @package ShopLogisticsRu
 */
class Delivery extends ApiClass
{
    const TYPE_ALL = 'all';
    const TYPE_COURIER = 'courier';
    const TYPE_PICKUP = 'pickup';

    const ADD_ERROR_NO = 0;
    const ADD_ERROR_INVALID_DATA_FORMAT = 4;
    const ADD_ERROR_UPDATE = 5;

    const GET_STATUS_ERROR_ORDER_NOT_FOUND = 3;

    const CALC_PRICE_ERROR_INVALID_ARGUMENTS = 45;
    const CALC_PRICE_ERROR_REQUIRED_PICKUP_PLACE = 46;

    /**
     * Required delivery data fields for add
     *
     * @var array
     */
    private $requiredDeliveryFieldsForAdd = [
        'delivery_date' => ['string', 'notEmpty'],
        'picking_date' => ['string', 'notSet'],
        'date_transfer_to_store' => ['string', 'notEmpty'],
        'from_city' => ['int', 'string', 'notEmpty'],
        'to_city' => ['int', 'string', 'notEmpty'],
        'time_from' => ['string', 'notEmpty'],
        'time_to' => ['string', 'notEmpty'],
        'order_id' => ['string', 'int', 'notEmpty'],
        'metro' => ['int', 'string', 'notSet'],
        'address' => ['string', 'notEmpty'],
        'address_index' => ['int', 'notSet'],
        'contact_person' => ['string', 'notEmpty'],
        'phone' => ['string', 'int', 'notEmpty'],
        'phone_sms' => ['string', 'int', 'notSet'],
        'price' => ['float', 'int'],
        'ocen_price' => ['float', 'int'],
        'additional_info' => ['string', 'notSet'],
        'site_name' => 'string',
        'pickup_place' => ['int', 'notSet'],
        'zabor_places_code' => ['int', 'notSet'],
        'partial_ransom' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'prohibition_opening_to_pay' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'delivery_price_for_customer' => ['float', 'int', 'notSet'],
        'delivery_price_for_customer_required' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'delivery_price_porog_for_customer' => ['float', 'int', 'notSet'],
        'delivery_discount_for_customer' => ['float', 'int', 'notSet'],
        'delivery_discount_porog_for_customer' => ['float', 'int', 'notSet'],
        'return_shipping_documents' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'use_from_canceled' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'add_product_from_disct' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'number_of_place' => 'int',
        'delivery_speed' => ['string', 'notEmpty'],
        'shop_logistics_cheque' => ['int', 'min' => 0, 'max' => 1, 'notSet'],
        'delivery_partner' => ['int', 'notSet'],
        'barcodes' => ['array', 'notSet'],
        'products' => ['array', 'notSet']
    ];

    /**
     * Required products data fields for add
     *
     * @var array
     */
    private $requiredProductsFieldsForAdd = [
        'articul' => 'string',
        'name' => ['string', 'notEmpty'],
        'quantity' => 'float',
        'item_price' => 'float'
    ];

    /**
     * Required parameter fields for get tariffs
     *
     * @var array
     */
    private $requiredFieldsForTariffs = [
        'from_city' => 'int',
        'to_city' => 'int',
        'weight' => ['float', 'int', 'notSet'],
        'order_length' => ['float', 'int', 'notSet'],
        'order_width' => ['float', 'int', 'notSet'],
        'order_height' => ['float', 'int', 'notSet'],
        'num' => ['int', 'notSet'],
        'order_price' => ['float', 'int'],
        'ocen_price' => ['float', 'int'],
    ];

    /**
     * Add new delivery
     *
     * @param array $deliveryData Delivery data
     *
     * @return array|bool Return delivery data or false in case of an error
     */
    public function add(array $deliveryData)
    {
        ArgValidator::arrayAssert($deliveryData, $this->requiredDeliveryFieldsForAdd);

        foreach ($deliveryData['products'] as $product) {
            ArgValidator::arrayAssert($product['product'], $this->requiredProductsFieldsForAdd);
        }

        unset($deliveryData['code']);

        return $this->_update($deliveryData);
    }

    /**
     * Update delivery
     *
     * @param string|int $code Delivery code
     * @param array $deliveryData Delivery dat
     *
     * @return bool|array Return delivery data or false in case of an error
     */
    public function update($code, $deliveryData)
    {
        ArgValidator::assert($code, ['string', 'int']);
        ArgValidator::arrayAssert($deliveryData, $this->requiredDeliveryFieldsForAdd);

        if (isset($deliveryData['products'])) {
            foreach ($deliveryData['products'] as $product) {
                ArgValidator::arrayAssert($product['product'], $this->requiredProductsFieldsForAdd);
            }
        }

        return $this->_update(array_merge($deliveryData, ['code' => $code]));
    }

    /**
     * Update delivery
     *
     * @param array $deliveryData
     *
     * @return bool|array
     */
    private function _update(array $deliveryData)
    {
        if (!$this->callMethod('add_delivery', ['deliveries' => ['delivery' => $deliveryData]])) {
            return false;
        }

        $answerDeliveryData = $this->answer['deliveries']['delivery'];

        if ((isset($answerDeliveryData['error_code'])
            && $answerDeliveryData['error_code'] !== self::ADD_ERROR_NO)
        ) {
            $this->errorCode = (int)$answerDeliveryData['error_code'];

            return false;
        }

        return $answerDeliveryData;
    }

    /**
     * Get deliveries by filter
     *
     * @param array $filter Filter for deliveries
     *
     * @return bool|array Return deliveries list or false in case of an error
     */
    public function getDeliveries(array $filter)
    {
        ArgValidator::assert($filter, ['array', 'notEmpty']);
        ArgValidator::arrayAssert($filter, [
            'delivery_date' => ['string', 'notSet'],
            'delivery_added' => ['string', 'notSet'],
            'order_id' => ['string', 'int', 'notSet'],
            'status' => ['string', 'notSet'],
            'code' => ['string', 'notSet'],
            'start_date_added' => ['string', 'notSet'],
            'end_date_added' => ['string', 'notSet']
        ]);

        if (!$this->callMethod('get_deliveries', $filter)) {
            return false;
        }

        return $this->returnAsArrayList('deliveries', 'delivery');
    }

    /**
     * Get delivery status by code
     *
     * @param string $deliveryCode Delivery code
     *
     * @return bool|array Return delivery status data or false in case of an error
     */
    public function getStatus($deliveryCode)
    {
        ArgValidator::assert($deliveryCode, ['notEmpty']);

        if (!$this->callMethod('get_order_status', ['code' => $deliveryCode])) {
            return false;
        }

        return $this->answer['deliveries']['delivery'];
    }

    /**
     * Get list of delivery statuses data
     *
     * @param array $deliveryCodes List of delivery codes
     *
     * @return bool|array Return list of delivery statuses data or false in case of an error
     */
    public function getStatuses(array $deliveryCodes)
    {
        ArgValidator::assert($deliveryCodes, ['arrayOf', 'string']);

        if (!$this->callMethod('get_order_status_array', ['deliveries' => ['code' => $deliveryCodes]])) {
            return false;
        }

        return $this->returnAsArrayList('deliveries', 'delivery');
    }

    /**
     * Update site name
     *
     * @param int $code Code
     * @param string $siteName Site name
     *
     * @throws NotImplementedException
     */
    public function updateSiteName($code, $siteName)
    {
        throw new NotImplementedException(__CLASS__, __METHOD__);
    }

    /**
     * Calculate estimated delivery time and price
     *
     * @param array $parameters Parameters for calculate
     *
     * @return array|bool Return array with time and price or false in case of an error
     */
    public function calculateDeliveryTimeAndPrice(array $parameters)
    {
        ArgValidator::arrayAssert($parameters, array_merge($this->requiredFieldsForTariffs, [
            'tarifs_type' => ['int', 'min' => 1, 'max' => 2],
            'delivery_partner' => ['int', 'string', 'notSet'],
            'pickup_place' => ['int', 'notSet']
        ]));

        if (!$this->callMethod('get_delivery_price', $parameters)) {
            return false;
        }

        if ($this->answer['error_code'] !== self::ADD_ERROR_NO) {
            return false;
        }

        return $this->answer->toArray();
    }

    /**
     * Calculate estimated delivery time
     *
     * @param array $parameters Parameters for calculate
     *
     * @return bool|string Return estimated delivery time or false in case of an error
     */
    public function calculateEstimatedDeliveryTime(array $parameters)
    {
        $result = $this->calculateDeliveryTimeAndPrice($parameters);

        if (!$result) {
            return false;
        }

        return $result['srok_dostavki'];
    }

    /**
     * Calculate delivery price
     *
     * @param array $parameters Parameters for calculate
     *
     * @return bool|float Return delivery price or false in case of an error
     */
    public function calculateDeliveryPrice(array $parameters)
    {
        $result = $this->calculateDeliveryTimeAndPrice($parameters);

        if (!$result) {
            return false;
        }

        return $result['price'];
    }

    /**
     * Get delivery variants by parameters
     *
     * @param array $parameters Parameters
     *
     * @return bool|array Return delivery variants or false in case of an error
     */
    public function getVariants(array $parameters)
    {
        ArgValidator::arrayAssert($parameters, $this->requiredFieldsForTariffs);

        if (!$this->callMethod('get_deliveries_tarifs', $parameters)) {
            return false;
        }

        return $this->returnAsArrayList('tarifs', 'tarif');
    }

    /**
     * Get delivery tariffs
     *
     * @param array $parameters Parameters
     *
     * @return bool|array|null Return all delivery tariffs, null if result is empty or false in case of an error
     */
    public function getTariffs(array $parameters)
    {
        ArgValidator::arrayAssert($parameters, [
            'from_city_code' => 'int',
            'weight' => ['float', 'int', 'notEmpty'],
            'order_length' => ['float', 'int', 'notSet'],
            'order_width' => ['float', 'int', 'notSet'],
            'order_height' => ['float', 'int', 'notSet'],
            'delivery_type' => ['string', 'notEmpty'],
            'num' => ['int', 'notEmpty'],
            'max_price' => ['int', 'min' => 0, 'notEmpty']
        ]);

        if (!$this->callMethod('get_all_tarifs', $parameters)) {
            return false;
        }

        return $this->returnAsArrayList('tarifs', 'tarif');
    }
}