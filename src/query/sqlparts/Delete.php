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

class Delete
{
    
    private $table  = "";

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
                
                return $this->{$key};
        }
        
        throw new BambooException("Deleteでは {$key} は取得できません。");
    }
    
    public function table(string $table): Delete
    {
        $this->table    = $table;
        return $this;
    }
    
    public function toSQL(): string
    {
        if(EX::empty($this->table))
        {
            throw new BambooException("DELETE を行うテーブルが指定されていません。table(テーブル名) で指定してください。");
        }
        return DBUTIL::wrapID($key);
    }
    
}
