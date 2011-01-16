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
		foreach(DBP_SQLDialect::$adapters as $name) $adapters->push(new ArrayData(array('Name' => $name, 'Available' => (bool)(DB::getConn() instanceof MSSQLDatabase || $name != 'MSSQL'), 'Selected' => (bool)preg_match('/^' . $name . '/i', get_class(DB::getConn())))));
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
			$config->push(new ArrayData(array(
				'key' => _t('DBP_Database.DB_CONFIG_' . strtoupper($key), $key),
				'val' => $val,
			)));
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
			case 'openoffice':
				header("Content-type: gzip; charset=utf-8");
				header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) .  '.ods"');
				echo $this->openoffice($request->postVar('tables'));
				break;
		}
	}
// http://localhost/clean/admin/dbplumber/database/export

	function openoffice($tables) {
		// http://www.ibm.com/developerworks/web/library/wa-odf/index.html?ca=drs-#list10
		include('../dbplumber/thirdparty/ods-php-0.1rc1/ods.php'); //include the class and wrappers
		$rawsheetcontent = '<?xml version="1.0" encoding="UTF-8"?>
		<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0"><office:scripts/><office:font-face-decls><style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/><style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/></office:font-face-decls><office:automatic-styles><style:style style:name="co1" style:family="table-column"><style:table-column-properties fo:break-before="auto" style:column-width="2.267cm"/></style:style><style:style style:name="ro1" style:family="table-row"><style:table-row-properties style:row-height="0.453cm" fo:break-before="auto" style:use-optimal-row-height="true"/></style:style><style:style style:name="ta1" style:family="table" style:master-page-name="Default"><style:table-properties table:display="true" style:writing-mode="lr-tb"/></style:style></office:automatic-styles><office:body><office:spreadsheet></office:spreadsheet></office:body></office:document-content>';
		$sheet = new ods();
		$sheet->parse($rawsheetcontent);	
		foreach($tables as $table) {
			$rowno = 0;
			foreach(DB::query("SELECT * FROM \"$table\"") as $row) {
				$colno = 0;
				foreach($row as $key => $val) {
					if($rowno == 0) $sheet->addCell($table,0,$colno,$key,'string');
					$sheet->addCell($table, $rowno + 1, $colno, Convert::raw2xml($val), is_numeric($val) ? 'float' : 'string');
					$colno++;
				}
				$rowno++;
			}
			if(!$rowno) {
				$colno = 0;
				foreach(DB::fieldList($table) as $name => $spec) $sheet->addCell($table,0,$colno++,$name,'string');
			}
		}
		saveOds($sheet, TEMP_FOLDER . '/new.ods'); //save the object to a ods file
		$output = file_get_contents(TEMP_FOLDER . '/new.ods');
		unlink(TEMP_FOLDER . '/new.ods');
		return $output;
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
						$cell = "'" . DBP_SQLDialect::get($dialect)->escape($cell) . "'";
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
			echo _t("DBP_Database.NOARTEFACTSMSG", "The database does not contain obsolete tables or columns.");
		} else {
			echo _t("DBP_Database.ARTEFACTSMSG", "These tables / columns are obsolete:") . " (<a href='dev/tasks/RemoveArtefactsTask'>" . _t("DBP_Database.ARTEFACTSTASK", "CleanUpSchemaTask") . "</a>)";
			echo '<ul>';
			foreach($artefacts as $table => $drop) {
				if(is_array($drop)) {
					echo "<li>" . _t("DBP_Database.ARTEFACTS_COLUMN", "Column") . " {$table}." . implode("</li><li>" . _t("DBP_Database.ARTEFACTS_COLUMN", "Column") . " {$table}.", $drop) . "</li>";
				} else {
					echo "<li>" . _t("DBP_Database.ARTEFACTS_TABLE", "Table") . " $table</li>";
				}
			}
			echo '</ul>';
		}
	}
}
