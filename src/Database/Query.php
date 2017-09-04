<?php
/**
 * Bill Schmidt
 * Date: 8/22/2017
 * Time: 3:59 PM
 */

namespace BillBudget\Database;


class Query {
    public $sel = [];
    public $from = [];
    public $where = [];
    public $group_by = [];
    public $having = [];
    public $order_by = [];

    public $limit = 0;
    public $offset = 0;

    public $values = [];

    public $assoc = true;

    /**
     * @return \PDOStatement
     */
    public function run() {
        return DB::query($this->__toString(), $this->values, $this->assoc);
    }

    /**
     * @return string
     */
    public function __toString() {
        // unique arrays
        $this->sel = array_unique($this->sel);
        $this->where = array_unique($this->where);
        $this->order_by = array_unique($this->order_by);
        $this->from = array_unique($this->from);
        $this->group_by = array_unique($this->group_by);
        $this->having = array_unique($this->having);

        if (empty($this->sel) || empty($this->from)) {
            return '';
        }

        $query = 'select ' . implode(', ', $this->sel) . ' from '.implode(' ', $this->from);

        if (!empty($query->where)) {
            $query .= ' where ' . implode(' and ', $this->where);
        }

        if (!empty($query->group_by)) {
            $query .= ' group by ' . implode(', ', $this->group_by);
        }

        if (!empty($this->having)) {
            $query .= ' having ' . implode(' and ', $this->having);
        }

        if (!empty($this->order_by)) {
            $query .= ' order by ' . implode(', ', $this->order_by);
        }

        if ($this->limit > 0) {
            $query .= ' limit ' . (int) $this->limit;
        }

        if ($this->offset > 0) {
            $query .= ' offset ' . (int) $this->offset;
        }

        return $query;
    }

    /**
     * @return int
     */
    public function count() {
        $query = 'select count(*) from (' . $this->__toString() . ') as "aggregate_table"';
        $stmt = DB::query($query, $this->values, $this->assoc);

        return $stmt->fetch()[0];
    }
}