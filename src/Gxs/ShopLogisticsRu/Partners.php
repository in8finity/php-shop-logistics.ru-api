<?php


namespace Gxs\ShopLogisticsRu;

use Yalesov\ArgValidator\ArgValidator;

/**
 * Class Partners
 * @package ShopLogisticsRu
 */
class Partners extends ApiClass
{
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
        ArgValidator::assert($fromCity, ['int', 'notEmpty']);
        ArgValidator::assert($toCity, ['int', 'null']);

        if (!$this->callMethod('get_all_couriers_partners', [
            'from_city_code' => $fromCity,
            'to_city_code' => $toCity
        ])
        ) {
            return false;
        }

        return $this->returnAsArrayList('partners', 'partner');
    }
}