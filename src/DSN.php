<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/14
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

class DSN
{
    
    public static function get(DB $db, bool $use_db = false) : string
    {
        switch ($db->system)
        {
            case "MySQL":
                $dsn = ($use_db)
                            ? "mysql:host={$db->dbhost};port={$db->port};charset={$db->charset};dbname={$db->dbname}"
                            : "mysql:host={$db->dbhost};port={$db->port};charset={$db->charset}"
                            ;
                break;
            
            case "PostgreSQL":
                $dsn = ($use_db)
                            ? "pgsql:host={$db->dbhost};port={$db->port};dbname={$db->dbname}"
                            : "pgsql:host={$db->dbhost};port={$db->port}"
                            ;
                break;
            
            case "SQLite":
                $dsn = ($use_db)
                            ? "sqlite:{$db->dbhost}.{$db->dbname}"
                            : "sqlite:{$db->dbhost}"
                            ;
                break;
            
            case "SQLServer":
                $dsn = "sqlsrv:Server={$db->dbhost};Database={$db->dbname}";
                break;
            
            case "Oracle":
                $dsn = "oci:dbname=//{$env->dbhost}:{$env->port}/{$env->dbname}";
                 break;
             
            default:
                throw new BambooException(I18N::get("DBConnect.un_support_database", [$db->system], "このシステムでは対応しないDataBaseです。[:@0]"));
        }
        return $dsn;
    }
    
}
