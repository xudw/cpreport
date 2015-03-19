<?php

namespace Home\Model;

use Think\Model;

class CpindexModel extends Model
{

    protected $tableName = 'dopool_channel_video';
    protected $db_link_num = array(
        'DB_DATA_DSN' => 1,
        'DB_A3_DSN' => 2,
    );

    //系统所有用到的数据来自这三个appkey
    public function appkey()
    {
        $appkey = "('4muahmqff1yr','iphone_4muahmqff1yr','wnjy48akhyvc')";
        return $appkey;
    }

    //获得相应cp下的所有频道id
    public function getVideoid($cpid)
    {
        $appkey = $this->appkey();

        //拿到CP下的所有频道id
        $videoid_sql = "select videoid from dopool_channel_video where appkey in $appkey and cpid='$cpid' group by videoid";
        $result = $this->query($videoid_sql);
        foreach ($result as $res) {
            $videoids[] = "'" . $res['videoid'] . "'";
        }
        $muchvideo = count($videoids);
        $videoids = implode(',', $videoids);
        
        $videoid['much'] = $muchvideo;
        $videoid['vid'] = $videoids;
        
        return $videoid;
    }

    //CP日报
    public function getPlayCount($cpid)
    {
        $videoid = $this->getVideoid($cpid);
        $appkey = $this->appkey();

        //通过频道id查到播放次数
        $date = date("Y-m-d", time() - 86400);
        $cplaycount_sql = "select sum(cnt) as cnt from dopool_a3_data.appkey_video_playcountday"
                . " where videoid in ($videoid[vid]) and appkey in $appkey and l_date='$date'";
        $cplaycount = $this->db(2, C("DB_A3_DSN"))->query($cplaycount_sql);

        //昨日手机电视播放次数
        $dopoolplaycount_sql = "select sum(cnt) as dcnt from dopool_a3_data.playcountday"
                . " where appkey in $appkey and l_date='$date'";
        $dopoolplaycount = $this->db(2, C("DB_A3_DSN"))->query($dopoolplaycount_sql);

        $data['cplay'] = number_format($cplaycount[0]['cnt']);
        $data['dopoolplay'] = number_format($dopoolplaycount[0]['dcnt']);
        $data['per'] = round($cplaycount[0]['cnt'] / $dopoolplaycount[0]['dcnt'], 2) * 100 . "%";
        $data['muchvideo'] = $videoid['much'];

        return $data;
    }

    //CP周月年报
    public function getWeekMonthYearInfor($cpid)
    {
        $videoid = $this->getVideoid($cpid);
        $appkey = $this->appkey();

        //周开始结束时间
        $weektoday = date('w');
        $weekend = date("Y-m-d", time() - 86400 * 4);
        $weekstart = date("Y-m-d", strtotime($weekend) - 86400 * 6);
        $weekdata = $this->getPlaycountCycle($weekstart, $weekend, $videoid, "week");

        //月开始结束时间
        $month = date("m") - 1;
        if ($month == '0' || $month == '01' || $month == '03' || $month == '05' || $month == '07' || $month == '08' || $month == '10' || $month == '12') {
            $monthday = '31';
        } else if ($month == '04' || $month == '06' || $month == '09' || $month == '11') {
            $monthday = '30';
        } else if ($month == '02') {
            $monthday = '28';
        }
        $monthtoday = date("d");
        $monthend = date("Y-m-d", time() - 86400 * 19);
        $monthstart = date("Y-m-d", strtotime($monthend) - 86400 * ($monthday - 1));
        $monthdata = $this->getPlaycountCycle($monthstart, $monthend, $videoid, "month");

        //年开始结束时间
        $year = date("Y") - 1;
        $yearstart = $year . '-01' . '-01';
        $yearend = $year . '-12' . '-31';
        $yeardata = $this->getPlaycountCycle($yearstart, $yearend, $videoid, $year);

        $enddata['week'] = $weekdata;
        $enddata['month'] = $monthdata;
        $enddata['year'] = $yeardata;
        
        return $enddata;
    }

    public function getPlaycountCycle($start, $end, $videoid, $type)
    {
        
        $appkey = $this->appkey();
        
        if (is_numeric($type)) {
            $table = "appkey_video_playcountday_".$type;
        }else{
            $table = "appkey_video_playcountday";
        }
        
        //CP
        $cpsql = "select sum(cnt) as cnt from dopool_a3_data.$table"
                . " where videoid in ($videoid[vid]) and appkey in $appkey and l_date>='$start' and l_date<='$end'";
        $cplaycount = $this->db(2, C("DB_A3_DSN"))->query($cpsql);

        //手机电视
        $dopoolplaycountsql = "select sum(cnt) as dcnt from dopool_a3_data.playcountday"
                . " where appkey in $appkey and l_date>='$start' and l_date<='$end'";
        $dopoolplaycount = $this->db(2, C("DB_A3_DSN"))->query($dopoolplaycountsql);

        $data['cplay'] = number_format($cplaycount[0]['cnt']);
        $data['dopoolplay'] = number_format($dopoolplaycount[0]['dcnt']);
        $data['per'] = round($cplaycount[0]['cnt'] / $dopoolplaycount[0]['dcnt'], 2) * 100 . "%";

        return $data;
        
    }

}
