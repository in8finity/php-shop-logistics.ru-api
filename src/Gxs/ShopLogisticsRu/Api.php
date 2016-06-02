<?php


namespace Gxs\ShopLogisticsRu;


use Curl\Curl;
use Heartsentwined\ArgValidator\ArgValidator;
use LSS\Array2XML;
use LSS\XML2Array;
use Gxs\ShopLogisticsRu\Exception\AnswerException;

/**
 * Class Api
 * @package ShopLogisticsRu
 */
class Api
{
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';
    
    const API_KEY_TEST = '577888574a3e4df01867cd5ccc9f18a5';

    /**
     * Instance of Api class
     *
     * @var Api|null
     */
    private static $instance = null;

    /**
     * API Key
     *
     * @var string|null
     */
    protected $apiKey = null;

    /**
     * Object of Curl class
     *
     * @var Curl|null
     */
    protected $curl = null;

    /**
     * API Url
     *
     * @var null
     */
    protected $apiUrl = null;

    /**
     * Map of api classes
     *
     * @var array
     */
    private $apiClasses = [
        'delivery' => '\Gxs\ShopLogisticsRu\Delivery',
        'dictionary' => '\Gxs\ShopLogisticsRu\Dictionary',
        'mail_delivery' => '\Gxs\ShopLogisticsRu\MailDelivery',
        'partners' => '\Gxs\ShopLogisticsRu\Partners',
        'pickup' => '\Gxs\ShopLogisticsRu\Pickup',
        'products' => '\Gxs\ShopLogisticsRu\Products'
    ];

    /**
     * Api constructor.
     *
     * @param string $apiKey API Key
     * @param string $environment API environment
     */
    private function __construct($apiKey, $environment)
    {
        $this->curl = new Curl();
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setUserAgent('Mozilla/5.0 (X11; Linux x86_64; rv:46.0) Gecko/20100101 Firefox/46.0');
        $this->apiUrl = $this->getApiUrl($environment);
        $this->apiKey = $apiKey;
	}

    /**
     * Api destructor
     */
    public function __destruct()
    {
        $this->curl = null;
    }

    /**
     * Get API URL by environment
     *
     * @param string $environment Environment
     *
     * @return string URL
     */
    private function getApiUrl($environment)
    {
        switch ($environment) {
            case static::ENV_TEST:
                $url = 'https://test.client-shop-logistics.ru/index.php?route=deliveries/api';
                break;
            case static::ENV_PROD:
            default:
                $url = 'http://client-shop-logistics.ru/index.php?route=deliveries/api';
                break;
        }

        return $url;
    }

    /**
     * Prepare array for convert to xml
     *
     * @param string $method Method name
     * @param array $args Method arguments
     *
     * @return array Array for convert to xml
     */
    protected function prepareArrayForConvertToXml($method, array $args)
    {
        $array = [
            'function' => $method,
            'api_id' => $this->apiKey
        ];

        return array_merge($array, $args);
    }

    /**
     * Prepare xml for request
     *
     * @param string $method Method name
     * @param array $args Method arguments
     *
     * @return string XML fore request
     */
    protected function prepareXmlForRequest($method, array $args)
    {
        $arrayToXml = Array2XML::createXML('request', $this->prepareArrayForConvertToXml($method, $args));
        return $arrayToXml->saveXML();
    }

    /**
     * Parse answer xml and return ShopLogisticsRu\Answer object
     * 
     * @param string $xml Answer xml data
     *
     * @return Answer
     * @throws AnswerException
     * @throws \Exception
     */
    protected function parseAnswer($xml)
    {
        $xmlToArray = XML2Array::createArray($xml);

        if (!is_array($xmlToArray) || !isset($xmlToArray['answer']))
        {
            //TODO: Change error message
            throw new AnswerException('Empty data answer');
        }
        
        return new Answer((array)$xmlToArray['answer']);
    }

    /**
     * Get instance of Api class
     *
     * @param string $apiKey API Key
     * @param string $environment API Environment
     *
     * @return Api
     */
    public static function factory($apiKey, $environment = self::ENV_PROD)
    {
        ArgValidator::assert($apiKey, ['string', 'notEmpty']);
        ArgValidator::assert($environment, ['string', 'notEmpty']);

        if (null === self::$instance) {
            self::$instance = new self($apiKey, $environment);
        } else {
            if (self::$instance->apiKey !== $apiKey) {
                self::$instance = new self($apiKey, $environment);
            }
        }

        return self::$instance;
    }

    /**
     * Call method and get answer
     * 
     * @param string $method Method name
     * @param array $args Method arguments
     *
     * @return Answer
     * @throws AnswerException
     */
    public function callMethod($method, array $args = [])
    {
        ArgValidator::assert($method, ['string', 'notEmpty']);
        $xml = $this->prepareXmlForRequest($method, $args);
        $result = $this->curl->post($this->apiUrl, [
            'xml' => base64_encode($xml)
        ]);

        return $this->parseAnswer($result);
    }

    /**
     * Return new instance of api class
     *
     * @param string $apiClass Api class
     *
     * @return ApiClass|null
     */
    public function get($apiClass)
    {
        ArgValidator::assert($apiClass, ['string', 'notEmpty']);

        if (!isset($this->apiClasses[$apiClass])) {
            return null;
        }

        return new $this->apiClasses[$apiClass]($this);
    }
}