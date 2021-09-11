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

use \mikisan\core\basis\bamboo\OrderBy;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

class OrderPiece
{
    
    private $expression;
    private $sort   = BambooSettings::DEFAULT_SORT;

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();
        
        if ($nums > 0)
        {
            $this->setOrderPiece($nums, $args);
        }
    }
    
    /**
     * getter
     * 
     * @param   string      $key
     * @return  mixed
     * @throws  BambooException
     */
    public function __get(string $key)
    {
        switch($key)
        {
            case "expression":
            case "sort":
                
                return $this->$key;
        }
        
        throw new BambooException("OrderPieceでは {$key} は取得できません。");
    }
    
    public function setExpression($expression): OrderPiece
    {
        if(!is_string($expression) && (is_object($expression) && get_class($expression) !== "mikisan\\core\\basis\\bamboo\\Exp"))
        {
            $type   = gettype($ob);
            $value  = (string)$ob;
            throw new BambooException("OrderPiece に不正なデータが渡されました。[{$value}:{$type}]");
        }
        $this->expression   = $expression;
        return $this;
    }
    
    public function setSort(string $sort): OrderPiece
    {
        if($sort !== OrderBy::ASC && $sort !== OrderBy::DESC)
        {
            throw new BambooException("OrderPieceの第二引数に不正なソート条件が渡されました。[{$sort}]");
        }
        
        $this->sort         = $sort;
        return $this;
    }
    
    private function setOrderPiece(int $nums, array $args) : void
    {
        if ($nums > 2)
        {
            throw new BambooException("OrderPieceに不正な数のパラメターが渡されました。[要素数: {$nums}]");
        }
        
        ($nums === 1)
                ? $this->setExpression($args[0])
                : $this->set_expression_and_sort($args[0], $args[1])
                ;
    }
    
    private function set_expression_and_sort($expression, string $sort)
    {
        $this->setExpression($expression)->setSort($sort);
    }
    
    public function toSql(): string
    {
        return is_string($this->expression)
                        ? DBUTIL::wrapID($this->expression) . " {$this->sort}"
                        : "{$this->expression->toSql()} {$this->sort}"
                        ;
    }
}
