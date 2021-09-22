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
use \mikisan\core\basis\bamboo\Op;
use \mikisan\core\basis\bamboo\Exp;
use \mikisan\core\basis\bamboo\Query;
use \mikisan\core\basis\bamboo\Where;
use \mikisan\core\basis\bamboo\GroupBy;
use \mikisan\core\basis\bamboo\Having;
use \mikisan\core\basis\bamboo\OrderBy;
use \mikisan\core\basis\bamboo\Column;
use \mikisan\core\basis\bamboo\Indexer;
use \mikisan\core\basis\settings\BambooSettings;
use \mikisan\core\exception\BambooException;

require_once __DIR__ . "/../../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../../src", true);
Autoload::register(__DIR__ . "/../folder", true);

class Query_Test extends TestCase
{
    use TestCaseTrait;
    
    private     $classname  = "mikisan\\core\\basis\\bamboo\\Query";

    public function setUp(): void
    {
        Indexer::reset();
    }
    
    public function test_build()
    {
        $qry  = Query::build();
        $this->assertSame($this->classname, get_class($qry));
    }
    
    public function test_select()
    {
        $qry  = Query::build()->select("A.test1, B.test2, C.test3");
        $expect = <<< EOL
SELECT
    `A`.`test1`, `B`.`test2`, `C`.`test3`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from()
    {
        $qry  = Query::build()->from("test");
        $expect = <<< EOL
FROM
    `test`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from_with_alias()
    {
        $qry  = Query::build()->from("test|:A");
        $expect = <<< EOL
FROM
    `test` AS A
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from_multiple_with_alias()
    {
        $qry  = Query::build()->from("test1|:A, test2|:B, test3|:C");
        $expect = <<< EOL
FROM
    `test1` AS A,
    `test2` AS B,
    `test3` AS C
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from_query()
    {
        $from   = Query::build()->from("test")->where(["param1", 1])->select("*")->alias("A");
        $qry    = Query::build()->from($from);
        $expect = <<< EOL
FROM
    (
        SELECT
            *
        FROM
            `test`
        WHERE `param1` = :param1_0
    ) AS A
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from_multiple_query()
    {
        $from1  = Query::build()->from("test")->where(["param1", 1])->select("*")->alias("A");
        $from2  = Query::build()->from("test")->where(["param1", 1])->select("*")->alias("B");
        $from3  = Query::build()->from("test")->where(["param1", 1])->select("*")->alias("C");
        $qry    = Query::build()->from($from1, $from2, $from3);
        $expect = <<< EOL
FROM
    (
        SELECT
            *
        FROM
            `test`
        WHERE `param1` = :param1_0
    ) AS A,
    (
        SELECT
            *
        FROM
            `test`
        WHERE `param1` = :param1_1
    ) AS B,
    (
        SELECT
            *
        FROM
            `test`
        WHERE `param1` = :param1_2
    ) AS C
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_from_string()
    {
        $q = <<< EOL
SELECT [:start_date] + INTERVAL seq_no DAY AS date
FROM (
    SELECT @seq_no := 0 AS seq_no
    UNION
    SELECT @seq_no := @seq_no + 1 AS seq_no FROM information_schema.COLUMNS
    LIMIT 5
) AS tmp
EOL;
        
        $qry   = Query::build()->from(Query::text($q)->as("A"));
        $expect = <<< EOL
FROM
    (
        SELECT [:start_date] + INTERVAL seq_no DAY AS date
        FROM (
            SELECT @seq_no := 0 AS seq_no
            UNION
            SELECT @seq_no := @seq_no + 1 AS seq_no FROM information_schema.COLUMNS
            LIMIT 5
        ) AS tmp
    ) AS A
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_inner_join_on()
    {
        $qry  = Query::build()->from("test1|:A")->innerJoin("test2|:B")->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
INNER JOIN
    `test2` AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_inner_join_using()
    {
        $qry  = Query::build()->from("test1|:A")->innerJoin("test2|:B")->using("test1");
        $expect = <<< EOL
FROM
    `test1` AS A
INNER JOIN
    `test2` AS B
USING `test1`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_left_join_on()
    {
        $qry  = Query::build()->from("test1|:A")->leftJoin("test2|:B")->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
LEFT OUTER JOIN
    `test2` AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_left_join_using()
    {
        $qry  = Query::build()->from("test1|:A")->leftJoin("test2|:B")->using("test1");
        $expect = <<< EOL
FROM
    `test1` AS A
LEFT OUTER JOIN
    `test2` AS B
USING `test1`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_right_join_on()
    {
        $qry  = Query::build()->from("test1|:A")->rightJoin("test2|:B")->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
RIGHT OUTER JOIN
    `test2` AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_right_join_using()
    {
        $qry  = Query::build()->from("test1|:A")->rightJoin("test2|:B")->using("test1");
        $expect = <<< EOL
FROM
    `test1` AS A
RIGHT OUTER JOIN
    `test2` AS B
USING `test1`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }

    public function test_full_join_on()
    {
        $qry  = Query::build()->from("test1|:A")->fullJoin("test2|:B")->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
FULL OUTER JOIN
    `test2` AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_full_join_using()
    {
        $qry  = Query::build()->from("test1|:A")->fullJoin("test2|:B")->using("test1");
        $expect = <<< EOL
FROM
    `test1` AS A
FULL OUTER JOIN
    `test2` AS B
USING `test1`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_cross_join_on()
    {
        $qry  = Query::build()->from("test1|:A")->crossJoin("test2|:B")->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
CROSS JOIN
    `test2` AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_cross_join_using()
    {
        $qry  = Query::build()->from("test1|:A")->crossJoin("test2|:B")->using("test1");
        $expect = <<< EOL
FROM
    `test1` AS A
CROSS JOIN
    `test2` AS B
USING `test1`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_join_by_query()
    {
        $table  = Query::build()->from("test2")->where(["test", 1])->select("test1, test2, test3")->alias("B");
        $qry    = Query::build()->from("test1|:A")->leftJoin($table)->on("A.test1", "B.test2");
        $expect = <<< EOL
FROM
    `test1` AS A
LEFT OUTER JOIN
    (
        SELECT
            `test1`, `test2`, `test3`
        FROM
            `test2`
        WHERE `test` = :test_0
    ) AS B
ON `A`.`test1` = `B`.`test2`
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_where()
    {
        $qry  = Query::build()->where(["A.test1", 1]);
        $this->assertSame("WHERE `A`.`test1` = :A_test1_0", $qry->toSQL());
    }
    
    public function test_where_multiple()
    {
        $qry    = Query::build()->where(["A.test1", 1], ["B.test2", Op::GTE, 7], ["test3", Op::IN, [3,5,7]]);
        $expect = <<< EOL
WHERE `A`.`test1` = :A_test1_0
  AND `B`.`test2` >= :B_test2_1
  AND `test3` IN (:test3_2_0, :test3_2_1, :test3_2_2)
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_where_multiple2()
    {
        $where  = new Where(["B.test2", Op::GTE, 7], ["test3", Op::IN, [3,5,7]]);
        $qry    = Query::build()->where(Where::OR, ["A.test1", 1], $where);
        $expect = <<< EOL
WHERE `A`.`test1` = :A_test1_0
   OR (`B`.`test2` >= :B_test2_1
  AND `test3` IN (:test3_2_0, :test3_2_1, :test3_2_2)
      )
EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_groupBy()
    {
        $qry  = Query::build()->groupBy("test1", "test2", "test3");
        $this->assertSame("GROUP BY `test1`, `test2`, `test3`", $qry->toSQL());
    }
    
    public function test_having()
    {
        $qry  = Query::build()->having([Exp::desc("COUNT(:@)", "test"), Op::GTE, 20]);
        $this->assertSame("HAVING COUNT(`test`) >= :COUNT_test__0", $qry->toSQL());
    }
    
    public function test_having_multiple()
    {
        $qry  = Query::build()->having(
                Having::AND, 
                [Exp::desc("COUNT(:@)", "test1"), Op::GTE, 20], 
                [Exp::desc("MAX(:@)", "test2"), 100]);
        $this->assertSame("HAVING COUNT(`test1`) >= :COUNT_test1__0 AND MAX(`test2`) = :MAX_test2__1", $qry->toSQL());
    }
    
    public function test_orderBy()
    {
        $qry  = Query::build()->orderBy(["test1", OrderBy::ASC]);
        $this->assertSame("ORDER BY `test1` ASC", $qry->toSQL());
    }
    
    public function test_orderBy_multiple()
    {
        $qry  = Query::build()->orderBy(["test1", OrderBy::ASC], ["test2", OrderBy::DESC], "test3");
        $this->assertSame("ORDER BY `test1` ASC, `test2` DESC, `test3` " . BambooSettings::DEFAULT_SORT, $qry->toSQL());
    }
    
    public function test_limit()
    {
        $qry  = Query::build()->limit(3);
        $this->assertSame("LIMIT 3", $qry->toSQL());
    }
    
    public function test_limit_with_offset()
    {
        $qry  = Query::build()->limit(3,7);
        $this->assertSame("LIMIT 3 OFFSET 7", $qry->toSQL());
    }
    
    public function test_lock()
    {
        $qry  = Query::build()->lock();
        $this->assertSame("FOR UPDATE", $qry->toSQL());
    }
    
    public function test_lock_false()
    {
        $qry  = Query::build()->lock(false);
        $this->assertSame("", $qry->toSQL());
    }
    
    public function test_lock_true()
    {
        $qry  = Query::build()->lock(true);
        $this->assertSame("FOR UPDATE", $qry->toSQL());
    }
    
    public function test_insert()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->insert("test")->add($values);
        $expect = <<< EOL
INSERT INTO `test`(
    `param1`, `param2`, `param3`
) VALUES (
    :param1_0, :param2_1, :param3_2
)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_insert_specify_table()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->insert()->table("test")->add($values);
        $expect = <<< EOL
INSERT INTO `test`(
    `param1`, `param2`, `param3`
) VALUES (
    :param1_0, :param2_1, :param3_2
)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_insert_add_multiple()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->insert()->table("test")->add($values)->add(["param4" => "def"], ["param5" => 999]);
        $expect = <<< EOL
INSERT INTO `test`(
    `param1`, `param2`, `param3`, `param4`, `param5`
) VALUES (
    :param1_0, :param2_1, :param3_2, :param4_3, :param5_4
)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_insert_select_all()
    {
        $src    = Query::build()->from("table1")
                                    ->where(["param1", 1], ["param2", Op::GT, 2], ["param3", Op::IN, [1,2,3]])
                                    ->select("*")
                                    ;
        $qry    = Query::build()->insert("table2")->source($src);
        $expect = <<< EOL
INSERT INTO `table2`
SELECT
    *
FROM
    `table1`
WHERE `param1` = :param1_0
  AND `param2` > :param2_1
  AND `param3` IN (:param3_2_0, :param3_2_1, :param3_2_2)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_insert_select_piece()
    {
        $src    = Query::build()->from("table1")
                                    ->where(["param1", 1], ["param2", Op::GT, 2], ["param3", Op::IN, [1,2,3]])
                                    ->select("param1, param2, param3")
                                    ;
        $qry    = Query::build()->insert("table2")->add(["param1", "param2", "param3"])->source($src);
        $expect = <<< EOL
INSERT INTO `table2`(
    `param1`, `param2`, `param3`
)
SELECT
    `param1`, `param2`, `param3`
FROM
    `table1`
WHERE `param1` = :param1_0
  AND `param2` > :param2_1
  AND `param3` IN (:param3_2_0, :param3_2_1, :param3_2_2)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->update("test")->set($values);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = :param1_0, `param2` = :param2_1, `param3` = :param3_2

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_with_continual_set()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->update("test")->set($values)->set(["param4" => "abc", "param5" => 999]);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = :param1_0, `param2` = :param2_1, `param3` = :param3_2, `param4` = :param4_3, `param5` = :param5_4

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_with_continual_set_multiple()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->update("test")->set($values)->set(["param4" => "abc"], ["param5" => 999]);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = :param1_0, `param2` = :param2_1, `param3` = :param3_2, `param4` = :param4_3, `param5` = :param5_4

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_specify_table()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->update()->table("test")->set($values);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = :param1_0, `param2` = :param2_1, `param3` = :param3_2

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_with_where()
    {
        $values = [
            "param1"    => 1,
            "param2"    => "abc",
            "param3"    => new DateTime("now")
        ];
        $qry    = Query::build()->update("test")->set($values)->where(["param1", 5], ["param2", Op::GT, 999]);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = :param1_0, `param2` = :param2_1, `param3` = :param3_2
WHERE `param1` = :param1_3
  AND `param2` > :param2_4

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_exp()
    {
        $values = [
            "param1"    => Exp::desc("CONCAT(:@, :@)", ["first_name", "last_name"])
        ];
        $qry    = Query::build()->update("test")->set($values);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = CONCAT(`first_name`, `last_name`)

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_subquery()
    {
        $values = [
            "param1"    => Query::build()
                                ->from("managers")
                                ->where(["test.no", Column::as("manager.no")])
                                ->select("managers.name")
        ];
        $qry    = Query::build()->update("test")->set($values);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = (
        SELECT
            `managers`.`name`
        FROM
            `managers`
        WHERE `test`.`no` = `manager`.`no`
    )

EOL;
        $this->assertSame($expect, $qry->toSQL());
    }
    
    public function test_update_case()
    {
        $values = [
            "param1"    => Exp::case("test.gender")->when("M", "Mr.")->when("F", "Ms.")
        ];
        $qry    = Query::build()->update("test")->set($values);
        $expect = <<< EOL
UPDATE `test`
SET
    `param1` = CASE `test`.`gender`
        WHEN 'M' THEN 'Mr.'
        WHEN 'F' THEN 'Ms.'
    END

EOL;    
        $this->assertSame($expect, $qry->toSQL());
    }
    
}
