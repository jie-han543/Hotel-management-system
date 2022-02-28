<?php
//提交入住
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_POST as $index => $value) $_POST[$index] = strtr($_POST[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//保存数据
    $username=array_key_exists('username', $_POST) ? trim($_POST["username"]) : "";
    $groupName=array_key_exists('groupName', $_POST) ? trim($_POST["groupName"]) : "";
    $contectName=array_key_exists('contectName', $_POST) ? trim($_POST["contectName"]) : "";
    $tel=array_key_exists('tel', $_POST) ? trim($_POST["tel"]) : "";
    $time=array_key_exists('time', $_POST) ? trim($_POST["time"]) : "";
    $keepDays=array_key_exists('keepDays', $_POST) ? trim($_POST["keepDays"]) : "";
    $membersnum=array_key_exists('membersnum', $_POST) ? trim($_POST["membersnum"]) : "";
    $names=array_key_exists('names', $_POST) ? $_POST["names"] : "";
    $tels=array_key_exists('tels', $_POST) ? $_POST["tels"] : "";
    $IDnumbers=array_key_exists('IDnumbers', $_POST) ? $_POST["IDnumbers"] : "";
    $prices=array_key_exists('prices', $_POST) ? $_POST["prices"] : "";
    $roomids=array_key_exists('roomids', $_POST) ? $_POST["roomids"] : "";
    $textarea=array_key_exists('textarea', $_POST) ? $_POST["textarea"] : "";
    $photos=array_key_exists('photos', $_POST) ? $_POST["photos"] : "";

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

//增加团队信息
    //查找团队是否存在
    $sql = "SELECT groupid FROM groups WHERE groupname='$groupName' AND membersnum='$membersnum' AND contacttel='$tel' AND contactname='$contectName';";
    if (!($query = mysqli_query($db,$sql))){
        $errno = mysqli_errno($db);
        $errinfo = mysqli_error($db);
        echo '{"state":"数据库错误1: '.$errno.' : '.$errinfo.'"}';
        mysqli_close($db);
        exit();
    }
    if (mysqli_affected_rows($db)==0) {
        //团队不存在
        mysqli_free_result($query);
        $sql = "INSERT groups SET groupname='$groupName', membersnum='$membersnum', contacttel='$tel' , contactname='$contectName';";
        //IDnumber='$IDnumber', name='$name', sex='$sex', telephone='$tel',roomid='$roomid', photo='".addslashes($photo)."';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误2: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"保存团队信息失败"}';
            mysqli_close($db);
            exit();
        }
        $id=mysqli_insert_id($db);
    }else{
        //团队存在
        $row=mysqli_fetch_assoc($query);
        $id=$row['groupid'];
        mysqli_free_result($query);
    }

foreach ($IDnumbers as $i => $IDnumber) {
    if(substr($IDnumber,16,1)%2==0){ $sex='F'; }
    else{ $sex='M'; }
    $photo = file_get_contents($photos[$i]);
    // 增加用户信息
        //查找各个用户是否存在
        $sql = "SELECT guestid FROM guests WHERE name='$names[$i]' AND telephone='$tels[$i]' AND sex='$sex';";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误3: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        //客户不存在
        if (mysqli_affected_rows($db)==0) {
            mysqli_free_result($query);
            $sql = "INSERT guests SET IDnumber='$IDnumber', name='$names[$i]', birthday=substring('$IDnumber',7,8), sex='$sex', telephone='$tels[$i]', roomid='$roomids[$i]', groupid='$id', photo='".addslashes($photo)."';";
            if (!($query = mysqli_query($db,$sql))){
                $errno = mysqli_errno($db);
                $errinfo = mysqli_error($db);
                echo '{"state":"数据库错误4: '.$errno.' : '.$errinfo.'"}';
                mysqli_close($db);
                exit();
            }
            if (mysqli_affected_rows($db)==0) {
                echo '{"state":"保存顾客信息失败1"}';
                mysqli_close($db);
                exit();
            }
            $id2=mysqli_insert_id($db);
        }
        //客户存在
        else{
            $row=mysqli_fetch_assoc($query);
            $id2=$row['guestid'];
            mysqli_free_result($query);
            $sql = "UPDATE guests SET IDnumber='$IDnumber', birthday=substring('$IDnumber',7,8), roomid='$roomids[$i]', groupid='$id', photo='".addslashes($photos[$i])."' WHERE guestid='$id2';";
            if (!($query = mysqli_query($db,$sql))){
                $errno = mysqli_errno($db);
                $errinfo = mysqli_error($db);
                echo '{"state":"数据库错误5: '.$errno.' : '.$errinfo.'"}';
                mysqli_close($db);
                exit();
            }
            if (mysqli_affected_rows($db)==0) {
                echo '{"state":"保存顾客信息失败2"}';
                mysqli_close($db);
                exit();
            }
        } 
    // 增加入住记录
        $sql = "INSERT INTO records(orderid,ordertype,guesttype,guestid,groupid,roomid,`time`,bill,predictdays,memo) VALUES ".
        "(concat('I',date_format(now(),'%Y%m%d%h%i%s'),cast(round((rand()+1)*10000) AS CHAR)),'CheckIn','Group', '$id2','$id','$roomids[$i]','$time','$prices[$i]','$keepDays','$textarea');";
        if (!($query = mysqli_query($db,$sql))){
            $errno = mysqli_errno($db);
            $errinfo = mysqli_error($db);
            echo '{"state":"数据库错误: '.$errno.' : '.$errinfo.'"}';
            mysqli_close($db);
            exit();
        }
        if (mysqli_affected_rows($db)==0) {
            echo '{"state":"预约失败"}';
        }
        unlink($photos[$i]); 
}
    
//修改房间状态
    foreach ($roomids as $i => $roomid) {
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
    }
    echo '{"state":"OK"}';
    mysqli_close($db);
?>
