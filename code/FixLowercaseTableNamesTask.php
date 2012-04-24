<?php

class FixLowercaseTableNamesTask extends BuildTask {

	protected $title = 'Fix Lowercase Table Names';

	protected $description = 'When migrating databases from a Windows machine with lowercase tablenames to a *nix machine tables have to be renamed.';

	function init() {
		parent::init();

		if(!Permission::check('ADMIN')) {
			return Security::permissionFailure($this);
		}
	}

	public function run($request) {
		$db = DB::getConn();
		if(!($db instanceof MySQLDatabase)) {
			echo '<h3>This task only appies to MySQL databases. This installation is using a ' . get_class($db) . '</h3>';
			return;
		}
		$oldschema = array();
		$newschema = array();
		$renamed = 0;
		$current = DB::getConn()->currentDatabase();
		foreach(DB::getConn()->tableList() as $lowercase => $dbtablename) $oldschema[] = $dbtablename;

		DB::getConn()->selectDatabase('tmpdb');
		$test = new SapphireTest();
		$test->create_temp_db();
		foreach(DB::getConn()->tableList() as $lowercase => $dbtablename) $newschema[] = $dbtablename;
		$test->kill_temp_db();
		DB::getConn()->selectDatabase($current);
		
		echo "<ul>\n";
		foreach($newschema as $table) if(in_array(strtolower($table), $oldschema)) {
			echo "<li>renaming $table</li>";
			$db->renameTable(strtolower($table), $table);
			$renamed++;
		}
		echo "</ul>\n";
		echo "<p>$renamed tables renamed.</p>\n";
	}
}
