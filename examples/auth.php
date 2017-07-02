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

use SocialAuther\Provider\AuthProviderBase;
use SocialAuther\Provider\AuthProviderFactory;
use SocialAuther\SocialAuther;

class AuthExample
{
    // Social Auth settings
    protected $config = array(
        AuthProviderBase::PROVIDER_VKONTAKTE => array(
            'client_id'     => '6097024',
            'client_secret' => 'Fzo6IqM8fMczAXycXpFl',
            'redirect_uri'  => 'http://localhost/examples/auth.php?provider=' . AuthProviderBase::PROVIDER_VKONTAKTE,
        ),
    );

    /**
     *
     */
    public function run()
    {
        // build array of providers
        $providers = array();
        foreach ($this->config as $provider => $settings) {
            $providers[$provider] = AuthProviderFactory::create($provider, $settings);
        }

        $provider = isset($_GET['provider']) ? $_GET['provider'] : null;
        if (array_key_exists($provider, $providers)) {
            $provider = $providers[$provider];
        } else {
            $provider = null;
        }

        if (empty($provider)) {
            $this->printAuthList($providers);
        } else {

            $auther = new SocialAuther($provider);

            if ($auther->authenticate($_GET)) {
                $this->printAuthInfo($auther);
            } else {
                echo "Auth failed";
            }
        }
    }

    /**
     * @param array $providers
     */
    protected function printAuthList(array $providers)
    {
        foreach ($providers as $provider) {
            echo '<p><a href="' . $provider->getAuthenticationUrl() . '">Auth via ' . $provider->getProvider() . '</a></p>';
        }
    }

    /**
     * @param SocialAuther $auther
     */
    protected function printAuthInfo(SocialAuther $auther)
    {
        if (!is_null($auther->getId()))
            echo "User ID: " . $auther->getId() . '<br />';

        if (!is_null($auther->getName()))
            echo "Name: " . $auther->getName() . '<br />';

        if (!is_null($auther->getEmail()))
            echo "Email: " . $auther->getEmail() . '<br />';

        if (!is_null($auther->getPageUrl()))
            echo "Page URL: " . $auther->getPageUrl() . '<br />';

        if (!is_null($auther->getSex()))
            echo "Gender: " . $auther->getSex() . '<br />';

        if (!is_null($auther->getBirthday()))
            echo "Birthday: " . $auther->getBirthday() . '<br />';

        // аватар пользователя
        if (!is_null($auther->getAvatarUrl()))
            echo '<img src="' . $auther->getAvatarUrl() . '" />'; echo "<br />";
    }
}

$example = new AuthExample();
$example->run();
