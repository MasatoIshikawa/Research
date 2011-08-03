<?php

/*
 * about this file.
 */

/*
 * about this class.
 */

class Query_analysiser
{
    private function read_file($read_file_name)
    {
        echo '<br>';
        echo "***** $read_file_name *****";
        echo '<br>';
        
        /*getting program code from directory*/
        $program_content = file_get_contents($read_file_name);
        
        return $program_content;
    }

    public function semicolon_separator($read_file_name)
    {
        //$read_file_name = 'C:\xampp\Code\extensions\subject\SubjectModel.php';
        //$read_file_name = 'C:\xampp\Code\application\baseModels\ConfigBaseModel.php';

        $program_content = $this->read_file($read_file_name);
        
        /*dividing program code by ';'*/
        $program_contents = explode(';', $program_content);
        
        /**/
        for($i = 0; $i < count($program_contents); $i++) {
            $program_contents[$i] = trim($program_contents[$i]);
        }

        return $program_contents; 
    }

    public function query_cleaner($query)
    {
        /*all space change english space*/
        $query = preg_replace('/\s+/', ' ', $query);

        /*to erase space*/
        $queries = explode(' ', $query);

        /*to erase '*/
        $queries = str_replace("'","",$queries);
        
        /*to erase "*/
        $queries = str_replace('"','',$queries);
        
        /*to erase (*/
        $queries = str_replace('(','',$queries);
        
        /*to erase ,*/
        $queries = str_replace(',','',$queries);
        
        return $queries;
    }
    
    public function update_query_extractor($program_contents)
    {
        /*1:picking out 'update' from program code. 2:picking our 'set' from program code*/
        for($i = 0; $i < count($program_contents); $i++) { 
            $keyword = stristr($program_contents[$i], 'update');
            if ($keyword) {
                $keyword_sub = stristr($keyword, 'set');
                if ($keyword_sub) {
                    $update_set[] = $program_contents[$i];
                }
            }
        }
        
        return $update_set;
    }
    
    public function update_query_table_extractor($queries)
    {
        for($i = 0; $i < count($queries); $i++) {
            if (!strcasecmp($queries[$i], 'UPDATE')) {
                for($j = $i; $j < count($queries); $j++) {
                    if (!strcasecmp($queries[$j], 'SET')) {
                        if ($i+2 === $j) {
                            return $queries[$i+1];
                        }
                    }
                }
            }
        }
    }
    
    public function update_zendframework_function_extractor($program_contents)
    {
        /*picking out 'db->update' from program code*/
        for($i = 0; $i < count($program_contents); $i++) {     
            $keyword = stristr($program_contents[$i], 'db->update');
            
            if ($keyword) {
                $db_update[] = $program_contents[$i];
            }
        }
        
        return $db_update;
    }
    
    public function update_zendframework_function_table_extractor($db_update)
    {
        /*
         * . : searching both side.
         * [\(] and [\)] : it means [(] and [)].
         * g : multi lines.
         * + : multi characters.
         * ? : short matching.
         */        
        preg_match('/\(.+?\,/s', $db_update, $case_arc_comma);
        
        $db_update_table = $this->query_cleaner($case_arc_comma[0]);
        
        return $db_update_table[0];
    }
    
    public function table_names_searching($table_names, $read_file_name)
    {
        /*getting program code from directory*/
        //$program_content = file_get_contents('C:\xampp\Code\extensions\subject\SubjectModel.php');
        
        //$read_file_name = 'C:\xampp\Code\extensions\subject\SubjectModel.php';

        $program_content = $this->read_file($read_file_name);
                
        for($i = 0; $i < count($table_names); $i++) {            
            if (strstr($program_content, '$'.$table_names[$i])) {
                /*this case might be just variable*/      
                $used_table_numbers["$table_names[$i]"] = 0;
            } elseif (strstr($program_content, $table_names[$i])) {
                /*this case is the ture tables with a high rate*/
                $used_table_numbers["$table_names[$i]"] = 1;
            } else {
                /*nothing anything*/
                $used_table_numbers["$table_names[$i]"] = 0;
            }
        }
        
        return $used_table_numbers;
    }
    
    public function query_prepare_zendframework_function_extractor()
    {
                
    }
}

?>
