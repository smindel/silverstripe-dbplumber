<?php
/*

ods-php a library to read and write ods files from php.

This library has been forked from eyeOS project and licended under the LGPL3
terms available at: http://www.gnu.org/licenses/lgpl-3.0.txt (relicenced
with permission of the copyright holders)

Copyright: Juan Lao Tebar (juanlao@eyeos.org) and Jose Carlos Norte (jose@eyeos.org) - 2008 

https://sourceforge.net/projects/ods-php/

*/


include("ods.php"); //include the class and wrappers
$object = newOds(); //create a new ods file
$object->addCell(0,0,0,1,'float'); //add a cell to sheet 0, row 0, cell 0, with value 1 and type float
$object->addCell(0,0,1,2,'float'); //add a cell to sheet 0, row 0, cell 1, with value 1 and type float
$object->addCell(0,1,0,1,'float'); //add a cell to sheet 0, row 1, cell 0, with value 1 and type float
$object->addCell(0,1,1,2,'float'); //add a cell to sheet 0, row 1, cell 1, with value 1 and type float
saveOds($object,'/tmp/new.ods'); //save the object to a ods file

$object=parseOds('/tmp/new.ods'); //load the ods file
$object->editCell(0,0,0,25); //change the value for the cell in sheet 0, row 0, cell 0, to 25
saveOds($object,'/tmp/new2.ods'); //save with other name


?>