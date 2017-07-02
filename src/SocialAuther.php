<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther;

use SocialAuther\Provider\AuthProviderInterface;
use SocialAuther\Exception\InvalidArgumentException;

class SocialAuther
{
    /**
     * Instance of Provider
     *
     * @var AuthProviderInterface
     */
    protected  $provider = null;

    /**
     * Constructor.
     *
     * @param AuthProviderInterface $provider
     */
    public function __construct(AuthProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Call either local or adapter's method
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (method_exists($this->provider, $method)) {
            return call_user_func_array([$this->provider, $method], $params);
        }

        throw new InvalidArgumentException("Unknown method " . __CLASS__ . "->$method()");
    }
}
