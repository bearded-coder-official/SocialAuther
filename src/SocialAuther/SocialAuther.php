<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author Stanislav Protasevich
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther;

use SocialAuther\Adapter\AdapterInterface;

class SocialAuther
{
    /**
     * Adapter manager
     *
     * @var AdapterInterface
     */
    protected  $adapter = null;


    /**
     * Constructor.
     *
     * @param string $provider
     * @param array $config
     * @throws Exception\InvalidArgumentException
     * @author Andrey Izman <cyborgcms@gmail.com>
     */
    public function __construct($provider, $config)
    {
        $adapter = 'SocialAuther\\Adapter\\'.ucfirst(strtolower($provider));
        try {
            $this->adapter = new $adapter($config);

        }
        catch (Exception $e) {
            throw new Exception\InvalidArgumentException(
                'Unknown provider "'.$provider.'"'
            );
        }
    }

    /**
     * Call method authenticate() of adapter class
     *
     * @return bool
     */
    public function authenticate()
    {
        return $this->adapter->authenticate();
    }

    /**
     * Call method getAuthUrl() of adapter class
     *
     * @author Andrey Izman <cyborgcms@gmail.com>
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->adapter->getAuthUrl();
    }

    /**
     * Magic method to get SocialUser
     *
     * @param string $name
     * @return \SocialAuther\SocialUser
     * @throws Exception\LogicException
     * @author Andrey Izman <cyborgcms@gmail.com>
     */
    public function __get($name)
    {
        if ($name === 'user')
        {
            return $this->adapter->user;
        }

        throw new Exception\InvalidArgumentException("Property $name is not defined in " . __CLASS__);
    }

}