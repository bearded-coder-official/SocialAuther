<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author Stanislav Protasevich
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 0.2
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
     * Server response
     *
     * @var array
     */
    protected $response = array();

    /**
     * User info
     *
     * @var array
     */
    protected $userInfo = array();

    /**
     * User profile
     *
     * @var \SocialUserProfile
     */
    protected $userProfile = null;

    /**
     * Last curl request http code
     *
     * @var integer
     */
    public $request_http_code = 200;


    /**
     * Prepare params for authentication url
     *
     * @return array Params
     */
    abstract protected function prepareAuthParams();

    /**
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    abstract protected function readUserProfile();

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
     * Get user name or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getName()
    {
        if (!array_key_exists('name', $this->userInfo))
        {
            $name = trim($this->getInfoVar('firstName'). ' ' .$this->getInfoVar('secondName'));
            $this->userInfo['name'] = !empty($name) ? $name : null;
        }

        return $this->userInfo['name'];
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
     * Get user sex or null.
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getSex()
    {
        if (array_key_exists('sex', $this->userInfo) && in_array($this->userInfo['sex'], array('male', 'female'))) {
            return $this->userInfo['sex'];
        }

        return null;
    }

    /**
     * Get user birthdate in format dd.mm.YYYY or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    final public function getBirthDate()
    {
        # getting caching data from userProfile
        $day = intval($this->userProfile->birthDay);
        $month = intval($this->userProfile->birthMonth);
        $year = intval($this->userProfile->birthYear);

        if ($day > 0 && $month > 0 && $year > 0)
        {
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
    final public function getBirthDay()
    {
        $day = intval($this->getInfoVar('birthDay'));
        return ($day > 0 && $day <= 31)? $day: null;
    }

    /**
     * Get user birth month or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return integer|null
     */
    final public function getBirthMonth()
    {
        $month = intval($this->getInfoVar('birthMonth'));
        return ($month > 0 && $month <= 12)? $month: null;
    }

    /**
     * Get user birth year or null if it is not set
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return integer|null
     */
    final public function getBirthYear()
    {
        $year = intval($this->getInfoVar('birthYear'));
        return ($year > 1900 && $year <= date('Y'))? $year: null;
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
     * Get user location (e.g. "Odessa, Ukraine").
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getLocation()
    {
        if (!array_key_exists('location', $this->userInfo))
        {
            if (array_key_exists('city', $this->userInfo) && $this->userInfo['city'] !== null) {
                $location[] = $this->userInfo['city'];
            }
            if (array_key_exists('country', $this->userInfo) && $this->userInfo['country'] !== null) {
                $location[] = $this->userInfo['country'];
            }

            $this->userInfo['location'] = isset($location) ? count($location) > 1 ? implode(', ', $location) : $location[0] : null;
        }

        return $this->userInfo['location'];
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
     * Redirect to provider authentication url or
     * authenticate and read user profile when redirected back.
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @throws Exception\InvalidArgumentException
     *
     * @return boolean
     */
    public function login()
    {
        if ($this->isRedirected() && !$this->haveErrors()) {
            return $this->readUserProfile();
        }
        elseif (!$this->haveErrors()) {
            $config = $this->prepareAuthParams();

            if (isset($config['auth_url']) && isset($config['auth_params'])) {
                $login_url = $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
                header('Location: '. $login_url);
                exit;
            }
            else {
                throw new Exception\InvalidArgumentException(
                    'Invalid params on '.ucfirst($this->provider).'::prepareAuthParams()'
                );
            }
        }
        return false;
    }

    /**
     * Checking for redirect from the provider
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return boolean
     */
    public function isRedirected()
    {
        return isset($_GET['code']) || $this->haveErrors();
    }

    /**
     * Checking for errors
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return boolean
     */
    public function haveErrors()
    {
        return isset($_GET['error']);
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
        curl_setopt($curl, CURLOPT_USERAGENT, 'SocialAuther v0.2 http://github.com/mervick/SocialAuther');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        $this->request_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($result && $parse) {
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
        curl_setopt($curl, CURLOPT_USERAGENT, 'SocialAuther v0.2 http://github.com/mervick/SocialAuther' );
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt($curl, CURLOPT_TIMEOUT, 30 );
        curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        $this->request_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($result && $parse) {
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
    final protected function getInfoVar($name)
    {
        $name = lcfirst($name);

        if (array_key_exists($name, $this->userInfo)) {
            return $this->userInfo[$name];;
        }

        return null;
    }

    /**
     * Parse user data and create user profile
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @param array $response
     * @throws Exception\InvalidArgumentException
     */
    final protected function parseUserData($response)
    {
        if (!is_array($response))
        {
            throw new Exception\InvalidArgumentException(
                 'Invalid param $response on '.__METHOD__
            );
        }

        $this->userInfo = array();
        $this->response = $response;

        foreach (array('id', 'name', 'firstName', 'secondName', 'sex', 'email', 'page', 'image', 'phone', 'location', 'country', 'city') as $key)
        {
            if (isset($this->fieldsMap[$key]) && isset($response[$this->fieldsMap[$key]])) {
                $this->userInfo[$key] = $response[$this->fieldsMap[$key]];
            }
        }

        $this->userProfile = new \SocialAuther\SocialUserProfile($this);
    }

    /**
     * Get user profile
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return \SocialUserProfile
     */
    final public function getUserProfile()
    {
        return $this->userProfile;
    }

}
