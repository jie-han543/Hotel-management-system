<?php
//登录并获取用户相关信息用于显示
    $dbcfg = parse_ini_file("./config.ini",true);
    foreach ($_GET as $index => $value) 
        $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    $username = array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $password = array_key_exists('password', $_GET) ? trim($_GET["password"]) : "";
    $userdata='{'.
        '"state":"%s",'.
        '"username":"%s",'.
        '"name":"%s",'.
        '"sex":"%s",'.
        '"job":"%s",'.
        '"telephone":"%s",'.
        '"permissions":%d'.
    '}';
    if ($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database'])) {
        @mysqli_query($db,"set character_set_client = 'utf8'");
        @mysqli_query($db,"set character_set_connection = 'utf8'");
        @mysqli_query($db,"set character_set_results = 'utf8'");
        $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password';";
        if ($query = mysqli_query($db,$sql)) {
            if (mysqli_num_rows($query)==1) {
                $row=mysqli_fetch_assoc($query);//获取执行结果
                header("Content-type: text/plain");//声明纯文本文件类型
                echo sprintf($userdata,"OK",$username,$row["name"],$row["sex"],$row["job"],$row["telephone"],$row["permissions"]);
            }else{
                echo sprintf($userdata,"ERROR","","","","","","");
            }
            mysqli_free_result($query);
        }else{
            echo sprintf($userdata,"ERROR1","","","","","","");
            echo mysqli_error($db);
        }
        mysqli_close($db);
    }else{
        echo sprintf($userdata,"ERROR2","","","","","","");
        echo mysqli_error($db);
    }
?>