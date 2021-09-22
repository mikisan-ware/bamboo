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

use \mikisan\core\util\STR;
use \mikisan\core\exception\BambooException;

class Where
{

    const   AND = "AND", OR = "OR";
    
    private $where      = [];
    private $and_or     = Where::AND;

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();
        
        if($nums > 0)
        {
            $this->setWhere($args);
        }
    }
    
    public function __get(string $key)
    {
        switch($key)
        {
            case "where":
                return $this->where;
        
            case "and_or":
                return $this->and_or;
        }
        
        throw new BambooException("Where では {$key} は取得できません。");
    }
    
    public function add($where): Where
    {
        $this->add_piece($where);
        
        return $this;
    }
    
    private function setWhere(array $args) : void
    {
        foreach ($args as $where)
        {
            if(is_string($where) && ($where === Where::AND || $where === Where::OR))
            {
                $this->and_or   = $where;
                continue;
            }
            $this->add_piece($where);
        }
    }
    
    private function add_piece($val)
    {
        if($val instanceof Where)
        {
            $this->where[] = $val;
            return;
        }
        if(is_array($val))
        {
            $cnt    = count($val);
            if ($cnt >= 2 && $cnt <= 4)
            {
                $this->where[] = new Piece(...$val);
                return;
            }
            
            throw new BambooException("Where に渡された配列の引数の数が不正です。許容値は2つ以上4つ以下です。[要素数: {$cnt}]");
        }
        
        $obj    = gettype($val);
        $value  = (string)$val;
        throw new BambooException("Where に不正なデータが渡されました。[{$value}:{$obj}]");
    }
    
    public function toSQL(int &$idx = 0)
    {
        $array = [];
        foreach($this->where as $piece)
        {
            $array[] = (is_object($piece) && get_class($piece) === "mikisan\\core\\basis\\bamboo\Where")
                            ? "({$piece->toSql($idx)}\n      )"
                            : $piece->toSql($idx)
                            ;
            $idx++;
        }
        
        return implode("\n" . STR::lpad($this->and_or, 5) . " ", $array);
    }
    
}
