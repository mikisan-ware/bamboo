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

use \mikisan\core\util\EX;
use \mikisan\core\util\STR;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class From
{
    
    private $from   = [];
    private $join   = [];

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if($nums > 0)
        {
            $this->setFrom($args);
        }
        
        return $this;
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "from":
                
                return $this->from;
        }
        
        throw new BambooException("From では {$key} は取得できません。");
    }
    
    public function add($from): From
    {
        $this->add_piece($from);
        
        return $this;
    }
    
    private function setFrom(array $args) : void
    {
        foreach ($args as $from)
        {
            $this->add_piece($from);
        }
    }
    
    private function add_piece($from): From
    {
        if($from instanceof Query)
        {
            $this->from[]   = $from;
            return $this;
        }
        if(is_string($from))
        {
            $parts  = explode(",", $from);
            foreach($parts as $f)
            {
                $this->from[]   = trim($f);
            }
            return $this;
        }
        
        $type   = gettype($from);
        $value  = (string)$from;
        throw new BambooException("From に不正なデータが渡されました。[{$value}:{$type}]");
    }
    
    public function join($join, $type = Join::LEFT): From
    {
        if(is_string($join) || $join instanceof Query)
        {
            $this->join[]   = new Join($join, $type);
            return $this;
        }
        
        $object = gettype($join);
        $value  = (string)$join;
        throw new BambooException("Join に不正なデータが渡されました。[{$value}:{$object}]");
    }
    
    public function on(string ...$on): From
    {
        if(count($on) !== 2)
        {
            $nums   = count($on);
            throw new BambooException("on のパラメタ数が不正です。[要素数: {$nums}]");
        }
        $this->join[count($this->join) - 1]->on(...$on);
        return $this;
    }
    
    public function using(string ...$on): From
    {
        if(count($on) !== 1)
        {
            $nums   = count($on);
            throw new BambooException("using のパラメタ数が不正です。[要素数: {$nums}]");
        }
        $this->join[count($this->join) - 1]->using(...$on);
        return $this;
    }
    
    public function toSQL(): string
    {
        $from   = $this->build_from_sql();
        if(EX::empty($this->join))  { return $from; }
        
        $from   .= "\n" . $this->build_join_sql();
        return $from;
    }
    
    private function build_from_sql(): string
    {
        $array = [];
        foreach($this->from as $from)
        {
            $array[]    = is_string($from)
                                ? $this->sql_from_string($from)
                                : $from->toSQL(1)
                                ;
        }
        return STR::indent(implode(",\n", $array));
    }
    
    private function build_join_sql(): string
    {
        $array = [];
        foreach($this->join as $join)
        {
            $array[]    = $join->toSQL();
        }
        return implode("\n", $array);
    }
    
    private function sql_from_string(string $from): string
    {
        $parts  = DBUTIL::separateAlias($from);
        return (count($parts) === 1)
                    ? DBUTIL::wrapID($parts[0])
                    : DBUTIL::wrapID($parts[0]) . " AS " . DBUTIL::strip($parts[1])
                    ;
    }
    
}
