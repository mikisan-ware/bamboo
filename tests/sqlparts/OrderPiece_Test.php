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

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\bamboo\Exp;
use \mikisan\core\basis\bamboo\OrderBy;
use \mikisan\core\basis\bamboo\OrderPiece;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../src", true);
Autoload::register(__DIR__ . "/../../tests/folder", true);

class OrderPiece_Test extends TestCase
{
    use TestCaseTrait;
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $op     = new OrderPiece();
        
        $this->assertSame(null, $op->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $op->sort);
    }
    
    public function test_constructor_set()
    {
        $op     = new OrderPiece("test");
        
        $this->assertSame("test", $op->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $op->sort);
    }
    
    public function test_constructor_set_and_sort()
    {
        $op     = new OrderPiece("test", OrderBy::ASC);
        
        $this->assertSame("test", $op->expression);
        $this->assertSame(OrderBy::ASC, $op->sort);
    }
    
    public function test_constructor_set_expression_and_sort()
    {
        $op     = new OrderPiece(Exp::as("MAX(:@)", "test"), OrderBy::ASC);
        
        $this->assertSame("MAX(`test`)", $op->expression->toSQL());
        $this->assertSame(OrderBy::ASC, $op->sort);
    }
    
    public function test_constructor_set_multipart_expression_and_sort()
    {
        $op     = new OrderPiece(Exp::as(":@ - :@", ["test1", "test2"]), OrderBy::ASC);
        
        $this->assertSame("`test1` - `test2`", $op->expression->toSQL());
        $this->assertSame(OrderBy::ASC, $op->sort);
    }
    
    public function test_getter_invalid_parameter()
    {
        $op     = new OrderPiece("test");
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderPieceでは other は取得できません。");
        
        $test   = $op->other;
    }
    
    public function test_constructor_a_lot_of_parameters()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderPieceに不正な数のパラメターが渡されました。[要素数: 3]");
        
        $op     = new OrderPiece("test", OrderBy::ASC, 3);
    }
    
    public function test_constructor_invalid_sort()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderPieceの第二引数に不正なソート条件が渡されました。[medium]");
        
        $op     = new OrderPiece("test", "medium");
    }
    
    public function test_chain_method()
    {
        $op     = (new OrderPiece())->setExpression("test1");
        $this->assertSame("test1", $op->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $op->sort);
        //
        $op->setSort(OrderBy::ASC);
        $this->assertSame(OrderBy::ASC, $op->sort);
        $op->setExpression("test2")->setSort(OrderBy::DESC);
        $this->assertSame("test2", $op->expression);
        $this->assertSame(OrderBy::DESC, $op->sort);
    }
    
    public function test_toSQL()
    {
        $op     = new OrderPiece("test");
        $this->assertSame("`test` " . BambooSettings::DEFAULT_SORT, $op->toSQL());
    }
    
    public function test_expression_toSQL()
    {
        $op     = new OrderPiece(Exp::as("MAX(:@)", ["test"]), OrderBy::ASC);
        $this->assertSame("MAX(`test`) ASC", $op->toSQL());
    }
    
    public function test_multipart_expression_toSQL()
    {
        $op     = new OrderPiece(Exp::as(":@ - :@", ["A.test1", "B.test2"]), OrderBy::DESC);
        $this->assertSame("`A`.`test1` - `B`.`test2` DESC", $op->toSQL());
    }
    
}
