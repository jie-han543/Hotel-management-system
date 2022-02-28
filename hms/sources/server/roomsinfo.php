<?php
//获取房间信息
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));


//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
//定义数据json格式
    $roomtype = '{'.
        '"typeid":"%d",'.
        '"typename":"%s",'.
        '"bedsnum":"%d",'.
        '"price":"%.2f",'.
        '"description":"%s"'.
    '},';
    $room = '{'.
        '"roomid":"%s",'.
        '"floor":"%s",'.
        '"state":"%s",'.
        '"price":"%.2f",'.
        '"description":"%s",'.
        '"typename":"%s",'.
        '"roomtypeid":"%d"'.
    '},';
    $roomdata = '{'.
        '"state":"%s",'.
        '"roomtypes":[%s],'.
        '"rooms":[%s]'.
    '}';

//校验
    //if (!input_check($product_id, 'version')) {     }

//连接数据库    
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","roomtypes":[],"rooms":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username' AND (permissions AND 1) != 0;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomtypes":[],"rooms":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","roomtypes":[],"rooms":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询房间类型
    $sql = "SELECT * FROM roomtype;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomtypes":[],"rooms":[]}';
        mysqli_close($db);
        exit();
    }
    $roomtypes=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $roomtypes.=sprintf($roomtype,$row['typeid'],$row['typename'],$row['bedsnum'],$row['price'],$row['description']);
    } 
    mysqli_free_result($query);

//查询房间详细信息
    $sql = "SELECT rooms.roomid,rooms.floor,rooms.status,rooms.price,rooms.description,roomtype.typename,rooms.roomtypeid FROM rooms,roomtype WHERE roomtype.typeid=rooms.roomtypeid;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","roomtypes":[],"rooms":[]}';
        mysqli_close($db);
        exit();
    }
    $rooms=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $rooms.=sprintf($room,$row['roomid'],$row['floor'],$row['status'],$row['price'],$row['description'],$row['typename'],$row['roomtypeid']);
    } 
    mysqli_free_result($query);
//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($roomdata,"OK",substr($roomtypes,0,-1),substr($rooms,0,-1));

?>