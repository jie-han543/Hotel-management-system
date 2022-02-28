<?php 
//根据搜素内容返回相关入住信息
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
    $tel = array_key_exists('tel', $_GET) ? trim($_GET['tel']) : "";
    $contectName = array_key_exists('contectName', $_GET) ? trim($_GET['contectName']) : "";
    $groupName = array_key_exists('groupName', $_GET) ? trim($_GET['groupName']) : "";

//定义数据json格式
    $datas = '{'.
        '"state":"%s",'."\n".
        '"CheckInData":[%s]'."\n".
    '}';
    $order = '{'.
        '"groupid":"%s",'."\n".
        '"groupName":"%s",'."\n".
        '"tel":"%s",'."\n".
        '"name":"%s",'."\n".
        '"membersnum":"%d",'."\n".
        '"time":"%s",'."\n".
        '"predictdays":"%d",'."\n".
        '"keepdays":"%s"'."\n".
    '},';

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","CheckInData":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");

    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","CheckInData":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","CheckInData":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询预约信息
    if($groupName!=''){
        $sql = "SELECT DISTINCT checkin_info.groupid, checkin_info.`time`, checkin_info.predictdays, datediff(addtime(now(),'23:50:00'),checkin_info.time) as keepdays, groups.groupname,groups.membersnum,groups.contactname,groups.contacttel ".
        "FROM checkin_info,groups WHERE checkin_info.groupid=groups.groupid AND checkin_info.guesttype='Group' AND groups.groupname LIKE '%$groupName%' AND NOT (checkin_info.groupid IN( SELECT DISTINCT groupid FROM checkout_info WHERE not ISNULL(groupid) ));";
    }else if($contectName!=''){
        $sql = "SELECT DISTINCT checkin_info.groupid, checkin_info.`time`, checkin_info.predictdays, datediff(addtime(now(),'23:50:00'),checkin_info.time) as keepdays, groups.groupname,groups.membersnum,groups.contactname,groups.contacttel ".
        "FROM checkin_info,groups WHERE checkin_info.groupid=groups.groupid AND checkin_info.guesttype='Group' AND groups.contactname LIKE '%$contectName%' AND NOT (checkin_info.groupid IN( SELECT DISTINCT groupid FROM checkout_info WHERE not ISNULL(groupid) ));";
    }
    else if($tel!=''){
        $sql = "SELECT DISTINCT checkin_info.groupid, checkin_info.`time`, checkin_info.predictdays, datediff(addtime(now(),'23:50:00'),checkin_info.time) as keepdays, groups.groupname,groups.membersnum,groups.contactname,groups.contacttel ".
        "FROM checkin_info,groups WHERE checkin_info.groupid=groups.groupid AND checkin_info.guesttype='Group' AND groups.contacttel LIKE '%$tel%' AND NOT (checkin_info.groupid IN( SELECT DISTINCT groupid FROM checkout_info WHERE not ISNULL(groupid) ));";
    }else{
        echo '{"state":"OK","CheckInData":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","CheckInData":[]}';
        mysqli_close($db);
        exit();
    }
    $Data=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $Data.=sprintf($order,
            $row['groupid'],
            $row['groupname'],
            $row['contacttel'],
            $row['contactname'],
            $row['membersnum'],
            substr($row['time'],0,10),
            $row['predictdays'],
            $row['keepdays']==0 ? 1 : $row['keepdays']);
    } 
    mysqli_free_result($query);

//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($datas,"OK",substr($Data,0,-1));


?>