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

class DB
{
    
    public $id;
    public $system;
    public $version;
    public $dbhost;
    public $port;
    public $dbname;
    public $schema;
    public $l_schema;       // 変更履歴ログ用スキーマ
    public $dbuser;
    public $dbpass;
    public $engine;
    public $charset;
    public $collate;
    
}
