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

use \mikisan\core\exception\BambooException;

class Having
{
    
    const   AND = "AND", OR = "OR";

    private $having     = [];
    private $and_or     = Having::AND;

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();
        
        if($nums > 0)
        {
            $this->setHaving($args);
        }
    }
    
    public function __get(string $key)
    {
        switch($key)
        {
            case "having":
                return $this->having;
        
            case "and_or":
                return $this->and_or;
        }
        
        throw new BambooException("Having では {$key} は取得できません。");
    }
    
    public function add($where): Where
    {
        $this->add_piece($where);
        
        return $this;
    }
    
    private function setHaving(array $args) : void
    {
        foreach ($args as $having)
        {
            if(is_string($having) && ($having === Having::AND || $having === Having::OR))
            {
                $this->and_or   = $having;
                continue;
            }
            $this->add_piece($having);
        }
    }
    
    private function add_piece($val)
    {
        if($val instanceof Having)
        {
            $this->having[] = $val;
            return;
        }
        if(is_array($val))
        {
            $cnt    = count($val);
            if ($cnt >= 2 && $cnt <= 4)
            {
                $this->having[] = new Piece(...$val);
                return;
            }
            
            throw new BambooException("Having に渡された配列の引数の数が不正です。許容値は2つ以上4つ以下です。[要素数: {$cnt}]");
        }
        
        $obj    = gettype($val);
        $value  = (string)$val;
        throw new BambooException("Where に不正なデータが渡されました。[{$value}:{$obj}]");
    }
    
    public function toSQL(int &$idx = 0)
    {
        $array = [];
        foreach($this->having as $piece)
        {
            $array[] = (is_object($piece) && get_class($piece) === "mikisan\\core\\basis\\bamboo\Having")
                            ? "({$piece->toSql($idx)})"
                            : $piece->toSql($idx)
                            ;
            $idx++;
        }
        return implode(" {$this->and_or} ", $array);
    }
    
}
