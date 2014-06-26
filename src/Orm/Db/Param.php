<?php

namespace Kern\Orm\Db;

/* configuration class that holds all of the configuration parameters for a model */
class Param
{
    public $table;
    public $primary_key;
    public $class;                      /* key to reference the model at */
    public $fields_to_sql_map   = [];   /* maps of fields to specific sql */
    public $wheres_to_sql_map   = [];   /* maps of where portions to specific sql */
    public $sorts_to_sql_map    = [];   /* maps of sorts to portions of sql */
    public $joins_map           = [];   /* should_join_map values -> actual join strings */
    public $related_models      = [];
    public $with_many           = [];   /* special data when a model has to be joined as has-many */
    public $where_transformer   = [];
    
    public function __construct($params = [])
    {
        foreach ($params as $key => $val) {
            $this->{$key} = $val;
        }
    }
}
