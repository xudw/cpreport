<?php if (!defined('THINK_PATH')) exit();?><!--公共左侧导航文件-->

<!--dopool权限-->
<?php if($_SESSION['role']=='1'){ ?>
<ul>
    <li>财务管理</li>
</ul>
<ul>
    <li>CP日报</li>
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
    <li>dopool</li>
</ul>
<?php } ?>

<a href="/cpreport/index.php/Home/LoginPage/logout">退出</a>