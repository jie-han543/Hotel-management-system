<?php
//获取page1-1的展示信息
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";

//定义数据json格式
/*
    传输状态：OK/ERROR;
    信息数组[]：
        当前顾客信息：guestname、tel、sex、age、checkin_time(过去入住日期)、predictdays(预计住的天数)、checkout_time(预计离店日期)、deposit押金与否;
        房间信息：roomid、floor、status、price、description、*typename、*bedsnum;
        下一预约信息：nextorder、keepdays、ordername预约客户;
*/
    $datas = '{'.
        '"state":"%s",'."\n".
        '"infos":[%s],'."\n".
        '"type":[%s]'."\n".
    '}';   
    $info = '{'.
        '"roomid":"%s",'."\n".
        '"floor":"%s",'."\n".
        '"typename":"%s",'."\n".
        '"status":"%s",'."\n".
        '"guestid":"%s",'."\n".
        '"guestname":"%s",'."\n".
        '"leave_time":"%s",'."\n".
        '"ordernameid":"%s",'."\n".
        '"ordername":"%s",'."\n".
        '"order_time":"%s"'."\n".
    '},'."\n";
//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","infos":[],"type":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","infos":[],"type":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","infos":[],"type":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询房间信息
    $sql = 
    'CREATE TEMPORARY TABLE orders '.
    'SELECT roomid, guestid, substring_index(GROUP_CONCAT( `name` order BY `time` separator " "), " ", 1) as name, MIN(time) as nextorder '.
    '   FROM order_info '.
    '   WHERE to_days(`time`) >= to_days(NOW()) '.
    '   GROUP BY roomid; '
    ;
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","infos":[],"type":[]}';
        echo mysqli_error($db);
        mysqli_close($db);
        exit();
    }

    $sql = 
    'SELECT room_info.roomid AS roomID, room_info.floor, room_info.typename, room_info.status, '.
    '   guest_info.guestid, guest_info.name AS guestname, guest_info.end_time AS leave_time, '.
    '   orders.guestid AS ordernameid, orders.name AS ordername, Date_format(nextorder,"%Y-%m-%d") AS order_time '.
    'FROM room_info '.
    '    LEFT JOIN guest_info on guest_info.roomid=room_info.roomid '.
    '    LEFT JOIN orders on room_info.roomid=orders.roomid'.
    '    ORDER BY roomID;';
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","infos":[],"type":[]}';
        echo mysqli_error($db);
        mysqli_close($db);
        exit();
    }
    $infos='';
    while ($row=mysqli_fetch_assoc($query)) {
        $infos.=sprintf($info,
            $row['roomID'],
            $row['floor'],
            $row['typename'],
            $row['status'],
            $row['guestid'],
            $row['guestname'],
            $row['leave_time'],
            $row['ordernameid'],
            $row['ordername'],
            $row['order_time']
        );
    } 

//查询房间类型
    $sql="SELECT typename FROM roomtype;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","infos":[],"type":[]}';
        echo mysqli_error($db);
        mysqli_close($db);
        exit();
    }
    $type='';
    while ($row=mysqli_fetch_assoc($query)) {
        $type.='"'.$row['typename'].'",';
    } 
    mysqli_free_result($query);
//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($datas,"OK",substr($infos,0,-2),substr($type,0,-1));
/**/
?>