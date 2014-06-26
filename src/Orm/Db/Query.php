<?php

namespace Kern\Orm\Db;

use Kern;
use FluentPDO;

class WherePortion
{
    public $binds = [];
    public $sql;
    public $type;
    public $key = '';
    
    const CUSTOM = 0;
    const NORMAL = 1;
    
    /* we only use this for the build_joins_from_sql_map in Query */
    public function __toString()
    {
        return $this->key;
    }
    
    public function build_where_custom($sql, $binds)
    {
        $this->sql      = $sql;
        $this->binds    = $binds;
        $this->type     = self::CUSTOM;
    }
    
    public function build_where_normal($key, $sql_key, $op, $val)
    {
        $this->key  = $key;
        $this->sql  = $sql_key;
                    
        if ($op == 'in')
        {
            $in_str = str_repeat('?, ', count($val));
            $this->sql .= ' in (' . substr($in_str, 0, -2) . ")";
            $this->binds = $val;
        }
        else
        {
            $this->sql .= ' ' . $op . " ?";
            $this->binds[] = $val;
        }
        
        $this->type = self::NORMAL;
    }
}

class Query extends Kern\Orm\Query
{
    public $model;
    public $slug;
    public $param;
    public $withs           = [];
    public $fields          = [];
    public $wheres          = [];
    public $sorts           = [];
    public $limit;
    public $group_by_pkey   = false;
    
    public $additional_queries = [];
    public $parent;          /* the parent query for an additional query */
    public $parent_pkey_ids; /* used to where on the additional query */
    
    public $is_child_query      = false;
    public $is_additional_query = false;
    
    public $utable = '';
    
    /* static to conserve data... */
    public static $valid_ops = [
        'eq'    => '=',
        'ne'    => '!=',
        'lt'    => '<',
        'le'    => '<=',
        'gt'    => '>',
        'ge'    => '>=',
        'in'    => 'in',
        'like'  => 'like',
    ];
    
    
    
    public function __construct($model)
    {
        $this->model    = $model;
        $this->param    = Kern\Orm\Db::get_param($model);
                
        if ($this->param == null) {
            throw new Kern\Exception("Building query for $model that hasn't been registered");
        }
        
        $this->utable   = self::get_utable($this->param->table);
    }
    
    public function with($model, $query = null)
    {
        if (is_array($model))
        {
            foreach ($model as $mdl) {
               $this->with($mdl);
            }
            return $this;
        }
    
        if (!$query) {
            $query = new Query($this->param->related_models[$model]);
        }
    
        $query->is_child_query  = true;
        $query->slug            = $model;
        
        $this->withs[$model] = $query;
        return $this;
    }
    
    public function fields($fields, $model = null)
    {
        if ($model)
        {
            $this->withs[$model]->fields($fields);
            return $this;;
        }
        
        $this->fields = $fields;
        return $this;
    }
    
    public function group_by_pkey($should_group = null)
    {
        if ($should_group === null) {
            return $this->group_by_pkey;
        }
        
        $this->group_by_pkey = $should_group;
        return $this;
    }
    
    public function where_custom($sql, $binds)
    {
        $wh = new WherePortion();
        $wh->build_where_custom($sql, $binds);
        $this->wheres[] = $wh;
    }
    
    public function where($key, $op, $val)
    {
        if (!array_key_exists($key, $this->param->wheres_to_sql_map)) {
            return $this;
        }
        if (!array_key_exists($op, self::$valid_ops)) {
            return $this;
        }
        
        if (is_array($val)) {
            $op = 'in'; /* only in can be specified if an array was supplied */
        }
        
        if (array_key_exists($key, $this->param->where_transformer))
        {
            if (is_array($val))
            {
                /* loop over vals and transform each on separately */
                foreach ($val as &$v) {
                    $v = $this->param->where_transformer[$key]($v);
                }
            }
            else {
                $val = $this->param->where_transformer[$key]($val);
            } 
        }
        
        $wh = new WherePortion();
        $wh->build_where_normal(
            $key,
            $this->param->wheres_to_sql_map[$key],
            self::$valid_ops[$op],
            $val
        );
        
        $this->wheres[] = $wh;
        
        return $this;
    }
    
    public function sort($key, $dir = 'asc')
    {
        if (!array_key_exists($key, $this->param->sorts_to_sql_map)) {
            return $this;
        }
        if ($dir != 'asc' && $dir != 'desc') {
            $dir = 'asc';
        }
        
        $this->sorts[] = [
            $key,
            $dir,
        ];
        
        return $this;
    }
    
