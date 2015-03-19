<?php

/*
 * 用户登录验证页面
 */

namespace Home\Controller;

use Think\Controller;

class LoginPageController extends Controller
{

    //登录页面
    public function index()
    {
        // 用户登录页面
        if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        } else {
            $app_name = strtoupper(APP_NAME);
            $accessList = $_SESSION['_ACCESS_LIST'][$app_name];
            $modules = array_keys($accessList);
            $first_module = ucwords(strtolower($modules[0]));
            $first_action = strtolower(array_shift(array_flip($accessList[$modules[0]])));
            $this->redirect($first_module . '/' . $first_action);
        }
    }

    //登陆检测
    public function checkLogin()
    {
        $user = D("cp_sys_user");
        $user->tableName = 'cp_sys_user';

        $email = htmlspecialchars(trim($_POST['loginemail']));
        $password = htmlspecialchars(trim($_POST['loginpassword']));

        if (empty($email)) {
            $this->assign("error", "请填写登录邮箱");
            $this->display("LoginPage:index");
            exit;
        } else if (empty($password)) {
            $this->assign("error", "请填写密码");
            $this->display("LoginPage:index");
            exit;
        }
        if (preg_match('/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$/', $email)) {
            $list = $user->query("SELECT * FROM cp_sys_user where email='$email' and status=1");
        } else {
            $this->assign("error", "请填写正确的邮箱地址");
            $this->display("LoginPage:index");
            exit;
        }

        if (empty($list)) {
            $this->assign("error", "此用户不存在");
            $this->display("LoginPage:index");
            exit;
        } else {
            $val = $list[0];  
            
            if (sha1($password) == $val['cpassword']) {
                $_SESSION[C("USER_AUTN_KEY")] = $val['nickname'];
                $_SESSION['role'] = $val['roleid'];
                $_SESSION['email'] = $val['email'];
				$_SESSION['cpname'] = $val['cpname'];
				$_SESSION['cpid'] = $val['cpid'];
                $_SESSION['uid'] = $val['id'];

                $url = "/cpreport/index.php/Home/Cpindex/index";

                if ($val['email'] == 'admin@dopool.com') {
                    redirect("/cpreport/index.php/Home/Manage/index");
                } else {
                    redirect($url);
                }
            } else {
                $this->assign("error", "密码错误");
                $this->display("LoginPage:index");
                exit;
            }
        }
    }

    //用户登出
    public function logout()
    {
        if (isset($_SESSION)) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION['email']);
            unset($_SESSION);
            session_destroy();
        }
        $this->display("LoginPage:index");
    }

}
