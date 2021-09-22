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

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\bamboo\Op;
use \mikisan\core\basis\bamboo\Exp;
use \mikisan\core\basis\bamboo\Piece;
use \mikisan\core\basis\bamboo\Where;
use \mikisan\core\basis\bamboo\Indexer;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../../src", true);
Autoload::register(__DIR__ . "/../../../tests/folder", true);

class Piece_Test extends TestCase
{
    use TestCaseTrait;
    
    public function setUp(): void
    {
        Indexer::reset();
    }
    
    public function test_constructor()
    {
        $piece  = new Piece();
        
        $this->assertSame(null, $piece->key);
        $this->assertSame(null, $piece->value);
        $this->assertSame(Op::MATCH, $piece->type);
        $this->assertSame(null, $piece->extra);
    }
    
    public function test_match()
    {
        $piece  = new Piece("test", Op::MATCH, 1);
        $this->assertSame("`test` = :test_0", $piece->toSQL());
    }
    
    public function test_not_match()
    {
        $piece  = new Piece("test", Op::NOTMATCH, 1);
        $this->assertSame("`test` <> :test_0", $piece->toSQL());
    }
    
    public function test_not()
    {
        $piece  = new Piece("test", Op::NOTMATCH, 1);
        $this->assertSame("`test` <> :test_0", $piece->toSQL());
    }
    
    public function test_eq()
    {
        $piece  = new Piece("test", Op::EQ, 1);
        $this->assertSame("`test` = :test_0", $piece->toSQL());
    }
    
    public function test_noteq()
    {
        $piece  = new Piece("test", Op::NOTEQ, 1);
        $this->assertSame("`test` != :test_0", $piece->toSQL());
    }
    
    public function test_lt()
    {
        $piece  = new Piece("test", Op::LT, 1);
        $this->assertSame("`test` < :test_0", $piece->toSQL());
    }
    
    public function test_gt()
    {
        $piece  = new Piece("test", Op::GT, 1);
        $this->assertSame("`test` > :test_0", $piece->toSQL());
    }
    
    public function test_lte()
    {
        $piece  = new Piece("test", Op::LTE, 1);
        $this->assertSame("`test` <= :test_0", $piece->toSQL());
    }
    
    public function test_gte()
    {
        $piece  = new Piece("test", Op::GTE, 1);
        $this->assertSame("`test` >= :test_0", $piece->toSQL());
    }
    
    public function test_in()
    {
        $piece  = new Piece("test", Op::IN, [1,2,3]);
        $this->assertSame("`test` IN (:test_0_0, :test_0_1, :test_0_2)", $piece->toSQL());
    }
    
    public function test_not_in()
    {
        $piece  = new Piece("test", Op::NOTIN, [1,2,3]);
        $this->assertSame("`test` NOT IN (:test_0_0, :test_0_1, :test_0_2)", $piece->toSQL());
    }
    
    public function test_is_null()
    {
        $piece  = new Piece("test", Op::ISNULL);
        $this->assertSame("`test` IS NULL", $piece->toSQL());
    }
    
    public function test_is_not_null()
    {
        $piece  = new Piece("test", Op::ISNOTNULL);
        $this->assertSame("`test` IS NOT NULL", $piece->toSQL());
    }
    
    public function test_between()
    {
        $piece  = new Piece("test", Op::BETWEEN, [1,10]);
        $this->assertSame("`test` BETWEEN :test_0_0 AND :test_0_1", $piece->toSQL());
    }
    
    public function test_not_between()
    {
        $piece  = new Piece("test", Op::NOTBETWEEN, [1,10]);
        $this->assertSame("`test` NOT BETWEEN :test_0_0 AND :test_0_1", $piece->toSQL());
    }
    
    public function test_like()
    {
        $piece  = new Piece("test", Op::LIKE, 1);
        $this->assertSame("`test` LIKE :test_0", $piece->toSQL());
    }
    
    public function test_likew()
    {
        $piece  = new Piece("test", Op::LIKEW, 1);
        $this->assertSame("`test` LIKE :test_0", $piece->toSQL());
    }
    
    public function test_wlike()
    {
        $piece  = new Piece("test", Op::WLIKE, 1);
        $this->assertSame("`test` LIKE :test_0", $piece->toSQL());
    }
    
    public function test_like_()
    {
        $piece  = new Piece("test", Op::LIKE_, 1);
        $this->assertSame("`test` LIKE :test_0", $piece->toSQL());
    }
    
    public function test__like()
    {
        $piece  = new Piece("test", Op::_LIKE, 1);
        $this->assertSame("`test` LIKE :test_0", $piece->toSQL());
    }
    
    public function test_not_like()
    {
        $piece  = new Piece("test", Op::NOTLIKE, 1);
        $this->assertSame("`test` NOT LIKE :test_0", $piece->toSQL());
    }
    
    public function test_not_likew()
    {
        $piece  = new Piece("test", Op::NOTLIKEW, 1);
        $this->assertSame("`test` NOT LIKE :test_0", $piece->toSQL());
    }
    
    public function test_not_wlike()
    {
        $piece  = new Piece("test", Op::NOTWLIKE, 1);
        $this->assertSame("`test` NOT LIKE :test_0", $piece->toSQL());
    }
    
    public function test_not_like_()
    {
        $piece  = new Piece("test", Op::NOTLIKE_, 1);
        $this->assertSame("`test` NOT LIKE :test_0", $piece->toSQL());
    }
    
    public function test_not__like()
    {
        $piece  = new Piece("test", Op::NOT_LIKE, 1);
        $this->assertSame("`test` NOT LIKE :test_0", $piece->toSQL());
    }
    
    public function test_default()
    {
        $piece  = new Piece("test", "REGEXP", "^A.*");
        $this->assertSame("`test` REGEXP :test_0", $piece->toSQL());
    }
    
    public function test_key_expression()
    {
        $piece  = new Piece(Exp::desc("MAX(:@)", ["test"]), Op::MATCH, 1);
        $this->assertSame("MAX(`test`) = :MAX_test__0", $piece->toSQL());
    }
    
}
