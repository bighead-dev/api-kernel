<?php

namespace Kern\Orm;

use Kern;
use PDO;
use FluentPDO;
use ArrayObject;
use Traversable;

interface DbHydratable
{
    public function orm_db_hydrate($fields, $data);
}

class Db implements Kern\OrmDriver
{
    public static $params = [];

    public function save($data)
    {
        /* if array, then convert to an ArrayObject to simplify array vs iterator code */
        if (is_array($data)) {
            $data = new ArrayObject($data);
        }
        
        /* make sure that we are traversable... */
        if ($data instanceof Traversable == false) {
            $data = new ArrayObject([$data]);
        }
        
        /* save the data */
        return $this->save_many($data);
    }
    
    private function insert_values($objs, $values, $param)
    {
        $fpdo = new FluentPDO(kern_db());
        
        $insert_id = $fpdo->insertInto($param->table)->values($values)->execute();
        
        if (!$insert_id) {
            return $insert_id; /* return the error if there was one */
        }
        
        /* update the ids of the models */
        foreach ($objs as $obj)
        {
            $obj->id = $insert_id++;
        }
    }
    
    private function update_values($values, $param)
    {
        $fpdo = new FluentPDO(kern_db());
        
        /* TODO - fix todo batch updates */
        foreach ($values as $val)
        {
            $id = $val['id'];
            unset($val['id']);
            $fpdo->update($param->table)
                ->set($val)
                ->where('id', $id)
                ->execute();
        }
    }
    
    private function save_many($objs)
    {        
        $values = [];
        $table  = '';
        
        /* reference to the first object */
        $first_obj = null;
        
        foreach ($objs as $obj)
        {
            if (!$first_obj) {
                $first_obj = $obj;
            }
            
            if ($obj instanceof Kern\OrmSerializable == false) {
                throw new Kern\Exception("Object being saved isn't an instance of OrmSerializable");
            }
            
            $values[] = $obj->orm_data_to_serialize();
        }
        
        if (count($values) == 0) {
            return true; /* nothing to save */
        }
        
        $param = self::get_param(get_class($first_obj));
        
        if (!$param) {
            throw new Kern\Exception("Orm object doesn't have paramater");
        }
        
        if (isset($values[0]['id'])) {
            $this->update_values($values, $param);
        }
        else {
            $this->insert_values($objs, $values, $param);
        }
    }
    
    public function update(&$data)
    {
    
    }
    
    public function delete(&$data)
    {
    
    }
    
    public function query($model)
    {
        return new Db\Query($model);
    }
    
    private function create_and_hydrate_model_recursive($row, &$col_idx, $query)
    {
        $mdl = new $query->param->class();
        
        if ($mdl instanceof DbHydratable == false) {
            throw new Kern\Exception("{$query->model} isn't an instance of Orm\DbHydratable");
        }
        
        /* hydrate the model */
        $mdl->orm_db_hydrate($query->fields, array_slice($row, $col_idx, count($query->fields)));
        
        $col_idx += count($query->fields);
        
        foreach ($query->withs as $slug => $sub_query)
        {
            if ($sub_query->is_additional_query) {
                continue;
            }
            
            $mdl->{$slug} = $this->create_and_hydrate_model_recursive($row, $col_idx, $sub_query);
        }
        
        return $mdl;
    }
    
    public function get(Kern\Orm\Query $query)
    {
        $stmt = $query->execute();
        $mdls = [];

        for ($row = $stmt->fetch(PDO::FETCH_NUM); $row; $row = $stmt->fetch(PDO::FETCH_NUM))
        {
            $col_idx = 0;
            $mdl = $this->create_and_hydrate_model_recursive($row, $col_idx, $query);
            $mdls[] = $mdl;
            
            if ($query->is_additional_query) {
                $mdl->{$query->parent->get_pkey_field_alias()} = end($row);
            }
        }
        
        /* now, make the additional queries */
        $pkey_field = $query->param->primary_key;
        foreach ($query->additional_queries as $sub_query)
        {
            $id_to_idx_map = [];
            $sub_query->parent_pkey_ids = [];
            foreach ($mdls as $idx => $mdl)
            {
                $id_to_idx_map[$mdl->{$pkey_field}] = $idx;
                $sub_query->parent_pkey_ids[] = $mdl->{$pkey_field};
                $mdl->{$sub_query->slug} = [];
            }
                        
            /* recusively make queries */
            $res = $this->get($sub_query);
            
            /* now append to the models */
            foreach ($res as $sub_model)
            {
                $pkey = $query->get_pkey_field_alias();
                $idx = $id_to_idx_map[$sub_model->{$pkey}];
                unset($sub_model->{$pkey});
                $mdls[$idx]->{$sub_query->slug}[] = $sub_model;
            }
        }
        
        return $mdls;
    }
    
    public function register($param)
    {
        if ($param instanceof Db\Param == false) {
            throw new Kern\Exception('Param must be an instance of Db\Param');
        }
        self::$params[$param->class] = $param;
    }
    
    public static function get_param($model)
    {
        if (!array_key_exists($model, self::$params)) {
            return null;
        }
        
        return self::$params[$model];
    }
}
