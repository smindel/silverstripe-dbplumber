<?php

class DBPlumberTest extends FunctionalTest {
	
	static $fixture_file = 'sapphire/tests/SiteTreeTest.yml';
	
	protected $extraDataObjects = array(
		'DBPlumberTest_Object',
	);

	function setUp() {
		parent::setUp();
	}
	
	function testSplitScript() {
		
		$script = "SELECT * FROM \"SiteTree\"";
		$commands = DBP_Sql::split_script($script);
		$this->assertType('array', $commands, 'Result is array');
		
		$script = " \n SELECT * FROM \"SiteTree\"\r\n ; ";
		$commands = DBP_Sql::split_script($script);
		$this->assertEquals(1, count($commands), 'Return only one command');
		$this->assertEquals(trim($script), $commands[0], 'Return correct command');
		
		$script = "SELECT * FROM \"SiteTree\"\r\n WHERE \"ID\" > 15;\nUPDATE \"File\" SET \"Name\" = 'somename' WHERE \"ID\" = 79; insert into ErrorPage (ID) values ('79')";
		$commands = DBP_Sql::split_script($script);
		$this->assertEquals(3, count($commands), 'Return all 3 commands');
		$this->assertEquals("insert into ErrorPage (ID) values ('79');", $commands[2], 'Split commands corrctly');
		
		$script = "SELECT * FROM \"SiteTree\"\r\n WHERE \"Content\" LIKE '%;%'; INSERT INTO \"SiteTree\" (\"Content\") values ('\nUPDATE \"File\" SET \"Name\" = 'somename' WHERE \"ID\" = 79;\n')";
		$commands = DBP_Sql::split_script($script);
		$this->assertEquals(2, count($commands), 'Return only 2 commands');
		$this->assertEquals("SELECT", substr($commands[0],0,6), 'Don\'t split on ; when inside quotes');
		$this->assertEquals("INSERT", substr($commands[1],0,6), 'Don\'t split if command is inside quotes');
		
	}
	
	function testExecute() {
		
		// query with output
		$command = "SELECT * FROM \"SiteTree\"";
		$query = new DBP_Sql($command);
		$result = $query->execute();
		$this->assertType('array', $result, 'Result is array');
		$this->assertEquals($command, $result['Query'], 'Return command correctly');
		$this->assertEquals("DBP_Field", get_class($result['Fields']->First()), 'Result contains fields');
		$this->assertEquals("DBP_Record", get_class($result['Records']->First()), 'Result contains records');
		$this->assertNotEquals('error', $result['Message']['type'], 'No errors message');
		
		// query without output
		$command = "UPDATE \"SiteTree\" SET \"URLSegment\" = 'someurl' WHERE \"ID\" > 5000;";
		$query = new DBP_Sql($command);
		$result = $query->execute();
		$this->assertEquals($command, $result['Query'], 'Return command correctly');
		$this->assertNotEquals('error', $result['Message']['type'], 'No errors message');
		
		// query with escaped songle quoute
		$command = "INSERT INTO \"SiteTree\" (\"ClassName\", \"URLSegment\", \"Title\", \"Content\") VALUES ('ErrorPage', 'page-not-found', 'Page not found', '<p>Sorry, it seems you were trying to access a page that doesn''t exist.</p><p>Please check the spelling of the URL you were trying to access and try again.</p>');";
		$query = new DBP_Sql($command);
		$result = $query->execute();
		$this->assertNotEquals('error', $result['Message']['type'], 'Escaping quotes works: ' . print_r($result, true));

		// force an error
		$command = "force error";
		$query = new DBP_Sql($command);
		$result = $query->execute();
		$this->assertEquals($command, $result['Query'], 'Return command correctly');
		$this->assertEquals('error', $result['Message']['type'], 'Return error');
		$this->assertType('string', $result['Message']['text'], 'Return error message');
		
	}
	
	function testExecuteScriptContainingOnlyOneCommand() {
		
		// only one command, with output
		$script = "SELECT * FROM \"SiteTree\"";
		$result = DBP_Sql::execute_script($script);
		$this->assertType('array', $result, 'Result is array');
		$this->assertEquals($script . ';', $result['Query'], 'Return SELECT command correctly');
		$this->assertEquals("DBP_Field", get_class($result['Fields']->First()), 'Result contains fields');
		$this->assertEquals("DBP_Record", get_class($result['Records']->First()), 'Result contains records');
		$this->assertNotEquals('error', $result['Message']['type'], 'No errors message');

		// only one command, without output
		$script = "INSERT INTO \"ErrorPage\" (\"ID\", \"ErrorCode\") VALUES ('99', '999')";
		$result = DBP_Sql::execute_script($script);
		$this->assertEquals($script . ';', $result['Query'], 'Return UPDATE command correctly');
		$this->assertNotEquals('error', $result['Message']['type'], 'No errors message');
		$this->assertEquals(999, DB::query("SELECT \"ErrorCode\" FROM \"ErrorPage\" WHERE \"ID\" = '99'")->Value(), 'Make sure the query really gets executed');
		
		// only one command, forcing an error
		$script = "force error";
		$result = DBP_Sql::execute_script($script);
		$this->assertEquals($script . ';', $result['Query'], 'Return ERROR command correctly');
		$this->assertEquals('error', $result['Message']['type'], 'Return error');
		$this->assertType('string', $result['Message']['text'], 'Return error message');
		
	}
	
