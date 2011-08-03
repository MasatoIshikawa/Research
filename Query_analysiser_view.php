<?php

require_once('Query_analysiser.php');

$Query_analysiser = new Query_analysiser();

echo '<br>';
echo '********** Query Extractor **********';
echo '<br>';

$program_contents = $Query_analysiser->semicolon_separator('C:\xampp\Code\extensions\subject\SubjectModel.php');

echo '<br>';
echo '***** Keyword [;] *****';
echo '<br>';

echo '<pre>';
print_r($program_contents);
echo '</pre>';

echo '<br>';
echo '***** Keyword [update+set] *****';
echo '<br>';

$update_set = $Query_analysiser->update_query_extractor($program_contents);

echo '<pre>';
print_r($update_set);
echo '</pre>';

for($i = 0; $i < count($update_set); $i++) {
    $queries = $Query_analysiser->query_cleaner($update_set[$i]);
    
    /*
    echo '<pre>';
    print_r($queries);
    echo '</pre>';
     * 
     */
    
    echo '<br>';
    echo "***** Used Tables[$i] *****";
    echo '<br>';
    
    $update_set_table = $Query_analysiser->update_query_table_extractor($queries);
    
    echo '<br>';
    echo $update_set_table;
    echo '<br>';
}

echo '<br>';
echo '***** Keyword [db->update] *****';
echo '<br>';

$db_update = $Query_analysiser->zendframework_function_extractor($program_contents, 'db->update');

echo '<pre>';
print_r($db_update);
echo '</pre>';

for($i = 0; $i < count($db_update); $i++) {
    $db_update_table = $Query_analysiser->zendframework_function_update_extractor($db_update[$i]);
    
    echo '<br>';
    echo "***** Used Tables[$i] *****";
    echo '<br>';
    
    echo '<br>';
    echo $db_update_table;
    echo '<br>';
}

echo '<br>';
echo '********** Table Name Searching **********';
echo '<br>';

$table_names = array('tannin', 'years', 'classes', 'teachers', 'campuses', 'courses', 'subjects', 'semesters');

$used_table_numbers = $Query_analysiser->table_names_searching($table_names, 'C:\xampp\Code\extensions\subject\SubjectModel.php');

echo '<pre>';
print_r($used_table_numbers);
echo '</pre>';

echo '<br>';
echo '********** Variable Case[test code] **********';
echo '<br>';

/********************************************************/
$program_content = '$table = "test";
$params = array (
    "name" => "a",
    "detail" => "a",
);
$where= $db->quoteInto("id = ?" , 1);
$ret = $db->update($table, $params, $where);';

$program_contents = explode(';', $program_content);

for($i = 0; $i < count($program_contents); $i++) {
    $program_contents[$i] = trim($program_contents[$i]);
}
/********************************************************/

echo '<pre>';
print_r($program_contents);
echo '</pre>';

echo '<br>';
echo '***** Keyword [db->update] *****';
echo '<br>';

$db_prepare = $Query_analysiser->zendframework_function_extractor_2nd($program_contents, '$db->update');

echo '<pre>';
print_r($db_prepare);
echo '</pre>';

foreach( $db_prepare as $key => $value ) {
    $db_update_table = $Query_analysiser->zendframework_function_update_extractor($value);
    
    echo '<br>';
    echo "***** Used Tables[$key] *****";
    echo '<br>';
    
    echo '<br>';
    echo $db_update_table;
    echo '<br>';
    
    if (strstr($db_update_table, '$')) {
        for($i = 0; $i < $key; $i++) {
            $keyword = stristr($program_contents[$i], $db_update_table);
            
            if ($keyword) {
                echo '<br>';
                echo "***** Used Variable part *****";
                echo '<br>';
                
                echo '<br>';
                echo $program_contents[$i];
                echo '<br>';
                
                echo '<br>';
                echo "***** Used Variable *****";
                echo '<br>';
                
                $queries = $Query_analysiser->query_cleaner($program_contents[$i]);
                
                for($j = 0; $j < count($queries); $j++) {
                    if (!strcasecmp($queries[$j], $db_update_table)) {
                        echo '<br>';
                        echo $queries[$j+2];
                        echo '<br>';
                    }
                }
            }
        }
    }
}

?>
