<?php
namespace Zardak;
use PDO;

class Model
{
	private $query_parts = null;
	private $want_first_record = false;

	public function __construct($query_parts = null)
	{
		$this->query_parts = $query_parts;
	}

	protected static function queryOn($tables) {
		$query_parts = array(
			'table_name' 		=> $tables,
			'and_where_clauses' 	=> array(
				'prepared_stmts' 	=> array(),
				'data' 			=> array()
			),
			'or_where_clauses' 	=> array(
				'prepared_stmts' 	=> array(),
				'data' 			=> array()
			),
			'and_or_set'		=> array(
				'prepared_stmts'	=> array(),
				'data'			=> array(),
			),
			'or_and_set'		=>array(
				'prepared_stmts'	=> array(),
				'data'			=> array(),
			),
			'and_constraint'		=> array(),
			'query_filters' 		=>array(),
			'fetch_type'		=> null,
		);
		return new self($query_parts);
	}

	protected function where ($col, $data = '*', $cond = '=') {
		if (!is_array($col))
		{
			$this->query_parts['and_where_clauses']['prepared_stmts'][] 	= $col . ' ' . $cond . ' ?';
			$this->query_parts['and_where_clauses']['data'][] 		= $data;
		}
		else
		{
			$or_set = $col;
			$or_set_stmt = '(';
			$or_set_data = array();
			for($i = 0; $i < count($or_set); $i++) 
			{
				if ($i > 0) {
					$or_set_stmt .= ' OR ';
				}
				$or_set_stmt .= $or_set[$i]['prepared_stmt'];
				$or_set_data[] = $or_set[$i]['data'];
			}
			$or_set_stmt .= ')';
			$this->query_parts['and_or_set']['prepared_stmts'][] 	= $or_set_stmt;
			$this->query_parts['and_or_set']['data'][]		= $or_set_data;
		}
		return $this;
	}

	protected function orWhere ($col, $data = '*', $cond = '=') {
		if (!is_array($col))
		{
			$this->query_parts['or_where_clauses']['prepared_stmts'][] 	= $col . ' ' . $cond . ' ?';
			$this->query_parts['or_where_clauses']['data'][] 			= $data;
		}
		else
		{
			$and_set = $col;
			$and_set_stmt = '(';
			$and_set_data = array();
			for($i = 0; $i < count($and_set); $i++) 
			{
				if ($i > 0) {
					$and_set_stmt .= ' AND ';
				}
				$and_set_stmt .= $and_set[$i]['prepared_stmt'];
				$and_set_data[] = $and_set[$i]['data'];
			}
			$and_set_stmt .= ')';
			$this->query_parts['or_and_set']['prepared_stmts'][] 	= $and_set_stmt;
			$this->query_parts['or_and_set']['data'][]		= $and_set_data;
		}
		return $this;
	}

	protected function constraint($col1, $col2, $cond = '=') {
		$this->query_parts['and_constraint'][] = $col1 . $cond . $col2;
		return $this;
	}

