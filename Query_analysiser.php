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
    
    function result_viewer($view_content){ 
        for($i = 0; $i < count($view_content); $i++){
            echo "<br>";
            echo $view_content[$i];
            echo "<br>";
        }
    }

    function query_cleaner($query){
        /*all space change english space*/
        $query = preg_replace('/\s+/', ' ', $query);

        /*to erase space*/
        $queries = explode(' ', $query);

        /*to erase '*/
        $queries = str_replace("'","",$queries);
        
        /*to erase "*/
        $queries = str_replace('"','',$queries);
        
        /*to erase (*/
        $queries = str_replace("(","",$queries);
        
        /*to erase ,*/
        $queries = str_replace(",","",$queries);
        
        return $queries;
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
    
    function update_zendframework_function_table_extractor($db_update){
        /*
         * . : searching both side.
         * [\(] and [\)] : it means [(] and [)].
         * g : multi lines.
         * + : multi characters.
         * ? : short matching.
         */        
        preg_match("/\(.+?\,/s", $db_update, $case_arc_comma);
        
        $db_update_table = $this->query_cleaner($case_arc_comma[0]);
        
        return $db_update_table[0];
    }
}

?>
