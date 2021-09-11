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
use \mikisan\core\basis\bamboo\Having;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../src", true);
Autoload::register(__DIR__ . "/../../tests/folder", true);

class Having_Test extends TestCase
{
    use TestCaseTrait;
    
    private $classname      = "mikisan\\core\\basis\\bamboo\\Piece";
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $having  = new Having();
        
        $this->assertCount(0, $having->having);
        $this->assertSame(Having::AND, $having->and_or);
    }
    
    public function test_constructor_and()
    {
        $having  = new Having(Having::AND);
        
        $this->assertCount(0, $having->having);
        $this->assertSame(Having::AND, $having->and_or);
    }
    
    public function test_constructor_or()
    {
        $having  = new Having(Having::OR);
        
        $this->assertCount(0, $having->having);
        $this->assertSame(Having::OR, $having->and_or);
    }
    
    public function test_getter_invalid_parameter()
    {
        $having  = new Having();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Having では other は取得できません。");
        //
        $test   = $having->other;
    }
    
    public function test_constructor_3_parameters()
    {
        $having  = new Having([Exp::as("MAX(:@)", "test"), Op::LT, 7]);
        $this->assertSame("MAX(`test`) < :MAX_test__0", $having->toSQL());
    }
    
}
