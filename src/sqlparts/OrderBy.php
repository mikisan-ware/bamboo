<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/04
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\basis\bamboo\OrderPiece;
use \mikisan\core\exception\BambooException;

class OrderBy
{
    
    const   ASC = "ASC", DESC = "DESC";
    
    private $order_by   = [];

    public function __construct()
    {
        $nums = func_num_args();
        $args = func_get_args();

        if($nums > 0)
        {
            $this->setOrderBy($args);
        }
        
        return $this;
    }
    
    public function __get(string $key): array
    {
        switch(true)
        {
            case $key === "order_by":
                
                return $this->order_by;
        }
        
        throw new BambooException("OrderByでは {$key} は取得できません。");
    }
    
    public function add($ob): OrderBy
    {
        $this->add_piece($ob);
        
        return $this;
    }
    
    private function setOrderBy(array $args) : void
    {
        foreach ($args as $ob)
        {
            $this->add_piece($ob);
        }
    }
    
    private function add_piece($ob)
    {
        if(is_string($ob))
        {
            $this->order_by[]   = new OrderPiece($ob);
            return;
        }
        if($ob instanceof Exp)
        {
            $this->order_by[]   = new OrderPiece($ob);
            return;
        }
        if(is_array($ob))
        {
            $this->order_by[]   = new OrderPiece(...$ob);
            return;
        }
        if($ob instanceof OrderPiece)
        {
            $this->order_by[]   = $ob;
            return;
        }

        $type   = gettype($ob);
        $value  = (string)$ob;
        throw new BambooException("OrderByに不正なデータが渡されました。[{$value}:{$type}]");
    }
    
    public function toSQL(): string
    {
        $array = [];
        foreach($this->order_by as $ob)
        {
            $array[]    = $ob->toSQL();
        }
        return implode(", ", $array);
    }
    
}
