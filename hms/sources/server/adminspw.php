<?php
//修改用户密码
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    $olduser=trim($_GET["olduser"]);
    $password=trim($_GET["password"]);
    
    if ($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database'])) {
        @mysqli_query($db,"set character_set_client = 'utf8'");
        @mysqli_query($db,"set character_set_connection = 'utf8'");
        @mysqli_query($db,"set character_set_results = 'utf8'");
        $sql = "UPDATE admins SET password='$password' WHERE username='$olduser';";
        if ($query = mysqli_query($db,$sql)) {
            if (mysqli_affected_rows($db)==1) {
                echo '{"state":"OK2"}';
            }else if (mysqli_affected_rows($db)==0) {
                echo '{"state":"没有修改或用户不存在"}';
            }
        }else{
            echo '{"state":"修改密码错误"}';
        }
        mysqli_close($db);
    }else{
        echo '{"state":"连接错误"}';
    }

?>