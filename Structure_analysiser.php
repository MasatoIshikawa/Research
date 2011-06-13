<?php

/*
 * here is information to connect to database.
 * those parts will change to $_GET[""].
 */

$tablename = "Research";
$username = "";
$password = "";

require_once("Connection.php");

$db = new Connection();

$mysqlquery1 = "about read all tables";

$DB_tableanems[] = execution_query($mysqlquery1);

while(until end of last table with $X)
{
	$mysqlquery2 = "about read all clams with $DB_tablenames[$X]";
	
	$"$DB_tablenames"_clams[] = execution_query($mysqlquery2);
	
	$mysqlqury3 = "about checking primary key in this tables";
	
	$"$DB_tablenames"_primarykey = execution_query($mysqlquery3);
}

//question! : primarykey is absolutely one in table?
while(until end of last table with $X)
{
	$"$X"_primarykey;
	
	while(until end of last lable with $Y without $X)
	{
		while(until end of last clam with $Y)
		{
			if($"$Y"_clams[] === $"$X"_primarykey)
		{
				
		} 
}

?>
