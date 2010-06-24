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

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new Exception($errstr);
}

class DBP_Sql {
	
	public $query;
	
	function __construct($query = false) {
		if($query) $this->query = $query;
	}
	
	function type() {
		return preg_match('/^(\w+)/', $this->query, $matches) ? strtoupper($matches[1]) : false;
	}
	
	function execute() {
		set_error_handler('exception_error_handler');
		$results = false;
		$msg = array('text' => 'no errors', 'type' => 'good');
		try {
			$results = DB::getConn()->query($this->query, E_USER_NOTICE);
		} catch(Exception $e) {
			$msg = array('text' => htmlentities($e->getMessage()), 'type' => 'error');
		}
		restore_error_handler();

		$fields = new DataObjectSet();
		$records = new DataObjectSet();
		if(isset($results) && $results instanceof SS_Query) {
			foreach($results as $result) {
				$record = new DBP_Record();
				$data = array();
				foreach($result as $field => $val) {
					if(!$fields->find('Label', $field)) $fields->push(new DBP_Field($field));
					$data[$field] = strlen($val) > 64 ? substr($val,0,63) . '<span class="truncated">&hellip;</span>' : $val;
				}
				$record->Data($data);
				$records->push($record);
			}
		}
		
		return array(
			'Query' => $this->query,
			'Fields' => $fields,
			'Records' => $records,
			'Message' => $msg,
		);
	}

	static function execute_script($queries) {
		$queries = DBP_Sql::split_script($queries);
		switch(count($queries)) {
			case 0: return array();
			case 1:
				$query = new DBP_Sql($queries[0]);
				$records = $query->execute();
				return $records;
			default:
				$status = 'highlight';
				if(DB::getConn()->supportsTransactions()) DB::getConn()->startTransaction();
				foreach($queries as $query) {
					$query = new DBP_Sql($query);
					$result = $query->execute();
					if($result['Message']['type'] == 'error') {
						$msg[] = $result['Query'] . '<br />' . $result['Message']['text'];
						$status = 'error';
						break;
					}
					$msg[] = $query->type() . ' ' . ($result['Message']['text'] ? $result['Message']['text'] : 'no error');
				}
				if(DB::getConn()->supportsTransactions()) {
					if($status == 'error') DB::getConn()->transactionRollback(); else DB::getConn()->endTransaction();
					$msg[] = 'Transaction rolled back';
				}
				$result = array(
					'Query' => implode(";\r\n", $queries),
					'Message' => array(
						'text' => implode("<br />\n", $msg),
						'type' => $status
					)
				);
				return $result;
		}
	}
	
	static function split_script($commands) {

		// grouping characters
		$bracketcharacters = array(
			array('open' => '(', 'close' => ')', 'escape' => false),
			array('open' => '"', 'close' => '"', 'escape' => false),
			array('open' => "'", 'close' => "'", 'escape' => true),
		);

		// clean up a little first, make it one big string, trim it and make sure it ends with a ;
		if(is_array($commands)) $commands = implode("\n", $commands);
		$commands = trim($commands);
		if(substr($commands, -1) != ';') $commands .= ';';

		// looping over the script and finding ;'s OUTSIDE of brackets
		$bcstack = array();
		$output =  array();
		while(strlen($commands) > 1) {
			$continue = false;
			for($i = 0; $i < strlen($commands); $i++) {
				foreach($bracketcharacters as $id => $bc) {
					// if we hit a closing character and it is matching the opening character currently open (on top of the bc stack)
					if($commands[$i] == $bc['close'] && $bcstack[count($bcstack) - 1] === $id) {
						array_pop($bcstack);
						continue;
					}
					if($commands[$i] == $bc['open']) {
						$bcstack[] = $id;
						continue;
					}
				}
				if($commands[$i] == ';' && count($bcstack) == 0) {
					$o = trim(substr($commands, 0, $i));
					if(strlen($o) > 1) $output[] = $o;
					$commands = trim(substr($commands, $i + 1));
					$continue = true;
					break;
				}
			}
			if(!$continue) {
				$o = trim($commands);
				if(strlen($o) > 1) $output[] = $o;
				$commands = '';
				break;
			}
		}
		
		return $output;
	}
}