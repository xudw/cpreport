<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>数据系统--CP报表</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="pragma" content="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta name="format-detection" content="telephone=no"/>
    </head>

    <form  name="loginAction"  action="/cpreport/index.php/Home/LoginPage/checkLogin" method="post"> 
        <div class="loginFormRow">
            <span class="left" style="line-height:35px;">
                &nbsp;
            </span>
            <div id="loginBoxErrors" style="color:red">

                <?php echo ($error); ?>
            </div>
        </div>
        <div class="loginFormRow clearBoth">
            <label for="emailInput" class="left bold">邮箱:</label>
            <input type="text" name="loginemail" value="" tabindex="1" id="emailInput" class="inputfield"/>
        </div>
        <div class="loginFormRow">
            <label for="passwordInput" class="left bold">密码:</label>
            <input type="password" name="loginpassword" tabindex="2" id="passwordInput" class="inputfield"/>
        </div>
        <input type="submit" value="登录" name="subin">
    </form>
   </html>