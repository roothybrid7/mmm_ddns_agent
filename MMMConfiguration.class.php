<?php

// load config
class MMMConfiguration
{
    static protected $instance = null;
    protected $loaded = false;
    protected $config = Array();
    private $ini_file = '';

    static public function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // Singleton
    protected function __construct() {}

    public function load($ini_file)
    {
        if (file_exists($ini_file))
        {
            $this->ini_file = $ini_file;

            try {
                $this->config = parse_ini_file($this->ini_file);
                $this->loaded = true;
            } catch (Exception $e) {
                throw new Exception("Config file parse error: $e->message");
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    public function item($key)
    {
        return ($this->config[$key] ? $this->config[$key] : null);
    }

    public function getConfig()
    {
        return $this->config;
    }
}