	function testExecuteScriptContainingMultipleCommands() {
		
		// mixed commands, with'n without output, without errors
		$script = "SELECT * FROM \"SiteTree\"\r\n WHERE \"ID\" > 15;\nUPDATE \"File\" SET \"Name\" = 'somename' WHERE \"ID\" = 79; insert into \"ErrorPage\" (\"ID\") values ('79')";
		$result = DBP_Sql::execute_script($script);
		$this->assertType('array', $result, 'Result is array');
		$this->assertEquals('SELECT', substr($result['Query'], 0, 6), 'Return command');
		$this->assertEquals('highlight', $result['Message']['type'], 'Return message but no error');
		$this->assertContains('no error', $result['Message']['text'], 'Return message string');
		
		// mixed commands, with errors
		$script = "SELECT * FROM \"SiteTree\"\r\n WHERE \"ID\" > 15;\nUPDATE \"File\" SET \"Name\" = 'somename' WHERE \"ID\" = 79; insert outof ErrorPage (ID) values ('79')";
		$result = DBP_Sql::execute_script($script);
		$this->assertType('array', $result, 'Result is array');
		$this->assertEquals('SELECT', substr($result['Query'], 0, 6), 'Return command');
		$this->assertEquals('error', $result['Message']['type'], 'Return message with error');
		
	}

	function testExecuteScriptWithRollback() {
		
		// skip if adapter doesn't support transactions
		if(!DB::getConn()->supportsTransactions()) return;
		
		// mixed commands, with errors and rollback
		$script = "
			INSERT INTO \"SiteTree\" (\"Title\", \"Content\") VALUES ('DBPRollbackTest', '<p>This should be gone after rollback.</p>');
			force error;
			UPDATE \"SiteTree\" SET \"URLSegment\" = 'rollback-1' WHERE \"Title\" = 'DBPRollbackTest';
		";
		$result = DBP_Sql::execute_script($script);
		$this->assertType('array', $result, 'Result is array');
		$this->assertEquals('INSERT', substr($result['Query'], 0, 6), 'Return command');
		$this->assertEquals('error', $result['Message']['type'], 'Return message with error');
		$this->assertStringEndsWith('Transaction rolled back', $result['Message']['text'], 'Message states transaction state');
		$this->assertEquals(0, DB::query("SELECT COUNT(*) FROM \"SiteTree\" WHERE \"Title\" = 'DBPRollbackTest'")->Value(), 'Make sure the transaction really got rolled back');
		
	}

	function testExportingSpecialChars() {

		$obj = new DBPlumberTest_Object();
		$dbp = new DBP_Database_Controller();
		
		$specialchars = array(
			'TEST' => array(
				'MySQL' => 'TEST',
				'SQLite' => 'TEST',
				'MSSQL' => 'TEST',
				'Postgres' => 'TEST',
			),
			'\'' => array(
				'MySQL' => '\\\'',
				'SQLite' => "''",
				'MSSQL' => "''",
				'Postgres' => "''",
			),
			'"' => array(
				'MySQL' => '\"',
				'SQLite' => '"',
				'MSSQL' => '"',
				'Postgres' => '"',
			),
			"\\" => array(
				'MySQL' => "\\\\",
				'SQLite' => "\\",
				'MSSQL' => "\\",
				'Postgres' => "\\",
			),
			"\\'" => array(
				'MySQL' => "\\\\\'",
				'SQLite' => "\\''",
				'MSSQL' => "\\''",
				'Postgres' => "\\''",
			),
			"<a></a>" => array(
				'MySQL' => '<a></a>',
				'SQLite' => '<a></a>',
				'MSSQL' => '<a></a>',
				'Postgres' => '<a></a>',
			),
		);
		
		foreach($specialchars as $raw => $converted) {

			$obj->SpecialChar = 'START_TOKEN' . $raw . 'END_TOKEN';
			$obj->write();

			foreach(DBP::$adapters as $class => $dialect) {
				if($dialect != 'MSSQL' || DB::getConn() instanceof MSSQLDatabase) {
					$dump = $dbp->backup(array('DBPlumberTest_Object'), $dialect);
					foreach($dump as $line) if(substr($line, 0, 6) == 'INSERT') $insert = $line;
					$this->assertTrue((bool)strpos($insert, 'START_TOKEN' . $converted[$dialect] . 'END_TOKEN'), print_r($raw, true) . ' has been properly converted from ' . get_class(DB::getConn()) . ' to a ' . $dialect . ' INSERT');

					if(DB::getConn() instanceof $class && preg_match('/START_TOKEN.*END_TOKEN/', $insert, $matches)) $import = $matches[0];
				}
			}

			if(!empty($import)) {
				DB::query("UPDATE \"DBPlumberTest_Object\" SET \"SpecialChar\" = '$import:NEW' WHERE \"ID\" = " . $obj->ID);
				$obj = DataObject::get_by_id('DBPlumberTest_Object', $obj->ID);
				$this->assertEquals('START_TOKEN' . $raw . 'END_TOKEN:NEW', $obj->SpecialChar, print_r($raw, true) . ' has been properly restored for ' . get_class(DB::getConn()));
			}
		}
	}
}

class DBPlumberTest_Object extends DataObject {

	static $db = array(
		'SpecialChar' => 'Text',
	);

}