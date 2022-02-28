<?php
//提交散客入住
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_GET) ? trim($_GET["username"]) : "";
    $name=array_key_exists('name', $_GET) ? trim($_GET["name"]) : "";
    $tel=array_key_exists('tel', $_GET) ? trim($_GET["tel"]) : "";
    $time=array_key_exists('time', $_GET) ? substr(trim($_GET["time"]),0,10) : "";
    $IDnumber=array_key_exists('IDnumber', $_GET) ? trim($_GET["IDnumber"]) : "";
    $sex=substr($IDnumber,-2,1)%2==1 ? 'M':'F';
    $keepDays=array_key_exists('keepDays', $_GET) ? trim($_GET["keepDays"]) : "";
    $price=array_key_exists('price', $_GET) ? $_GET["price"] : array();
    $roomid=array_key_exists('roomid', $_GET) ? $_GET["roomid"] : array();
    $textarea=array_key_exists('textarea', $_GET) ? $_GET["textarea"] : "";
    $URL=array_key_exists('URL', $_GET) ? $_GET["URL"] : array();
    $photo = file_get_contents($URL);

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
    //查找客户是否存在
    $sql = "SELECT guestid FROM guests WHERE name='$name' AND sex='$sex' AND telephone='$tel';";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误1: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==0) {
        //客户不存在
        mysqli_free_result($query);
        $sql = "INSERT guests SET IDnumber='$IDnumber', birthday=substring('$IDnumber',7,8), name='$name', sex='$sex', telephone='$tel',roomid='$roomid', photo='".addslashes($photo)."';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误2: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"保存顾客信息失败1"}';
            mysqli_close($db);
            exit();
        }
        $id=mysqli_insert_id($db);
    }else{
        //客户存在
        $row=mysqli_fetch_assoc($query);
        $id=$row['guestid'];
        $sql = "UPDATE guests SET photo='".addslashes($photo)."', IDnumber='$IDnumber', birthday=substring('$IDnumber',7,8), roomid='$roomid' WHERE guestid='$id';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误3: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"保存顾客信息失败2"}';
            mysqli_close($db);
            exit();
        }
    }  

//修改房间状态
    $sql = "UPDATE rooms SET status='used' WHERE roomid='$roomid';";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==0) {
        echo '{"state":"修改房间状态失败"}';
        mysqli_close($db);
        exit();
    }

//增加入住记录
    $sql = "INSERT INTO records(orderid,ordertype,guesttype,guestid,roomid,`time`,bill,predictdays,memo) VALUES ".
        "(concat('I',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)),'CheckIn','Solo', '$id','$roomid','$time','$price','$keepDays','$textarea');";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==1) {
        echo '{"state":"OK"}';
    }else if (mysqli_affected_rows($db)==0) {
        echo '{"state":"预约失败"}';
    }

    unlink($URL);
    mysqli_close($db);
?>
