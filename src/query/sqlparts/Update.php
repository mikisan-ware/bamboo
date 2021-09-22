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
use \mikisan\core\basis\bamboo\Exp;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\basis\bamboo\Indexer;
use \mikisan\core\exception\BambooException;

class Update
{
    
    private $table  = "";
    private $update   = [];

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
            case $key === "update":
                
                return $this->{$key};
        }
        
        throw new BambooException("Updateでは {$key} は取得できません。");
    }
    
    public function table(string $table): Update
    {
        $this->table    = $table;
        return $this;
    }
    
    public function set(array ...$update): Update
    {
        foreach($update as $args)
        {
            $this->add_piece($args);
        }
        return $this;
    }
    
    private function add_piece(array $args): void
    {
        foreach ($args as $key => $value)
        {
            $this->update[$key] = $value;
        }
    }
    
    public function toSQL(): string
    {
        if(EX::empty($this->table))
        {
            throw new BambooException("UPDATE を行うテーブルが指定されていません。table(テーブル名) で指定してください。");
        }
        $sets   = [];
        foreach($this->update as $key => $value)
        {
            if($value instanceof Exp)
            {
                $sets[]     = DBUTIL::wrapID($key) . " = " . $value->toSQL();
                continue;
            }
            if($value instanceof Query)
            {
                $sets[]     = DBUTIL::wrapID($key) . " = (\n" . STR::indent($value->toSQL()) . "\n)";
                continue;
            }

            $sets[]     = DBUTIL::wrapID($key) . " = :" . DBUTIL::strip($key) . "_" . Indexer::get();
            Indexer::increment();
        }
        return STR::indent(implode(", ", $sets));
    }
    
}
