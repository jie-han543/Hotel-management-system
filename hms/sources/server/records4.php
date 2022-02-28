<?php
//获取日报表
    $dbcfg = parse_ini_file("./config.ini",true);
foreach ($_GET as $index => $value) $_GET[$index] = strtr($_GET[$index], array('"'=>'', '`'=>'', '\\'=>'', "'"=>''));
//验证
    $username = array_key_exists('username', $_GET) ? trim($_GET['username']) : "";
    $time = array_key_exists('time', $_GET) ? $_GET['time'] : "";
    $type = array_key_exists('type', $_GET) ? trim($_GET['type']) : "";
    $page = array_key_exists('page', $_GET) ? trim($_GET['page']) : "";
    $lines = 0;
    if(is_array($time)){
        foreach ($time as $key => $value) {
            $time[$key]=substr(trim($value),0,10);
        }
    }else{
        $time=array();
        $time[0]="0000-00-00";
        $time[1]="9999-12-31";
    }
//定义数据json格式
    $datas = '{'.
        '"state":"%s",'."\n".
        '"records":[%s],'."\n".
        '"type":"'.$type.'",'."\n".
        '"lines":%d'."\n".
    '}';
    $records = '{'.
        '"date":"%s",'."\n".
        '"Cash":"%s",'."\n".
        '"Card":"%s",'."\n".
        '"Online":"%s",'."\n".
        '"total":"%s"'."\n".
    '},';

//连接数据库
    if (!($db = mysqli_connect($dbcfg['database']['host'],$dbcfg['database']['user'],$dbcfg['database']['password'],$dbcfg['database']['database']))){
        echo '{"state":"连接失败","records":[],"type":"'.$type.'","lines":0}';
        exit();
    }
    @mysqli_query($db,"set character_set_client = 'utf8'");
    @mysqli_query($db,"set character_set_connection = 'utf8'");
    @mysqli_query($db,"set character_set_results = 'utf8'");
    $sql = "SELECT * FROM admins WHERE username='$username';";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"数据库错误","records":[],"type":"'.$type.'","lines":0}';
        mysqli_close($db);
        exit();
    } 
    if (mysqli_num_rows($query)!=1){
        echo '{"state":"用户名无效或没有权限","records":[],"type":"'.$type.'","lines":0}';
        mysqli_free_result($query);
        mysqli_close($db);
        exit();
    }

//查询信息
    $sql = "SELECT COUNT(*) AS `lines` FROM ( SELECT DISTINCT SUBSTR(`time`,1,10) AS `date` FROM checkout_info ".
        "WHERE SUBSTR(`time`,1,10)>=\"$time[0]\" AND SUBSTR(`time`,1,10)<=\"$time[1]\" GROUP BY `date`) AS test;";
    if (!($query = mysqli_query($db,$sql))){
        echo '{"state":"连接失败","records":[],"type":"'.$type.'","lines":0}';
        mysqli_close($db);
        exit();
    }
    if(!($row = mysqli_fetch_assoc($query))){
        echo '{"state":"连接失败","records":[],"type":"'.$type.'","lines":0}';
        mysqli_close($db);
        exit();
    };
    $lines = $row['lines'];
    mysqli_free_result($query);

        $a=($page-1)*16;
        $sql = "SELECT SUBSTR(`time`,1,10) AS `time`, ".
            "SUM(if(payment='Card',bill,0)) AS Card, ".
            "SUM(if(payment='Cash',bill,0)) AS Cash, ".
            "SUM(if(payment='Online',bill,0)) AS `Online`, ".
            "SUM(bill) AS total ".
            "FROM checkout_info ".
            "WHERE `time`>=\"$time[0]\" AND `time`<=\"$time[1]\" ".
            "GROUP BY SUBSTR(`time`,1,10)".
            "LIMIT $a,16;";
            //echo $sql;
        if (!($query = mysqli_query($db,$sql))){
            echo '{"state":"连接失败","records":[],"type":"'.$type.'","lines":0}';
            mysqli_close($db);
            exit();
        }
        $recordsData=''; 
        while ($row=mysqli_fetch_assoc($query)) {
            $recordsData.=sprintf($records,$row['time'],$row['Cash'],$row['Card'],$row['Online'],$row['total']);
        } 
        mysqli_free_result($query);

//关闭连接
    mysqli_close($db);
//输出传输内容
    echo sprintf($datas,"OK",substr($recordsData,0,-1),$lines);
    //echo sprintf($datas,"OK",substr($recordsData,0,-1),100);
?>