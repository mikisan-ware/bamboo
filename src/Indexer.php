<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/22
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

class Indexer
{
    
    private static $instance;
    private $counter;
        
    public function __construct()
    {
        $this->counter  = 0;
    }
    
    public static function get(): int
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance->counter;
    }
    
    public static function increment(): void
    {
        self::$instance->counter++;
    }
    
    public static function reset(): void
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        self::$instance->counter    = 0;
    }
    
}
