<?php
//获取当前入住者信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    
    $datas = '{'.
        '"state":"%s",'."\n".
        '"guestCheckIn":[%s]'."\n".
    '}';   
    $info = '{'.
        '"CheckInid":"%s",'."\n".
        '"name":"%s",'."\n".
        '"tel":"%s",'."\n".
        '"IDnumber":"%s",'."\n".
        '"roomid":"%s",'."\n".
        '"guestid":"%s",'."\n".
        '"time":"%s",'."\n".
        '"predictdays":"%d",'."\n".
        '"keepdays":"%d",'."\n".
        '"price":"%.2f"'."\n".
    '},'."\n";

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
//查询在住顾客
    $sql = 
    "SELECT checkin_info.*, guests.guestid, guests.IDnumber, guests.telephone, datediff(addtime(now(),'23:50:00'),checkin_info.time) as keepdays ".
    "   FROM checkin_info, guests ".
    "   WHERE checkin_info.guestid = guests.guestid AND checkin_info.guesttype = 'Solo' AND NOT (checkin_info.guestid IN( SELECT DISTINCT guestid FROM checkout_info WHERE ISNULL(groupid) )); "
    ;
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","guestCheckIn":[]}';
        echo mysqli_error($db);
        mysqli_close($db);
        exit();
    }

    $infos='';
    while ($row=mysqli_fetch_assoc($query)) {
        $infos.=sprintf($info,
            $row['orderid'],
            $row['name'],
            $row['telephone'],
            $row['IDnumber'],
            $row['roomid'],
            $row['guestid'],
            substr($row['time'],0,10),
            $row['predictdays'],
            $row['keepdays'],
            $row['bill']
        );
    } 
    mysqli_free_result($query);
//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($datas,"OK",substr($infos,0,-1));
/**/
?>