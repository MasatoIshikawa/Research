<?php

/*indefinite number infomation*/
/******************************/

/*here is information to connect to database. those parts will change to $_GET[""]*/
$hostname = "localhost";
$database = "research";
$username = "root";
$password = "";

/*all queries*/
$show_tables = "";

/******************************/

require_once("Connection.php");

$db = new Connection();

$db->hostname = $hostname;
$db->database = $database;
$db->username = $username;
$db->password = $password;

$db->construct();

/*this sql query is for reading all tables*/
$show_tables = "show tables";

$result = $db->query($show_tables);

/*query result is {["Tables_in_research"] = "tablename"}*/
while( $row = mysql_fetch_array($result,MYSQL_NUM) ){
    $db_all_tablenames[] = $row[0];    
}

/*this is for count which how much dattabase has tables*/
$db_all_tablenames_count = count($db_all_tablenames);

for( $x = 0; $x <= $db_all_tablenames_count-1; $x++ ){
    $show_clums = "describe $db_all_tablenames[$x]";
    
    $result = $db->query($show_clums);
    
    while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ){
        ${'db_'.$db_all_tablenames[$x].'_all_clums'}[] = $row["Field"];
        
        ${'db_'.$db_all_tablenames[$x].'_all_clums_count'} = count(${'db_'.$db_all_tablenames[$x].'_all_clums'});
        
        if( !empty($row["Key"]) ){
            ${'db_'.$db_all_tablenames[$x].'_key'} = $row["Field"];
        }
    }
}

for( $x = 0; $x <= $db_all_tablenames_count-1; $x++ ){
    $Key = ${'db_'.$db_all_tablenames[$x].'_key'};
    
    for( $y = 0; $y <= $db_all_tablenames_count-1; $y++ ){
        if( $x !== $y ){
            for( $z = 0; $z <= ${'db_'.$db_all_tablenames[$y].'_all_clums_count'}-1; $z++ ){
                if( ${'db_'.$db_all_tablenames[$y].'_all_clums'}[$z] === $Key ){
                    echo ${'db_'.$db_all_tablenames[$y].'_all_clums'}[$z];
                }
            }
        }
    }
}

/*
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
 */

$db->destruct();

?>
