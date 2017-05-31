<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther;

use SocialAuther\Adapter\AdapterInterface;
use SocialAuther\Exception\InvalidArgumentException;

class SocialAuther
{
    /**
     * Instance of Adapter
     *
     * @var AdapterInterface
     */
    protected  $adapter = null;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
        if (method_exists($this, $method)) {
            return $this->$method($params);
        }

        if (method_exists($this->adapter, $method)) {
            return $this->adapter->$method();
        }

        throw new InvalidArgumentException("Unknown method " . __CLASS__ . "->$method()");
    }
}
