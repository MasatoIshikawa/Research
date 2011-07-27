<?php

/*getting program code from directory*/
$content = @file_get_contents('./Code/extensions/subject/SubjectModel.php');

echo "<br>";
echo "***** character code *****";
echo "<br>";

echo "<br>";
echo mb_detect_encoding($content);
echo "<br>";

/*dividing program code by ";"*/
$contents = explode(';', $content);
//$contents = strtok($content, ";");
//$contents = split('[;]', $content);

echo "<br>";
echo "***** character code *****";
echo "<br>";

echo "<br>";
echo mb_detect_encoding($contents[0]);
echo "<br>";

echo "<br>";
echo "***** Keyword [;] *****";
echo "<br>";

echo "<br>";
echo "stop now";
echo "<br>";

//mb_convert_variables("ASCII", "UTF-8", $contents);

for($i = 0; $i < count($contents); $i++){
    //$contents[$i] = mb_convert_encoding($contents[$i], 'UTF-8', 'auto');
    //trim($contents[$i]);
    
    //echo "<br>";
    //echo $contents[$i];
    //echo "<br>";
}

echo "<br>";
echo "***** character code *****";
echo "<br>";

echo "<br>";
echo mb_detect_encoding($contents[0]);
echo "<br>";

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
echo "***** Used Tables *****";
echo "<br>";

echo "<pre>";
echo var_dump($update_set);
echo "</pre>";

for($i = 0; $i < count($update_set); $i++){
    //$update_set[$i] = mb_convert_encoding($update_set[$i], 'EUC-JP', 'auto');
    //$update_set[$i] = mb_ereg_replace('　', ' ', $update_set[$i]);
    //echo mb_detect_encoding($update_set[$i]);
    //$update_set[$i] = mb_convert_kana($update_set[$i], 's', 'utf-8');
    //$update_sets = explode(' ', $update_set[$i]);
    //$update_sets = strtok($update_set[$i], " ");
    
    $update_set[$i] = preg_replace('/\s+/', ' ', $update_set[$i]);//???
    $update_sets = explode(' ', $update_set[$i]);
    
    echo "<pre>";
    echo var_dump($update_sets);
    echo "</pre>";
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
