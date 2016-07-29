<?php


namespace Gxs\ShopLogisticsRu;

use Yalesov\ArgValidator\ArgValidator;

/**
 * Class Pickup
 * @package ShopLogisticsRu
 */
class Pickup extends ApiClass
{
    const ADD_ERROR_NO = 0;
    const ADD_ERROR_DATE_CLOSED = 43;
    const ADD_ERROR_PICKUP_EXISTS = 44;

    /**
     * Required data fields for add new pickup place
     *
     * @var array
     */
    private $requiredFieldsForAddPlace = [
        'address' => ['string', 'notEmpty'],
        'contact_person' => ['string', 'notEmpty'],
        'phone' => ['string', 'int'],
        'zabor_from_supplier' => ['int', 'min' => 0, 'max' => 1],
        'name_supplier' => 'string',
        'is_regular_days' => ['string', 'notEmpty'],
        'add_documents' => ['int', 'min' => 0, 'max' => 1],
        'time_from' => ['string', 'notEmpty'],
        'time_to' => ['string', 'notEmpty'],
        'delivery_speed' => ['string', 'notSet'],
        'comment' => ['string', 'notSet'],
        'metro' => ['string', 'int', 'notSet']
    ];

    /**
     * Add new pickup
     *
     * @param int $place Place code
     * @param string $date Delivery date
     *
     * @return bool|array Return pickup data or false in case of an error
     * @throws \InvalidArgumentException
     */
    public function add($place, $date)
    {
        ArgValidator::assert($place, ['int', 'notEmpty']);
        ArgValidator::assert($date, ['string', 'notEmpty']);

        $args = [
            'zabors' => [
                'zabor' => [
                    'zabor_places_code' => $place,
                    'delivery_date' => $date
                ]
            ]
        ];

        if (!$this->callMethod('add_zabor', $args)) {
            return false;
        }

        $answerPickupData = $this->answer['zabors']['zabor'];

        if (isset($answerPickupData['error_code'])
            && $answerPickupData['error_code'] !== self::ADD_ERROR_NO
        ) {
            $this->errorCode = (int)$answerPickupData['error_code'];

            return false;
        }

        return $answerPickupData;
    }

    /**
     * Add new pickup place
     *
     * @param array $placeData Place data
     * @param string $errorMessage Error message
     *
     * @return bool|string Return place code or false in case of an error
     */
    public function addPlace(array $placeData, &$errorMessage = '')
    {
        ArgValidator::arrayAssert($placeData, $this->requiredFieldsForAddPlace);
        unset($placeData['code']);

        return $this->_updatePlace($placeData, $errorMessage);
    }

    /**
     * Update pickap place data
     *
     * @param array $placeData
     * @param string $errorMessage
     *
     * @return bool
     */
    protected function _updatePlace(array $placeData, &$errorMessage)
    {
        if (!$this->callMethod('client_add_update_zabor_place', ['zabor_places' => ['zabor_place' => $placeData]])) {
            return false;
        }

        $answerPlaceData = $this->answer['zabor_places']['zabor_place'];

        if (!empty($answerPlaceData['msg'])) {
            $errorMessage = $answerPlaceData['msg'];

            return false;
        }

        return $answerPlaceData['code'];
    }

    /**
     * Update pickup place
     *
     * @param string $code Pickup place code
     * @param array $placeData Place data
     * @param string $errorMessage Error message
     *
     * @return bool|string Return place code or false in case of an error
     */
    public function updatePlace($code, array $placeData, &$errorMessage = '')
    {
        ArgValidator::assert($code, ['string', 'int', 'notEmpty']);
        ArgValidator::arrayAssert($placeData, $this->requiredFieldsForAddPlace);

        return $this->_updatePlace(array_merge($placeData, ['code' => $code]), $errorMessage);
    }
}