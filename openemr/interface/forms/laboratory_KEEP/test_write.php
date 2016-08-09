<?php
$ignoreAuth=true; // signon not required!!
$here = dirname(dirname(dirname(__FILE__)));
require_once($here."/globals.php");

$params = array();
$params[] = 1;
$params[] = "one";
$params[] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam blandit sit amet libero et dignissim. Etiam nec congue neque. Sed at nisi nulla. Morbi vestibulum mollis felis quis laoreet. Pellentesque cursus massa sit amet felis aliquet, nec eleifend dui aliquet. Phasellus porttitor varius diam id tincidunt. Nam ultricies risus at neque auctor auctor. Proin ac velit quis nisl interdum rhoncus. Vestibulum fermentum sem at massa lacinia dignissim. Nulla dapibus bibendum purus ac pretium. Praesent iaculis risus quis lobortis condimentum. Duis eget mi urna. Suspendisse neque odio, finibus sed molestie in, dictum id risus. Sed diam odio, dictum eget lectus quis, commodo imperdiet magna. Mauris lobortis viverra nunc, vitae elementum justo bibendum quis. ";
sqlInsert("INSERT INTO test_it SET test_int = ?, test_varchar = ?, test_text = ?",$params);

echo "<pre>\n\n";
$result = sqlStatement("SELECT * FROM test_it");
while ($record = sqlFetchArray($result)) var_dump($record);
echo "\n\n</pre>\n\n";

die("Done...");
