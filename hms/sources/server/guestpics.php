<?php
//发送返回客户的照片
$dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
    header("Content-Type: image/jpeg");
    header("Content-Transfer-Encoding: binary"); 
    header('Cache-Control: no-cache,must-revalidate');  
    header("Keep-Alive: timeout=0,max=0"); 
    header('Pragma: no-cache');   
    header("Expires: 0");
    //header("Content-Disposition:attachment;filename=1.TXT");
    ob_clean(); //防止php将utf8的bom头输出
    $default = file_get_contents("../pics/default2.jpeg");
    
// 取得客户id
    $guestid = array_key_exists('guestid', $_GET) ? trim($_GET['guestid']) : "";

//连接数据库    
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        header('Content-Length: ' . strlen($default));
        echo $default;
        exit();
    }

//查询顾客信息
    $sql = "SELECT photo FROM guests WHERE guestid='$guestid';";
    if (!($query = mysqli_query($db,$sql))){
        header('Content-Length: ' . strlen($default));
        echo $default;
        mysqli_close($db);
        exit();
    }

    if (!($row = mysqli_fetch_assoc($query))) {
        header('Content-Length: ' . strlen($default));
        echo $default;
        mysqli_close($db);
        exit();
    }

    if (strlen($row['photo']) == 0) {
        header('Content-Length: ' . strlen($default));
        echo $default;
    } else {
        header('Content-Length: ' . strlen($row['photo']));
        echo $row['photo'];
    }

//关闭连接
    mysqli_free_result($query);
    mysqli_close($db);
?>