<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait QueryKit
{
    /**
     * Insert new rows or update existed rows
     *
     * @param array $data
     * @param array $insertKeys
     * @param array $updateKeys
     *
     * @return void
     */
    public static function insertDuplicate(array $data, array $insertKeys, array $updateKeys)
    {
        $model = new static;
        $query = "INSERT INTO {$model->getTable()} __INSERTKEYS__ VALUES __INSERTVALUES__ ON DUPLICATE KEY UPDATE __UPDATEVALUES__";
        $tmpInKeys = array_fill_keys($insertKeys, null);
        $tmpUpKeys = array_fill_keys($updateKeys, null);

        try {
            DB::beginTransaction();
            foreach ($data as $item) {
                $insertValue = array_intersect_key($item, $tmpInKeys);

                $updateValue = implode(', ', array_map(
                    function ($v, $k) { return sprintf("`%s`='%s'", $k, $v); },
                    array_intersect_key($item, $tmpUpKeys),
                    $updateKeys
                ));

                $statement = str_replace(
                    ['__INSERTKEYS__', '__INSERTVALUES__', '__UPDATEVALUES__'],
                    ["(`" . implode("`,`", $insertKeys) . "`)", "('" . implode("','", $insertValue) . "')", $updateValue],
                    $query
                );
                DB::statement($statement);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get the array of columns
     *
     * @return mixed
     */
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * Retrieve a subset of the output data
     *
     * @param mixed $query
     * @param array $columns
     *
     * @return mixed
     */
    public function scopeExclude($query, array $columns = [])
    {
        if (empty($this->getExcludable()) && empty($columns)) {
            throw new \Exception('Too few arguments');
        }
        if ($columns && empty($this->getExcludable())) {
            $select = array_diff($this->getTableColumns(), $columns);
        }
        if ($this->getExcludable() && empty($columns)) {
            $select = array_diff($this->getTableColumns(), $this->getExcludable());
        }
        return $query->select($select);
    }

    /**
     * Set excludable
     *
     * @param array $excludable
     *
     * @return void
     */
    public function setExcludable(array $excludable)
    {
        $this->excludable = $excludable;
    }

    /**
     * Get excludable
     *
     * @return void
     */
    public function getExcludable()
    {
        return $this->excludable;
    }

    /**
     * Set filterable
     *
     * @param array $filterable
     *
     * @return void
     */
    public function setFilterable(array $filterable)
    {
        $this->filterable = $filterable;
    }

    /**
     * Get filterable
     *
     * @return void
     */
    public function getFilterable()
    {
        return $this->filterable;
    }

    /**
     * Filter
     * @param  array  $params Params
     * @return $this
     */
    public function scopeFilter($query, array $params)
    {
        if (empty($this->getFilterable())) {
            throw new \Exception('Empty Filterable');
        }

        if (!is_array($this->getFilterable())) {
            throw new \Exception('Invalid Filterable');
        }

        $query = $this->prepareFilterable($query, $params);

        return $query;
    }

    /**
     * Prepare filterable
     * @param  array  $params Params
     * @return array
     */
    public function prepareFilterable($query, array $params)
    {
        $default = ['where', null, null];
        foreach ($params as $key => $value) {
            if (false === isset($this->filterable[$key]) && false === in_array($key, $this->filterable)) {
                continue;
            }

            list($whereClause, $operator, $likeSyntax) = in_array($key, $this->filterable) ? $default : array_replace($default, $this->filterable[$key]);
            if ($likeSyntax) {
                $value = str_replace('{'.$key.'}', $value, $likeSyntax);
            }

            if ($operator) {
                $query->$whereClause($key, $operator, $value);
                continue;
            }

            $query->$whereClause($key, $value);
        }

        return $query;
    }
}
