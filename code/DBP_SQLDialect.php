<?php

class DBP_SQLDialect {

	static function drop_table($table) {
		DB::query("DROP TABLE \"$table\"");
	}

	static function drop_columns($table, $columns) {
		DB::query("ALTER TABLE \"$table\" DROP \"" . implode('", "', $columns) . "\"");
	}
}