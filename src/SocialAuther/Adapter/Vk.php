<?php

namespace SocialAuther\Adapter;

class Vk extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'uid',
            'avatar'     => 'photo_big',
            'birthday'   => 'bdate',
            'token'      => 'token',
            'firstName'  => 'first_name',
            'secondName' => 'last_name',
            'phone'      => 'mobile_phone',
            'country'    => 'country_name',
            'city'       => 'city_name',
        );

        $this->provider = 'vk';
    }

    /**
     * Get user social page or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;

        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
    }

    /**
     * Get user phone number
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getPhone()
    {
        if (isset($this->userInfo['mobile_phone']) && !empty($this->userInfo['mobile_phone'])) {
            $phone = $this->userInfo['mobile_phone'];
        }

        elseif (isset($this->userInfo['home_phone']) && !empty($this->userInfo['home_phone'])) {
            $phone = $this->userInfo['home_phone'];
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
     * Get user country name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCountry()
    {
        if (isset($this->userInfo['country_name'])) {
            return $this->userInfo['country_name'];
        }

        $result = null;

        if (isset($this->userInfo['country']) && isset($this->userInfo['token']['access_token']))
        {
            $params = array(
                'cids'         => $this->userInfo['country'],
                'access_token' => $this->userInfo['token']['access_token'],
                'lang'         => $this->lang
            );

            $countryInfo = $this->get('https://api.vk.com/method/places.getCountryById', $params);
            if (isset($countryInfo['response'][0]['name'])) {
                $result = $this->userInfo['country_name'] = $countryInfo['response'][0]['name'];
            }
        }

        return $result;
    }

    /**
     * Get user city name
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string|null
     */
    public function getCity()
    {
        if (isset($this->userInfo['city_name'])) {
            return $this->userInfo['city_name'];
        }

        $result = null;

        if (isset($this->userInfo['city']) && isset($this->userInfo['token']['access_token']))
        {
            $params = array(
                'cids'         => $this->userInfo['city'],
                'access_token' => $this->userInfo['token']['access_token'],
                'lang'         => $this->lang
            );

            $cityInfo = $this->get('https://api.vk.com/method/places.getCityById', $params);
            if (isset($cityInfo['response'][0]['name'])) {
                $result = $this->userInfo['city_name'] = $cityInfo['response'][0]['name'];
            }
        }

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        $result = false;

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
                if (isset($userInfo['response'][0]['uid'])) {
                    $this->userInfo = $userInfo['response'][0];
                    $this->userInfo['token'] = $tokenInfo;
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
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