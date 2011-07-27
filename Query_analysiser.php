<?php

/*getting program code from directory*/
$content = @file_get_contents('./Code/extensions/subject/SubjectModel.php');

/*dividing program code by ";"*/
$contents = explode(';', $content);

echo "<br>";
echo "***** Keyword [;] *****";
echo "<br>";

//mb_convert_variables("ASCII", "UTF-8", $contents);

for($i = 0; $i < count($contents); $i++){
    $contents[$i] = trim($contents[$i]);
    
    echo "<br>";
    echo $contents[$i];
    echo "<br>";
}

echo "<br>";
echo "***** Keyword [updata+set] *****";
echo "<br>";

/*1:picking out "update" from program code. 2:picking our "set" from program code*/
for($i = 0; $i < count($contents); $i++){ 
    $keyword = stristr($contents[$i], 'update');
    if($keyword){
        $keyword_sub = stristr($keyword, 'set');
        if($keyword_sub){
            echo "<br>";
            echo $contents[$i];
            echo "<br>";
            
            $update_set[] = $contents[$i];
        }
    }
}

echo "<br>";
echo "***** Used SQL *****";
echo "<br>";

echo "<pre>";
echo var_dump($update_set);
echo "</pre>";

for($i = 0; $i < count($update_set); $i++){ 
    $update_set[$i] = preg_replace('/\s+/', ' ', $update_set[$i]);//???
    $update_sets = explode(' ', $update_set[$i]);
    
    echo "<pre>";
    echo var_dump($update_sets);
    echo "</pre>";
}

for($i = 0; $i < count($update_sets); $i++){
    $update_sets[$i] = str_replace("'","",$update_sets[$i]);
    $update_sets[$i] = str_replace('"','',$update_sets[$i]);
}

echo "<pre>";
echo var_dump($update_sets);
echo "</pre>";

echo "<br>";
echo "***** Used Tables *****";
echo "<br>";

for($i = 0; $i < count($update_sets); $i++){
    if(!strcasecmp($update_sets[$i], "UPDATE")){
    //if($update_sets[$i] === "update" || $update_sets[$i] === "UPDATE"){
        for($j = $i; $j < count($update_sets); $j++){
            //if($update_sets[$j] === "set" || $update_sets[$j] === "SET"){
            if(!strcasecmp($update_sets[$j], "SET")){
                if($i+2 === $j){
                    echo "<br>";
                    echo $update_sets[$i+1];
                    echo "<br>";
                }
            }
        }
    }
}

echo "<br>";
echo "***** Keyword [db->updata] *****";
echo "<br>";

/*picking out "db->update" from program code*/
for($i = 0; $i < count($contents); $i++){      
    $keyword = stristr($contents[$i], 'db->update');
    if($keyword){
        echo "<br>";
        echo $contents[$i];
        echo "<br>";
    }
}

?>
