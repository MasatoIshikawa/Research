<?php

/*getting program code from directory*/
$content = @file_get_contents('./Code/extensions/subject/SubjectModel.php');

/*dividing program code by ";"*/
$contents = explode(';', $content);

echo "<br>";
echo "***** Keyword [;] *****";
echo "<br>";

for($i = 0; $i < count($contents); $i++){
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
