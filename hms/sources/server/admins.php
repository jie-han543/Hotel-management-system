<?php
//修改“我的”信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    $olduser=trim($_GET["olduser"]);
    $username=trim($_GET["username"]);
    $name=trim($_GET["name"]);
    $sex=trim($_GET["sex"]);
    $job=trim($_GET["job"]);
    $telephone=trim($_GET["telephone"]);
    if ($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database'])) {
        @mysqli_query($db,"set character_set_client = 'utf8'");
        @mysqli_query($db,"set character_set_connection = 'utf8'");
        @mysqli_query($db,"set character_set_results = 'utf8'");
        $sql = "UPDATE admins SET username='$username',name='$name',sex='$sex',job='$job',telephone='$telephone' WHERE username='$olduser';";
        if ($query = mysqli_query($db,$sql)) {
            if (mysqli_affected_rows($db)==1) {
                if(file_exists("../pics/$olduser.png")){
                    rename("../pics/$olduser.png","../pics/$username.png");//重命名用户头像文件
                }
                echo '{"state":"OK1"}';
            }elseif (mysqli_affected_rows($db)==0) {
                echo '{"state":"没有修改或用户不存在"}';
            }
        }else{
            echo '{"state":"修改用户信息错误"}';
        }
        mysqli_close($db);
    }else{
        echo '{"state":"连接错误"}';
    }

?>