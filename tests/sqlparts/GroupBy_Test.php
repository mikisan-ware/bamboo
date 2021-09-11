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
use \mikisan\core\basis\bamboo\GroupBy;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../src", true);
Autoload::register(__DIR__ . "/../../tests/folder", true);

class GroupBy_Test extends TestCase
{
    use TestCaseTrait;
    
    private $exp_classname  = "mikisan\\core\\basis\\bamboo\\Exp";
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $gb     = new GroupBy();
        
        $this->assertCount(0, $gb->group_by);
    }
    
    public function test_getter_invalid_parameter()
    {
        $ob     = new GroupBy();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("GroupByでは other は取得できません。");
        //
        $test   = $ob->other;
    }
    
    public function test_constructor_string_arg()
    {
        $gb     = new GroupBy("test");
        
        $this->assertCount(1, $gb->group_by);
        $this->assertSame("test", $gb->group_by[0]);
    }
    
    public function test_constructor_string_args()
    {
        $gb     = new GroupBy("test1", "test2", "test3");
        
        $this->assertCount(3, $gb->group_by);
        $this->assertSame("test1", $gb->group_by[0]);
        $this->assertSame("test2", $gb->group_by[1]);
        $this->assertSame("test3", $gb->group_by[2]);
    }
    
    public function test_constructor_expression()
    {
        $gb     = new GroupBy(Exp::as("MAX(:@)", "test"));
        
        $this->assertCount(1, $gb->group_by);
        $this->assertSame($this->exp_classname, get_class($gb->group_by[0]));
        $this->assertSame("MAX(`test`)", $gb->group_by[0]->toSql());
    }
    
    public function test_toSQL_single()
    {
        $gb     = new GroupBy("test");
        $this->assertSame("`test`", $gb->toSQL());
    }
    
    public function test_toSQL_multiple()
    {
        $gb     = new GroupBy("test1", "A.test2", "test3");
        $this->assertSame("`test1`, `A`.`test2`, `test3`", $gb->toSQL());
    }
    
    public function test_toSQL_expression()
    {
        $gb     = new GroupBy(Exp::as("MAX(:@)", "test"));
        $this->assertSame("MAX(`test`)", $gb->toSQL());
    }
    
}
