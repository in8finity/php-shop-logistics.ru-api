<?php


namespace ShopLogisticsRu;

use Heartsentwined\ArgValidator\ArgValidator;

/**
 * Class Partners
 * @package ShopLogisticsRu
 */
class Partners extends ApiClass
{
    /**
     * Partners constructor.
     *
     * @inheritdoc
     */
    public function __construct($apiKey, $environment = Api::ENV_PROD)
    {
        parent::__construct($apiKey, $environment);
    }

    /**
     * Get partners by city
     *
     * @param int $fromCity From city
     * @param int|null $toCity To city
     *
     * @return bool|array Return list of partners or false in case  of an error
     */
    public function getPartners($fromCity, $toCity = null)
    {
        ArgValidator::assert($fromCity, ['notEmpty']);
        ArgValidator::assert($toCity, ['int', 'null']);

        if (!$this->callMethod('get_all_couriers_partners', [
            'from_city_code' => $fromCity,
            'to_city_code' => $toCity
        ])) {
            return false;
        }

        return $this->answer->getData()['partners']['partner'];
    }
}