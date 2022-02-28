<?php
//更改房间类型的信息
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";

//保存数据
    $op=array_key_exists('op', $_GET) ? trim($_GET["op"]) : "";
    $typeid=array_key_exists('typeid', $_GET) ? trim($_GET["typeid"]) : "";
    $typename=array_key_exists('typename', $_GET) ? trim($_GET["typename"]) : "";
    $bedsnum=array_key_exists('bedsnum', $_GET) ? trim($_GET["bedsnum"]) : "";
    $price=array_key_exists('price', $_GET) ? trim($_GET["price"]) : "";
    $description=array_key_exists('description', $_GET) ? trim($_GET["description"]) : "";

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","roomtypes":[]}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username' AND (permissions AND 1) != 0;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误"}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限"}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }
    
//修改/增加/删除房间详细信息
    if( $op == 'update' ){
        $sql = "UPDATE roomtype SET typename='$typename', bedsnum='$bedsnum', price='$price', description='$description' WHERE typeid='$typeid';";
    }else if( $op == 'add' ){
        $sql = "INSERT INTO roomtype (typename, bedsnum, price, description) 
            VALUES ('$typename','$bedsnum','$price','$description');";
    }else{
        $sql = "DELETE FROM roomtype WHERE typeid='$typeid';";
    }

    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = '';
        if($errno == 1062){
            $errinfo = '房间类型名已存在';
        }else if($errno == 1451){
            $errinfo = '请先删除该类型的所有房间';
        }
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==1) {
        echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"操作失败: '.mysqli_error($db).'"}';
    }
    mysqli_close($db);
?>