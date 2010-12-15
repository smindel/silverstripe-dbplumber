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

	function Adapters() {
		$adapters = new DataObjectSet();
		foreach(DBP::$adapters as $name) $adapters->push(new ArrayData(array('Name' => $name, 'Available' => (bool)(DB::getConn() instanceof MSSQLDatabase || $name != 'MSSQL'), 'Selected' => (bool)preg_match('/^' . $name . '/i', get_class(DB::getConn())))));
		return $adapters;
	}

	function Transactions() {
		return DB::getConn()->supportsTransactions();
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) $tables->push(new DBP_Table($table));
		
		$tables->sort('LowerCaseName');

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
	
	function ExposeConfig() {
		global $databaseConfig;
		if(!DatabaseBrowser::$expose_config) return;
		$config = new DataObjectSet();
		foreach($databaseConfig as $key => $val) {
			if(!$val) continue;
			if($key == "password") $val = "*****";
			$key = ucfirst($key);
			$config->push(new ArrayData(array('key' => $key, 'val' => $val)));
		}
		return $config;
	}
	
	function Artefacts() {
		$oldschema = array();
		$newschema = array();
		$current = DB::getConn()->currentDatabase();
		foreach(DB::getConn()->tableList() as $lowercase => $dbtablename) $oldschema[$dbtablename] = DB::getConn()->fieldList($dbtablename);

		DB::getConn()->selectDatabase('tmpdb');
		$test = new SapphireTest();
		$test->create_temp_db();
		foreach(DB::getConn()->tableList() as $lowercase => $dbtablename) $newschema[$lowercase] = DB::getConn()->fieldList($dbtablename);
		$test->kill_temp_db();
		DB::getConn()->selectDatabase($current);
		
		$artefacts = array();
		foreach($oldschema as $table => $fields) {
			if(!isset($newschema[strtolower($table)])) {
				$artefacts[$table] = $table;
				continue;
			}
			
			foreach($fields as $field => $spec) {
				if(!isset($newschema[strtolower($table)][$field])) {
					$artefacts[$table][$field] = $field;
				}
			}
		}
		return $artefacts;
	}
}

class DBP_Database_Controller extends DBP_Controller {

	function execute($request) {

		$vars = $this->getRequest()->requestVars();

		$result = new ArrayData(DBP_Sql::execute_script($vars['query']));
		
		return $result ? $result->renderWith('DBP_Database_sql') : $this->instance->renderWith('DBP_Database_sql');

	}
	
	function export($request) {
		$dialect = $request->postVar('SqlDialect');
		
		@ini_set('max_execution_time', '0');
		
		if($dialect == 'MSSQL' && !method_exists(DB::getConn(), "getIdentityColumn")) return "DB PLUMBER ERROR: method MSSQLDatabase::getIdentityColumn() does not exist. Update your mssql module.";
		
		switch($request->postVar('exporttype')) {
			case 'backup':
				$commands = implode("\r\n", $this->backup($request->postVar('tables'), $dialect));
				header("Content-type: text/sql; charset=utf-8");
				header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '_' . $dialect .  '.sql"');
				echo $commands;
				break;
			case 'compressed':
				$commands = gzencode(implode("\r\n", $this->backup($request->postVar('tables'), $dialect)), 9);
				header("Content-type: gzip; charset=utf-8");
				header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '_' . $dialect .  '.sql.gz"');
				echo $commands;
				break;
		}
	}

	function backup($tables, $dialect) {
		global $databaseConfig;

		$commands = array(
			'/*',
			'   SQL Dump of ' . get_class(DB::getConn()) . ' ' . DB::getConn()->currentDatabase() . (DB::getConn() instanceof Sqlite3Database ? ' in ' . $databaseConfig['path'] : ' on ' . $databaseConfig['server']),
			"   SQL Dialect $dialect",
			'   Created on ' . date('r'),
			'   Created with Database Plumber for Silverstripe',
			"   =============================================",
			"   DISCLAIMER: NO WARRANTY, USE AT YOUR OWN RISC",
			"   =============================================",
			'*/', ''
		);
		if($dialect == 'MySQL') $commands[] = "SET sql_mode = 'ANSI';";
		
		foreach($tables as $table) {
			$fields = array();
			if($dialect == 'MSSQL' && ($idcol = DB::getConn()->getIdentityColumn($table))) $commands[] = "SET IDENTITY_INSERT \"$table\" ON;";
			$commands[] = 'DELETE FROM "' . $table . '";';
			foreach(DB::fieldList($table) as $name => $spec) $fields[] = $name;
			foreach(DB::query('SELECT * FROM "' . $table . '"') as $record) {
				$cells = array();
			
				foreach($record as $cell) {
					if(is_null($cell)) {
						$cell = 'NULL';
					} else if(is_string($cell)) {
						$cell = "'" . str_replace('\'', '\'\'', $cell) . "'";
					}
					$cells[] = $cell;
				}
				$commands[] = 
					"INSERT INTO \"$table\" (\"" . 
					implode('", "', $fields) . 
					"\") VALUES (" . 
					implode(", ", $cells) . 
					");";
			}
			if($dialect == 'MSSQL' && $idcol) $commands[] = "SET IDENTITY_INSERT \"$table\" OFF;";
		}

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
	
	function showartefact() {
		$artefacts = $this->instance->Artefacts();
		if(empty($artefacts)) {
			echo "The database does not contain obsolete tables or columns.";
		} else {
			echo "These tables / columns are obsolete: (<a href='dev/tasks/RemoveArtefactsTask'>CleanUpSchemaTask</a>)";
			echo '<ul>';
			foreach($artefacts as $table => $drop) {
				if(is_array($drop)) {
					echo "<li>column {$table}." . implode("</li><li>column {$table}.", $drop) . "</li>";
				} else {
					echo "<li>table $table</li>";
				}
			}
			echo '</ul>';
		}
	}
}
