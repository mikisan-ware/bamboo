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

use \mikisan\core\util\EX;
use \mikisan\core\util\STR;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\exception\BambooException;

class Exp
{
    
    const DESC = "DESC", CASE = "CASE";
    
    private $type   = self::DESC;
    private $expression;
    private $params     = [];
    
    private $object;
    private $when       = [];
    private $else       = null;
    private $alias      = null;
    
    public function __construct(string $expression, $params = [], $type = self::AS)
    {
        $this->type = $type;
        $parts      = DBUTIL::separateAlias($expression);
        
        switch(true)
        {
            case $type === self::CASE:
                
                $this->object       = $parts[0];
                if(count($parts) > 1)
                {
                    $this->alias    = $parts[1];
                }
                break;
            
            case $type === self::DESC:
            default:
                
                $this->expression   = (count($parts) === 1)
                                            ? DBUTIL::strip($parts[0])
                                            : DBUTIL::strip($parts[0]) . " AS " . DBUTIL::strip($parts[1])
                                            ;
                $this->params  = (is_string($params)) ? [$params] : $params ;
        }
    }
    
    public static function desc(string $expression, $ids = []): Exp
    {
        return new Exp($expression, $ids, self::DESC);
    }
    
    public static function case(string $expression = ""): Exp
    {
        return new Exp($expression, null, self::CASE);
    }
    
    public function when(...$when): Exp
    {
        $this->when[]   = $when;
        return $this;
    }
    
    public function else($else): Exp
    {
        $this->else     = DBUTIL::escape($else);
        return $this;
    }
    
    public function toSQL(): string
    {
        $matches    = [];
        return ($this->type === self::DESC)
                    ? $this->replace_exp()
                    : $this->replace_case()
                    ;
    }
    
    private function replace_case(): string
    {
        $object     = (!EX::empty($this->object))   ? " " .DBUTIL::wrapID($this->object) : "";
        $alias      = (!EX::empty($this->alias))    ? " AS " .DBUTIL::strip($this->alias) : "";
        
        $exp    = [];
        foreach($this->when as $w)
        {
            $exp[]  = "WHEN {$this->get_when($w[0])} THEN " . DBUTIL::escape($w[1]);
        }
        if(!EX::empty($this->else))
        {
            $exp[]  = "ELSE {$this->else}";
        }
        $when       = STR::indent(implode("\n", $exp), 1);
        
        return <<< EOL
CASE{$object}
{$when}
END{$alias}
EOL;
    }
    
    private function get_when($when): string
    {
        return (is_object($when) && get_class($when) === "mikisan\\core\\basis\\bamboo\\Exp")
                    ? $when->toSQL()
                    : DBUTIL::escape($when)
                    ;
    }
    
    private function replace_exp(): string
    {
        $expression = $this->expression;
        $result     = preg_match_all('/:(@|#)/u', $expression, $matches, PREG_OFFSET_CAPTURE);
        if(!$result)    { return $expression; }
        
        $cnt    = count($matches[0]);
        for($i = $cnt - 1; $i >= 0; $i--)
        {
            $ahead      = mb_substr($expression, 0, $matches[0][$i][1]);
            $posterior  = mb_substr($expression, $matches[0][$i][1] + 2);
            $replace    = ($matches[0][$i][0] === ":@")
                                ? DBUTIL::wrapID($this->params[$i])
                                : DBUTIL::escape($this->params[$i])
                                ;
            $expression = $ahead . $replace . $posterior;
        }
        
        return $expression;
    }
    
    public function toTinyLabel(): string
    {
        $matches    = [];
        $expression = preg_replace("/[().]/u", "_", $this->expression);
        
        $result = preg_match_all('/:@/u', $this->expression, $matches, PREG_OFFSET_CAPTURE);
        if(!$result)    { return $expression; }
        
        $cnt    = count($matches[0]);
        for($i = $cnt - 1; $i >= 0; $i--)
        {
            $ahead      = mb_substr($expression, 0, $matches[0][$i][1]);
            $posterior  = mb_substr($expression, $matches[0][$i][1] + 2);
            $replace    = $this->params[$i];
            $expression = $ahead . $replace . $posterior;
        }
        
        return $expression;
    }
    
}
