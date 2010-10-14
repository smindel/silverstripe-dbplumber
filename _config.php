<?php

/*

Use the following static properties and methods to control DBPlumber

// limit DBPlumber to certain environments
DatabaseBrowser::$trusted_envs = array('test', 'dev');

// limit DBPlumber to trusted IPs
DatabaseBrowser::$trusted_ips = array('127.0.0.1');

// deactivate DBPlumber, useful in combination with _ss_environment.php
DatabaseBrowser::$activated = false;

// hide DBPlumber from the CMS menu. Useful if DBPlumber is accessible but
// you don't want it to appear in the CMS but only access it through http://your-domain.com/admin/dbplumber
DatabaseBrowser::hide_from_menu();

*/