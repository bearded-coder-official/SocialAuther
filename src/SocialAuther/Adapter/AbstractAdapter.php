<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Language
     * Usage: Set field 'lang' in constructor array $param.
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
    protected $socialFieldsMap = array();

    /**
     * Storage for user info
     *
     * @var array
     */
    protected $userInfo = null;


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
    public function getSocialId()
    {
        return $this->getInfoVar('socialId');
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
    public function getSocialPage()
    {
        return $this->getInfoVar('socialPage');
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->getInfoVar('avatar');
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
     * Get user birthday in format dd.mm.YYYY or null if it is not set
     *
     * @return string|null
     */
    public function getBirthday()
    {
        return $this->getInfoVar('birthday');
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

    public function getUserInfoRaw()
    {
        return $this->userInfo;
    }

    protected function getInfoVar($name)
    {
        $name = lcfirst($name);
        if (isset($this->socialFieldsMap[$name])) {
            $name = $this->socialFieldsMap[$name];
        }
        if (!isset($this->userInfo[$name])) {
            return null;
        }
        return $this->userInfo[$name];
    }

    function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        if (strpos($name, 'get')===0) {
            $varName = substr($name, 3);
            return $this->getInfoVar($varName);
        }

        throw new \LogicException("method $name not defined in " . __CLASS__);
    }


}