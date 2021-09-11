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

use \mikisan\core\util\STR;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Join
{
    
    const LEFT = "LEFT", RIGHT = "RIGHT", INNER = "INNER", CROSS = "CROSS", FULL = "FULL";
    const ON = "ON", USING = "USING";
    
    private $join       = null;
    private $alias      = null;
    private $type       = self::LEFT;
    private $connect    = null;
    private $references = null;
    
    public function __construct($join, string $type)
    {
        $this->setType($type);
        $this->setJoin($join);
        
        return $this;
    }
    
    public function on(string $on1, string $on2): Join
    {
        $this->connect      = self::ON;
        $this->references   = [$on1, $on2];
        return $this;
    }
    
    public function using(string $on): Join
    {
        $this->connect      = self::USING;
        $this->references   = $on;
        return $this;
    }
    
    private function setType(string $type): void
    {
        switch(true)
        {
            case $type === Join::INNER:
            case $type === Join::LEFT:
            case $type === Join::RIGHT:
            case $type === Join::FULL:
            case $type === Join::CROSS:

                $this->type     = $type;
                return;
        }
        
        $object = gettype($type);
        $value  = (string)$type;
        throw new BambooException("Join の type に不正なデータが渡されました。[{$value}:{$object}]");
    }
    
    private function setJoin($join) : void
    {
        if(is_string($join))
        {
            $parts  = DBUTIL::separateAlias($join);
            $this->join     = $parts[0];
            $this->alias    = $parts[1] ?? null;
            return;
        }
        
        $type   = gettype($join);
        $value  = (string)$join;
        throw new BambooException("Join に不正なデータが渡されました。[{$value}:{$type}]");
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "join":
            case $key === "type";
            case $key === "alias";
            case $key === "connect":
                
                return $this->{$key};
        }
        
        throw new BambooException("Join では {$key} は取得できません。");
    }
    
    public function toSQL(): string
    {
        return $this->get_join_type() . "\n" . STR::indent($this->get_join_table()) . "\n" . $this->get_connect();
    }
    
    private function get_join_type(): string
    {
        switch(true)
        {
            case $this->type === Join::INNER:     return "INNER JOIN";
            case $this->type === Join::LEFT:      return "LEFT OUTER JOIN";
            case $this->type === Join::RIGHT:     return "RIGHT OUTER JOIN";
            case $this->type === Join::FULL:      return "FULL OUTER JOIN";
            case $this->type === Join::CROSS:     return "CROSS JOIN";
        }
    }
    
    private function get_join_table(): string
    {
        return DBUTIL::wrapID($this->join) . " AS " . DBUTIL::strip($this->alias);
    }
    
    private function get_connect(): string
    {
        switch(true)
        {
            case $this->connect === self::ON:
                return "ON " . DBUTIL::wrapID($this->references[0]) . " = " . DBUTIL::wrapID($this->references[1]);
                
            case $this->connect === self::USING:
            default:
                return "USING " . DBUTIL::wrapID($this->references);
        }
    }
    
}
