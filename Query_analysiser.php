<?php

class Query_analysiser{
    
    function semicolon_separator(){
        /*getting program code from directory*/
        $program_content = @file_get_contents('C:\xampp\Code\extensions\subject\SubjectModel.php');

        /*dividing program code by ";"*/
        $program_contents = explode(';', $program_content);
        
        /**/
        for($i = 0; $i < count($program_contents); $i++){
            $program_contents[$i] = trim($program_contents[$i]);
        }

        return $program_contents;
    }
    
    function update_query_extractor($program_contents){
        /*1:picking out "update" from program code. 2:picking our "set" from program code*/
        for($i = 0; $i < count($program_contents); $i++){ 
            $keyword = stristr($program_contents[$i], 'update');
            if($keyword){
                $keyword_sub = stristr($keyword, 'set');
                if($keyword_sub){
                    $update_set[] = $program_contents[$i];
                }
            }
        }
        
        return $update_set;
    }

    function result_viewer($view_content){
        for($i = 0; $i < count($view_content); $i++){
            echo "<br>";
            echo $view_content[$i];
            echo "<br>";
        }
    }

    function query_cleaner($query){
        $query = preg_replace('/\s+/', ' ', $query);//???

        $queries = explode(' ', $query);

        $queries = str_replace("'","",$queries);
        $queries = str_replace('"','',$queries);
        
        return $queries;
    }
    
    function update_query_table_extractor($queries){
        for($i = 0; $i < count($queries); $i++){
            if(!strcasecmp($queries[$i], "UPDATE")){
                for($j = $i; $j < count($queries); $j++){
                    if(!strcasecmp($queries[$j], "SET")){
                        if($i+2 === $j){
                            return $queries[$i+1];
                        }
                    }
                }
            }
        }
    }

    function update_zendframework_function_extractor($program_contents){
        /*picking out "db->update" from program code*/
        for($i = 0; $i < count($program_contents); $i++){      
            $keyword = stristr($program_contents[$i], 'db->update');
            
            if($keyword){
                $db_update[] = $program_contents[$i];
            }
        }
        
        return $db_update;
    }
}

?>
