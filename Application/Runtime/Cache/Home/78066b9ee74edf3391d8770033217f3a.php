<?php if (!defined('THINK_PATH')) exit();?><html>
    <head>
        <title>数据系统--CP报表</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="pragma" content="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta name="format-detection" content="telephone=no"/>


    <link rel="stylesheet" type="text/css" href="/cpreport/Public/jquery.custom/css/ui-lightness/jquery-ui-1.8.11.custom.css" />
    <script type="text/javascript" src="/cpreport/Public/jquery.custom/js/jquery-ui-1.8.11.custom.min.js"></script>
    <script type="text/javascript" src="/cpreport/Public/jquery.custom/js/ui.datepicker-zh.js"></script>
    <script type="text/javascript" src="/cpreport/Public/javascript/jquery-ui-timepicker-addon.js"></script>

    <script type="text/javascript" src="/cpreport/Public/jquery-1.11.1.min.js"></script>

    <script type="text/javascript" src="/cpreport/Public/tokeninput/src/jquery.tokeninput.js"></script>
    <link rel="stylesheet" href="/cpreport/Public/tokeninput/styles/token-input-error.css" type="text/css" />
    <link rel="stylesheet" href="/cpreport/Public/tokeninput/styles/token-input-facebook-error.css" type="text/css" />

    <link rel="stylesheet" type="text/css" href="/cpreport/Public/css/main.css">

</head>
<body>    
    <div id="header_top">
        <div id="header_top_sty">
            <div style='float: left;margin-left: 20px;font-weight: bold;color: #747474;'>数据系统--CP报表系统</div>
            <div style='color: #747474;float: right;margin-right: 20px;font-size: 14px;'><?php echo $_SESSION[C("USER_AUTN_KEY")]; ?> | <a style="color: #747474;text-decoration:none;" href="/cpreport/index.php/Home/LoginPage/logout">退出</a></div>
        </div>
    </div>
    <div style='clear:both;'></div>
<div id="main">
    <div id="left">
        <div id="menu_sty">
            <div id="munu_sty_s">
                <!--公共左侧导航文件-->

<!--dopool权限-->
<?php if($_SESSION['role']=='1'){ ?>
<ul>
    <li>财务管理</li>
</ul>
<ul>
    <li><a href="/cpreport/index.php/Home/Cpindex/index">CP日报</a></li>
    <li><a href="/cpreport/index.php/Home/Cpindex/cpWeekMonthYear">CP周、月、年报</a></li>
</ul>
<ul>
    <li>dopool</li>
</ul>
<ul>
    <li>频道信息</li>
</ul>

<!--CP权限-->
<?php }elseif($_SESSION['role']=='2'){ ?>
<ul>
    <li><a href="/cpreport/index.php/Home/Cpindex/index">CP日报</a></li>
    <li><a href="/cpreport/index.php/Home/Cpindex/cpWeekMonthYear">CP周、月、年报</a></li>
</ul>
<?php } ?>

            </div>
        </div>
    </div>
    <div id="right">
        <div id="titles">
            <div id="titles_sty">CP日报</div>
        </div>
        <div id="cpinfor"> <span class="cpinfor_sty"><?php echo ($yesterday); ?></span> <span class="cpinfor_sty"><?php echo ($cpname); ?></span> </div>
        <div id="cpnuminfor">
            <span class="cpnuminfor_sty">CP播放数：<?php echo ($show_infor["cplay"]); ?></span>
            <span class="cpnuminfor_sty">手机电视播放数：<?php echo ($show_infor["dopoolplay"]); ?></span>
            <span class="cpnuminfor_sty">CP播放占比：<?php echo ($show_infor["per"]); ?></span>
            <span class="cpnuminfor_sty">频道数量：<?php echo ($show_infor["muchvideo"]); ?></span>
        </div>
        <div id="drawline"></div>
        <div id="changek">
            广告订单列表&nbsp;&nbsp;<input type="text" id="orders" style="width:100px;height:30px;">
        </div>
        <div id="orange"></div>
    </div>
</div>
<div id="footer">
    
</div>
</body>
</html>