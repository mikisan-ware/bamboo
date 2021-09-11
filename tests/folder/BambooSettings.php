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

namespace mikisan\core\basis\settings;

use \mikisan\core\basis\bamboo\OrderBy;

class BambooSettings
{
    const   DEFAULT_SORT    = OrderBy::DESC;
    
    /*
    const   CONNECT         = true;
    const   RETRY_CONNECT   = 3;        // 接続失敗時のリトライ回数
    const   RETRY_INTERVAL  = 500;      // 接続失敗時のリトライインターバル（ミリ秒）
    
    const   DEFAULT_USER    = "SYSTEM";
    const   DEFAULT_PROC    = "PINE";
    
    const   VIEW_PREFIX = "view_";
    const   VIEW_SUFFIX = "";
    
    const   TIMESTAMP_PRECISION = 0;
    const   TIMESTAMP_PRECISION_DEFINITION = "";    //"(".self::TIMESTAMP_PRECISION.")";    // PHPのバージョンによってサポートしない
    const   TIME_FORMAT = "Y-m-d H:i:s.u";          //"Y-m-d H:i:s.u";                      // PHP 5.2.2でサポートされているが、少数が指数扱いになる場合にエラーが発生するバグがあるようだ
    
    const   SELECT = "SELECT", UPSERT = "UPSERT", UPDATE = "UPDATE", INSERT = "INSERT", DELETE = "DELETE", QUERY = "QUERY",
            DDL = "DDL", CREATETABLE = "CREATETABLE", DROPTABLE = "DROPTABLE", TRUNCATE = "TRUNCATE", COUNT = "COUNT",
            CREATEVIEW = "CREATEVIEW", DROPVIEW = "DROPVIEW",
            UNKNOWN = "UNKNOWN";
    
    const   ACTUAL = "actual", LOG = "log";
    
    const   LOG_PREFIX  = "zzz_", LOG_SUFFIX = "";

    const   ADD_AT    = "add_at",
            ADD_PROC  = "add_process",
            ADD_USER  = "add_user",
            UPD_AT    = "upd_at",
            UPD_PROC  = "upd_process",
            UPD_USER  = "upd_user",
            LG_DEL    = "deleted",
            LOG_AT    = "log_at",
            LOG_PROC  = "log_process",
            LOG_USER  = "log_user",
            COLLATE   = null,
            TIMESTAMP = "timestamp";

    private static $requireLogs = array(
        self::LOG_AT => array(
            "type" => "DATETIME".self::TIMESTAMP_PRECISION_DEFINITION, "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "ログタイムスタンプ",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::LOG_PROC => array(
            "type" => "VARCHAR(255)", "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "ログプロセス",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::LOG_USER => array(
            "type" => "VARCHAR(64)", "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "操作者ID",
            "pdo_type" => \PDO::PARAM_STR
        )
    );
    
    private static $requireAdds = array(
        self::ADD_AT => array(
            "type" => "DATETIME".self::TIMESTAMP_PRECISION_DEFINITION, "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "作成タイムスタンプ",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::ADD_PROC => array(
            "type" => "VARCHAR(255)", "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "作成者プロセス",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::ADD_USER => array(
            "type" => "VARCHAR(64)", "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => null, "other" => null, "comment" => "作成者ID",
            "pdo_type" => \PDO::PARAM_STR
        )
    );
    
    private static $requireUpdates = array(
        self::UPD_AT => array(
            "type" => "DATETIME".self::TIMESTAMP_PRECISION_DEFINITION, "collate" => self::COLLATE, "attribute" => null, "null" => true,
            "default" => null, "other" => null, "comment" => "更新タイム",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::UPD_PROC => array(
            "type" => "VARCHAR(255)", "collate" => self::COLLATE, "attribute" => null, "null" => true,
            "default" => null, "other" => null, "comment" => "更新プロセス",
            "pdo_type" => \PDO::PARAM_STR
        ),
        self::UPD_USER => array(
            "type" => "VARCHAR(64)", "collate" => self::COLLATE, "attribute" => null, "null" => true,
            "default" => null, "other" => null, "comment" => "更新者ID",
            "pdo_type" => \PDO::PARAM_STR
        )
    );
    
    private static $requireLgDel = array(
        self::LG_DEL => array(
            "type" => "BOOLEAN", "collate" => self::COLLATE, "attribute" => null, "null" => false,
            "default" => false, "other" => null, "comment" => "論理削除",
            "pdo_type" => \PDO::PARAM_BOOL
        )
    );

    public static function getRequireLogs()
    {
        return self::$requireLogs;
    }
    
    public static function getRequireAdds()
    {
        return self::$requireAdds;
    }
    
    public static function getRequireUpdates()
    {
        return self::$requireUpdates;
    }
    
    public static function getRequireLgDel()
    {
        return self::$requireLgDel;
    }
    
    public static function getRequires()
    {
        $requires = array_merge(self::$requireAdds, self::$requireUpdates);
        $requires = array_merge($requires, self::$requireLgDel);
        return $requires;
    }
    
    public static function getRequireStructures(BambooDto $dto)
    {
        $requires = self::getRequires();
        return self::getStructures($dto, $requires);
    }

    public static function getLogStructures(BambooDto $dto)
    {
        $structureArray = array();
        return self::getStructures($dto, self::$requireLogs);
    }

    private static function getStructures(BambooDto $dto, $array)
    {
        $structureArray = array();
        foreach ($array as $key => $val) {
            $s = DBUTIL::makeTableStructure($dto->db->system, $key, $val);
            $structureArray[] = $s;
        }
        return $structureArray;
    }
     */
}
