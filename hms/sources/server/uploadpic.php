<?php

//上传用户头像
    foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    $username = $_GET["username"];
    $tmp = $_FILES['file']['tmp_name'];
    $filepath = '../pics/';
    
    if(move_uploaded_file($tmp,$filepath.$username.".png")){
        //debug($tmp);
        echo "成功！";
    }else{
        //debug($tmp);
        echo "失败！";
    }
    /*
    function debug($word='',$file=''){
        $file=($file=='')?'./log.txt':trim($file);
        if($fp=fopen($file, "a")){
            flock($fp,LOCK_EX);
            fwrite($fp, date("Y-m-d H:i:s: ").$word."\r\n");
            flock($fp,LOCK_UN);
            fclose($fp);
        }
    }
    */
?>