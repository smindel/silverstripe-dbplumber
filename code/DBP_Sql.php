<?php

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
		$msg = array('text' => $this->type() == 'SELECT' ? 'no records' : 'no errors', 'type' => 'good');
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

		@ini_set('max_execution_time', '0');

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
					if($status == 'error') {
						DB::getConn()->transactionRollback();
						$msg[] = 'Transaction rolled back';
					} else {
						 DB::getConn()->endTransaction();
					}
				}
				$result = array(
					'Query' => implode("\r\n", $queries),
					'Message' => array(
						'text' => implode("<br />\n", $msg),
						'type' => $status
					)
				);
				return $result;
		}
	}
	
	static function split_script($commands, $doindent = false) {
		
		if($c = Controller::curr()) if($r = $c->getRequest()) if($r->requestVar('indent')) $doindent = true;
		
		// clean up a little first, make it one big string, trim it and make sure it ends with a ;
		if(is_array($commands)) $commands = implode("\n", $commands);
		$commands = trim($commands);
		if(substr($commands, -1) != ';') $commands .= ';';

		// looping over the script character by character
		$scope = 'root';
		$indent = 0;
		$index = 0;
		$output = array();
		for($i = 0; $i < strlen($commands); $i++) {

			$char = $commands[$i];

			if(!isset($output[$index])) $output[$index] = '';

			// when we are not in an identifier or a literal all whitespaces are converted to spaces
			if($doindent && $scope != 'identifier' && $scope != 'literal' && trim($char) == '') $char = ' ';

			// when we are not in an identifier or a literal remove unneccessary spaces and semicolons
			if(
				$doindent &&
				$scope != 'identifier' &&
				$scope != 'literal' &&
				(
					(
						trim(substr($output[$index],-1)) == '' && 
						$char == ' '
					) || (
						trim(substr($output[$index],-1)) == ';' &&
						$char == ';'
					) || (
						$char == ' ' &&
						(trim($commands[$i + 1]) == '' || $commands[$i + 1] == ';')
					)
				)
			) continue;

			// remove unneccessary spaces and semicolons from the beginning of the command
			if($doindent && $scope == 'root' && trim($output[$index]) == '' && $char == ';') continue;

			// determin if we are in an identifier or a literal
			if($scope == 'root') {
				if($char == '"') $scope = 'identifier';
				if($char == "'") $scope = 'literal';
			} else if($scope == 'identifier' && $char == '"') {
				$scope = 'root';
			} else if($scope == 'literal' && $char == "'" && (!(DB::getConn() instanceof MySQLDatabase) || substr($output[$index],-1) != "\\")) {
				$scope = 'root';
			} else if($scope == 'bracket' && $char == ")") {
				$scope = 'root';
			}

			// add the current character to the current command
			if(!empty($output[$index]) || $char != ' ') {
				if($doindent && $char == ' ' && preg_match('/\bSELECT$|\bFROM$|\bWHERE$|\bGROUP BY$|\bHAVING$|\bJOIN$|\bUNION$|\bINTERSECT$|\bEXCEPT$|\bUPDATE$|\bSET$|\bINSERT INTO$|\bVALUES$|\bORDER BY$|\bLIMIT$/i', $output[$index])) {
					$ind = ++$indent > 0 ? str_repeat("\t", $indent) : '';
					$output[$index] .= "\n$ind";
				} else if($doindent && $char == ' ' && !empty($output[$index]) && preg_match('/^\s+FROM\b|^\s+WHERE\b|^\s+GROUP BY\b|^\s+HAVING\b|^\s+LEFT\b|^\s+RIGHT\b|^\s+INNER\b|^\s+UNION\b|^\s+INTERSECT\b|^\s+EXCEPT\b|^\s+UPDATE\b|^\s+SET\b|^\s+INSERT INTO\b|^\s+VALUES\b|^\s+ORDER BY\b|^\s+LIMIT\b/i', substr($commands,$i))) {
					$ind = --$indent > 0 ? str_repeat("\t", $indent) : '';
					$output[$index] .= "\n$ind";
				} else if($doindent && $char == ' ' && $scope == 'root' && preg_match('/,$|\bAND$|\bOR$/i', $output[$index])) {
					$ind = $indent > 0 ? str_repeat("\t", $indent) : '';
					$output[$index] .= "\n$ind";
				} else {
					$output[$index] .= $char;
				}
			}

			// end current command if we hit a semicolon
			if($scope == 'root' && $char == ';') {
				$index++;
				$indent = 0;
			}
		}

		$commands = array();
		foreach($output as $command) {
			$command = trim($command);
			if(empty($command) || $command == ';') continue;
			$commands[] = $command;
		}
		return $commands;
	}
			
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new Exception($errstr);
}