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

use \mikisan\core\util\EX;
use \mikisan\core\util\STR;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Insert
{
    
    private $table  = "";
    private $insert   = [];

    public function __construct(string $table = "")
    {
        if(!EX::empty($table))
        {
            $this->table    = $table;
        }
        return $this;
    }
    
    public function __get(string $key)
    {
        switch(true)
        {
            case $key === "table":
            case $key === "insert":
                
                return $this->{$key};
        }
        
        throw new BambooException("Insertでは {$key} は取得できません。");
    }
    
    public function table(string $table): Insert
    {
        $this->table    = $table;
        return $this;
    }
    
    public function add(array ...$insert): Insert
    {
        foreach($insert as $args)
        {
            $this->add_piece($args);
        }
        return $this;
    }
    
    private function add_piece(array $args): void
    {
        foreach ($args as $key => $value)
        {
            $this->insert[$key] = $value;
        }
    }
    
    public function toSQL(): string
    {
        if(EX::empty($this->table))
        {
            throw new BambooException("INSERT を行うテーブルが指定されていません。table(テーブル名) で指定してください。");
        }
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
        if(EX::empty($this->table))
        {
            throw new BambooException("INSERT を行うテーブルが指定されていません。table(テーブル名) で指定してください。");
        }
        
        if(EX::empty($this->insert))    { return ""; }
        $params = [];
        foreach($this->insert as $key => $value)
        {
            if(is_int($key))    { $key = $value; }
            $params[]   = DBUTIL::wrapID($key);
        }
        return "(\n" . STR::indent(implode(", ", $params)) . "\n)";
    }
    
}
