<?php

class DBP_Database extends ViewableData {
	
	function Name() {
		return DB::getConn()->currentDatabase();
	}

	function Type() {
		return DB::getConn()->getDatabaseServer();
	}

	function Version() {
		return DB::getConn()->getVersion();
	}

	function Adapter() {
		return get_class(DB::getConn());
	}

	function Transactions() {
		return DB::getConn()->supportsTransactions();
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) $tables->push(new DBP_Table($table));
		
		$tables->sort('Name');

		return $tables;
	}
	
	function Link() {
		return Controller::curr()->Link() . 'database/show';
	}
	
	function forTemplate() {
		return $this->renderWith($this->class);
	}

	function DBPLink() {
		return Controller::curr()->Link();
	}

}

class DBP_Database_Controller extends DBP_Controller {

	function execute($request) {

		$vars = $this->getRequest()->requestVars();

		$result = new ArrayData(DBP_Sql::execute_script($vars['query']));
		
		return $result ? $result->renderWith('DBP_Database_sql') : $this->instance->renderWith('DBP_Database_sql');

	}
	
	function export($request) {
		switch($request->postVar('exporttype')) {
			case 'backup':
				$this->backup($request->postVar('tables'));
				break;
		}
	}
	
	function backup($tables) {
		$commands = array();
		if(DB::getConn() instanceof MSSQLDatabase) $commands[] = 'SET IDENTITY_INSERT ON;';
		foreach($tables as $table) {
			$fields = array();
			$commands[] = 'DELETE FROM "' . $table . '";';
			foreach(DB::fieldList($table) as $name => $spec) $fields[] = $name;
			foreach(DB::query('SELECT * FROM "' . $table . '"') as $record) {
				$cells = array();
			
				foreach($record as $cell) {
					if(is_null($cell)) $cell = 'NULL';
					else if(is_string($cell)) $cell = "'" . str_replace("'", "''", $cell) . "'";
					$cells[] = $cell;
				}
				$commands[] = 
					"INSERT INTO \"$table\" (\"" . 
					implode('", "', $fields) . 
					"\") VALUES (" . 
					implode(", ", $cells) . 
					");";
			}
		}
		if(DB::getConn() instanceof MSSQLDatabase) $commands[] = 'SET IDENTITY_INSERT OFF;';
		header("Content-type: text/sql; charset=utf-8");
		header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '_' . $this->instance->Type() .  '.sql"');
		foreach($commands as $command) echo $command . "\r\n";
	}
	
	function import($request) {
		$result = false;
		$file = $request->postVar('importfile');
		if(!empty($file['tmp_name'])) {
			if($request->postVar('importtype') == 'rawsql') {
				$result = new ArrayData(DBP_Sql::execute_script(file($file['tmp_name'])));
			}
		}

		return $result ? $result->renderWith('DBP_Database_sql') : $this->instance->renderWith('DBP_Database_sql');
	}
	
}