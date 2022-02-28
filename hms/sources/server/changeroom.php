<?php
//更改房间的信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
//保存数据
    $op=array_key_exists('op', $_GET) ? trim($_GET["op"]) : "";
    $roomtypeid=array_key_exists('roomtypeid', $_GET) ? trim($_GET["roomtypeid"]) : "";
    $roomid=array_key_exists('roomid', $_GET) ? trim($_GET["roomid"]) : "";
    $floor=array_key_exists('floor', $_GET) ? trim($_GET["floor"]) : "";
    $state=array_key_exists('state', $_GET) ? trim($_GET["state"]) : "";
    $price=array_key_exists('price', $_GET) ? trim($_GET["price"]) : "";
    $description=array_key_exists('description', $_GET) ? trim($_GET["description"]) : "";
    $oldroomid=array_key_exists('oldroomid', $_GET) ? trim($_GET["oldroomid"]) : "";
//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败"}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username' AND (permissions AND 1) != 0;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误"}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限"}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }
//修改/增加/删除房间详细信息
    if( $op == 'update' ){
        $sql = "UPDATE rooms SET roomtypeid='$roomtypeid', roomid='$roomid', floor='$floor', status='$state', price='$price', description='$description' WHERE roomid='$oldroomid';";
    }else if( $op == 'add' ){
        $sql = "INSERT INTO rooms (roomid, floor, status, price, description, roomtypeid) 
            VALUES ('$roomid','$floor','$state','$price','$description','$roomtypeid');";
    }else{
        $sql = "DELETE FROM rooms WHERE roomid='$roomid';";
    }

    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = '';
        if($errno == 1062){
            $errinfo = '房间号已存在';
        }else if($errno == 1452){
            $errinfo = '该房间类型不存在，请刷新重新获取数据';
        }
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==1) {
            echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"操作失败，请刷新确认该房间是否已被删除或本次操作没有进行修改。"}';
    }
    mysqli_close($db);
?>
