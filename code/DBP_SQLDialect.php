<?php

class DBP_SQLDialect {

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
}

class DBP_MySQL_Dialect extends DBP_SQLDialect {
	function escape($string) {
		return addslashes($string);
	}
}

class DBP_MSSQL_Dialect extends DBP_SQLDialect {}

class DBP_Postgres_Dialect extends DBP_SQLDialect {}

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