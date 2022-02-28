<?php
//根据选择的日期获取可预约的房间
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
    $orderTime = array_key_exists('orderTime', $_GET) ? trim($_GET['orderTime']) : "";
    $keepDays = array_key_exists('keepDays', $_GET) ? trim($_GET['keepDays']) : "";

//定义数据json格式
    $datas = '{'.
        '"state":"%s",'."\n".
        '"roomsData":[%s],'."\n".
        '"type":[%s]'."\n".
    '}';
    $rooms = '{'.
        '"roomid":"%s",'."\n".
        '"floor":"%s",'."\n".
        '"state":"%s",'."\n".
        '"price":"%.2f",'."\n".
        '"description":"%s",'."\n".
        '"typename":"%s",'."\n".
        '"roomtypeid":"%d",'."\n".
        '"bedsnum":"%d"'."\n".
    '},';

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","roomsData":[],"type":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomsData":[],"type":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","roomsData":[],"type":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询房间信息
    $orderTime = substr($orderTime,0,10);
    $orderEndTime = DateTime::createFromFormat("Y-m-d",$orderTime);
    $incdays = new DateInterval("P"."$keepDays"."D");
    date_add($orderEndTime,$incdays);
    $orderEndTime = $orderEndTime->format('Y-m-d');
    //echo $orderEndTime."\n";
    //echo $orderTime."\n";
    $sql = "SELECT rooms.roomid,rooms.floor,rooms.status,rooms.price,rooms.description,roomtype.typename,rooms.roomtypeid,roomtype.bedsnum FROM rooms,roomtype ".
    "WHERE roomtype.typeid=rooms.roomtypeid AND rooms.roomid NOT IN (".
    "SELECT roomid FROM order_info WHERE (".
    "'$orderEndTime' >= time AND end_time >= '$orderTime')".
    ");";
    //echo $sql."\n";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomtypes":[],"rooms":[]}';
        mysqli_close($db);
        exit();
    }
    $roomsData=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $roomsData.=sprintf($rooms,$row['roomid'],$row['floor'],$row['status'],$row['price'],$row['description'],$row['typename'],$row['roomtypeid'],$row['bedsnum']);
    } 
    mysqli_free_result($query);

//查询房间类型
    $sql="SELECT typename FROM roomtype;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomsData":[],"type":[]}';
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
    echo sprintf($datas,"OK",substr($roomsData,0,-1),substr($type,0,-1));

?>