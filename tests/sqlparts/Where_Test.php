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
use \mikisan\core\basis\bamboo\Where;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../src", true);
Autoload::register(__DIR__ . "/../../tests/folder", true);

class Where_Test extends TestCase
{
    use TestCaseTrait;
    
    private $classname      = "mikisan\\core\\basis\\bamboo\\Piece";
    
    public function setUp(): void {}
    
    public function test_constructor()
    {
        $where  = new Where();
        
        $this->assertCount(0, $where->where);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_and()
    {
        $where  = new Where(Where::AND);
        
        $this->assertCount(0, $where->where);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_or()
    {
        $where  = new Where(Where::OR);
        
        $this->assertCount(0, $where->where);
        $this->assertSame(Where::OR, $where->and_or);
    }
    
    public function test_getter_invalid_parameter()
    {
        $where  = new Where();
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Where では other は取得できません。");
        //
        $test   = $where->other;
    }
    
    public function test_constructor_2_parameters()
    {
        $where  = new Where(["test", 1]);
        
        $this->assertCount(1, $where->where);
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::MATCH, $where->where[0]->type);
        $this->assertSame(null, $where->where[0]->extra);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_3_parameters()
    {
        $where  = new Where(["test", Op::NOT, 1]);
        
        $this->assertCount(1, $where->where);
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::NOT, $where->where[0]->type);
        $this->assertSame(null, $where->where[0]->extra);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_4_parameters()
    {
        $where  = new Where(["test", Op::MATCH, 1, "INET_ATON(:key)"]);
        
        $this->assertCount(1, $where->where);
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::MATCH, $where->where[0]->type);
        $this->assertSame("INET_ATON(:key)", $where->where[0]->extra);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_3_parameters_and()
    {
        $where  = new Where(Where::AND, ["test", Op::NOT, 1]);
        
        $this->assertCount(1, $where->where);
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::NOT, $where->where[0]->type);
        $this->assertSame(null, $where->where[0]->extra);
        $this->assertSame(Where::AND, $where->and_or);
    }
    
    public function test_constructor_3_parameters_or()
    {
        $where  = new Where(Where::OR, ["test", Op::NOT, 1]);
        
        $this->assertCount(1, $where->where);
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::NOT, $where->where[0]->type);
        $this->assertSame(null, $where->where[0]->extra);
        $this->assertSame(Where::OR, $where->and_or);
    }
    
    public function test_constructor_1_parameters()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Where に渡された配列の引数の数が不正です。許容値は2つ以上4つ以下です。[要素数: 1]");
        $where  = new Where(["test"]);
    }
    
    public function test_constructor_5_parameters()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Where に渡された配列の引数の数が不正です。許容値は2つ以上4つ以下です。[要素数: 5]");
        $where  = new Where(["test", Op::MATCH, 1, "INET_ATON(:key)", "test"]);
    }
    
    public function test_constructor_invalid_value()
    {
        $this->expectException(BambooException::class);
        $this->expectExceptionMessage("Where に不正なデータが渡されました。[3:integer]");
        $where  = new Where(3);
    }
    
    public function test_constructor_multiple()
    {
        $where  = new Where(["test1", Op::NOT, 1], ["test2", Op::LTE, 5], ["test3", Op::IN, [1, 2, 3]]);
        
        $this->assertCount(3, $where->where);
        $this->assertSame(Where::AND, $where->and_or);
        //
        $this->assertSame($this->classname, get_class($where->where[0]));
        $this->assertSame("test1", $where->where[0]->key);
        $this->assertSame(1, $where->where[0]->value);
        $this->assertSame(Op::NOT, $where->where[0]->type);
        $this->assertSame(null, $where->where[0]->extra);
        //
        $this->assertSame($this->classname, get_class($where->where[1]));
        $this->assertSame("test2", $where->where[1]->key);
        $this->assertSame(5, $where->where[1]->value);
        $this->assertSame(Op::LTE, $where->where[1]->type);
        $this->assertSame(null, $where->where[1]->extra);
        //
        $this->assertSame($this->classname, get_class($where->where[2]));
        $this->assertSame("test3", $where->where[2]->key);
        $this->assertCount(3, $where->where[2]->value);
        $this->assertContains(1, $where->where[2]->value);
        $this->assertContains(2, $where->where[2]->value);
        $this->assertContains(3, $where->where[2]->value);
        $this->assertSame(Op::IN, $where->where[2]->type);
        $this->assertSame(null, $where->where[2]->extra);
    }
    
    public function test_toSQL_multiple()
    {
        $where  = new Where(Where::AND, ["test1", Op::NOT, 1], ["A.test2", Op::LTE, 5], ["test3", Op::IN, [1, 2, 3]]);
        
        $expect = <<< EOL
`test1` <> :test1_0
  AND `A`.`test2` <= :A_test2_1
  AND `test3` IN (:test3_2_0, :test3_2_1, :test3_2_2)
EOL;
        $this->assertSame($expect, $where->toSQL());
    }
    
    public function test_toSQL_nested_multiple()
    {
        $w      = new Where(Where::AND, ["test2", Op::NOT, 1], ["test3", 2]);
        $where  = new Where(Where::OR, ["test1", Op::LT, 1], $w);
        
        $expect = <<< EOL
`test1` < :test1_0
   OR (`test2` <> :test2_1
  AND `test3` = :test3_2
      )
EOL;
        $this->assertSame($expect, $where->toSQL());
    }
}
