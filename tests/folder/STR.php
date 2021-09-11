<?php
/**
 * Project Name: Pine
 * Description : string関連ユーティリティクラス
 * Start Date  : 2019/03/06
 * Copyright   : Katsuhiko Miki   http://feijoa.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\util;

class STR
{
    const   NL2BR                           = 0b00000001;
    const   STRPAD                          = 0b00000010;
    const   REPLACE_BLANK_TO_FULLWIDTHSPACE = 0b00000100;
    
    const   FILL_BOTH   = 0b00000011;
    const   FILL_LEFT   = 0b00000010;
    const   FILL_RIGHT  = 0b00000001;
    
    /**
     * HTMLタグを実態参照に置き換える
     * 
     * @param   string      $string
     * @return  string
     */
    public static function h(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES|ENT_HTML5, \pine\app\CONSTS::SYSTEM_ENCODE);
    }
    
    public static function urlencode($string, $strict = true) : string
    {
        return ($strict) ? rawurlencode($string) : urlencode($string);
    }
    
    public static function urldecode($string, $strict = true) : string
    {
        return ($strict) ? rawurldecode($string) : urldecode($string);
    }
    
    /**
     * 改行コードを統一する
     * 
     * @param   string  $content
     * @param   string  $to
     * @return  string
     */
    public static function normalize_cr(string $content, string $to = "\n") : string
    {
        $temp   = preg_replace("/(\r\n|\r)/u", "\n", $content);
        return preg_replace("/\n/u", $to, $temp);
    }
    
    /**
     * 文字列を省略して返す
     * 
     * @param   string      $string
     * @param   int         $length
     * @return  string
     */
    public static function omit(string $string, int $length = 20): string
    {
        return  (mb_strlen($string) <= $length)
                    ? $string
                    : mb_substr($string, 0, $length) . "..."
                    ;
    }
    
    /**
     * 複数行の文字列を省略して返す
     * 
     * @param   string      $string
     * @param   int         $lines
     * @return  string
     */
    public static function lines(string $string, int $lines = 2): string
    {
        $string = preg_replace("/(\r\n|\r|\n)/", "\n", $string);
        $string = preg_replace("/\n{2,}/", "\n", $string);
        $split  = explode("\n", $string);
        $response   = [];
        for($i = 0; $i < min($lines, count($split)); $i++)
        {
            $response[] = $split[$i];
        }
        return implode("\n", $response) . (($lines < count($split))? " ..." : "");
    }
    
    /**
     * 改行コードを統一する
     * 
     * @param   string      $val        改行コードを置き換える文字列
     * @param   string      $to         統一する改行コード
     * @return  string
     */
    public static function setEOL(string $val, string $to): string
    {
        return preg_replace("/\r\n|\r|\n/", $to, $val);
    }
    
    /**
     * 与えられた文字列について、与えられた文字コード $to に変換して返す
     * 
     * @param   string  $val        変換元の文字列
     * @param   string  $to         変換後の文字エンコード
     * @param   string  $from       変換前の文字エンコード
     * @return  string
     */
    public static function convert($val, $to, $from) : string
    {
        $to_fix     = str_replace("-", "", strtolower($to));
        $from_fix   = str_replace("-", "", strtolower($to));
        
        if($to_fix === $from_fix) { return $val; }
        
        return mb_convert_encoding($val, $to, $from);
    }
    
    /**
     * 与えられた文字列をcp932(Windows-31J)に変換する
     * 
     * @param   string      $val
     * @param   string      $encode         $valのエンコード情報
     * @return  string
     */
    public static function cp932(string $val, string $encode = \pine\app\CONSTS::SYSTEM_ENCODE): string
    {
        return mb_convert_encoding($val, "sjis-win", $encode);
    }
    
    /**
     * 与えられた文字列をCONSTS::TERMINAL_ENCODEDに変換する
     * 
     * @param   string      $val
     * @param   string      $encode         $valのエンコード情報
     * @return  string
     */
    public static function terminal(string $val, string $encode = \pine\app\CONSTS::SYSTEM_ENCODE): string
    {
        return mb_convert_encoding($val, \pine\app\CONSTS::TERMINAL_ENCODE, $encode);
    }

    /**
     * stringをスネークケースにする（abcDefをabc_def型にする）
     * 
     * @param   string      $val            スネークケースにする文字列
     * @return  string
     */
    public static function snake(string $val): string
    {
        $val = lcfirst($val);
        $val = preg_replace("/([A-Z])/", "_$1", $val);
        $val = strtolower($val);
        $val = preg_replace("/^_/", "", $val);

        return $val;
    }

    /**
     * stringをキャメルケースにする（abc_de型をabcDef型またはAbcDef型にする）
     * 
     * @param   string          $val                キャメルケースにする文字列
     * @param   boolean         $capitalize         最初の文字を大文字にするか？
     * @return  string
     */
    public static function camel(string $val, bool $capitalize = true): string
    {
        $first = true;
        $newString = "";
        
        $val_array = explode("_", $val);
        foreach ($val_array as $section)
        {
            if($first)
            {
                $newString  = ($capitalize)? ucfirst($section) : lcfirst($section);
                $first      = false;
                continue;
            }
            $newString .= ucfirst($section);
        }

        return $newString;
    }
    
    /**
     * stringをアッパーキャメルケースにして返す（STR::camel()のラッパー）
     * 
     * @param   string          $val                キャメルケースにする文字列
     * @param   boolean         $capitalize         最初の文字を大文字にするか？
     * @return  string
     */
    public static function ucamel(string $val): string
    {
        return self::camel($val, true);
    }
    
    /**
     * stringをローワーキャメルケースにして返す（STR::camel()のラッパー）
     * 
     * @param   string          $val                キャメルケースにする文字列
     * @param   boolean         $capitalize         最初の文字を大文字にするか？
     * @return  string
     */
    public static function lcamel(string $val): string
    {
        return self::camel($val, false);
    }
    
    /**
     * 渡された引数を連結してアッパーキャメル文字列として返す
     * 
     * @param type $args
     * @return string
     */
    public static function ucamel_concat(string ...$args) : string
    {
        $result = "";
        foreach($args as $val)
        {
            $result .= self::camel($val, true);
        }
        return $result;
    }
    
    /**
     * 渡された引数を連結してスネーク文字列として返す
     * 
     * @param type $args
     * @return string
     */
    public static function snake_concat(string ...$args) : string
    {
        $result = [];
        foreach($args as $val)
        {
            $result[] = self::snake($val);
        }
        return implode("_", $result);
    }
    
    /**
     * 文字列を固定長の他の文字列で埋める（str_pad()のラッパー）
     * 
     * @param   string      $string
     * @param   int         $length
     * @param   string      $pad_string
     * @param   int         $pad_type
     * @return  string
     */
    public static function pad(string $string, int $length, string $pad_string = " ", int $pad_type = STR_PAD_RIGHT) : string
    {
        return str_pad($string, $length, $pad_string, $pad_type);
    }
    
    /**
     * 文字列の左側を固定長の他の文字列で埋める（str_pad()のラッパー）
     * 
     * @param   string      $string
     * @param   int         $length
     * @param   string      $pad_string
     * @param   int         $pad_type
     * @return  string
     */
    public static function lpad(string $string, int $length, string $pad_string = " ") : string
    {
        return str_pad($string, $length, $pad_string, STR_PAD_LEFT);
    }
    
    /**
     * 文字列の右側を固定長の他の文字列で埋める（str_pad()のラッパー）
     * 
     * @param   string      $string
     * @param   int         $length
     * @param   string      $pad_string
     * @param   int         $pad_type
     * @return  string
     */
    public static function rpad(string $string, int $length, string $pad_string = " ") : string
    {
        return str_pad($string, $length, $pad_string, STR_PAD_RIGHT);
    }
    
    /**
     * 文字列の両端を固定長の他の文字列で埋める（str_pad()のラッパー）
     * 
     * @param   string      $string
     * @param   int         $length
     * @param   string      $pad_string
     * @param   int         $pad_type
     * @return  string
     */
    public static function bpad(string $string, int $length, string $pad_string = " ") : string
    {
        return str_pad($string, $length, $pad_string, STR_PAD_BOTH);
    }
    
    /**
     * $value を指定したインデントレベルでインデントして返す
     * 
     * @param   string      $value
     * @param   int         $level
     * @param   int         $string_repeat
     * @param   string      $pad_string
     * @return  string
     */
    public static function indent(string $value, int $level = 1, int $string_repeat = 4, string $pad_string = " ") : string
    {
        $array  = explode("\n", $value);
        foreach($array as &$v)
        {
            $v = str_repeat($pad_string, $string_repeat * $level) . $v;
        }
        unset($v);
        return implode("\n", $array);
    }
    
    /**
     * マルチバイトを考慮したtrim
     * 
     * @param   string      $val
     * @return  string
     */
    public static function mb_trim(string $val): string
    {
        $val = self::mb_ltrim($val);
        $val = self::mb_rtrim($val);
        
        return $val;
    }
    
    /**
     * マルチバイトを考慮したltrim
     * 参考：【PHP】マルチバイト(全角スペース等)対応のtrim処理
     * https://qiita.com/fallout/items/a13cebb07015d421fde3
     * 
     * @param   string      $val
     * @return  string
     */
    public static function mb_ltrim(string $val): string
    {
        return preg_replace("/\A[\p{Cc}\p{Cf}\p{Z}]++/u", "", $val);
    }
    
    /**
     * マルチバイトを考慮したrtrim
     * 
     * @param   string      $val
     * @return  string
     */
    public static function mb_rtrim(string $val): string
    {
        return preg_replace("/[\p{Cc}\p{Cf}\p{Z}]++\z/u", "", $val);
    }
    
    /**
     * 文字列からnullバイトを除去する
     * 
     * @param   string      $val
     * @return  string
     */
    public static function strip_null(string $val): string
    {
        return str_replace("\0", "", $val);
    }
    
    /**
     * $templateのプレースホルダを$paramsの定義で置き換えて返す
     * 
     * @param   string      $template           
     * @param   array       $params
     * @param   int         $flag           制御フラグ nl2br | str_pad
     * @param   array       $options        オプション
     * @param   int         $width          半角幅
     * @return  string
     */
    public static function inject($template, $params, $flag = 0, $options = [])
    {
        // プレースホルダの置き換え
        foreach($params as $key => $param)
        {
            $param      = self::sub_inject($param, $params);

            $param      = self::h($param);
            $param      = ($flag & self::NL2BR) ? nl2br($param) : $param;
            $param      = ($flag & self::REPLACE_BLANK_TO_FULLWIDTHSPACE && empty($param))? "　" : $param; 
            
            $template   = ($flag & self::STRPAD)? self::labeling($template, $options) : $template;
            $template   = str_replace("{:{$key}}", $param, $template);
            $template   = str_replace("<:--{$key}-->", self::titling($param, $options), $template);
        }
        
        return $template;
    }
    private static function sub_inject($template, $params)
    {
        foreach($params as $key => $param)
        {
            $template   = str_replace("{:{$key}}", $param, $template);
        }
        
        return $template;
    }
    
    private static function titling($string , $options)
    {
        $fill_text  = (isset($options["title_fill_text"]))  ? $options["title_fill_text"] : "=";
        $width      = (isset($options["title_width"]))      ? (int)$options["title_width"]: 70;
        $fillment   = (isset($options["title_fillment"]))   ? $options["title_fillment"] : self::FILL_BOTH;
        
        $length     = self::length_herf($string);
        $remain     = $width - $length;
        
        switch($fillment)
        {
            case self::FILL_LEFT:
                
                return str_repeat($fill_text, $remain) . $string;
                
            case self::FILL_RIGHT:
                
                return $string . str_repeat($fill_text, $remain);
                
            case self::FILL_BOTH:
            default:
                
                $fills  = max((int)floor($remain / 2), 0);
                return str_repeat($fill_text, $fills) . $string . str_repeat($fill_text, $fills);
                
        }
    }
    
    private static function labeling($template, $options)
    {
        $width  = (isset($options["label_width"]))  ? (int)$options["label_width"] : 20;
        
        preg_match_all("/(\[:.+\])/", $template, $matches, PREG_PATTERN_ORDER);
        foreach($matches[0] as $match)
        {
            $plain      = preg_replace("/\[:(.+)\]/u", "[$1]", $match);
            $length     = self::length_herf($plain);
            $suffix     = str_repeat(" ", $width - $length);
            
            $template   = str_replace($match, "{$plain}{$suffix}", $template);
        }
        
        return $template;
    }
    
    private static function length_herf($string)
    {
        $result = preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
        $length = 0;
        foreach($result as $char)
        {
            $length += (strlen($char) === mb_strlen($char, "UTF-8"))? 1 : 2;
        }
        return $length;
    }
    
    /**
     * パスワード等をアスタリスクマスクする
     * 
     * @param type $string
     * @return string
     */
    public static function mask($string) : string
    {
        return str_pad("", strlen((string)$string), "*");
    }
    
    
    /**
     * ランダムな文字列を返す
     * 
     * @param   int         $length
     * @return  string
     */
    public static function random(int $length = 8) : string
    {
        return substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!#$%&-=~^|_"), 0, $length);
    }
}
