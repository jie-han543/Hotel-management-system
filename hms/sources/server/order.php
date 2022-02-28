<?php
//提交预约
    $dbcfg = parse_ini_file("./config.ini",true);
    //foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $type=array_key_exists('type', $_GET) ? trim($_GET["type"]) : "";
    $name=array_key_exists('name', $_GET) ? trim($_GET["name"]) : "";
    $tel=array_key_exists('tel', $_GET) ? trim($_GET["tel"]) : "";
    $sex=array_key_exists('sex', $_GET) ? trim($_GET["sex"]) : "";
    $groupName=array_key_exists('groupName', $_GET) ? trim($_GET["groupName"]) : "";
    $contectName=array_key_exists('contectName', $_GET) ? trim($_GET["contectName"]) : "";
    $orderTime=array_key_exists('orderTime', $_GET) ? substr(trim($_GET["orderTime"]),0,10) : "";
    $keepDays=array_key_exists('keepDays', $_GET) ? trim($_GET["keepDays"]) : "";
    $textarea=array_key_exists('textarea', $_GET) ? trim($_GET["textarea"]) : "";
    $groupCheckedNum=array_key_exists('groupCheckedNum', $_GET) ? trim($_GET["groupCheckedNum"]) : "";
    $roomids=array_key_exists('roomids', $_GET) ? $_GET["roomids"] : array();
    $arrlength=count($roomids);
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
//增加用户信息
    if($type=="guest"){
        $sql = "SELECT guestid FROM guests WHERE name='$name' AND sex='$sex' AND telephone='$tel';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误1: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            mysqli_free_result($query);
            $sql = "INSERT guests SET name='$name', sex='$sex', telephone='$tel';";
            if (!($query = mysqli_query($db,$sql))){
                $errno = mysqli_errno($db);
                $errinfo = mysqli_error($db);
                echo '{"state":"数据库错误2: '.$errno.' : '.$errinfo.'"}';
                mysqli_close($db);
                exit();
            }
            if (mysqli_affected_rows($db)==0) {
                echo '{"state":"保存顾客信息失败"}';
                mysqli_close($db);
                exit();
            }
            $id=mysqli_insert_id($db);
        }else{
            $row=mysqli_fetch_assoc($query);
            $id=$row['guestid'];
            mysqli_free_result($query);
        }  
    }else{
        $sql = "INSERT groups SET groupname='$groupName', membersnum='$groupCheckedNum', contactname='$contectName', contacttel='$tel';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误3: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"保存顾客信息失败"}';
            mysqli_close($db);
            exit();
        }
        $id=mysqli_insert_id($db);
    }

//增加预约记录
    if($type=="guest"){
        $sql = "INSERT INTO records(orderid,ordertype,guesttype,guestid,roomid,`time`,predictdays,memo) VALUES ";
        for( $x=0; $x<$arrlength; $x++){
            $rmid = $roomids[$x];
            $sql .= "(concat('P',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)),'Order','Solo', '$id','$rmid','$orderTime','$keepDays','$textarea'),";
        }
        $sql=substr($sql,0,-1).";";
        //echo "$sql";
    }else{
        $sql = "INSERT INTO records(orderid,ordertype,guesttype,groupid,roomid,`time`,predictdays,memo) VALUES ";
        for( $x=0; $x<$arrlength; $x++){
            $rmid = $roomids[$x];
            $sql .= "(concat('P',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)), 'Order', 'Group', '$id', '$rmid', '$orderTime', '$keepDays','$textarea'),";
        }
        $sql=substr($sql,0,-1).";";
    }
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)>=1) {
        echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"预约失败"}';
    }
    /*if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }*/
    mysqli_close($db);
?>
