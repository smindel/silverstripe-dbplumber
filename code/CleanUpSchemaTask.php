<?php

class CleanUpSchemaTask extends BuildTask {

	protected $title = 'Clean Up Database Schema';
	
	protected $description = 'During devolpment of your Silverstripe application you may have deleted a data object class or removed a field from a data object. This leaves obsolete columns and tables in your databse behind. Because these columns or tables may contain data that must not be deleted sapphire doesn\'t delete those automatically. But if you know what you are doing and you are sure that these remains are no longer required, you can run this task to delete them. This is irreversible.';
	
	function init() {
		parent::init();
		
		if(!Permission::check('ADMIN')) {
			return Security::permissionFailure($this);
		}
	}
	
	public function run($request) {
		$db = new DBP_Database();
		$artefacts = $db->Artefacts();
		if(empty($artefacts)) {
			echo '<h3>Schema is clean, nothin to drop.</h3>';
		} else {
			echo '<h3>Dropping:</h3>';
			echo '<ul>';
			foreach($artefacts as $table => $drop) {
				if(is_array($drop)) {
					DBP_SQLDialect::get()->dropColumns($table, $drop);
					echo "<li>column " . implode("</li><li>column ", $drop) . "</li>";
				} else {
					echo "<li>table $table</li>";
					DBP_SQLDialect::get()->dropTable($table);
				}
			}
			echo '</ul>';
		}
	}
}