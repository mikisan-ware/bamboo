<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/22
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Column
{
    
    private $column;
    
    public function __construct(string $column)
    {
        $this->column  = $column;
    }
    
    public static function as(string $column = ""): Column
    {
        return new Column($column);
    }
 
    public function toSQL(): string
    {
        return DBUTIL::wrapID($this->column);
    }
    
}
