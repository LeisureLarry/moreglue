<?php

namespace MoreGlue\Symfony\Component\Routing;

class RouteMatches
{
    protected static $_singletonInstance = null;

    protected $_matches;

    public static function getInstance(array $matches)
    {
        if (self::$_singletonInstance === null) {
            self::$_singletonInstance = new RouteMatches($matches);
        }
        return self::$_singletonInstance;
    }

    protected function __construct($matches)
    {
        $this->_matches = $matches;
    }

    public function all()
    {
        return $this->_matches;
    }

    public function has($key)
    {
        $hasMatch = false;

        if (isset($this->_matches[$key])) {
            $hasMatch = true;
        }

        return $hasMatch;
    }

    public function get($key)
    {
        $match = null;
        if ($this->has($key)) {
            $match = $this->_matches[$key];
        }

        return $match;
    }
}