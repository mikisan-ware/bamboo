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

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\bamboo\DBConnect;
use \mikisan\core\basis\bamboo\DB;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);

class DBConnect_Test extends TestCase
{
    use TestCaseTrait;
    
    private $classname  = "mikisan\\core\\basis\\bamboo\\DBConnect";
    
    private function get_db_mysql()
    {
        $db                 = new DB;
        $db->id             = "default_mysql";
        $db->system         = "MySQL";
        $db->version        = "8.0";
        $db->dbhost         = "127.0.0.1";
        $db->port           = 3308;
        $db->dbname         = "bamboo_db";
        $db->schema         = "";
        $db->l_schema       = "";
        $db->dbuser         = "bamboo_user";
        $db->dbpass         = "bamboo_pass";
        $db->engine         = "InnoDB";
        $db->charset        = "utf8mb4";
        $db->collate        = "utf8mb4_general_ci";
        $db->persistent     = true;
        return $db;
    }
    
    private function get_db_pgsql()
    {
        $db                 = new DB;
        $db->id             = "default_postgres";
        $db->system         = "PostgreSQL";
        $db->version        = "13.4";
        $db->dbhost         = "127.0.0.1";
        $db->port           = 5432;
        $db->dbname         = "bamboo_db";
        $db->schema         = "";
        $db->l_schema       = "";
        $db->dbuser         = "root";
        $db->dbpass         = "root";
        $db->engine         = "InnoDB";
        $db->charset        = "utf8mb4";
        $db->collate        = "utf8mb4_general_ci";
        $db->persistent     = true;
        return $db;
    }
    
    public function setUp(): void
    {
        DBConnect::instance()->close_all();
    }
    
    public function test_instance()
    {
        $con    = DBConnect::instance();
        $this->assertSame($this->classname, get_class($con));
    }
    
    public function test_getPDO_mysql()
    {
        $db     = $this->get_db_mysql();
        $con    = DBConnect::instance()->getPDO($db, false);
        $this->assertSame("PDO", get_class($con));
    }
    
    public function test_getPDO_pgsql()
    {
        $db     = $this->get_db_pgsql();
        $con    = DBConnect::instance()->getPDO($db, false);
        $this->assertSame("PDO", get_class($con));
    }
    
}
