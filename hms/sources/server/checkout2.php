<?php
//提交团体退房
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $groupid=array_key_exists('groupid', $_GET) ? trim($_GET["groupid"]) : "";
    $keepdays=array_key_exists('keepdays', $_GET) ? trim($_GET["keepdays"]) : "";
    $price=array_key_exists('price', $_GET) ? trim($_GET["price"]) : "";
    $payment=array_key_exists('payment', $_GET) ? trim($_GET["payment"]) : "";

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败"}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";

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

//查找入住订单
    $sql = "SELECT * FROM checkin_info WHERE checkin_info.groupid='$groupid'";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误1: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==0) {
        mysqli_free_result($query);
        echo '{"state":"查找入住订单失败，无法退房"}';
        mysqli_close($db);
        exit();
    }
    $guestid=array();
    $roomid=array();
    while ($row=mysqli_fetch_assoc($query)) {
        $guestid[]=$row['guestid'];
        $roomid[]=$row['roomid'];
    }
    mysqli_free_result($query);


//修改房间状态
    foreach ($guestid as $key => $value) {
        $sql = "UPDATE guests SET roomid=NULL WHERE guestid='$value';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"修改用户状态失败"}';
            mysqli_close($db);
            exit();
        }
    }
    foreach ($roomid as $key => $value) {
        $sql = "UPDATE rooms SET status='idle' WHERE roomid='$value';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"修改房间状态失败"}';
            mysqli_close($db);
            exit();
        }
    }
    
    
//增加退房记录
    foreach ($guestid as $key => $value) {
        $sql = "INSERT INTO records(orderid,ordertype,guesttype,guestid,groupid,roomid,`time`,bill,payment,predictdays) VALUES ".
            "(concat('O',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)),'CheckOut','Group', '$value','$groupid','$roomid[$key]',now(),'$price','$payment',$keepdays);";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)!=1) {
            echo '{"state":"退房失败"}';
            mysqli_close($db);
            exit();
        }
    }
    echo '{"state":"OK"}';
    mysqli_close($db);
?>

