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
use \mikisan\core\basis\bamboo\Limit;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../../src", true);

class Limit_Test extends TestCase
{
    use TestCaseTrait;
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $limit  = new Limit();
        
        $this->assertSame(null, $limit->count);
        $this->assertSame(null, $limit->offset);
    }
    
    public function test_constructor_set_count()
    {
        $limit  = new Limit(7);
        
        $this->assertSame(7, $limit->count);
        $this->assertSame(null, $limit->offset);
    }
    
    public function test_constructor_set_count_offset()
    {
        $limit  = new Limit(7, 3);
        
        $this->assertSame(7, $limit->count);
        $this->assertSame(3, $limit->offset);
    }
    
    public function test_setter()
    {
        $limit  = new Limit();
        $limit->count   = 88;
        
        $this->assertSame(88, $limit->count);
    }
    
    public function test_setter_count()
    {
        $limit  = new Limit();
        $limit->count   = 88;
        
        $this->assertSame(88, $limit->count);
    }
    
    public function test_setter_offset()
    {
        $limit  = new Limit();
        $limit->offset   = 12;
        
        $this->assertSame(12, $limit->offset);
    }
    
    public function test_setter_invalid_parameter()
    {
        $limit  = new Limit();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Limitでは other は設定できません。");
        
        $limit->other   = 12;
    }
    
    public function test_getter_invalid_parameter()
    {
        $limit  = new Limit();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Limitでは other は取得できません。");
        
        $test   = $limit->other;
    }
    
    public function test_constructor_a_lot_of_parameter()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Limitに不正な数のパラメターが渡されました。[要素数: 3]");
        
        $limit  = new Limit(3, 2, 1);
    }
    
    public function test_constructor_invalid_type_arg1()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Limitの第一引数に数値以外が渡されました。[a:string]");
        
        $limit  = new Limit("a");
    }
    
    public function test_constructor_invalid_type_arg2()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Limitの第二引数に数値以外が渡されました。[a:string]");
        
        $limit  = new Limit(3, "a");
    }
    
    public function test_chain_method()
    {
        $limit  = (new Limit())->setCount(123);
        $this->assertSame(123, $limit->count);
        //
        $limit->setOffset(30);
        $this->assertSame(30, $limit->offset);
        //
        $limit->setCount(543)->setOffset(210);
        $this->assertSame(543, $limit->count);
        $this->assertSame(210, $limit->offset);
    }
}
