<?php

namespace MosparoOsTicket;

class Filter
{
    protected static $instance = null;

    protected $filters = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
        throw new Exception('Cannot unserialize object because it is a singleton.');
    }

    public function addFilter($filter, $callable)
    {
        if (!isset($this->filters[$filter])) {
            $this->filters[$filter] = [];
        }

        $this->filters[$filter][] = $callable;
    }

    public function applyFilter($filter, $value, ...$additionalArgs)
    {
        if (!isset($this->filters[$filter]) || empty($this->filters[$filter])) {
            return $value;
        }

        foreach ($this->filters[$filter] as $callback) {
            $value = call_user_func_array($callback, array_merge([$value], $additionalArgs));
        }

        return $value;
    }
}