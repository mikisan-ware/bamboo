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

class OrderBy_Test extends TestCase
{
    use TestCaseTrait;
    
    private $classname      = "mikisan\\core\\basis\\bamboo\\OrderPiece";
    private $exp_classname  = "mikisan\\core\\basis\\bamboo\\Exp";
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $ob     = new OrderBy();
        
        $this->assertCount(0, $ob->order_by);
    }
    
    public function test_getter_invalid_parameter()
    {
        $ob     = new OrderBy();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderByでは other は取得できません。");
        //
        $test   = $ob->other;
    }
    
    public function test_constructor_string_arg()
    {
        $ob     = new OrderBy("test");
        
        $this->assertCount(1, $ob->order_by);
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test", $ob->order_by[0]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
    }
    
    public function test_constructor_expression()
    {
        $ob     = new OrderBy(Exp::as("MAX(:@)", "test"));
        
        $this->assertCount(1, $ob->order_by);
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame($this->exp_classname, get_class($ob->order_by[0]->expression));
        $this->assertSame("MAX(`test`)", $ob->order_by[0]->expression->toSql());
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
    }
    
    public function test_constructor_expression_arg()
    {
        $ob     = new OrderBy(Exp::as(":@ - :@", ["test1", "test2"]));
        
        $this->assertCount(1, $ob->order_by);
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame($this->exp_classname, get_class($ob->order_by[0]->expression));
        $this->assertSame("`test1` - `test2`", $ob->order_by[0]->expression->toSql());
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
    }
    
    public function test_constructor_string_args()
    {
        $ob     = new OrderBy("test1", "test2", "test3");
        
        $this->assertCount(3, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test1", $ob->order_by[0]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[1]));
        $this->assertSame("test2", $ob->order_by[1]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[1]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[2]));
        $this->assertSame("test3", $ob->order_by[2]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[2]->sort);
    }
    
    public function test_constructor_array_arg()
    {
        $ob     = new OrderBy(["test", OrderBy::ASC]);
        
        $this->assertCount(1, $ob->order_by);
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test", $ob->order_by[0]->expression);
        $this->assertSame(OrderBy::ASC, $ob->order_by[0]->sort);
    }
    
    public function test_constructor_array_arg_a_lot_of_elements()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderPieceに不正な数のパラメターが渡されました。[要素数: 3]");
        
        $ob  = new OrderBy(["test", OrderBy::ASC, "much_element"]);
    }
    
    public function test_constructor_array_args()
    {
        $ob     = new OrderBy(["test1", OrderBy::ASC], ["test2", OrderBy::DESC], ["test3"]);
        
        $this->assertCount(3, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test1", $ob->order_by[0]->expression);
        $this->assertSame(OrderBy::ASC, $ob->order_by[0]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[1]));
        $this->assertSame("test2", $ob->order_by[1]->expression);
        $this->assertSame(OrderBy::DESC, $ob->order_by[1]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[2]));
        $this->assertSame("test3", $ob->order_by[2]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[2]->sort);
    }
    
    public function test_constructor_OrderPiece_arg()
    {
        $ob     = new OrderBy(new OrderPiece("test", OrderBy::ASC));
        
        $this->assertCount(1, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test", $ob->order_by[0]->expression);
        $this->assertSame(OrderBy::ASC, $ob->order_by[0]->sort);
    }
    
    public function test_constructor_multiple_args()
    {
        $op     = new OrderPiece("test3", OrderBy::ASC);
        $ob     = new OrderBy("test1", ["test2", OrderBy::DESC], $op, [Exp::as(":@ * :@", ["test4", "test5"]), OrderBy::ASC]);
        
        $this->assertCount(4, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test1", $ob->order_by[0]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[1]));
        $this->assertSame("test2", $ob->order_by[1]->expression);
        $this->assertSame(OrderBy::DESC, $ob->order_by[1]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[2]));
        $this->assertSame("test3", $ob->order_by[2]->expression);
        $this->assertSame(OrderBy::ASC, $ob->order_by[2]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[3]));
        $this->assertSame($this->exp_classname, get_class($ob->order_by[3]->expression));
        $this->assertSame("`test4` * `test5`", $ob->order_by[3]->expression->toSql());
        $this->assertSame(OrderBy::ASC, $ob->order_by[3]->sort);
    }
    
    public function test_constructor_invalid_arg()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("OrderByに不正なデータが渡されました。[3:integer]");
        
        $ob     = new OrderBy(3);
    }
    
    public function test_chain_method()
    {
        $ob     = (new OrderBy())->add("test");
        
        $this->assertCount(1, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test", $ob->order_by[0]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
    }
    
    public function test_chain_methods()
    {
        $ob     = (new OrderBy())->add("test1")->add(["test2", OrderBy::ASC])->add(new OrderPiece("test3", OrderBy::DESC));
        
        $this->assertCount(3, $ob->order_by);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[0]));
        $this->assertSame("test1", $ob->order_by[0]->expression);
        $this->assertSame(BambooSettings::DEFAULT_SORT, $ob->order_by[0]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[1]));
        $this->assertSame("test2", $ob->order_by[1]->expression);
        $this->assertSame(OrderBy::ASC, $ob->order_by[1]->sort);
        //
        $this->assertSame($this->classname, get_class($ob->order_by[2]));
        $this->assertSame("test3", $ob->order_by[2]->expression);
        $this->assertSame(OrderBy::DESC, $ob->order_by[2]->sort);
    }
    
    public function test_toSQL_single_arg()
    {
        $ob     = new OrderBy("test1");
        
        $expect = "`test1` ".BambooSettings::DEFAULT_SORT;
        $this->assertSame($expect, $ob->toSQL());
    }
    
    public function test_toSQL_multiple_args()
    {
        $op     = new OrderPiece("A.test3", OrderBy::ASC);
        $ob     = new OrderBy("test1", ["test2", OrderBy::DESC], $op, [Exp::as(":@ * :@", ["test4", "test5"]), OrderBy::ASC]);
        
        $expect = "`test1` ".BambooSettings::DEFAULT_SORT.", `test2` DESC, `A`.`test3` ASC, `test4` * `test5` ASC";
        $this->assertSame($expect, $ob->toSQL());
    }
    
}
