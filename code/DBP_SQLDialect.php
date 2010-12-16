<?php

class DBP_SQLDialect {

	public static $adapters = array(
		'MySQLDatabase' => 'MySQL',
		'SQLite3Database' => 'SQLite',
		'MSSQLDatabase' => 'MSSQL',
		'PostgreSQLDatabase' => 'Postgres',
	);

	static function get($type = null) {
		
		if($type) {
			$class = "DBP_{$type}_Dialect";
			return new $class();
		} else if(DB::getConn() instanceof MySQLDatabase) {
			return new DBP_MySQL_Dialect();
		} else if(DB::getConn() instanceof SQLite3Database) {
			return new DBP_SQLite_Dialect();
		} else if(DB::getConn() instanceof MSSQLDatabase) {
			return new DBP_MSSQL_Dialect();
		} else if(DB::getConn() instanceof PostgresDatabase) {
			return new DBP_Postgres_Dialect();
		}
	}

	function dropTable($table) {
		DB::query("DROP TABLE \"$table\"");
	}

	function dropColumns($table, $columns) {
		DB::query("ALTER TABLE \"$table\" DROP \"" . implode('", DROP "', $columns) . "\"");
	}
	
	function escape($string) {
		return str_replace('\'', '\'\'', $string);
	}

	static function select($column, $table, $filter = null, $order = null, $limit = null, $offset = null) {
		switch(DB::getConn()->getDatabaseServer()) {
			case 'oracle':
				if($filter) $filter = sprintf(" WHERE (%s)\n", $filter);
				if(empty($order)) $order = '"ID" ASC';
				if($limit || $offset) {
					$select = sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
					if(isset($offset)) $text = "SELECT $column FROM ($select) WHERE ROWNUM BETWEEN $offset AND " . ($offset + $limit);
					else $text = "SELECT $column FROM ($select) WHERE ROWNUM <= " . $limit;
					return $text;
				} else {
					return sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
				}
			default:
				if($filter) $filter = sprintf(" WHERE (%s)", $filter);
				if($order) $order = sprintf(" ORDER BY %s", $order);
				if($limit) $limit = isset($offset) ? sprintf(" LIMIT %d OFFSET %d", $limit, $offset) : sprintf(" LIMIT %d", $limit);
				return sprintf('SELECT %s FROM "%s"%s%s%s', $column, $table, $filter, $order, $limit);
		}
	}
}

class DBP_MySQL_Dialect extends DBP_SQLDialect {

	function escape($string) {
		return addslashes($string);
	}

}

class DBP_MSSQL_Dialect extends DBP_SQLDialect {

	function escape($string) {
    	$string=str_replace("'","''",$string);
    	$string=str_replace("\0","[NULL]",$string);
    	return $string;
	}

	static function select($column, $table, $filter = null, $order = null, $limit = null, $offset = null) {
		if($filter) $filter = sprintf(" WHERE (%s)\n", $filter);
		if(empty($order)) $order = '"ID" ASC';
		if($limit || $offset) {
			return sprintf('WITH results AS (
				    SELECT 
				        %s,
				        rowNo_hide = ROW_NUMBER() OVER( ORDER BY %s )
				    FROM "%s"
					%s
				) 
				SELECT * 
				FROM results
				WHERE rowNo_hide between %d and %d
				', $column, $order, $table, $filter, $offset, $offset + $limit);
		} else {
			return sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
		}
	}
}

class DBP_Postgres_Dialect extends DBP_SQLDialect {
	function escape($string) {
		return pg_escape_string($string);
	}
}

class DBP_SQLite_Dialect extends DBP_SQLDialect {

	function dropColumns($table, $columns) {

		$newColsSpec = $newCols = array();
		foreach(DB::getConn()->fieldList($table) as $name => $spec) {
			if(in_array($name, $columns)) continue;
			$newColsSpec[] = "\"$name\" $spec";
			$newCols[] = "\"$name\"";
		}

		$queries = array(
			"BEGIN TRANSACTION",
			"CREATE TABLE \"{$table}_cleanup\" (" . implode(',', $newColsSpec) . ")",
			"INSERT INTO \"{$table}_cleanup\" SELECT " . implode(',', $newCols) . " FROM \"$table\"",
			"DROP TABLE \"$table\"",
			"ALTER TABLE \"{$table}_cleanup\" RENAME TO \"{$table}\"",
			"COMMIT"
		);

		foreach($queries as $query) DB::query($query.';');

	}
}