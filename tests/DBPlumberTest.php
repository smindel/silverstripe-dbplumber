<?php

class DBPlumberTest extends FunctionalTest {
	
	static $fixture_file = 'sapphire/tests/SiteTreeTest.yml';
	
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
		$this->assertEquals("SELECT * FROM \"SiteTree\"", $commands[0], 'Return correct command');
		
		$script = "SELECT * FROM \"SiteTree\"\r\n WHERE \"ID\" > 15;\nUPDATE \"File\" SET \"Name\" = 'somename' WHERE \"ID\" = 79; insert into ErrorPage (ID) values ('79')";
		$commands = DBP_Sql::split_script($script);
		$this->assertEquals(3, count($commands), 'Return all 3 commands');
		$this->assertEquals("insert into ErrorPage (ID) values ('79')", $commands[2], 'Split commands corrctly');
		
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
		$this->assertFalse($result['Message'], 'No errors => no message');
		
		// query without output
		$command = "UPDATE \"SiteTree\" SET \"URLSegment\" = 'someurl' WHERE \"ID\" > 5000;";
		$query = new DBP_Sql($command);
		$result = $query->execute();
		$this->assertEquals($command, $result['Query'], 'Return command correctly');
		$this->assertFalse($result['Message'], 'No errors => no message');
		
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
		$this->assertEquals($script, $result['Query'], 'Return SELECT command correctly');
		$this->assertEquals("DBP_Field", get_class($result['Fields']->First()), 'Result contains fields');
		$this->assertEquals("DBP_Record", get_class($result['Records']->First()), 'Result contains records');
		$this->assertFalse($result['Message'], 'No errors => no message');

		// only one command, without output
		$script = "INSERT INTO \"ErrorPage\" (\"ID\", \"ErrorCode\") VALUES ('99', '999')";
		$result = DBP_Sql::execute_script($script);
		$this->assertEquals($script, $result['Query'], 'Return UPDATE command correctly');
		$this->assertFalse($result['Message'], 'No errors => no message');
		$this->assertEquals(999, DB::query("SELECT \"ErrorCode\" FROM \"ErrorPage\" WHERE \"ID\" = '99'")->Value(), 'Make sure the query really gets executed');
		
		// only one command, forcing an error
		$script = "force error";
		$result = DBP_Sql::execute_script($script);
		$this->assertEquals($script, $result['Query'], 'Return ERROR command correctly');
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
}