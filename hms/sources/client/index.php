<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="cache-control" content="no-cache">
    <title>客房管理系统</title>
    <script src=".\common\vue.js"></script>
    <script src=".\common\index.js"></script>
    <script src=".\common\axios.min.js"></script>
    <script src=".\common\common.js"></script>
    <link rel="stylesheet" type="text/css" href=".\themes\theme\index.css">
    <link rel="shortcut icon" href="favicon.ico" >
</head>
<body style="margin: 0">
    <!--所有组件渲染至app元素中-->
    <div id="app" :key="Key">
        <!--渲染登陆页面-->
        <login-page v-on:refreshui="refreshUI"></login-page>
        <!--渲染主界面-->
        <home-page v-on:refreshui="refreshUI"></home-page>
    </div>
</body>
</html>

<!--插入各功能组件代码-->
<?php
    function import($path) { 
        if (false === ($handle = opendir($path))) { return; }
        while (false !== ($file = readdir($handle))) {
            if ((substr($file, 0, 1) == ".") || (substr($file, 0, 1) == "_")) { continue; }
            if (is_dir("$path/$file")) { continue; }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext != "html") { continue; }
            if (false !== ($html = file_get_contents("$path/$file"))) {
                echo "$html\n";
            }
        }
    }
    import("./components");
    import("./views");
?>

<!--父JS-->
    <script>
        //注册Vue组件
        vue = new Vue({
            el:'#app',
            data:{
                Key:1
            },
            methods: {
                //通过监听Key值的变化来刷新页面
                refreshUI() { this.Key++; }
            }
        });
    </script>
