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
					if($status == 'error') {
						DB::getConn()->transactionRollback();
						$msg[] = 'Transaction rolled back';
					} else {
						 DB::getConn()->endTransaction();
					}
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

		// clean up a little first, make it one big string, trim it and make sure it ends with a ;
		if(is_array($commands)) $commands = implode("\n", $commands);
		$commands = trim($commands);
		if(substr($commands, -1) != ';') $commands .= ';';
		
		// looping over the script character by character
		$last = '';
		$scope = 'root';
		$indent = 0;
		$index = 0;
		$output = array();
		for($i = 0; $i < strlen($commands); $i++) {
			
			$char = $commands[$i];
			
			// determin if we are in an identifier or a literal
			if($scope == 'root') {
				if($char == '"') $scope = 'identifier';
				if($char == "'") $scope = 'literal';
			} else if($scope == 'identifier' && $char == '"') {
				$scope = 'root';
			} else if($scope == 'literal' && $char == "'" && $last != "\\") {
				$scope = 'root';
			}
			
			// when we are not in an identifier or a literal all whitespaces are converted to spaces
			if($scope == 'root' && preg_match('/^\s$/', $char)) $char = ' ';
			
			// when we are not in an identifier or a literal remove unneccessary spaces
			if($scope == 'root' && $last == ' ' && ($char == ';' || $char == ' ')) {
				$output[$index] = substr($output[$index], 0, strlen($output[$index]) - 1);
				$last = substr($output[$index], -1);
			}
			
			// add the current character to the current command
			if(!empty($output[$index]) || $char != ' ') {
				if($char == ' ' && preg_match('/\bSELECT$|\bFROM$|\bWHERE$|\bGROUP BY$|\bHAVING$|\bJOIN$|\bUNION$|\bINTERSECT$|\bEXCEPT$|\bUPDATE$|\bSET$|\bINSERT INTO$|\bVALUES$/i', $output[$index])) {
					$output[$index] .= "\n";
					$indent++;
				} else if($char == ' ' && $last != '' && preg_match('/^SELECT\b|^FROM\b|^WHERE\b|^GROUP BY\b|^HAVING\b|^LEFT\b|^RIGHT\b|^INNER\b|^UNION\b|^INTERSECT\b|^EXCEPT\b|^UPDATE\b|^SET\b|^INSERT INTO\b|^VALUES\b/i', substr($commands,$i))) {
					$output[$index] .= "\n";
					$indent--;
				} else {
					$output[$index] .= $char;
				}
			}
			
			// end current command if we hit a semicolon
			if($scope == 'root' && $char == ';' && $last != ';') {
				$last = '';
				$index++;
				$indent = 0;
			}
			
			$last = $char;
		}
		
		return $output;
	}
	
	static function old_split_script($commands) {

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
					if($commands[$i] == $bc['close'] && count($bcstack) && $bcstack[count($bcstack) - 1] === $id) {
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

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new Exception($errstr);
}