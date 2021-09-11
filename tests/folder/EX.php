<?php

/**
 * Project Name: mikisan-ware
 * Description : PHP拡張ユーティリティ
 * Start Date  : 2021/08/04
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\util;

class EX
{
    /**
     * htmlspecialchars() のラッパー
     * 
     * @param   string      $string
     * @return  string
     */
    public static function h(string $string, string $encode = "UTF-8") : string
    {
        return htmlspecialchars($string, ENT_QUOTES|ENT_HTML5, $encode);
    }
    
    /**
     * 本当の意味でのemptyか？（空文字、null、空配列の場合のみ true）
     * 
     * @param   mixed   $val
     * @return  bool
     */
    public static function is_empty($val) : bool
    {
        return self::empty($val);
    }
    
    /**
     * 本当の意味でのemptyか？（空文字、null、空配列の場合のみ true）
     * 
     * @param   mixed   $val
     * @return  bool
     */
    public static function empty($val) : bool
    {
        if($val === "")     { return true; }
        if($val === null)   { return true; }
        if(is_array($val) && count($val) === 0) { return true; }
        
        return false;
    }
    
    /**
     * explode()の拡張。空文字列の場合は空配列を返す
     * 
     * @param   string  $separator
     * @param   string  $string
     * @return  array
     */
    public static function explode(string $separator, string $string) : array
    {
        if($string === "")  { return []; }
        return explode($separator, $string);
    }
    
    /**
     *  変数の文字列表現を返す（var_export($value, true) のラッパー）
     * 
     * @param   mixed       $value
     * @return  string
     */
    public static function dump($value) : string
    {
        return var_export($value, true);
    }
    
    /**
     * 与えられた引数のうち、値が存在する物を返す。見つからない場合は最後の引数値を返す
     * 
     * @param   mixed       $args
     * @return  mixed
     */
    public static function insteadof(...$args)
    {
        foreach($args as $arg)
        {
            if(!self::empty($arg))  { return $arg; }
        }
        return $args[count($args) - 1];
    }
    
    
    
    
}
