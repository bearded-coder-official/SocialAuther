<?php

namespace SocialAuther\Adapter;

/**
 * Twitter adapter
 *
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Twitter extends AbstractAdapter
{
    /**
     * Default request params,
     * build automaticly in getDefaultParams() method.
     *
     * @var array
     */
    protected $defaultParams = null;


    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->fieldsMap = array(
            'id'         => 'id',
            'image'      => 'profile_image_url',
            'location'   => 'location',
        );

        $this->provider = 'twitter';
    }

    /**
     * Get user social page url or null if it is not set
     *
     * @return string|null
     */
    public function getPage()
    {
        return isset($this->response['screen_name']) ? 'http://twitter.com/'.$this->response['screen_name'] : '';
    }

    /**
     * Parsing user name.
     * Used in getFirstName() and getSecondName() methods.
     * Return user name (e.g. "Andrey Izman")
     *
     * @return string|null
     */
    public function getName()
    {
        if (isset($this->response['name']) && !empty($this->response['name']))
        {
            if (!array_key_exists('firstName', $this->userInfo) || !array_key_exists('secondName', $this->userInfo))
            {
                if (strpos($this->response['name'], '_') !== false) {
                    $name = explode('_', $this->response['name']);
                    $this->response['name'] = str_replace('_', ' ', $this->response['name']);
                }
                else {
                    $name = explode(' ', $this->response['name']);
                }

                $this->userInfo['firstName'] = trim($name[0]);
                $this->userInfo['secondName'] = isset($name[1]) ? trim($name[1]) : null;
            }

            return $this->response['name'];
        }

        return null;
    }

    /**
     * Get user firstName
     *
     * @return string|null
     */
    public function getFirstName()
    {
        if (!array_key_exists('firstName', $this->userInfo)) {
            $this->getName();
        }

        return $this->getInfoVar('firstName');
    }

    /**
     * Get user secondName
     *
     * @return string|null
     */
    public function getSecondName()
    {
        if (!array_key_exists('secondName', $this->userInfo)) {
            $this->getName();
        }

        return $this->getInfoVar('secondName');
    }

    /**
     * Parsing user location data.
     * Used in getCountry() and getCity() methods.
     * Return user location string (e.g. "Odessa, Ukraine").
     *
     * @return string|null
     */
    public function getLocation()
    {
        if (array_key_exists('location', $this->userInfo) && !empty($this->userInfo['location']))
        {
            if (!array_key_exists('city', $this->userInfo) || !array_key_exists('country', $this->userInfo))
            {
                $loc = preg_replace('/[\x03-\x20]{2,}/sxSX', ' ', $this->userInfo['location']);
                $glue = ',';

                if (strpos($location, $glue) === false) {
                    $glue = ' ';
                }
                $loc = explode($glue, $loc);

                if (count($loc) <= 3) {
                    $this->userInfo['city'] = trim($loc[0]);
                    $this->userInfo['country'] = isset($loc[1]) ? isset($loc[2]) ? trim($loc[2]) : trim($loc[1]) : null;
                }
                else {
                    $this->userInfo['city'] = null;
                    $this->userInfo['country'] = null;
                }
            }

            return $this->userInfo['location'];
        }

        return $this->userInfo['location'] = null;
    }

    /**
     * Get user country name
     *
     * @return string|null
     */
    public function getCountry()
    {
        if (!array_key_exists('country', $this->userInfo)) {
            $this->getLocation();
        }

        return $this->getInfoVar('country');
    }

    /**
     * Get user city name
     *
     * @return string|null
     */
    public function getCity()
    {
        if (!array_key_exists('city', $this->userInfo)) {
            $this->getLocation();
        }

        return $this->getInfoVar('city');
    }

    /**
     * Overridden method for starting session.
     *
     * @see \SocialAuther\Adapter\AbstractAdapter::login()
     * @throws \Exception
     */
    public function login()
    {
        $sessionStatus = session_status();

        if ($sessionStatus === PHP_SESSION_NONE) {
            session_start();
        }
        elseif ($sessionStatus === PHP_SESSION_DISABLED) {
            throw new \Exception(
                'Fatal error in '.__CLASS__.' with message: '.
                '"Your server configuration don`t perform to use the session, enable it in php.ini".'
            );
            exit;
        }

        return parent::login();
    }

    /**
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    protected function readUserProfile()
    {
        if (!isset($_SESSION['oauth_token_secret'])) {
            return false;
        }

        # 3 step:  getting access_token
        $url = 'https://api.twitter.com/oauth/access_token';

        $params = array(
            'oauth_token' => $_GET['oauth_token'],
            'oauth_verifier' => $_GET['oauth_verifier'],
        );
        $params = array_merge($params, $this->getDefaultParams());

        $signature = urlencode($this->createSignature($url, $params, $_SESSION['oauth_token_secret'], 'GET'));
        $params['oauth_signature'] = $signature;
        ksort($params);

        unset($_SESSION['oauth_token_secret']);

        $response = $this->get($url, $params, false);
        parse_str($response, $tokenInfo);

        if (!is_array($tokenInfo) || count(array_diff(array('oauth_token', 'oauth_token_secret', 'screen_name'), array_keys($tokenInfo))) > 0)
        {
            return false;
        }

        # 4 step:  getting user data
        $url = 'https://api.twitter.com/1.1/users/show.json';

        $params = array(
            'oauth_token' => $tokenInfo['oauth_token'],
            'screen_name' => $tokenInfo['screen_name']
        );
        $params = array_merge($params, $this->getDefaultParams());

        $signature = urlencode($this->createSignature($url, $params, $tokenInfo['oauth_token_secret'], 'GET'));
        $params['oauth_signature'] = $signature;
        ksort($params);

        $userInfo = $this->get($url, $params, true);

        if (isset($userInfo['id']))
        {
            $this->parseUserData($userInfo);

            if (isset($this->response['birthday'])) {
                $birthDate = explode('.', $this->response['birthday']);
                $this->userInfo['birthDay']   = isset($birthDate[0]) ? $birthDate[0] : null;
                $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                $this->userInfo['birthYear']  = isset($birthDate[2]) ? $birthDate[2] : null;
            }

            return true;
        }

        return false;
    }

    /**
     * Getting request_token and save it into session.
     * Prepare params for authentication url.
     *
     * @return array
     * @throws \Exception
     */
    protected function prepareAuthParams()
    {
        # clear session token
        if (isset($_SESSION['oauth_token_secret'])) {
            unset($_SESSION['oauth_token_secret']);
        }

        # 1 step:  getting request_token
        $url = 'https://api.twitter.com/oauth/request_token';

        $params = array_merge(
            array(
                'oauth_callback' => urlencode($this->redirectUri)
            ),
            $this->getDefaultParams()
        );

        $signature = urlencode($this->createSignature($url, $params, '', 'GET'));
        $params['oauth_signature'] = $signature;
        ksort($params);

        $response = $this->get($url, $params, false);

        parse_str($response, $tokenInfo);

        if (!isset($tokenInfo['oauth_token']) || !isset($tokenInfo['oauth_token_secret'])) {
            throw new \Exception(
                'Invalid response returned from '.$url
            );
            exit;
        }

        $_SESSION['oauth_token_secret'] = $tokenInfo['oauth_token_secret'];

        # 2 step:  prepare auth url for redirect to authorize
        return array(
                'auth_url'    => 'https://api.twitter.com/oauth/authorize',
                'auth_params' => array(
                        'oauth_token' => $tokenInfo['oauth_token']
                )
        );
    }


    /**
     * Create protected signature.
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @return string
     */
    protected function createSignature($url, $params, $token_secret = '', $method = 'GET')
    {
        ksort($params);

        $request  = strtoupper($method).'&'.urlencode($url).'&';
        $request .= urlencode(urldecode(http_build_query($params)));

        return base64_encode(hash_hmac("sha1", $request, $this->clientSecret.'&'.$token_secret, true));
    }

    /**
     * Get default params
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        if (is_null($this->defaultParams) || empty($this->defaultParams))
        {
            $this->defaultParams = array(
                'oauth_version' => '1.0',
                'oauth_consumer_key' => $this->clientId,
                'oauth_nonce' => md5(uniqid(rand(), true)),
                'oauth_timestamp' => time(),
                'oauth_signature_method' => 'HMAC-SHA1'
            );
        }

        return $this->defaultParams;
    }


    /**
     * Checking for redirect from the provider.
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return boolean
     */
    public function isRedirected()
    {
        return isset($_GET['oauth_token']) || isset($_GET['oauth_verifier']) || $this->haveErrors();
    }

    /**
     * Checking for errors.
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return boolean
     */
    public function haveErrors()
    {
        return isset($_GET['denied']) || (isset($_GET['oauth_token']) xor isset($_GET['oauth_verifier']));
    }

}
