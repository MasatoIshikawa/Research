<?php

require_once("Query_analysiser.php");

$Query_analysiser = new Query_analysiser();

echo "<br>";
echo "***** Keyword [;] *****";
echo "<br>";

$program_contents = $Query_analysiser->semicolon_separator();

$Query_analysiser->result_viewer($program_contents);

echo "<br>";
echo "***** Keyword [update+set] *****";
echo "<br>";

$update_set = $Query_analysiser->update_query_extractor($program_contents);

$Query_analysiser->result_viewer($update_set);

echo "<br>";
echo "***** Used SQL *****";
echo "<br>";

echo "<pre>";
echo var_dump($update_set);
echo "</pre>";

for($i = 0; $i < count($update_set); $i++){
    $queries = $Query_analysiser->query_cleaner($update_set[$i]);
    
    echo "<pre>";
    echo var_dump($queries);
    echo "</pre>";
    
    echo "<br>";
    echo "***** Used Tables *****";
    echo "<br>";
    
    $update_set_table = $Query_analysiser->update_query_table_extractor($queries);
    
    echo "<br>";
    echo $update_set_table;
    echo "<br>";
}

echo "<br>";
echo "***** Keyword [db->update] *****";
echo "<br>";

$db_update = $Query_analysiser->update_zendframework_function_extractor($program_contents);

$Query_analysiser->result_viewer($db_update);

echo "<br>";
echo "***** Used SQL *****";
echo "<br>";

for($i = 0; $i < count($db_update); $i++){
    $db_update_table = $Query_analysiser->update_zendframework_function_table_extractor($db_update[$i]);
   
    echo "<br>";
    echo $db_update_table;
    echo "<br>";
}

?>
