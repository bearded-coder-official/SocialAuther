<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once __DIR__.'/../vendor/autoload.php';

use SocialAuth\SocialAuth;

class SocialAuthExample
{
    // Social Auth settings
    protected $config = array(
        SocialAuth::PROVIDER_VKONTAKTE => array(
            'client_id'     => '6097024',
            'client_secret' => 'Fzo6IqM8fMczAXycXpFl',
            'redirect_uri'  => 'http://localhost/examples/auth.php?provider=' . SocialAuth::PROVIDER_VKONTAKTE,
        ),
    );

    /**
     *
     */
    public function run()
    {
        $auth = new SocialAuth($this->config);

        if (!$auth->initProvider(isset($_GET['provider']) ? $_GET['provider'] : null)) {
            // can't initialize provider from specified _GET params
            // this means no auth params provided - want to display list of available auth URLs
            $this->printAuthList($auth->getProvidersInfo());

        } elseif ($auth->authenticate($_GET)) {
            // can initialize provider from specified _GET params
            // and auth went OK
            $this->printAuthInfo($auth->getUserInfo());

        } else {
            // can initialize provider from specified _GET params
            // and auth FAILED
            echo "Auth failed";
        }
    }

    /**
     * @param array $info
     */
    protected function printAuthList(array $info)
    {
        foreach ($info as $id => $provider) {
            echo "<p>$id : <a href='{$provider['url']}'>Auth via {$provider['name']}</a></p>";
        }
    }

    /**
     * @param SocialAuth $auther
     */
    protected function printAuthInfo(array $info)
    {
        if (!is_null($info[SocialAuth::ATTRIBUTE_ID]))
            echo "User ID: {$info[SocialAuth::ATTRIBUTE_ID]}<br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_NAME]))
            echo "Name: {$info[SocialAuth::ATTRIBUTE_NAME]}<br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_EMAIL]))
            echo "Email: {$info[SocialAuth::ATTRIBUTE_EMAIL]}<br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_PAGE_URL]))
            echo "Page URL: <a href='{$info[SocialAuth::ATTRIBUTE_PAGE_URL]}'>{$info[SocialAuth::ATTRIBUTE_PAGE_URL]}</a><br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_SEX]))
            echo "Gender: {$info[SocialAuth::ATTRIBUTE_SEX]}<br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_BIRTHDAY]))
            echo "Birthday: {$info[SocialAuth::ATTRIBUTE_BIRTHDAY]}<br />";

        if (!is_null($info[SocialAuth::ATTRIBUTE_AVATAR_URL]))
            echo "<img src='{$info[SocialAuth::ATTRIBUTE_AVATAR_URL]}' /><br />";
    }
}

$example = new SocialAuthExample();
$example->run();
