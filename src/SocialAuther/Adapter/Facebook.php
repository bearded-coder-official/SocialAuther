<?php

namespace SocialAuther\Adapter;

class Facebook extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->fieldsMap = array(
            'id'         => 'id',
            'email'      => 'email',
            'page'       => 'link',
            'firstName'  => 'first_name',
            'secondName' => 'last_name',
            'sex'        => 'gender',
            'name'       => 'name',
        );

        $this->provider = 'facebook';
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getImage()
    {
        if (isset($this->response['username'])) {
            return 'http://graph.facebook.com/' . $this->response['username'] . '/picture?type=large';
        }

        return null;
    }

    /**
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    protected function readUserProfile()
    {
        if (isset($_GET['code']))
        {
            $params = array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'client_secret' => $this->clientSecret,
                'code'          => $_GET['code']
            );

            parse_str($this->get('https://graph.facebook.com/oauth/access_token', $params, false), $tokenInfo);

            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token']))
            {
                $params = array(
                    'access_token' => $tokenInfo['access_token']
                );

                $userInfo = $this->get('https://graph.facebook.com/me', $params);

                if (isset($userInfo['id']))
                {
                    if ($this->lang !== 'en' && isset($userInfo['locale']) && $userInfo['locale'] !== 'en_EN')
                    {
                        $params['locale'] = $userInfo['locale'];
                        $userInfo2 = $this->get('https://graph.facebook.com/me', $params);

                        if (isset($userInfo2['id'])) {
                            $userInfo2['gender'] = $userInfo['gender'];
                            $userInfo = $userInfo2;
                        }
                    }

                    $this->parseUserData($userInfo);

                    if (isset($this->response['birthday'])) {
                        $birthDate = explode('/', $this->response['birthday']);
                        $this->userInfo['birthDay']   = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[0]) ? $birthDate[0] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[2]) ? $birthDate[2] : null;
                    }

                    $fql = array('q' => 'SELECT+current_location,+hometown_location+FROM+user+WHERE+uid='.$this->userInfo['id']);
                    $location = $this->get('https://graph.facebook.com/fql', array_merge($fql, $params));

                    if (is_array($location) && isset($location['data']) && isset($location['data'][0]))
                    {
                        if (isset($location['data'][0]['current_location']) && !empty($location['data'][0]['current_location'])) {
                            $location = $location['data'][0]['current_location'];
                        }
                        elseif (isset($location['data'][0]['hometown_location']) && !empty($location['data'][0]['hometown_location'])) {
                            $location = $location['data'][0]['hometown_location'];
                        }

                        if (isset($location))
                        {
                            if (isset($location['name']) && !empty($location['name']))
                                $this->userInfo['city'] = $location['name'];
                            elseif (isset($location['city']) && !empty($location['city']))
                                $this->userInfo['city'] = $location['city'];

                            if (isset($location['country']) && !empty($location['country']))
                                $this->userInfo['country'] = $location['country'];

                            $location = array_intersect_key($this->userInfo, array_flip(array('city', 'country')));
                            $this->userInfo['location'] = count($location) > 0 ? implode(', ', $location) : null;
                        }
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
            'auth_url'    => 'https://www.facebook.com/dialog/oauth',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'scope'         => 'email,user_birthday'
            )
        );
    }
}