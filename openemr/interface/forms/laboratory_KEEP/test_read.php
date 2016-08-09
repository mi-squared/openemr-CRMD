<?php
$ignoreAuth=true; // signon not required!!
$here = dirname(dirname(dirname(__FILE__)));
require_once($here."/globals.php");

$result = sqlStatement("SELECT * FROM test_it");
echo "<pre>\n\n";
while ($record = sqlFetchArray($result)) var_dump($record);
echo "\n\n</pre>\n\n";

die("Done...");
