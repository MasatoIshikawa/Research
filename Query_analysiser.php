<?php

//$file = @file("./Code/extentions/subject/SubjectMode.php");
//$file = @file("index.php");

/*
$contents = @file_get_contents("index.php");

echo "***** Test code *****";
echo <<< Code
$contents
Code;
 * 
 */

$contents = @file_get_contects("index.php");

$fp = fopen("text.php", "r");
fwrite( $fp, $contents );
fclose( $fp );

?>
