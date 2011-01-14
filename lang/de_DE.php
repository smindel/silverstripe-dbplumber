<?php

/**
 * German (Germany) language pack
 * @package dbplumber
 * @subpackage i18n
 */

i18n::include_locale_file('dbplumber', 'en_US');

global $lang;

if(array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
	$lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
	$lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['DatabaseBrowser']['MENUTITLE'] = 'DB Klempner';

$lang['de_DE']['DBP_Database']['NOARTEFACTSMSG'] = 'Die Datenbank enth&auml;t keine Artefakte.';
$lang['de_DE']['DBP_Database']['ARTEFACTSMSG'] = 'Die folgenden Tabellen / Spalten sind obsolet:';
$lang['de_DE']['DBP_Database']['ARTEFACTSTASK'] = 'Schema bereiningen';
$lang['de_DE']['DBP_Database']['ARTEFACTS_TABLE'] = 'Tabelle';
$lang['de_DE']['DBP_Database']['ARTEFACTS_COLUMN'] = 'Spalte';
$lang['de_DE']['DBP_Database']['DB_CONFIG_TYPE'] = 'Adapter';
$lang['de_DE']['DBP_Database']['DB_CONFIG_SERVER'] = 'Server';
$lang['de_DE']['DBP_Database']['DB_CONFIG_USERNAME'] = 'Benutzername';
$lang['de_DE']['DBP_Database']['DB_CONFIG_DATABASE'] = 'Datenbank';

$lang['de_DE']['DBP_Database.ss']['DATABASE_HEADER'] = 'Datenbank';
$lang['de_DE']['DBP_Database.ss']['INFO_TAB'] = 'Info';
$lang['de_DE']['DBP_Database.ss']['SQL_TAB'] = 'SQL';
$lang['de_DE']['DBP_Database.ss']['IMPORT_EXPORT_TAB'] = 'Import / Export';
$lang['de_DE']['DBP_Database.ss']['ARTEFACTS_TAB'] = 'Artefakte';

$lang['de_DE']['DBP_Database.ss']['DB_TYPE'] = 'Typ';
$lang['de_DE']['DBP_Database.ss']['DB_VERSION'] = 'Version';
$lang['de_DE']['DBP_Database.ss']['DB_TRANSACTIONS'] = 'Transaktionen';
$lang['de_DE']['DBP_Database.ss']['DB_TRANACTIONS_SUPPORTED'] = 'unterst&uuml;tzt';
$lang['de_DE']['DBP_Database.ss']['DB_TRANSACTIONS_NOT_SUPPORTED'] = 'nicht unterst&uuml;tzt';

$lang['de_DE']['DBP_Database.ss']['COMPUTING_ARTEFACTS'] = 'Berechne Artefakte...';

$lang['de_DE']['DBP_Database_sql.ss']['BUTTON_SUBMIT'] = 'los';
$lang['de_DE']['DBP_Database_sql.ss']['CHECKBOX_INDENT_LABEL'] = 'SQL einr&uuml;cken f&uuml;r bessere Lesbarkeit';

$lang['de_DE']['DBP_Database_export.ss']['TABLES_TO_EXPORT'] = 'Tabellen';
$lang['de_DE']['DBP_Database_export.ss']['EXPORT_FORMAT'] = 'Exportformat';
$lang['de_DE']['DBP_Database_export.ss']['EXPORT_FORMAT_BACKUP'] = 'Backup (SQL DELETEs und INSERTs)';
$lang['de_DE']['DBP_Database_export.ss']['EXPORT_FORMAT_BACKUP_ZIPPED'] = 'Backup ZLIB-komprimiert';
$lang['de_DE']['DBP_Database_export.ss']['SQL_DIALECT'] = 'SQL-Dialekt';
$lang['de_DE']['DBP_Database_export.ss']['EXPORT_BUTTON'] = 'exportieren';

$lang['de_DE']['DBP_Database_import.ss']['MAX_FILE_SIZE'] = 'SQL-Datei (max. Dateigr&ouml;&szlig;e';
$lang['de_DE']['DBP_Database_import.ss']['IMPORT_FORMAT'] = 'Importformat';
$lang['de_DE']['DBP_Database_import.ss']['SQL_COMMANDS'] = 'SQL-Anweisungen';
$lang['de_DE']['DBP_Database_import.ss']['ZLIB_COMPRESSED'] = 'SQL-Anweisungen (ZLIB-komprimiert)';
$lang['de_DE']['DBP_Database_import.ss']['AUTO_DETECT'] = 'automatische erkennung';
$lang['de_DE']['DBP_Database_import.ss']['IMPORT_BUTTON'] = 'importieren';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO'] = 'HILFE';
$lang['de_DE']['DBP_Database_import.ss']['IMPORT_EXPORT'] = 'Import / Export';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO_LINE1'] = 'Klicke auf <i>exportieren</i> um eine Sicherheitskopie der Datenbank zu erstellen oder stelle einen alten Datenbank zustand wieder her indem du eine Sicherheitskopie hochl&auml;dst.';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO_LINE2'] = 'Sicherheitskopien enthalten nur die Daten in deiner Datenbank. Die Datei enth&auml;lt SQL-Anweisungen, eine DELETE-Anweisung pro Tabelle um alle derzeit in der Datenbank befindlichen Eintr&auml;ge zu l&uuml;schen und ein INSERT statement pro gesichertem Eintrag.';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO_LINE3'] = 'Sicherheitskopien enthalten nicht die Struktur der Datenbank. <b>Das hei&szlig;t, dass sich das Datenbankschema zischen Export und Import nicht unterscheiden darf. F&uuml;hre ggf. dev/build aus und entferne Artefakte.</b>';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO_LINE4'] = 'Gruns&auml;tzlich ist es m&ouml;glich, mittels Export/Import Daten zwischen verschiedenen Servern und sogar verschiedenen DBMS hin und her zu kopieren. Es w&auml;re z.B. m&ouml;glich, eine MySQL Datenbank von einem Produktionsserver in die SQLite Datenbank auf einem Entwicklungsserver zu kopieren, vorausgesetzt dass das Schema auf dem Entwicklungsserver mindestens alle Tabellen und Spalten enthaelt die auf dem Produktionsserver exportiert wurden. Eine Einschr&auml;nkung: Wenn zu SQL Server migriert wird, muss die Quelldatenbank ebenfalls SQL Server sein.';
$lang['de_DE']['DBP_Database_import.ss']['HOWTO_LINE5'] = 'Falls der Import fehlschl&auml;gt, pr&uuml;fe zunaechst die Gr&ouml;&szlig;e der Sicherheitskopie. Benutze Kompression um die Dateigr&ouml;&szlig;e zu reduzieren. Um die maximale Gr&ouml;&szlig;e zu erh&ouml;, &auml;ndere post_max_size und upload_max_filesize in der php.ini.';

