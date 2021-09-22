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

use \mikisan\core\util\STR;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Insert
{
    
    private $insert   = [];

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if($nums > 0)
        {
            $this->add_piece(...$args);
        }
        
        return $this;
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "insert":
                
                return $this->insert;
        }
        
        throw new BambooException("Insertでは {$key} は取得できません。");
    }
    
    public function add(array $insert): Insert
    {
        $this->add_piece($insert);
        
        return $this;
    }
    
    private function add_piece(array $args) : void
    {
        foreach ($args as $key => $value)
        {
            $this->insert[$key] = $value;
        }
    }
    
    public function toSQL(): string
    {
        $params = [];
        $values = [];
        $idx    = 0;
        foreach($this->insert as $key => $value)
        {
            $params[]   = DBUTIL::wrapID($key);
            $values[]   = ":" . DBUTIL::strip($key) . "_{$idx}";
            $idx++;
        }
        return "(\n" . STR::indent(implode(", ", $params)) . "\n) VALUES (\n" . STR::indent(implode(", ", $values)) . "\n)";
    }
    
    public function toSelectSQL(): string
    {
        if(EX::empty($this->insert))    { return ""; }
        $params = [];
        foreach($this->insert as $key => $value)
        {
            $params[]   = DBUTIL::wrapID($key);
        }
        return "(\n" . STR::indent(implode(", ", $params)) . "\n)";
    }
    
}