    public function limit($count, $offset = 0)
    {
        $this->limit = [$count, $offset];
    }
    
    public function sql()
    {
        /* first, see if we need to any additional queries for special related models */
        $this->find_additional_queries();
        
        $sql =  $this->build_select_sql() .
                $this->build_join_sql()   .
                $this->build_where_sql()  .
                $this->build_group_sql()  .
                $this->build_sort_sql()   .
                $this->build_limit_sql();
        
        return $sql;
    }
    
    public function execute()
    {
        $sql = $this->sql();
        $pdo = kern_db();
        
        $stmt = $pdo->prepare($sql);
        
        $where_vals = [];
        foreach ($this->wheres as $where)
        {
            foreach ($where->binds as $bind) {
                $where_vals[] = $bind;
            }
        }
        
        $stmt->execute($where_vals);
        return $stmt;
    }
    
    public function build_select_sql()
    {
        return "SELECT\n" .
                $this->build_select_fields() .
                "\nFROM {$this->param->table} as {$this->utable}\n";
    }
    
    /* build the sql for the current model specifically */
    protected function build_select_fields()
    {
        $fields = '  ';
        
        foreach ($this->fields as $field)
        {
            if (!array_key_exists($field, $this->param->fields_to_sql_map)) {
                continue;
            }
            
            $sql_fields = $this->param->fields_to_sql_map[$field];
            
            if (is_array($sql_fields))
            {
                foreach ($sql_fields as $s_field) {
                    $fields .= $this->alias_sql_field($s_field) . ', ';
                }
            }
            else {
                $fields .= $this->alias_sql_field($sql_fields) . ', ';
            }
        }
        
        $fields .= "\n";
                
        foreach ($this->withs as $model => $query)
        {
            if ($query->is_additional_query) {
                continue; /* additional queries are processed later in a separate query */
            }
            
            $fields .= $query->build_select_fields() . ", \n";
        }
        
        if ($this->is_additional_query)
        {
            /* we need to make one more special select for the additional queries.
               this field is for relating the additional result to the parent result
               set */
            $fields .= '  ' . $this->parent->get_pkey_field(true) . ", \n";
        }
        
    end:
        return substr($fields, 0, -3);
    }
    
    private function alias_sql_field($field)
    {
        if (!preg_match('/ as {self}\S+$/i', $field))
        {
            /* hasn't been aliased, so alias the column */
            /* grab the field name after {self}.* */
            $field_name = substr($field, strpos($field, '.') + 1);
            $field .= ' as {self}_' . $field_name;
        }
        
        /* escape the field now */
        return str_replace('{self}', $this->utable, $field);
    }
    
    public function build_join_sql()
    {
        $sql = "";
        $fields = '    ';
        
        $this->build_joins_from_sql_map($sql, $this->fields, 'se');
                
        foreach ($this->withs as $model => $query)
        {
            if (!array_key_exists($model, $this->param->joins_map)) {
                continue;
            }
            
            $sql .= str_replace(
                ["{buddy}", "{self}"],
                [$query->utable, $this->utable],
                "LEFT JOIN {$query->param->table} as {$query->utable} " . $this->param->joins_map[$model] . "\n"
            );
            
            $sql .= $query->build_join_sql();
        }
        
        if ($this->is_child_query && !$this->is_additional_query) {
            goto end; /* child queries can't do any more joining */
        }
        
        if (!$this->is_additional_query && !$this->is_child_query)
        {
            /* only the root query can have joins on wheres and sorts */
            $this->build_joins_from_sql_map($sql, $this->wheres, 'wh');
            $this->build_joins_from_sql_map($sql, $this->sorts, 'so');
            goto end;
        }
        
        /* we need to do the special join clauses to link the additional query with
           our parent query. Some tables might have one or more tables that need to 
           be joined in order to reference the parent table. So the first join clause is from
           this query to another table. The last join clause is from some table to the parent
           table. */
        $join_clauses = $this->parent->param->with_many[$this->slug];
        
        foreach ($join_clauses as $idx => $clause)
        {
            $sql .= "LEFT JOIN ";
            
            if ($idx == 0) {
                $clause = str_replace('{buddy}', $this->utable, $clause);
            }
            if ($idx + 1 == count($join_clauses)) {
                $clause = str_replace('{self}', $this->parent->utable, $clause);
                $sql .= $this->parent->param->table . " as {$this->parent->utable} " . $clause;
            }
            else
            {
                $sql .= $clause;
            }
            
            $sql .= "\n";
        }
        
    end:
        return $sql;
    }
    
