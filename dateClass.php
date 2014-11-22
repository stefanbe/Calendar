<?php
class CalDate extends CalDaten {

    public $para = array(
            "cols" => 3,
            "show_in" => false,
            "show_nav" => true,
            "show_event" => false,
            "show_weeknr" => true,
            "date" => false,
            "func" => false,
            "db" => false,
            "from" => false,
            "to" => false
        );

    function CalDate_init($value_array) {
        $this->holiday = $this->holidays(substr($this->make_Proper_Date(getRequestValue("date","get")),0,4));

        foreach($this->para as $key => $value) {
            if(false !== ($tmp = getRequestValue($key,'get')))
                $this->para[$key] = str_replace("%25","%",trim($tmp));
            elseif(isset($value_array[$key]))
                $this->para[$key] = trim($value_array[$key]);
            elseif(false !== ($pos = array_search($key,$value_array))) {
                $this->para[$key] = trim($value_array[$pos]);
            }
            if($this->para[$key] === "true")
                $this->para[$key] = true;
            if($this->para[$key] === "false")
                $this->para[$key] = false;
        }
        if(!$this->para["func"])
            $this->para["func"] = trim($value_array[0]);

        if(isset($value_array["from_to"])) {
            list($from,$to) = $this->make_Proper_FromTo(trim($value_array["from_to"]));
            $this->para["from"] = $from;
            $this->para["to"] = $to;
        }

        if(isset($value_array["maxlen"])) {
            list($from,$to) = $this->make_MaxLen(trim($value_array["maxlen"]));
            $this->para["from"] = $from;
            $this->para["to"] = $to;
        }
/*
        if(isset($value_array["autodel"])) {
            list($to,) = $this->make_Proper_FromTo(trim($value_array["autodel"]));
            $this->auto_Delete_Old($to);
        }*/

        if($this->para["date"])
            $this->para["date"] = $this->make_Proper_DateStr($this->para["date"]);

        if($this->para["show_event"])
            $this->para["show_event"] = $this->make_Proper_DateStr($this->para["show_event"]);
        # das ist für die links nötig
        if(!$this->para["show_in"])
            $this->para["show_in"] = CAT_REQUEST.":".PAGE_REQUEST;
    }

    function get_WeeksDays($year,$month) {
        $month = ($month * 1);
        $weeks = array();
        $offset_last_day_month = array_search(date("w",mktime(0,0,0,$month,1,$year)),$this->lang['week_days']) - 1;
        $max_days = date("t",strtotime($year."-".$month));
        $week_offset = date("W",strtotime($year."-".$month));
        $max_days_prev_month = date("t",strtotime($year."-".($month - 1)));
        $day_month = 1;
        $day_next_month = 1;
        for($week = 0;6 > $week;$week++) {
            foreach($this->lang['week_days'] as $day) {
                if($offset_last_day_month > -1) {
                    $weeks[$week + $week_offset][($max_days_prev_month - $offset_last_day_month)] = false;
                    $offset_last_day_month--;
                } elseif($day_month <= $max_days) {
                    $weeks[$week + $week_offset][$day_month] = true;
                    $day_month++;
                } else {
                    $weeks[$week + $week_offset][$day_next_month] = false;
                    $day_next_month++;
                }
            }
        }
        return $weeks;
    }

    function get_Href($cat_page,$date,$query = "") {
        global $CatPage;
        if(defined('PLUGINADMIN_GET_URL'))
            return PLUGINADMIN_GET_URL.'&amp;date='.$date.$query;
        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($cat_page);
        return $CatPage->get_Href($cat,$page,'date='.$date.$query);
    }

    function get_holiday($month,$day) {
        $month = ($month * 1);
        $day = ($day * 1);
        if($this->holiday !== false and isset($this->holiday[$month."-".$day]))
            return $this->holiday[$month."-".$day];
        return false;
    }

    private function holidays($year) {
        $K = floor($year / 100);
        $M = 15 + floor((( 3 * $K) + 3) / 4) - floor((( 8 * $K) + 13) / 25);
        $S = 2 - floor((( 3 * $K) + 3) / 4);
        $A = $year % 19;
        $D = (19 * $A + $M) % 30;
        $R = floor($D / 29) + (floor( $D / 28) - floor($D / 29)) * floor($A / 11);
        $OG = 21 + $D - $R;
        $SZ = 7 - (($year + floor($year / 4) + $S) % 7);
        $OE = 7 - (($OG - $SZ) %7);
        $OS = $OG + $OE;
        $holidays['1-1'] = $this->lang['holidays'][0];
        $holidays['5-1'] = $this->lang['holidays'][1];
        $holidays['10-3'] = $this->lang['holidays'][2];
        $holidays['12-25'] = $this->lang['holidays'][3];
        $holidays['12-26'] = $this->lang['holidays'][4];
        $holidays[date("n-j",mktime(0,0,0,3,$OS - 2,$year))] = $this->lang['holidays'][5];
        $holidays[date("n-j",mktime(0,0,0,3,$OS + 1,$year))] = $this->lang['holidays'][6];
        $holidays[date("n-j",mktime(0,0,0,3,$OS + 39,$year))] = $this->lang['holidays'][7];
        $holidays[date("n-j",mktime(0,0,0,3,$OS + 50,$year))] = $this->lang['holidays'][8];
        # Fronleichnam
        if(isset($this->lang['holidays'][9]))
            $holidays[date("n-j",mktime(0,0,0,3,$OS + 60,$year))] = $this->lang['holidays'][9];
        return $holidays;
    }
}
?>