<?php

namespace SocialAuther\Adapter;

/**
 * Googleplus adapter
 *
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Googleplus extends AbstractAdapter
{
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
            'page'       => 'url',
            'sex'        => 'gender',
        );

        $this->provider = 'googleplus';
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName()
    {
        if (array_key_exists('displayName', $this->response) && !empty($this->response['displayName'])) {
            return $this->response['displayName'];
        }
        $name = trim($this->getFirstName() . ' ' . $this->getSecondName());
        return !empty($name) ? $name : null;
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        if (isset($this->response['name']) && isset($this->response['name']['givenName']))
        {
            return $this->response['name']['givenName'];
        }

        return null;
    }

    /**
     * Get user second name
     *
     * @return string|null
     */
    public function getSecondName()
    {
        if (isset($this->response['name']) && isset($this->response['name']['familyName']))
        {
            return $this->response['name']['familyName'];
        }

        return null;
    }

    /**
     * Get user image url or null if it is not set
     *
     * @return string|null
     */
    public function getImage()
    {
        if (isset($this->response['image']) && isset($this->response['image']['url']))
        {
            return (!empty($this->response['image']['url'])) ? $this->response['image']['url'] : null;
        }

        return null;
    }

    /**
     * Get user location data.
     * Used in getCountry() and getCity() methods.
     *
     * @return string|null
     */
    public function getLocation()
    {
        if (array_key_exists('location', $this->userInfo)) {
            return $this->userInfo['location'];
        }

        elseif (isset($this->response['placesLived']) && is_array($this->response['placesLived']))
        {
            foreach ($this->response['placesLived'] as $location)
            {
                if (isset($location['primary']) && $location['primary'] == 1)
                {
                    if (isset($location['value']))
                    {
                        $loc = preg_replace('/[\x03-\x20]{2,}/sxSX', ' ', $location['value']);
                        $glue = ',';

                        if (strpos($location, $glue) === false) {
                            $glue = ' ';
                        }
                        $loc = explode($glue, $loc);

                        if(count($loc) <= 3) {
                            $this->userInfo['city'] = trim($loc[0]);
                            $this->userInfo['country'] = isset($loc[1]) ? isset($loc[2]) ? trim($loc[2]) : trim($loc[1]) : null;
                        }
                        else{
                            $this->userInfo['city'] = null;
                            $this->userInfo['country'] = null;
                        }

                        return $this->userInfo['location'] = $location['value'];
                    }
                    break;
                }
            }
        }

        $this->userInfo['city'] = null;
        $this->userInfo['country'] = null;

        return $this->userInfo['location'] = null;
    }

    /**
     * Get user country name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
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
     * @author Andrey Izman <cyborgcms@gmail.com>
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
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    protected function readUserProfile()
    {
        if (isset($_GET['code'])) {
            $params = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code']
            );

            $tokenInfo = $this->post('https://accounts.google.com/o/oauth2/token', $params);

            if (isset($tokenInfo['access_token']) && isset($tokenInfo['id_token']))
            {
                $params = array('id_token' => $tokenInfo['id_token']);
                $validateToken = $this->get('https://www.googleapis.com/oauth2/v1/tokeninfo', $params);

                if (!isset($validateToken['email'])) {
                    return false;
                }

                $params = array('access_token' => $tokenInfo['access_token']);
                $userInfo = $this->get('https://www.googleapis.com/plus/v1/people/me', $params);

                if (isset($userInfo['kind']) && $userInfo['kind'] == 'plus#person')
                {
                    $this->parseUserData($userInfo);

                    $this->userInfo['email'] = $validateToken['email'];

                    if (isset($this->response['birthday']))
                    {
                        $birthDate = explode('-', $this->response['birthday']);
                        $this->userInfo['birthDay']   = isset($birthDate[2]) ? $birthDate[2] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[0]) ? $birthDate[0] : null;
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    protected function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'https://accounts.google.com/o/oauth2/auth',
            'auth_params' => array(
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.me'
            )
        );
    }
}