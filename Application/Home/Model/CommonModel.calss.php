<?php

namespace Home\Model;

use Think\Model;

class CommonModel extends Model
{
    protected $tableName = 'cp_sys_user';
    
    //系统所有用到的数据来自这三个appkey
    public function appkey()
    {
        $appkey = "('4muahmqff1yr','iphone_4muahmqff1yr','4muahmqff1yr')";
        return $appkey;
    }

}
