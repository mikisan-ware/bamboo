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

use \mikisan\core\basis\bamboo\Op;
use \mikisan\core\basis\bamboo\Where;
use \mikisan\core\basis\bamboo\Query;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;
use \mikisan\core\util\EX;

class Piece
{
    
    private $key;
    private $value;
    private $type   = "MATCH";
    private $extra  = null;

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if ($nums > 0)
        {
            $this->setPiece(...$args);
        }
    }

    public function __get(string $key)
    {
        switch(true)
        {
            case $key === "key":
            case $key === "value":
            case $key === "type":
            case $key === "extra":
                return $this->{$key};
        }
        
        throw new BambooException("Pieceでは {$key} は取得できません。");
    }
    
    public function setPiece(): Piece
    {
        $nums = func_num_args();
        $args = func_get_args();
        
        if($nums === 2 && ($args[1] !== Op::ISNULL && $args[1] !== Op::ISNOTNULL))
        {
            $this->set_type(Op::MATCH);
            $this->set_key($args[0]);
            $this->set_val($args[1]);
        }
        else
        {
            $this->set_type($args[1]);
            $this->set_key($args[0]);
            $this->set_val($args[2] ?? null);
            $this->set_extra($args[3] ?? null);
        }
        return $this;
    }
    
    private function set_type(string $val): void
    {
        $this->type = $val;
    }

    private function set_key($key): void
    {
        $type   = strtoupper($this->type);
        if (is_null($key) && ($type !== Op::EXISTS && $type !== Op::NOTEXISTS))
        {
            throw new BambooException("Pieceにキーが指定されませんでした。");
        }
        $this->key = $key;
    }

    private function set_val($val) : void
    {
        $type   = strtoupper($this->type);
        
        if(is_object($val) && !($val instanceof Query))
        {
                $obj = gettype($val);
                throw new BambooException("Pieceに不正な型の値が渡されました。[{$obj}]");
        }
        if ($type === Op::EXISTS || $type === Op::NOTEXISTS)
        {
            if (!($val instanceof Query))
            {
                $obj    = gettype($val);
                $value  = (string)$val;
                throw new BambooException("EXISTS または NOTEXISTS の Piece の値に Query@SELECT 以外が指定されました。[{$value}:{$obj}]");
            }
            if (($val instanceof Query) || $val->type !== Query::SELECT)
            {
                $obj    = "Query@{$val->type}";
                $value  = (string)$val;
                throw new BambooException("EXISTS または NOTEXISTS の Piece の値に Query@SELECT 以外が指定されました。[{$value}:{$obj}]");
            }
        }
        if (is_array($val))
        {
            $cnt = count($val);
            
            if (!$this->type_array_value($type))
            {
                throw new BambooException("IN、NOTIN、BETWEEN、NOTBETWEEN 以外の Piece 値に配列が渡されました。");
            }
            if (($type === Op::BETWEEN || $type === Op::NOTBETWEEN) && $cnt !== 2)
            {
                throw new BambooException("BETEEEN または NOTBETWEEN の Piece に渡された配列の要素数が不正です。許容される要素数は 2 です。[要素数: {$cnt}]");
            }
            
            $i = 0;
            foreach ($val as $v)
            {
                if (!is_string($v) && !is_numeric($v) && (is_object($v) && get_class($v) !== "mikisan\\core\\basis\\bamboo\\Exp"))
                {
                    $obj    = gettype($v);
                    $value  = (string)$v;
                    throw new BambooException("Piece の値に渡された配列に文字列、数値、Expオブジェクト以外の値が含まれています。[要素: {$i}, {$value}:{$obj}]");
                }
                $i++;
            }
        }
        if (!is_array($val) && $this->type_array_value($type))
        {
            $obj    = gettype($v);
            $value  = (string)$v;
            throw new BambooException("IN、NOTIN、BETWEEN、NOTBETWEEN の Piece の値に配列以外が渡されました。[{$value}:{$obj}]");
        }
        if (is_null($val) && $this->type_value_nullable($type))
        {
            throw new BambooException("MATCH、NOT、ISNULL、ISNOTNULL 以外で null を指定することはできません。");
        }
        $this->value = $val;
    }
    
    private function type_array_value(string $type): bool
    {
        switch(true)
        {
            case $type === Op::IN:
            case $type === Op::NOTIN:
            case $type === Op::BETWEEN:
            case $type === Op::NOTBETWEEN:
                return true;
                
            default:
                return false;
        }
    }
    
    private function type_value_nullable(string $type): bool
    {
        switch(true)
        {
            case $type === "MATCH":
            case $type === "NOT":
            case $type === "ISNULL":
            case $type === "ISNOTNULL":
                return true;
                
            default:
                return false;
        }
    }
    
    private function set_extra($extra) : void
    {
        if (!is_null($extra) && !is_string($extra))
        {
            $obj    = gettype($v);
            $value  = (string)$v;
            throw new BambooException("Piece　の　extra に文字列以外が渡されました。[{$value}:{$obj}]");
        }
        $this->extra    = (EX::empty($extra))
                                ? null
                                : $extra
                                ;
    }
    
    public function toSQL(int $idx = 0): string
    {
        $place_holder   = ":{$this->to_tiny_label($this->key)}_{$idx}";
        
        switch(true)
        {
            case $this->type === Op::MATCH:
                return "{$this->key_to_sql($this->key)} = {$place_holder}";
                
            case $this->type === Op::NOTMATCH:
                return "{$this->key_to_sql($this->key)} <> {$place_holder}";
                
            case $this->type === Op::NOT:
                return "{$this->key_to_sql($this->key)} <> {$place_holder}";
                
            case $this->type === Op::EQ:
                return "{$this->key_to_sql($this->key)} = {$place_holder}";
                
            case $this->type === Op::NOTEQ: 
                return "{$this->key_to_sql($this->key)} != {$place_holder}";
                
            case $this->type === Op::LT:
                return "{$this->key_to_sql($this->key)} < {$place_holder}";
                
            case $this->type === Op::GT:
                return "{$this->key_to_sql($this->key)} > {$place_holder}";
                
            case $this->type === Op::LTE:
                return "{$this->key_to_sql($this->key)} <= {$place_holder}";
                
            case $this->type === Op::GTE:
                return "{$this->key_to_sql($this->key)} >= {$place_holder}";
                
            case $this->type === Op::IN:
            case $this->type === Op::NOTIN:
                
                $place_holders = [];
                foreach($this->value as $key => $val)
                {
                    $place_holders[]   = ":{$this->to_tiny_label($this->key)}_{$idx}_{$key}";
                }
                $in = ($this->type === Op::IN) ? "IN" : "NOT IN";
                return "{$this->key_to_sql($this->key)} {$in} (" . implode(", ", $place_holders) . ")";
                
            case $this->type === Op::ISNULL:
                return "{$this->key_to_sql($this->key)} IS NULL";
                
            case $this->type === Op::ISNOTNULL: 
                return "{$this->key_to_sql($this->key)} IS NOT NULL";
                
            case $this->type === Op::BETWEEN:
                return "{$this->key_to_sql($this->key)} BETWEEN {$place_holder}_0 AND {$place_holder}_1";
                
            case $this->type === Op::NOTBETWEEN: 
                return "{$this->key_to_sql($this->key)} NOT BETWEEN {$place_holder}_0 AND {$place_holder}_1";
            
            case $this->type === Op::LIKE:
            case $this->type === Op::LIKEW:
            case $this->type === Op::WLIKE:
            case $this->type === Op::LIKE_:
            case $this->type === Op::_LIKE:
                return "{$this->key_to_sql($this->key)} LIKE {$place_holder}";
                
            case $this->type === Op::NOTLIKE:
            case $this->type === Op::NOTLIKEW:
            case $this->type === Op::NOTWLIKE: 
            case $this->type === Op::NOTLIKE_:
            case $this->type === Op::NOT_LIKE:
                return "{$this->key_to_sql($this->key)} NOT LIKE {$place_holder}";
            
            case $this->type === Op::EXISTS:
            case $this->type === Op::NOTEXISTS: 
                
            default:
                return "{$this->key_to_sql($this->key)} {$this->type} {$place_holder}";
        }
    }
    
    private function key_to_sql($key): string
    {
        return (is_string($key))
                    ? DBUTIL::wrapID($key)
                    : $key->toSQL()
                    ;
    }
    
    private function to_tiny_label($key): string
    {
        return (is_string($key))
                    ? preg_replace("/[().]/u", "_", $key)
                    : $key->toTinyLabel()
                    ;
    }
    
}
