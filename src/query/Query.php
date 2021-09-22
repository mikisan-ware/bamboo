<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\basis\bamboo\OrderBy;
use \mikisan\core\basis\bamboo\Limit;
use \mikisan\core\basis\bamboo\DBUTIL;
use \mikisan\core\util\EX;
use \mikisan\core\util\STR;
use \mikisan\core\exception\BambooException;

class Query
{
    
    const   BUILD = "BUILD", STRING = "STRING";
    
    const   SELECT = "SELECT", INSERT = "INSERT", 
            UPDATE = "UPDATE", DELETE = "DELETE";
    
    private $mode       = self::BUILD;
    private $type       = self::SELECT;
    
    private $query      = "";
    
    private $select     = null;
    private $from       = null;
    private $where      = null;
    private $group_by   = null;
    private $having     = null;
    private $order_by   = null;
    private $limit      = null;
    private $lock       = false;
    private $alias      = null;
    
    private $insert     = null;
    private $source     = null;
    private $into       = null;
    
    public function __construct() {}
    
    public static function text(string $query): Query
    {
        $qry        = new Query();
        $qry->query = DBUTIL::strip($query);
        $qry->mode  = self::STRING;
        return $qry;
    }
    
    public static function build($type = self::SELECT): Query
    {
        $qry        = new Query();
        $qry->type  = $type;
        $qry->mode  = self::BUILD;
        return $qry;
    }
    
    public function __get(string $key): string
    {
        switch(true)
        {
            case $key === "mode":
            case $key === "type":
            case $key === "alias":
                
                return $this->{$key};
        }
        
        throw new BambooException("Queryでは {$key} は取得できません。");
    }
    
    public function reset(): Query
    {
        $this->counter  = 0;
    }
    
