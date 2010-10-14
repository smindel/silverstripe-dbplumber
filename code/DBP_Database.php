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

	function drop($table) {
		DB::query('DROP TABLE "' . $table . '"');
	}

	function MaxFileSize() {
		$max = 1073741824;
		$limits = array('post_max_size', 'upload_max_filesize');
		foreach($limits as $key) {
			if(preg_match('/^(\d+)(\w)$/i', trim(ini_get($key)), $matches)) {
				$limit = (int)$matches[1];
				$modifier = strtolower($matches[2]);
				switch($modifier) {
					case 'g': $limit *= 1024;
					case 'm': $limit *= 1024;
					case 'k': $limit *= 1024;
				}
				if($max > $limit) $max = $limit;
			}
		}
		return $max;
	}
	
	function HasZlibSupport() {
		return function_exists('gzencode');
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
				$commands = implode("\r\n", $this->backup($request->postVar('tables')));
				header("Content-type: text/sql; charset=utf-8");
				header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '_' . $this->instance->Type() .  '.sql"');
				echo $commands;
				break;
			case 'compressed':
				$commands = gzencode(implode("\r\n", $this->backup($request->postVar('tables'))), 9);
				header("Content-type: gzip; charset=utf-8");
				header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '_' . $this->instance->Type() .  '.sql.gz"');
				echo $commands;
				break;
		}
	}

	function backup($tables) {
		$commands = array();
		if(DB::getConn() instanceof MySQLDatabase) $commands[] = "SET sql_mode = 'ANSI';";
		if(DB::getConn() instanceof MSSQLDatabase) $commands[] = "SET IDENTITY_INSERT ON;";
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
		return $commands;
	}
	
	function import($request) {
		$result = false;
		$file = $request->postVar('importfile');
		if(!empty($file['tmp_name'])) {
			$importtype = $request->postVar('importtype');
			if($importtype == 'auto') $importtype = strtolower(substr($file['name'],-3) == '.gz') ? 'compressedsql' : 'rawsql';
			switch($importtype) {
				case 'rawsql':
					$result = new ArrayData(DBP_Sql::execute_script(file($file['tmp_name'])));
					break;
				case 'compressedsql':
					$result = new ArrayData(DBP_Sql::execute_script(gzfile($file['tmp_name'])));
					break;
			}
		}

		return $result ? $result->renderWith('DBP_Database_sql') : $this->instance->customise(array('Message' => array('type' => 'error', 'text' => 'Your file could not be imported. You might want to check if the file size exceeds ' . $this->instance->MaxFileSize() . ' which is the limit set in post_max_size and upload_max_filesize in your php.ini.')))->renderWith('DBP_Database_sql');
	}
	
	function drop($request) {
		$this->instance->drop($request->param('ID'));
		return $this->instance->renderWith('DBP_Database');
	}
}