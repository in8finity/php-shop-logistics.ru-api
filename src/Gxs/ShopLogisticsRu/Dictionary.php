<?php


namespace Gxs\ShopLogisticsRu;

use Yalesov\ArgValidator\ArgValidator;

/**
 * Class Dictionary
 * @package ShopLogisticsRu
 */
class Dictionary extends ApiClass
{
    /**
     * Get dictionary of cities
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getCities()
    {
        return $this->get('city', 'cities', 'city');
    }

    /**
     * Get dictionary by type
     *
     * @param string $type Dictionary type
     * @param string $answerRootKey Root key of answer array
     * @param string $answerItemKey Item key of answer array
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    protected function get($type, $answerRootKey, $answerItemKey)
    {
        ArgValidator::assert($type, ['string', 'notEmpty']);

        if (!$this->callMethod('get_dictionary', ['dictionary_type' => $type])) {
            return false;
        }

        return $this->returnAsArrayList($answerRootKey, $answerItemKey);
    }

    /**
     * Get dictionary of metro
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getMetro()
    {
        return $this->get('metro', 'metros', 'metro');
    }

    /**
     * Get dictionary of pickups
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getPickups()
    {
        return $this->get('pickup', 'pickups', 'pickup');
    }

    /**
     * Get dictionary of affiliates
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getAffiliate()
    {
        return $this->get('filials', 'filials', 'filial');
    }

    /**
     * Get dictionary of statuses
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getStatuses()
    {
        return $this->get('status', 'status_list', 'status');
    }

    /**
     * Get dictionary of partners
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getPartners()
    {
        return $this->get('partners', 'partners', 'partner');
    }

    /**
     * Get dictionary of states
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getStates()
    {
        return $this->get('oblast', 'oblast_list', 'oblast');
    }

    /**
     * Get dictionary of districts
     *
     * @return array|bool Return dictionary or false in case of an error
     */
    public function getDistricts()
    {
        return $this->get('district', 'district_list', 'district');
    }
}