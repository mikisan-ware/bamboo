<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/11
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Select
{
    
    private $select   = [];

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if($nums > 0)
        {
            $this->setSelect($args);
        }
        
        return $this;
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "select":
                
                return $this->select;
        }
        
        throw new BambooException("Select では {$key} は取得できません。");
    }
    
    public function add($gb): Select
    {
        $this->add_piece($gb);
        
        return $this;
    }
    
    private function setSelect(array $args) : void
    {
        foreach ($args as $sel)
        {
            $this->add_piece($sel);
        }
    }
    
    private function add_piece($sel)
    {
        if($sel instanceof Exp)
        {
            $this->select[]   = $sel;
            return;
        }
        if(is_string($sel))
        {
            $parts  = explode(",", $sel);
            foreach($parts as $p)
            {
                $this->select[]   = DBUTIL::separateAlias($p);
            }
            return;
        }
        
        $type   = gettype($sel);
        $value  = (string)$sel;
        throw new BambooException("Select に不正なデータが渡されました。[{$value}:{$type}]");
    }
    
    public function toSQL(): string
    {
        foreach($this->select as $sel)
        {
            $array[]    = is_array($sel)
                                ? $this->to_sql_with_alias($sel)
                                : $sel->toSQL()
                                ;
        }
        return implode(", ", $array);
    }
    
    private function to_sql_with_alias(array $sel): string
    {
        return (count($sel) === 1)
                    ? DBUTIL::wrapID($sel[0])
                    : DBUTIL::wrapID($sel[0]) . " AS " . DBUTIL::strip($sel[1])
                    ;
    }
    
}