$lang['de_DE']['DBP_Table.ss']['TABLE'] = 'Tabelle';
$lang['de_DE']['DBP_Table.ss']['BROWSE'] = 'Eintr&auml;ge';
$lang['de_DE']['DBP_Table.ss']['STRUCTURE'] = 'Struktur';
$lang['de_DE']['DBP_Table.ss']['FORM'] = 'Formular';
$lang['de_DE']['DBP_Table.ss']['EMPTY'] = 'Leeren';
$lang['de_DE']['DBP_Table.ss']['DROP'] = 'L&ouml;schen';
$lang['de_DE']['DBP_Table.ss']['FIELD_NAME'] = 'Feldname';
$lang['de_DE']['DBP_Table.ss']['FIELD_SPEC'] = 'Felddefinition';

$lang['de_DE']['DBP_Table_index.ss']['SHOWING_RECORDS'] = 'Eintr&auml;ge';
$lang['de_DE']['DBP_Table_index.ss']['TOTAL'] = 'gesamt';
$lang['de_DE']['DBP_Table_index.ss']['HOWTO'] = 'HILFE';
$lang['de_DE']['DBP_Table_index.ss']['BROWSE'] = 'Eintr&auml;ge';
$lang['de_DE']['DBP_Table_index.ss']['HELP1'] = 'Doppelklicke auf einen Eintrag um ihn zu bearbeiten.';
$lang['de_DE']['DBP_Table_index.ss']['HELP2'] = 'Klicke auf einen Eintrag um ihn zum L&ouml;schen auszuw&auml;hlen.';
$lang['de_DE']['DBP_Table_index.ss']['HELP3'] = 'Klicke auf die Spalten&uuml;berschrift um nach dieser Spalte zu sortieren';
$lang['de_DE']['DBP_Table_index.ss']['HELP4'] = 'Klicke erneut um die Sortierreihenfolge umzukehren';
$lang['de_DE']['DBP_Table_index.ss']['HELP5'] = 'Ziehe den Spaltenrand in der Kopfzeile um die Splatenbreite zu &auml;ndern.';
$lang['de_DE']['DBP_Table_index.ss']['NO_RECORDS'] = 'keine Eintr&auml;ge';

