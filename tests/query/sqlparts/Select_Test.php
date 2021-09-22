<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/11
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\bamboo\Exp;
use \mikisan\core\basis\bamboo\Select;
use \mikisan\core\basis\bamboo\Indexer;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../../src", true);
Autoload::register(__DIR__ . "/../../../tests/folder", true);

class Select_Test extends TestCase
{
    use TestCaseTrait;
    
    public function setUp(): void
    {
        Indexer::reset();
    }
    
    public function test_constructor()
    {
        $sel  = new Select();
        
        $this->assertCount(0, $sel->select);
    }
    
    public function test_constructor_string()
    {
        $sel  = new Select("test");
        $this->assertSame("`test`", $sel->toSQL());
    }
    
    public function test_constructor_string_with_object()
    {
        $sel  = new Select("A.test");
        $this->assertSame("`A`.`test`", $sel->toSQL());
    }
    
    public function test_constructor_string_with_alias()
    {
        $sel  = new Select("A.test|:alias");
        $this->assertSame("`A`.`test` AS alias", $sel->toSQL());
    }
    
    public function test_constructor_string_with_strict_alias()
    {
        $sel  = new Select("A.test as alias");
        $this->assertSame("`A`.`test` AS alias", $sel->toSQL());
    }
    
    
    public function test_constructor_multiple_string()
    {
        $sel  = new Select("test1, test2, test3");
        $this->assertSame("`test1`, `test2`, `test3`", $sel->toSQL());
    }
    
    public function test_constructor_multiple_string_with_object()
    {
        $sel  = new Select("A.test1, B.test2, C.test3");
        $this->assertSame("`A`.`test1`, `B`.`test2`, `C`.`test3`", $sel->toSQL());
    }
    
    public function test_constructor_multiple_string_with_alias()
    {
        $sel  = new Select("A.test1|:alias1, B.test2|:alias2, C.test3|:alias3");
        $this->assertSame("`A`.`test1` AS alias1, `B`.`test2` AS alias2, `C`.`test3` AS alias3", $sel->toSQL());
    }
    
    public function test_constructor_multiple_string_with_strict_alias()
    {
        $sel  = new Select("A.test1 as alias1, B.test2 as alias2, C.test3 as alias3");
        $this->assertSame("`A`.`test1` AS alias1, `B`.`test2` AS alias2, `C`.`test3` AS alias3", $sel->toSQL());
    }
    
    public function test_constructor_expression()
    {
        $sel  = new Select(Exp::desc("COUNT(*)"));
        $this->assertSame("COUNT(*)", $sel->toSQL());
    }
    
    public function test_constructor_expression_embed()
    {
        $sel  = new Select(Exp::desc("MAX(:@)", "test1"));
        $this->assertSame("MAX(`test1`)", $sel->toSQL());
    }
    
    public function test_constructor_expression_embed_with_alias()
    {
        $sel  = new Select(Exp::desc("MAX(:@)|:alias1", "test1"));
        $this->assertSame("MAX(`test1`) AS alias1", $sel->toSQL());
    }
    
    public function test_constructor_literal()
    {
        $sel  = new Select(Exp::desc(":#", "test"));
        $this->assertSame("'test'", $sel->toSQL());
    }
    
    public function test_constructor_literal_with_alias()
    {
        $sel  = new Select(Exp::desc(":#|:alias", "test"));
        $this->assertSame("'test' AS alias", $sel->toSQL());
    }
    
    public function test_constructor_expression_multiembed_with_alias()
    {
        $sel  = new Select(Exp::desc("MAX(:@) + :#|:alias1", ["test1", "test2"]));
        $this->assertSame("MAX(`test1`) + 'test2' AS alias1", $sel->toSQL());
    }
    
    public function test_constructor_variable()
    {
        $sel  = new Select(Exp::desc("@seq_no := 0|:seq_no"));
        $this->assertSame("@seq_no := 0 AS seq_no", $sel->toSQL());
    }
    
    public function test_constructor_case()
    {
        $sel  = new Select(Exp::case("gender|:gender")->when("男", 1)->when("女", 2)->else(99));
        $expect = <<< EOL
CASE `gender`
    WHEN '男' THEN 1
    WHEN '女' THEN 2
    ELSE 99
END AS gender
EOL;
        $this->assertSame($expect, $sel->toSQL());
    }
    
    public function test_constructor_case_expression()
    {
        $sel  = new Select(Exp::case()
                                ->when(Exp::desc(":@ = :#", ["gender", "男"]), 1)
                                ->when(Exp::desc(":@ = :#", ["gender", "女"]), 2)
                                ->else(99)
                            );
        $expect = <<< EOL
CASE
    WHEN `gender` = '男' THEN 1
    WHEN `gender` = '女' THEN 2
    ELSE 99
END
EOL;
        $this->assertSame($expect, $sel->toSQL());
    }
    
    public function test_constructor_case_expression2()
    {
        $sel  = new Select(Exp::case("|:is_new")
                                ->when(Exp::desc(":@ > CURRENT_TIMESTAMP() - INTERVAL :# DAY", ["publish_date", 7]), 1)
                                ->else(0)
                            );
        $expect = <<< EOL
CASE
    WHEN `publish_date` > CURRENT_TIMESTAMP() - INTERVAL 7 DAY THEN 1
    ELSE 0
END AS is_new
EOL;
        $this->assertSame($expect, $sel->toSQL());
    }
    
}
