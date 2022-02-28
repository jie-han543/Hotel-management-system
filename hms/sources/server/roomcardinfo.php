<?php
//获取房间卡片的弹出信息
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证与信息接收
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
    $roomid = array_key_exists('roomid', $_GET) ? trim($_GET['roomid']) : "";
//定义数据json格式
    $details = '{'.
    //弹窗卡片信息
        '"roomid":"%s",'."\n".
        '"floor":"%s",'."\n".
        '"typename":"%s",'."\n".
        '"status":"%s",'."\n".
        //
        '"guestid":"%s",'."\n".
        '"guestname":"%s",'."\n".//在住客户
        '"leave_time":"%s",'."\n".
        '"ordernameid":"%s",'."\n".
        '"ordername":"%s",'."\n".//预约客户
        '"order_time":"%s",'."\n".
    //更新弹窗信息
        '"price":"%.2f",'."\n".
        '"bedsnum":"%s",'."\n".
        '"description":"%s",'."\n".
        //
        '"tel":"%s",'."\n".
        '"sex":"%s",'."\n".
        '"age":"%s",'."\n".        
        '"checkin_time":"%s",'."\n".//入住时间（已经入住/预约预计入住）
        '"checkout_time":"%s"'."\n".//预计离开时间（已经入住/预约预计入住）
    '}'."\n";
    $data = '{'.
        '"state":"%s",'."\n".
        '"details":%s'.
    '}';
//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","infos":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误1","infos":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","infos":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询房间信息
    $sql = "SELECT * FROM room_info WHERE roomid='$roomid';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误2","details":[]}';
        mysqli_close($db);
        exit();
    }
    //处理查询异常……

    $status=$query;
    $row=mysqli_fetch_assoc($query);
    $details=sprintf($details,
        $roomid,
        $row['floor'],
        $row['typename'],
        $row['status'],
        "%s","%s","%s","%s","%s","%s",
        $row['price'],
        $row['bedsnum'],
        $row['description'],
        "%s","%s","%s","%s","%s");

    if($row['status']=='idle'){
        //空闲
        $sql="SELECT order_info.roomid, order_info.guestid, order_info.name, order_info.time, order_info.end_time, guests.telephone, guests.sex, FLOOR(DATEDIFF(now(),guests.birthday)/365.25) as age ".
        "FROM order_info, guests ".
        "WHERE order_info.roomid='$roomid' AND to_days(`time`) >= to_days(NOW()) AND order_info.guestid=guests.guestid ".
        "ORDER BY `time` LIMIT 1;";
        if (!($query = mysqli_query($db,$sql))){
            echo '{"state":"数据库错误3","details":[]}';
            echo mysqli_error($db);
            mysqli_close($db);
            exit();
        }
        //处理查询异常……
        if (mysqli_num_rows($query)==1){
            $row=mysqli_fetch_assoc($query);
            $details=sprintf($details,
            '','','',
            $row['guestid'],
            $row['name'],
            substr($row['time'],0,10),
            $row['telephone'],
            $row['sex'],
            $row['age'],
            '',
            substr($row['end_time'],0,10));
        }else{
            $details=sprintf($details,'','','','','','','','','','','');
        }
    }else if($row['status']=='used'){
        //使用中
        $sql = "SELECT *,FLOOR(DATEDIFF(now(),birthday)/365.25) as age FROM guest_info WHERE roomid='$roomid';";
        if (!($query = mysqli_query($db,$sql))){
            echo '{"state":"数据库错误4","details":[]}';
            mysqli_close($db);
            exit();
        }
        //处理查询异常……
        $row=mysqli_fetch_assoc($query);
        $details=sprintf($details,
            $row['guestid'],
            $row['name'],
            $row['end_time'],
            "%s","%s","%s",
            $row['telephone'],
            $row['sex'],
            $row['age'],
            substr($row['checkin_time'],0,10),
            $row['end_time']);

        $sql = "SELECT roomid, guestid as ordernameid, name as ordername, `time` ".
        "FROM order_info ".
        "WHERE roomid='$roomid' AND to_days(`time`) >= to_days(NOW()) ".
        "ORDER BY `time` LIMIT 1;";
        if (!($query = mysqli_query($db,$sql))){
            echo '{"state":"数据库错误5","details":[]}';
            mysqli_close($db);
            exit();
        }
        //处理查询异常……
        if($row=mysqli_fetch_assoc($query)){
            $details=sprintf($details,
                $row['ordernameid'],
                $row['ordername'],
                substr($row['time'],0,10));
        }else{
            $details=sprintf($details,'','','');
        }
    }else{
        //不可用
        $details=sprintf($details,'','','','','','','','','','','');
    }     
    
    mysqli_free_result($query);
//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($data,"OK",$details);
?>