	protected function get ($what = '*') {
		$data_or_and_set = array();
		$data_and_or_set = array();
		if (
			count($this->query_parts['and_where_clauses']['prepared_stmts']) 	== 0 &&
			count($this->query_parts['or_where_clauses']['prepared_stmts']) 	== 0 &&
			count($this->query_parts['and_or_set']['prepared_stmts']) 		== 0 &&
			count($this->query_parts['or_and_set']['prepared_stmts']) 		== 0
		) {
			$sql = 'SELECT ' . $what . ' FROM ' . $this->query_parts['table_name'];
		}
		else {
			$sql = 'SELECT ' . $what . ' FROM ' . $this->query_parts['table_name'] . ' WHERE ';
			for ($i = 0; $i < count($this->query_parts['and_constraint']); $i++) {
				if ($i > 0) {
					$sql .= ' AND ';
				}
				$sql .= $this->query_parts['and_constraint'][$i];
			}
			for ($i = 0; $i < count($this->query_parts['and_where_clauses']['prepared_stmts']); $i++) {
				if ($i > 0 || count($this->query_parts['and_constraint']) > 0) {
					$sql .= ' AND ';
				}
				$sql .= $this->query_parts['and_where_clauses']['prepared_stmts'][$i];
			}
			for ($i = 0; $i < count($this->query_parts['or_where_clauses']['prepared_stmts']); $i++) {
				if (
					$i > 0 || count($this->query_parts['and_constraint']) > 0 || 
					count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0
				){
					$sql .= ' OR ';
				}
				$sql .= $this->query_parts['or_where_clauses']['prepared_stmts'][$i];
			}
			for ($i = 0; $i < count($this->query_parts['or_and_set']['prepared_stmts']); $i++) {
				if ( 
					$i > 0 || count($this->query_parts['and_constraint']) > 0 || 
					count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0 ||
					count($this->query_parts['or_where_clauses']['prepared_stmts']) > 0
				) {
					$sql .= ' OR ';
				}
				$sql .= $this->query_parts['or_and_set']['prepared_stmts'][$i];
				$data_or_and_set = array_merge($data_or_and_set, $this->query_parts['or_and_set']['data'][$i]);
			}
			for ($i = 0; $i < count($this->query_parts['and_or_set']['prepared_stmts']); $i++) {
				if ( 
					$i > 0 || count($this->query_parts['and_constraint']) > 0 || 
					count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0 ||
					count($this->query_parts['or_where_clauses']['prepared_stmts']) > 0 ||
					count($this->query_parts['or_and_set']['prepared_stmts']) > 0 
				) {
					$sql .= ' AND ';
				}
				$sql .= $this->query_parts['and_or_set']['prepared_stmts'][$i];
				$data_and_or_set = array_merge($data_and_or_set, $this->query_parts['and_or_set']['data'][$i]);
			}
		}

		if (count($this->query_parts['query_filters']) > 0) {
			foreach ($this->query_parts['query_filters'] as $filter) {
				$sql .= " $filter";
			}
		}

		$query = DB::getInstance()->prepare($sql);
		$query->execute(
			array_merge(
				$this->query_parts['and_where_clauses']['data'],
				$this->query_parts['or_where_clauses']['data'],
				$data_or_and_set,
				$data_and_or_set
			)
		);

		if ($this->want_first_record) {
			if (false !== ($fetch_item = $query->fetch(PDO::FETCH_ASSOC)))
				return new Record($fetch_item);
			else
				return false;
		}

		$records = [];
		if (!empty($this->query_parts['fetch_type'])) {
			$results = $query->fetchAll($this->query_parts['fetch_type']);
			foreach ($results as $result) {
				$r = [];
				foreach ($result as $res) {
					$r[] = new Record((array)$res);
				}
				$records[] = $r;
			}
		}
		else {
			while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
				$records[] = new Record($row);
			}
		}
		unset($this->query_parts);
		return $records;
	}

	protected function insert($data) {
		$sql = 'INSERT INTO ' . $this->query_parts['table_name'] . ' (' . implode(',', array_keys($data)) . ') VALUES (';
		for ($i = 0; $i < count($data); $i++) {
			if ($i > 0) {
				$sql .= ',';
			}
			$sql .= '?';
		}
		$sql .= ')';

		unset($this->query_parts);
		$query = DB::getInstance()->prepare($sql);
		if ($query->execute(array_values($data))) {
			return DB::getInstance()->lastInsertId();
		}
		return false;
	}

	protected function delete() {
		$sql = 'DELETE FROM ' . $this->query_parts['table_name'] . ' WHERE ';
		for ($i = 0; $i < count($this->query_parts['and_where_clauses']['prepared_stmts']); $i++) {
			if ($i > 0) {
				$sql .= ' AND ';
			}
			$sql .= $this->query_parts['and_where_clauses']['prepared_stmts'][$i];
		}
		for ($i = 0; $i < count($this->query_parts['or_where_clauses']['prepared_stmts']); $i++) {
			if ($i > 0 || count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0) {
				$sql .= ' OR ';
			}
			$sql .= $this->query_parts['or_where_clauses']['prepared_stmts'][$i];
		}
		$query = DB::getInstance()->prepare($sql);
		$query->execute(
			array_merge(
				$this->query_parts['and_where_clauses']['data'],
				$this->query_parts['or_where_clauses']['data']
			)
		);
		unset($this->query_parts);
	}

	protected function update($update_key_value) {
		$sql = 'UPDATE ' . $this->query_parts['table_name'] . ' SET ' . implode(' = ?,', array_keys($update_key_value)) . '= ?';
		if(count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0 || count($this->query_parts['or_where_clauses']['prepared_stmts']) > 0) {
			$sql .= ' WHERE ';
		}
		for ($i = 0; $i < count($this->query_parts['and_where_clauses']['prepared_stmts']); $i++) {
			if ($i > 0) {
				$sql .= ' AND ';
			}
			$sql .= $this->query_parts['and_where_clauses']['prepared_stmts'][$i];
		}
		for ($i = 0; $i < count($this->query_parts['or_where_clauses']['prepared_stmts']); $i++) {
			if ($i > 0 || count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0) {
				$sql .= ' OR ';
			}
			$sql .= $this->query_parts['or_where_clauses']['prepared_stmts'][$i];
		}
		$data_or_and_set = array();
		for ($i = 0; $i < count($this->query_parts['or_and_set']['prepared_stmts']); $i++) {
			if ( 
				$i > 0 ||
				count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0 ||
				count($this->query_parts['or_where_clauses']['prepared_stmts']) > 0
			) {
				$sql .= ' OR ';
			}
			$sql .= $this->query_parts['or_and_set']['prepared_stmts'][$i];
			$data_or_and_set[] = $this->query_parts['or_and_set']['data'][$i];
		}
		$data_and_or_set = array();
		for ($i = 0; $i < count($this->query_parts['and_or_set']['prepared_stmts']); $i++) {
			if ( 
				$i > 0 || 
				count($this->query_parts['and_where_clauses']['prepared_stmts']) > 0 ||
				count($this->query_parts['or_where_clauses']['prepared_stmts']) > 0 ||
				count($this->query_parts['or_and_set']['prepared_stmts']) > 0 
			) {
				$sql .= ' AND ';
			}
			$sql .= $this->query_parts['and_or_set']['prepared_stmts'][$i];
			$data_and_or_set[] = $this->query_parts['and_or_set']['data'][$i];
		}

		$query = DB::getInstance()->prepare($sql);
		$query->execute(
			array_merge(
				array_values($update_key_value),
				$this->query_parts['and_where_clauses']['data'],
				$this->query_parts['or_where_clauses']['data'],
				$data_or_and_set,
				$data_and_or_set
			)
		);
		unset($this->query_parts);
	}

	protected function first() {
		$this->query_parts['query_filters'][] = 'LIMIT 1';
		$this->want_first_record = true;
		return $this;
	}

	protected function orderBy($col, $order_type = 'ASC') {
		$this->query_parts['query_filters'][] = "ORDER BY $col $order_type";
		return $this;
	}

	protected static function condition($col, $val, $cond = '=') {
		return array(
			'prepared_stmt' => "$col $cond ?",
			'data'		=> $val,
		);
	}

	protected function group() {
		$this->query_parts['fetch_type'] = PDO::FETCH_GROUP;
		return $this;
	}
}