    public function build_joins_from_sql_map(&$sql, $items, $prefix)
    {
        foreach ($items as $item)
        {
            if (is_array($item)) {
                $key = $prefix . '.' . $item[0];
            }
            else {
                $key = $prefix . '.' . $item;
            }
            
            if (!array_key_exists($key, $this->param->joins_map)) {
                continue;
            }
            
            $clauses = $this->param->joins_map[$key];
            
            if (!is_array($clauses))
            {
                $sql .= str_replace(
                    "{self}",
                    $this->utable,
                    "LEFT JOIN " . $clauses . "\n"
                );
                return;
            }
            
            foreach ($clauses as $clause)
            {
                $sql .= str_replace(
                    "{self}",
                    $this->utable,
                    "LEFT JOIN " . $clause . "\n"
                );
            }
        }
    }
    
    public function build_where_sql()
    {
        $sql = "WHERE\n";
        
        if (!$this->wheres && !$this->is_child_query) {
            return ''; /* I'm a parent, and no wheres */
        }
        
        if ($this->is_child_query && !$this->is_additional_query) {
            return ''; /* child queries aren't allowed to use wheres */
        }
        
        if ($this->is_additional_query)
        {
            /* I am a child query */
            $sql .= $this->build_where_sql_for_additional();
        }
                
        foreach ($this->wheres as $where) {
            $sql .= '  ' . $where->sql . " AND\n";
        }
                
        return str_replace("{self}", $this->utable, substr($sql, 0, -5) . "\n");
    }
    
    public function build_where_sql_for_additional()
    {
        $sql = "  " . $this->parent->get_pkey_field() . ' in (';
        $sql .= implode($this->parent_pkey_ids, ", ") . ") AND\n";
        return $sql;
    }
    
    public function build_sort_sql()
    {
        $sql = "ORDER BY\n";
        
        if (!$this->sorts) {
            return '';
        }
        
        if ($this->is_child_query && !$this->is_additional_query)
        {
            /* child queries aren't allowed to use wheres */
            return '';
        }
        
        foreach ($this->sorts as $sort) {
            $sql .= '  ' . $this->param->sorts_to_sql_map[$sort[0]] . ' ' . $sort[1] . ",\n";
        }
        
        return str_replace("{self}", $this->utable, substr($sql, 0, -2) . "\n");
    }
    
    public function build_group_sql()
    {
        if (!$this->group_by_pkey) {
            return '';
        }
        
        $sql = "GROUP BY\n";
        
        if ($this->is_additional_query) {
            $sql .= '  ' . $this->parent->get_pkey_field() . ",\n";
        }
        
        return $sql .= '  ' . $this->get_pkey_field() . "\n";
    }
    
    public function build_limit_sql()
    {
        if (!$this->limit) {
            return '';
        }
        
        return "LIMIT {$this->limit[0]} OFFSET {$this->limit[1]}\n";
    }
    
    /*
     * Goes through the related models and sees if any of them are 
     * in the with_many map which means to properly build the data,
     * we need to do an additional query
     */
    private function find_additional_queries()
    {        
        foreach ($this->withs as $model => $query)
        {
            if (!array_key_exists($model, $this->param->with_many)) {
                continue;
            }
            
            $query->is_additional_query         = true;
            $query->parent              	    = $this;
            $this->additional_queries[$model]   = $query;
        }
    }
    
    public function get_pkey_field($alias = false)
    {
        if (!$alias) {
            return "{$this->utable}.{$this->param->primary_key}";
        }
        return "{$this->utable}.{$this->param->primary_key} as {$this->utable}_{$this->param->primary_key}";
    }
    public function get_pkey_field_alias()
    {
        return "{$this->utable}_{$this->param->primary_key}";
    }
    
    /*
     * all fields and tables need to be aliased and uniquely aliased per
     * join as to avoid conflicts. The following manages that.
     */
    
    public static $tables_map = [];
    
    /* utable - unique table */
    public static function get_utable($table)
    {
        if (!array_key_exists($table, self::$tables_map)) {
            self::$tables_map[$table] = 0;
        }
        
        return $table . '_' . self::$tables_map[$table]++;
    }
}
