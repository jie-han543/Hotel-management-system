<?php
    foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    //保存客户的照片
    $username = $_GET["username"];
    $tmp = $_FILES['file']['tmp_name'];
    $filepath = '../pics/guests/';

    if(move_uploaded_file($tmp, $filepath.basename($tmp))){
        //debug($tmp);
        echo $filepath.basename($tmp);
    }else{
        //debug($tmp);
        echo "error";
    }

?>
