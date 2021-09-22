<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\exception\BambooException;

class Limit
{
    
    private $offset;
    private $count;

    public function __construct()
    {
        $nums   = func_num_args();
        $args   = func_get_args();
        
        if ($nums > 0)
        {
            $this->setLimit($nums, $args);
        }
    }
    
    /**
     * setter
     * 
     * @param   string  $key
     * @param   int     $val
     * @return  void
     * @throws  BambooException
     */
    public function __set(string $key, int $val): void
    {
        switch(true)
        {
            case $key === "offset":
            case $key === "count":
                
                $this->$key = $val;
                return;
        }
        
        throw new BambooException("Limitでは {$key} は設定できません。");
    }
    
    /**
     * getter
     * 
     * @param   string  $key
     * @return  int
     * @throws  BambooException
     */
    public function __get(string $key)
    {
        switch(true)
        {
            case $key === "offset":
            case $key === "count":
                
                return $this->$key;
        }
        
        throw new BambooException("Limitでは {$key} は取得できません。");
    }
    
    public function setCount(int $count): Limit
    {
        $this->count    = $count;
        return $this;
    }
    
    public function setOffset(int $offset): Limit
    {
        $this->offset   = $offset;
        return $this;
    }
    
    private function setLimit(int $nums, array $args): void
    {
        if ($nums > 2)
        {
            throw new BambooException("Limitに不正な数のパラメターが渡されました。[要素数: {$nums}]");
        }
        if (!is_int($args[0]))
        {
            $type   = gettype($args[0]);
            $value  = (string)$args[0];
            throw new BambooException("Limitの第一引数に数値以外が渡されました。[{$value}:{$type}]");
        }
        if ($nums === 2 && !is_int($args[1]))
        {
            $type   = gettype($args[1]);
            $value  = (string)$args[1];
            throw new BambooException("Limitの第二引数に数値以外が渡されました。[{$value}:{$type}]");
        }

        ($nums === 1)
                ? $this->set_count($args)
                : $this->set_count_and_limit($args)
                ;
    }
    
    private function set_count($args): void
    {
        $this->count    = $args[0];
    }
    
    private function set_count_and_limit($args): void
    {
        $this->count    = $args[0];
        $this->offset   = $args[1];
    }
    
    public function toSQL(): string
    {
        return isset($this->offset)
                    ? "{$this->count} OFFSET {$this->offset}"
                    : "{$this->count}"
                    ;
    }
}
