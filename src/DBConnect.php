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

use \mikisan\core\basis\bamboo\DSN;
use \mikisan\core\basis\bamboo\BambooSettings;
use \mikisan\core\exception\BambooException;

class DBConnect
{
    
    private static $instance;
    private $connections    = [];

    public static function instance()
    {
        if (self::$instance === null)   { self::$instance = new self; }
        return self::$instance;
    }
    
    /**
     * PDOインスタンスのゲッター（シングルトンモデルのため、同じ DB 定義では常に同一のインスタンスが返る）
     * 
     * @param   \pine\bamboo\DB     $db
     * @param   bool                $use_db     database指定するか？
     * @return  \PDO
     * @throws  BambooException
     */
    public function getPDO(DB $db, bool $use_db = true) : \PDO
    {
        $db_id      = "{$db->system}.{$db->dbname}@{$db->dbhost}:{$db->port}";
        if (isset($this->connections[$db_id]))  { return $this->connections[$db_id]["pdo"]; }
        try
        {
            //echo "\n >>> DSN: " . DSN::get($db, $use_db) . " <<< \n";
            $pdo    = $this->connect($db, $db_id, DSN::get($db, $use_db));
            
            $this->connections[$db_id]["pdo"]   = $pdo;
            $this->connections[$db_id]["db"]    = $db;
            
            return $pdo;
        }
        catch(\Throwable $th)
        {
            $tracking_number = "";
            //LOG::output(LOG::E, LOG::parseTh($th), $tracking_number);
            throw new BambooException("データベースに接続できませんでした。[{$db_id}]");
        }
    }
    
    /**
     * データベースへの接続
     * 
     * @param   pine\bamboo\DB  $db
     * @param   string          $db_id
     * @param   string          $dsn
     * @return  void
     */
    private function connect(DB $db, string $db_id, string $dsn): \PDO
    {
        $options = [
              \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION
            , \PDO::ATTR_PERSISTENT         => $db->persistent
            , \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS
            , \PDO::ATTR_EMULATE_PREPARES   => false    //プリペアエミュレートなし、複文の実行禁止
        ];
        $retry_count        = 0;
        $tracking_number    = "";
        //LOG::output(LOG::S, "(database:connection:open) Attempt connecting with database [{$db->id}].", $tracking_number);
        while(true)
        {
            $retry_count++;
            try
            {
                $pdo    = new \PDO($dsn, $db->dbuser, $db->dbpass, $options);
                //LOG::output(LOG::S, "(database:connection:success) Database connection established.", $tracking_number, 1);
                return $pdo;
            }
            catch(\Throwable $th)
            {
                //LOG::output(LOG::S, "(database:connection:error) Database Connection failed...[{$retry_count}]{$EOL}...{$trim($th->getMessage())}", $tracking_number, 1);
                if($retry_count >= BambooSettings::RETRY_CONNECT)
                {
                    throw new BambooException($th->getMessage(), $th->getCode(), $th);
                }
            }
            usleep(BambooSettings::RETRY_INTERVAL * 100);
        }
    }
    
    /**
     * 全てのDatabaseへの接続を切断する
     */
    public function close_all()
    {
        if(count($this->connections) === 0) { return; }
        
        $tracking_number = "";
        //LOG::output(LOG::S, "(database:close:start) All database connections will be closed.", $tracking_number, 1);
        
        foreach($this->connections as $db_id => $con)
        {
            if($con["pdo"]->inTransaction())
            {
                //LOG::output(LOG::S, "(connection:rollback) Database connection [{$con["db"]->id}] is in transaction and will be rollbacking.", $tracking_number, 2);
                $con["pdo"]->rollBack();
                //LOG::output(LOG::S, "(connection:rollback) Database connection [{$con["db"]->id}] is rollbacked.", $tracking_number, 2);
            }
            $con["pdo"] = null;
            //LOG::output(LOG::S, "(connection:close) Database connection [{$con["db"]->id}] has been closed.", $tracking_number, 2);
        }
        
        $this->connections = [];
        //LOG::output(LOG::S, "(database:close:done) All database connections has been closed.", $tracking_number, 1);
    }
    
}
