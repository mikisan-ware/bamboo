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

use \mikisan\core\util\STR;
use \mikisan\core\exception\BambooException;

class DBUTIL
{
    
    /**
     * 識別子をクオートして返す
     * 
     * @param   string      $value
     * @return  string
     */
    public static function wrapID(string $id): string
    {
        //$pdo        = DBConnect::getInstance()->getPDO($db, true);
        //$driver     = strtolower($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        
        //@test
        $driver     = "mysql";
        
        $ids        = explode(".", $id);
        $result     = [];
        foreach($ids as $val)
        {
            $val        = STR::mb_trim($val);
            if($val === "*")
            {
                $result[]   = "*";
                continue;
            }
            $id         = self::strip($val);
            $result[]   = self::id_quote($driver, $id);
        }
        
        return implode(".", $result);
    }
    
    private static function id_quote(string $driver, string $id)
    {
        switch(true)
        {
            case $driver === "mysql":
            case $driver === "sqlite":
                
                return "`{$id}`";
                
            case $driver === "sqlsrv":
                
                return "[{$id}]";
                
            default:    // ANSI規格: PostgreSQL, Oracle, DB2
                
                return "\"{$id}\"";
        }
    }
    
    /**
     * リテラルをエスケープする（SQLインジェクション対策）
     */
    public static function escape($value)
    {
        // return $pdo->quote($value);
        
        return (is_int($value) || is_double($value))
                    ? $value
                    : "'" . preg_replace("/'/u", "''", $value) . "'"
                    ;
    }
    
    /**
     * $value について、SQL インジェクションの危険性のある文字を全て削除して返す
     * 
     * @param   string      $value
     * @return  string
     */
    public static function strip(string $value): string
    {
        return preg_replace("/('|\"|;|--+)/u", "", $value);
    }
    
    /**
     * エイリアス構文を分離する
     * 
     * @param   string      $value
     * @return  array
     */
    public static function separateAlias(string $value): array
    {
        switch(true)
        {
            case preg_match("/\|\:/u", $value):
                $parts  = array_map(function($value){ return STR::mb_trim($value); }, explode("|:", $value));
                break;
        
            case preg_match("/( as )/ui", $value, $matches):
                $parts  = array_map(function($value){ return STR::mb_trim($value); }, explode($matches[0], $value));
                break;
            
            default:
                $parts = [$value];
        }
        return $parts;
    }
    
}
