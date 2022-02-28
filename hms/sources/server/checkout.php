<?php
//提交散客退房
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $CheckInid=array_key_exists('CheckInid', $_GET) ? trim($_GET["CheckInid"]) : "";
    $price=array_key_exists('price', $_GET) ? trim($_GET["price"]) : "";
    $payment=array_key_exists('payment', $_GET) ? trim($_GET["payment"]) : "";
    $keepdays=array_key_exists('keepdays', $_GET) ? trim($_GET["keepdays"]) : "";

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
    $sql = "SELECT * FROM checkin_info WHERE checkin_info.orderid='$CheckInid'";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误1: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)!=1) {
        mysqli_free_result($query);
        echo '{"state":"查找入住订单失败，无法退房"}';
        mysqli_close($db);
        exit();
    }
    $row=mysqli_fetch_assoc($query);
    $id=$row['guestid'];
    $roomid=$row['roomid'];
    mysqli_free_result($query);


//修改房间状态
    $sql = "UPDATE guests SET roomid=NULL WHERE guestid='$id';";
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
    $sql = "UPDATE rooms SET status='idle' WHERE roomid='$roomid';";
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
    
//增加退房记录
    $sql = "INSERT INTO records(orderid,ordertype,guesttype,guestid,roomid,`time`,bill,payment,predictdays) VALUES ".
        "(concat('O',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)),'CheckOut','Solo', '$id','$roomid',now(),'$price','$payment',$keepdays);";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==1) {
        echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"退房失败"}';
    }

    mysqli_close($db);
?>
