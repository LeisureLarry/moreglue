<?php

namespace MoreGlue\Doctrine\Tools\Console\Helper;

use Symfony\Component\Console\Helper\Helper;

class ArrayHelper extends Helper
{
    /**
     * Helper Name
     * @var Name
     */
    protected $_name;

    /**
     * Doctrine Fixtures Path
     * @var Path
     */
    protected $_args;

    /**
     * Constructor
     *
     * @param Array $args Arguments
     */
    public function __construct($name, Array $args)
    {
        $this->_name = $name;
        $this->_args = $args;
    }

    /**
     * Retrieves Doctrine Database Connection
     *
     * @return Connection
     */
    public function getArg($key)
    {
        $arg = null;
        if (isset($this->_args[$key])) {
            $arg = $this->_args[$key];
        }
        return $arg;
    }

    /**
     * @see Helper
     */
    public function getName()
    {
        return $this->_name;
    }
}
