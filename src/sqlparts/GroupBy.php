<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/10
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class GroupBy
{
    
    private $group_by   = [];

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if($nums > 0)
        {
            $this->setGroupBy($args);
        }
        
        return $this;
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "group_by":
                
                return $this->group_by;
        }
        
        throw new BambooException("GroupByでは {$key} は取得できません。");
    }
    
    public function add($gb): GroupBy
    {
        $this->add_piece($gb);
        
        return $this;
    }
    
    private function setGroupBy(array $args) : void
    {
        foreach ($args as $gb)
        {
            $this->add_piece($gb);
        }
    }
    
    private function add_piece($gb)
    {
        if(is_string($gb) || ($gb instanceof Exp))
        {
            $this->group_by[]   = $gb;
            return;
        }
        
        $type   = gettype($gb);
        $value  = (string)$gb;
        throw new BambooException("GroupByに不正なデータが渡されました。[{$value}:{$type}]");
    }
    
    public function toSQL(): string
    {
        $array = [];
        foreach($this->group_by as $gb)
        {
            $array[]    = is_string($gb)
                                ? DBUTIL::wrapID($gb)
                                : $gb->toSQL()
                                ;
        }
        return implode(", ", $array);
    }
    
}
