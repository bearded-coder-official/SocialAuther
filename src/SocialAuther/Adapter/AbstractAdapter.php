<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author Stanislav Protasevich
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Language
     *
     * @var string
     */
    protected $lang = 'en';

    /**
     * Social Client ID
     *
     * @var string null
     */
    protected $clientId = null;

    /**
     * Social Client Secret
     *
     * @var string null
     */
    protected $clientSecret = null;

    /**
     * Social Redirect Uri
     *
     * @var string null
     */
    protected $redirectUri = null;

    /**
     * Name of auth provider
     *
     * @var string null
     */
    protected $provider = null;

    /**
     * Social Fields Map for universal keys
     *
     * @var array
     */
    protected $fieldsMap = array();

    /**
     * User info
     *
     * @var array
     */
    protected $user = array();

    /**
     * Server response
     *
     * @var array
     */
    protected $response = array();


    abstract public function prepareAuthParams();

    /**
     * Constructor
     *
     * @param array $config
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($config)
    {
        if (!is_array($config))
            throw new Exception\InvalidArgumentException(
                __METHOD__ . ' expects an array with keys: `client_id`, `client_secret`, `redirect_uri`'
            );
        else {
            if (isset($config['lang']))
                $this->lang = $config['lang'];
        }

        foreach (array('client_id', 'client_secret', 'redirect_uri') as $param) {
            if (!array_key_exists($param, $config)) {
                throw new Exception\InvalidArgumentException(
                    __METHOD__ . ' expects an array with key: `' . $param . '`'
                );
            } else {
                $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
                $this->$property = $config[$param];
            }
        }
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getInfoVar('id');
    }

    /**
     * Get user email or null if it is not set
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getInfoVar('email');
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getInfoVar('firstName');
    }

    /**
     * Get user second name
     *
     * @return string|null
     */
    public function getSecondName()
    {
        return $this->getInfoVar('secondName');
    }

    /**
     * Get user social page url or null if it is not set
     *
     * @return string|null
     */
    public function getPage()
    {
        return $this->getInfoVar('page');
    }

    /**
     * Get user big image url or null if it is not set
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->getInfoVar('image');
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        return $this->getInfoVar('sex');
    }

    /**
     * Get user birthdate in format dd.mm.YYYY or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getBirthDate()
    {
        $day = $this->getBirthDay();
        $month = $this->getBirthMonth();
        $year = $this->getBirthYear();

        if (!is_null($day) || !is_null($month) || !is_null($year)) {
            return sprintf('%02d.%02d.%04d', $day, $month, $year);
        }
        return null;
    }

    /**
     * Get user birth day or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return integer|null
     */
    public function getBirthDay()
    {
        return $this->getInfoVar('birthDay');
    }

    /**
     * Get user birth month or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return integer|null
     */
    public function getBirthMonth()
    {
        return $this->getInfoVar('birthMonth');
    }

    /**
     * Get user birth year or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return integer|null
     */
    public function getBirthYear()
    {
        return $this->getInfoVar('birthYear');
    }

    /**
     * Get user phone number
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getPhone()
    {
        return $this->getInfoVar('phone');
    }

    /**
     * Get user country name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCountry()
    {
        return $this->getInfoVar('country');
    }

    /**
     * Get user city name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCity()
    {
        return $this->getInfoVar('city');
    }

    /**
     * Return name of auth provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get authentication url
     *
     * @return string
     */
    public function getAuthUrl()
    {
        $config = $this->prepareAuthParams();

        return $result = $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
    }

    /**
     * Make post request and return result
     *
     * @param string $url
     * @param string $params
     * @param bool $parse
     * @return array|string
     */
    protected function post($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if ($parse) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * Make get request and return result
     *
     * @param $url
     * @param $params
     * @param bool $parse
     * @return mixed
     */
    protected function get($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if ($parse) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * Get user data field
     *
     * @param string $name
     * @return mixed|NULL
     */
    protected function getInfoVar($name)
    {
        $name = lcfirst($name);
        if (isset($this->user[$name])) {
            return $this->user[$name];;
        }
        return null;
    }

    /**
     * Parse user data
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @param array $response
     */
    protected function parseUserData($response)
    {
        $this->user = array();
        $this->response = $response;

        foreach (array('id', 'firstName', 'secondName', 'sex', 'email', 'page', 'image', 'phone', 'country', 'city') as $key)
        {
            if (isset($this->fieldsMap[$key]) && isset($response[$this->fieldsMap[$key]])) {
                $this->user[$key] = $response[$this->fieldsMap[$key]];
            }
        }
    }

    /**
     * Magic method to getting user data fields as properties.
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @param string $name field name
     * @throws \LogicException
     * @return mixed
     */
    function __get($name)
    {
        if ($name === 'provider')
            return $this->provider;

        $method = 'get'.ucfirst($name);

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        }

        throw new \LogicException("Property $name not defined in " . __CLASS__);
    }

}
