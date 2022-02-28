<?php 
//根据搜素内容返回相关预约信息
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
    $groupName = array_key_exists('groupName', $_GET) ? trim($_GET['groupName']) : "";
    $contectName = array_key_exists('contectName', $_GET) ? trim($_GET['contectName']) : "";
    $tel = array_key_exists('tel', $_GET) ? trim($_GET['tel']) : "";

//定义数据json格式
    $datas = '{'.
        '"state":"%s",'."\n".
        '"orderData":[%s]'."\n".
    '}';
    $order = '{'.
        '"orderid":"%s",'."\n".
        '"groupName":"%s",'."\n".
        '"name":"%s",'."\n".
        '"tel":"%s",'."\n".
        '"membersnum":"%d",'."\n".
        '"textarea":"%s",'."\n".
        '"time":"%s",'."\n".
        '"predictdays":"%d",'."\n".
        '"roomids":[\'%s\']'."\n".
    '},';

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","orderData":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");

    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","orderData":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","orderData":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询预约信息
    if($groupName!=''){
        $sql = "SELECT * ,GROUP_CONCAT(records.roomid SEPARATOR \"','\") AS roomids FROM records,groups WHERE records.groupid=groups.groupid AND records.ordertype='Order' AND to_days(`time`) >= to_days(NOW()) AND groups.groupname LIKE '%$groupName%' GROUP BY records.groupid;";
    //echo $sql."\n";
    }else if($tel!=''){
        $sql = "SELECT * ,GROUP_CONCAT(records.roomid SEPARATOR \"','\") AS roomids FROM records,groups WHERE records.groupid=groups.groupid AND records.ordertype='Order' AND to_days(`time`) >= to_days(NOW()) AND groups.contacttel LIKE '%$tel%' GROUP BY records.groupid;";
    //echo $sql."\n";
    }else if($contectName!=''){
        $sql = "SELECT * ,GROUP_CONCAT(records.roomid SEPARATOR \"','\") AS roomids FROM records,groups WHERE records.groupid=groups.groupid AND records.ordertype='Order' AND to_days(`time`) >= to_days(NOW()) AND groups.contactname LIKE '%$contectName%' GROUP BY records.groupid;";
    }else{
        echo '{"state":"OK","orderData":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","orderData":[]}';
        mysqli_close($db);
        exit();
    }
    $Data=''; 

    while ($row=mysqli_fetch_assoc($query)) {
        $Data.=sprintf($order,$row['orderid'],$row['groupname'],$row['contactname'],$row['contacttel'],$row['membersnum'],$row['memo'],substr($row['time'],0,10),$row['predictdays'],$row['roomids']);
    } 
    mysqli_free_result($query);

//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($datas,"OK",substr($Data,0,-1));


?>