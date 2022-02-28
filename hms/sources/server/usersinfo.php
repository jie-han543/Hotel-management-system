<?php
//获取所有用户信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
ini_set('display_errors',1);
error_reporting(E_ALL);

    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
//定义数据json格式
    $usersData = '{'.
        '"username":"%s",'.
        '"name":"%s",'.
        '"sex":"%s",'.
        '"job":"%s",'.
        '"telephone":"%s",'.
        '"permissions":[%s]'.
    '},';
    $data = '{'.
        '"state":"%s",'.
        '"usersData":[%s]'.
    '}';

//校验
    //if (!input_check($product_id, 'version')) {     }

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","data":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username' AND (permissions AND 2) != 0;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","data":[]}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","data":[]}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }
    mysqli_free_result($query);

    $sql = "SELECT * FROM admins;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","data":[]}';
        mysqli_close($db);
        exit();
    }

    $usersDatas='';
    while ($row=mysqli_fetch_assoc($query)) {
        $permissions = '';
        $permission = $row['permissions'];
        //echo $permission." ";
        if(($permission & 1) != 0){ $permissions .= '"room",'; }
        if(($permission & 2) != 0){ $permissions .= '"user",'; }
        if(($permission & 4) != 0){ $permissions .= '"record",'; }
        //echo $permissions." ";
        $permissions = ($permissions == '' ? '' : substr($permissions,0,-1));
        //echo $permissions."\n";
        if($row['sex']=='M'){ $row['sex']='男'; }
        if($row['sex']=='F'){ $row['sex']='女'; }
        $usersDatas.=sprintf($usersData,$row['username'],$row['name'],$row['sex'],$row['job'],$row['telephone'],$permissions);
    }
    mysqli_free_result($query);
    mysqli_close($db);

//输出传输内容
    echo sprintf($data,"OK",substr($usersDatas,0,-1));

?>