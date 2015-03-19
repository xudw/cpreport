<?php

namespace Home\Controller;

use Think\Controller;

class CpindexController extends CommonController
{

    public function index()
    {
        $yesterday = date("Y年m月d日", time() - 86400); //昨日时间
        $cpname = $_SESSION['cpname']; //cp名称
        $cpid = $_SESSION['cpid']; //cp名称

        $d = new \Home\Model\CpindexModel('Cpindex', '', 'DB_DATA_DSN');
        $show_infor = $d->getPlayCount($cpid);

        $this->assign('yesterday', $yesterday);
        $this->assign('show_infor', $show_infor);
        $this->assign('cpname', $cpname);
        $this->display();
    }
    
    public function cpWeekMonthYear(){
        
        $cpname = $_SESSION['cpname']; //cp名称
        $cpid = $_SESSION['cpid']; //cp名称
        
        $d = new \Home\Model\CpindexModel('Cpindex', '', 'DB_DATA_DSN');
        $show = $d->getWeekMonthYearInfor($cpid);
        
        $this->assign('cpname', $cpname);
        $this->assign('show', $show);
        $this->display();
    }

}
