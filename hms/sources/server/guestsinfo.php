<?php
//获取顾客信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));

//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";

//定义数据json格式
    $guests = '{'.
        '"guestid":"%s",'.
        '"IDnumber":"%s",'.
        '"name":"%s",'.
        '"sex":"%s",'.
        '"age":"%s",'.
        '"telephone":"%s",'.
        '"roomid":"%s",'.
        '"groupname":"%s"'.
    '},';
    $groups = '{'.
        '"groupname":"%s",'.
        '"membersnum":"%s",'.
        '"contactname":"%s",'.
        '"contacttel":"%s"'.
    '},';
    $guestsData = '{'.
        '"state":"%s",'.
        '"guests":[%s],'.
        '"groups":[%s]'.
    '}';

//连接数据库    
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","guests":[],"groups":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","guests":[],"groups":[]}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","guests":[],"groups":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询顾客信息
    $sql = "SELECT DISTINCT guests.*,if(isnull(guests.groupid),'',groups.groupname) as groupname, FLOOR(DATEDIFF(now(),guests.birthday)/365.25) AS age FROM guests,groups WHERE isnull(guests.groupid) or groups.groupid=guests.groupid;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","guests":[],"groups":[]}';
        mysqli_close($db);
        exit();
    }
    $guestsinfo=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $guestsinfo.=sprintf($guests,$row['guestid'],$row['IDnumber'],$row['name'],$row['sex']=='M'?'男':'女',$row['age'],$row['telephone'],$row['roomid'],$row['groupname']);
    } 
    mysqli_free_result($query);

//查询团队信息
    $sql = "SELECT * FROM groups;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","guests":[],"groups":[]}';
        mysqli_close($db);
        exit();
    }
    $groupsinfo=''; 
    while ($row=mysqli_fetch_assoc($query)) {
        $groupsinfo.=sprintf($groups,$row['groupname'],$row['membersnum'],$row['contactname'],$row['contacttel']);
    } 
    mysqli_free_result($query);
//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($guestsData,"OK",substr($guestsinfo,0,-1),substr($groupsinfo,0,-1));

?>