    public function table(string $table): Query
    {
        switch(true)
        {
            case $this->type === Query::INSERT:
                $this->insert->table($table);
                break;
            
            case $this->type === Query::UPDATE:
                $this->update->table($table);
                break;
            
            default:
                throw new BambooException("現在の Query の動作モードでは table は設定できません。");
        }
        return $this;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //
    // INSERT Query
    
    public function insert(string $table = ""): Query
    {
        $this->insert   = new Insert($table);
        $this->type     = Query::INSERT;
        return $this;
    }
    
    public function add(...$insert): Query
    {
        $this->insert->add(...$insert);
        return $this;
    }
    
    public function into(string $into): Query
    {
        $this->insert->table($into);
        return $this;
    }
    
    public function source(Query $source): Query
    {
        $this->source   = $source;
        return $this;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //
    // UPDATE Query
    
    public function update(string $table = ""): Query
    {
        $this->update   = new Update($table);
        $this->type     = Query::UPDATE;
        return $this;
    }
    
    public function set(...$update): Query
    {
        $this->update->set(...$update);
        return $this;
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    //
    // SELECT Query
    
    public function select(...$select): Query
    {
        $this->select   = new Select(...$select);
        $this->type     = Query::SELECT;
        return $this;
    }
    
    public function from(...$from): Query
    {
        $this->from     = new From(...$from);
        return $this;
    }
    
    public function join($join, string $type = Join::LEFT): Query
    {
        if(EX::empty($this->from))
        {
            throw new BambooException("JOINを行うFROM句が指定されていません。");
        }
        $this->from->join($join, $type);
        return $this;
    }
    
    public function innerJoin($join): Query
    {
        return $this->join($join, Join::INNER);
    }
    
    public function leftJoin($join): Query
    {
        return $this->join($join, Join::LEFT);
    }
    
    public function rightJoin($join): Query
    {
        return $this->join($join, Join::RIGHT);
    }
    
    public function fullJoin($join): Query
    {
        return $this->join($join, Join::FULL);
    }
    
    public function crossJoin($join): Query
    {
        return $this->join($join, Join::CROSS);
    }
    
    public function on(string ...$on): Query
    {
        if(EX::empty($this->from))
        {
            throw new BambooException("JOINを行うFROM句が指定されていません。");
        }
        $this->from->on(...$on);
        return $this;
    }
    
    public function using(string ...$on): Query
    {
        if(EX::empty($this->from))
        {
            throw new BambooException("JOINを行うFROM句が指定されていません。");
        }
        $this->from->using(...$on);
        return $this;
    }
    
    public function where(...$where): Query
    {
        $this->where    = new Where(...$where);
        return $this;
    }
    
    public function groupBy(...$group_by): Query
    {
        $this->group_by = new GroupBy(...$group_by);
        return $this;
    }
    
    public function having(...$having): Query
    {
        $this->having   = new Having(...$having);
        return $this;
    }
    
    public function orderBy(...$order_by): Query
    {
        $this->order_by = new OrderBy(...$order_by);
        return $this;
    }
    
    public function limit(...$limit): Query
    {
        $this->limit    = new Limit(...$limit);
        return $this;
    }
    
    public function lock(bool $lock = true): Query
    {
        $this->lock     = $lock;
        return $this;
    }
    
    public function alias(string $alias): Query
    {
        $this->alias    = $alias;
        return $this;
    }
    
    public function as(string $alias): Query
    {
        $this->alias    = $alias;
        return $this;
    }
    
    public function toSQL(int $indent = 0)
    {
        return ($this->mode  === self::BUILD)
                    ? $this->build_query($indent)
                    : $this->query_from_string($indent)
                    ;
    }
    
    private function query_from_string(int $indent)
    {
        return (!EX::empty($this->alias))
                    ? "(\n" . STR::indent(trim($this->query), $indent) . "\n) AS {$this->get_alias()}"
                    : trim($this->query)
                    ;
    }
    
    private function build_query(int $indent)
    {
        switch(true)
        {
            case $this->type === Query::INSERT:
                return $this->build_query_insert($indent);
                
            case $this->type === Query::UPDATE:
                return $this->build_query_update($indent);
                    
            case $this->type === Query::SELECT:
            default:
                return $this->build_query_select($indent);
        }
    }
    
    private function build_query_insert(int $indent)
    {
        $qry    = $this->get_insert();
        return $qry;
    }
    
    private function build_query_update(int $indent)
    {
        $qry    = $this->get_update();
        $qry    .= !EX::empty($this->where)     ? $this->get_where() : "";
        return $qry;
    }
    
    private function build_query_select(int $indent)
    {
        $qry    = "";
        $qry    .= !EX::empty($this->select)    ? $this->get_select() : "";
        $qry    .= !EX::empty($this->from)      ? $this->get_from() : "";
        $qry    .= !EX::empty($this->where)     ? $this->get_where() : "";
        $qry    .= !EX::empty($this->group_by)  ? $this->get_group_by() : "";
        $qry    .= !EX::empty($this->having)    ? $this->get_having() : "";
        $qry    .= !EX::empty($this->order_by)  ? $this->get_order_by() : "";
        $qry    .= !EX::empty($this->limit)     ? $this->get_limit() : "";
        $qry    .= $this->get_lock();
        return (!EX::empty($this->alias))
                    ? "(\n" . STR::indent(trim($qry), $indent) . "\n) AS {$this->get_alias()}"
                    : trim($qry)
                    ;
    }
    
    private function get_select(): string
    {
        return "SELECT\n" . STR::indent($this->select->toSQL()) . "\n";
    }
    
    private function get_from(): string
    {
        return "FROM\n" . $this->from->toSQL() . "\n";
    }
    
    private function get_where(): string
    {
        return "WHERE {$this->where->toSQL()}\n";
    }
    
    private function get_group_by(): string
    {
        return "GROUP BY {$this->group_by->toSQL()}\n";
    }
    
    private function get_having(): string
    {
        return "HAVING {$this->having->toSQL()}\n";
    }
    
    private function get_order_by(): string
    {
        return "ORDER BY {$this->order_by->toSQL()}\n";
    }
    
    private function get_limit(): string
    {
        return "LIMIT {$this->limit->toSQL()}\n";
    }
    
    private function get_lock(): string
    {
        return ($this->lock) ? "FOR UPDATE\n" : "" ;
    }
    
    private function get_alias(): string
    {
        return DBUTIL::strip($this->alias);
    }
    
    private function get_insert(): string
    {
        $qry    = "INSERT INTO " . DBUTIL::wrapID($this->insert->table);
        $qry    .= !EX::empty($this->source)
                        ? $this->insert->toSelectSQL($this->from) . "\n" . $this->source->toSQL()
                        : $this->insert->toSQL()
                        ;
        $qry    .= "\n";
        return $qry;
    }
    
    private function get_update(): string
    {
        $qry    = "UPDATE " . DBUTIL::wrapID($this->update->table) . "\n";
        $qry    .= "SET\n" . $this->update->toSQL();
        $qry    .= "\n";
        return $qry;
    }
    
}
