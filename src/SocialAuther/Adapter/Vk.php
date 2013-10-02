<?php

namespace SocialAuther\Adapter;

class Vk extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->fieldsMap = array(
            'id'         => 'uid',
            'image'      => 'photo_big',
            'firstName'  => 'first_name',
            'secondName' => 'last_name',
        );

        $this->provider = 'vk';
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName()
    {
        $name = trim($this->getFirstName() . ' ' . $this->getSecondName());
        return !empty($name) ? $name : null;
    }

    /**
     * Get user social page or null if it is not set
     *
     * @return string|null
     */
    public function getPage()
    {
        if (isset($this->response['screen_name'])) {
            return 'http://vk.com/' . $this->response['screen_name'];
        }

        return null;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        if (isset($this->response['sex']) && in_array($this->response['sex'], array(1, 2), false)) {
            return $this->response['sex'] == 1 ? 'female' : 'male';
        }

        return null;
    }

    /**
     * Get user phone number
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getPhone()
    {
        if (isset($this->response['mobile_phone']) && !empty($this->response['mobile_phone'])) {
            $phone = $this->response['mobile_phone'];
        }

        elseif (isset($this->response['home_phone']) && !empty($this->response['home_phone'])) {
            $phone = $this->response['home_phone'];
        }

        if (isset($phone)) {
            $phone = explode('|', str_replace(array(',',';'), '|', $phone));

            if (preg_match('/^\+?[0-9 ()-]{7,}$/', $phone[0])) {
                return trim($phone[0]);
            }
        }

        return null;
    }

    /**
     * Get user location.
     *
     * @see \SocialAuther\Adapter\AbstractAdapter::getLocation()
     * @return string|null
     */
    public function getLocation()
    {
        if (array_key_exists('country', $this->userInfo)) {
            return $this->userInfo['location'];
        }
        $location = array();

        $country = $this->getCountry();
        $city = $this->getCity();

        if (!is_null($city)) {
        	$location[] = $city;
        }
        if (!is_null($country)) {
        	$location[] = $country;
        }

        return $this->userInfo['location'] = count($location) > 0 ? implode(', ', $location) : null;
    }

    /**
     * Get user country name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCountry()
    {
        if (array_key_exists('country', $this->userInfo)) {
            return $this->userInfo['country'];
        }
        elseif (isset($this->response['country']) && isset($this->response['token']['access_token']))
        {
            $params = array(
                'cids'         => $this->response['country'],
                'access_token' => $this->response['token']['access_token'],
                'lang'         => $this->lang
            );

            $countryInfo = $this->get('https://api.vk.com/method/places.getCountryById', $params);
            if (isset($countryInfo['response'][0]['name'])) {
                return $this->userInfo['country'] = $countryInfo['response'][0]['name'];
            }
        }

        return $this->userInfo['country'] = null;
    }

    /**
     * Get user city name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCity()
    {
        if (array_key_exists('city', $this->userInfo)) {
            return $this->userInfo['city'];
        }
        elseif (isset($this->response['city']) && isset($this->response['token']['access_token']))
        {
            $params = array(
                'cids'         => $this->response['city'],
                'access_token' => $this->response['token']['access_token'],
                'lang'         => $this->lang
            );

            $cityInfo = $this->get('https://api.vk.com/method/places.getCityById', $params);
            if (isset($cityInfo['response'][0]['name'])) {
                return $this->userInfo['city'] = $cityInfo['response'][0]['name'];
            }
        }

        return $this->userInfo['city'] = null;
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
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri
            );

            $tokenInfo = $this->get('https://oauth.vk.com/access_token', $params);
            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'uids'         => $tokenInfo['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big,city,country,contacts',
                    'access_token' => $tokenInfo['access_token'],
                    'lang'         => $this->lang
                );

                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);
                if (isset($userInfo['response'][0]['uid']))
                {
                    $this->parseUserData($userInfo['response'][0]);
                    $this->response['token'] = $tokenInfo;

                    if (isset($this->response['bdate'])) {
                        $birthDate = explode('.', $this->response['bdate']);
                        $this->userInfo['birthDay']   = isset($birthDate[0]) ? $birthDate[0] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[2]) ? $birthDate[2] : null;
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
            'auth_url'    => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'scope'         => 'notify',
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code'
            )
        );
    }
}