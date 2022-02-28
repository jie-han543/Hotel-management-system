<?php
//管理及修改其他用户信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $current_username=array_key_exists('current_username', $_GET) ? trim($_GET["current_username"]) : "";
    $op=array_key_exists('op', $_GET) ? trim($_GET["op"]) : "";
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $name=array_key_exists('name', $_GET) ? trim($_GET["name"]) : "";
    $sex=array_key_exists('sex', $_GET) ? trim($_GET["sex"]) : "";
    $job=array_key_exists('job', $_GET) ? trim($_GET["job"]) : "";
    $telephone=array_key_exists('telephone', $_GET) ? trim($_GET["telephone"]) : "";
    $permissions=array_key_exists('permissions', $_GET) ? $_GET["permissions"] : "";
    $oldusername=array_key_exists('oldusername', $_GET) ? trim($_GET["oldusername"]) : "";
    $permission_value=0;
    foreach ($permissions as $value) {
        if(trim($value)=='room'){ $permission_value += 1; }
        if(trim($value)=='user'){ $permission_value += 2; }
        if(trim($value)=='record'){ $permission_value += 4; }
    }
//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败"}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$current_username' AND (permissions AND 2) != 0;";
    //echo $permissions;
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误"}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query) != 1){
        echo '{"state":"用户名无效或没有权限"}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }
//修改/增加/删除用户详细信息
    if( $username != $current_username ){
        if( $op == 'update' ){
            $sql = "UPDATE admins SET username='$username', name='$name', sex='$sex', job='$job', telephone='$telephone', permissions=$permission_value WHERE username='$oldusername';";
        }else if( $op == 'add' ){
            $sql = "INSERT INTO admins (username, password, name, sex, job, telephone, permissions) 
                VALUES ('$username', md5('123456myhms'), '$name', '$sex', '$job', '$telephone', $permission_value);";
        }else{
            $sql = "DELETE FROM admins WHERE username='$username';";
        }
    }else{
        echo '{"state":"警告: 无法在此更改或删除当前用户！请在“我的”中修改个人信息。"}';
        mysqli_close($db);
        exit();
    }

    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        if($errno == 1062){
            $errinfo = '用户名已存在';
        }
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==1) {
        switch ($op) {
            case 'add':
                copy('../pics/default.png', "../pics/$username.png");
                break;
            case 'update':
                if($oldusername!=$username){
                    rename("../pics/$oldusername.png", "../pics/$username.png");
                }
                break;
            default:
                unlink("../pics/$username.png");
                break;
        }
        echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"该用户名已不存在，请刷新重新获取数据"}';
    }
    
    mysqli_close($db);
?>
