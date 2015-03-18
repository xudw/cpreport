<?php

namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{

    public function _initialize()
    {
        if (!isset($_SESSION['admin']) && empty($_SESSION['admin'])) {
            $this->redirect("LoginPage/index");
        }
    }

}
