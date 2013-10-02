<?php

namespace SocialAuther\Adapter;

class Mailru extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->fieldsMap = array(
            'id'         => 'uid',
            'email'      => 'email',
            'page'       => 'link',
            'image'      => 'pic_big',
            'firstName'  => 'first_name',
            'secondName' => 'last_name',
        );

        $this->provider = 'mailru';
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        if (isset($this->response['sex'])) {
            $result = $this->response['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
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
            if (isset($this->response['location']) && isset($this->response['location']['region']) && isset($this->response['location']['region']['name'])) {
                $location[] = $this->response['location']['region']['name'];
            }
            if (array_key_exists('country', $this->userInfo) && $this->userInfo['country'] !== null) {
                $location[] = $this->userInfo['country'];
            }

            $this->userInfo['location'] = isset($location) ? count($location) > 1 ? implode(', ', $location) : $location[0] : null;
        }

        return $this->userInfo['location'];
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
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'redirect_uri'  => $this->redirectUri
            );

            $tokenInfo = $this->post('https://connect.mail.ru/oauth/token', $params);

            if (isset($tokenInfo['access_token'])) {
                $sign = md5("app_id={$this->clientId}method=users.getInfosecure=1session_key={$tokenInfo['access_token']}{$this->clientSecret}");

                $params = array(
                    'method'       => 'users.getInfo',
                    'secure'       => '1',
                    'app_id'       => $this->clientId,
                    'session_key'  => $tokenInfo['access_token'],
                    'sig'          => $sign
                );

                $userInfo = $this->get('http://www.appsmail.ru/platform/api', $params);

                if (isset($userInfo[0]['uid'])) {
                    $this->parseUserData($userInfo[0]);

                    if (isset($this->response['location']) && is_array($this->response['location']))
                    {
                        if (isset($this->response['location']['country']) && isset($this->response['location']['country']['name']))
                            $this->userInfo['country'] = $this->response['location']['country']['name'];

                        if (isset($this->response['location']['city']) && isset($this->response['location']['city']['name']))
                            $this->userInfo['city'] = $this->response['location']['city']['name'];
                    }

                    if (isset($this->response['birthday']))
                    {
                        $birthDate = explode('.', $this->response['birthday']);
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
            'auth_url'    => 'https://connect.mail.ru/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'response_type' => 'code',
                'redirect_uri'  => $this->redirectUri
            )
        );
    }
}