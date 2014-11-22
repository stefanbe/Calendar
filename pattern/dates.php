<?php if(!defined('IS_CMS')) die();

class dates extends CalDate {

    private $in_year = false;
    private $adminTemplate = NULL;
    private $datenColAdmin = array();
    # user daten {CAL_DATE} ist pflicht und muss als erstes im array sein
    public $datenCols = array("{CAL_DATE}","{CAL_TITLE}","{CAL_TEXT}","{CAL_ORT}","{CAL_COLOR}");

    function admin_init() {
        # das input feld für das datum muss name="event[CAL_DATE]" sein
        # alle name= müssen name="event[$this->datenCols]" ohne {} benant werden
        $this->datenColAdmin[] = '<input type="text" name="event[CAL_DATE]" value="{CAL_DATE}" size="16" maxlength="16" />';
        $this->datenColAdmin[] = '<input class="cal-admin-text" type="text" name="event[CAL_TITLE]" value="{CAL_TITLE}" maxlength="255" />';
        $this->datenColAdmin[] = '<textarea class="cal-admin-text" name="event[CAL_TEXT]" cols="5" rows="6">{CAL_TEXT}</textarea>';
        $this->datenColAdmin[] = '<input class="cal-admin-text" type="text" name="event[CAL_ORT]" value="{CAL_ORT}" maxlength="255" />';
        $this->datenColAdmin[] = '<select name="event[CAL_COLOR]">'
            .'<option value="cal-color1" selected="selected">'.$this->language->getLanguageValue("cal_color1").'</option>'
            .'<option value="cal-color2">'.$this->language->getLanguageValue("cal_color2").'</option>'
            .'<option value="cal-color3">'.$this->language->getLanguageValue("cal_color3").'</option>'
        .'</select>';

        $this->adminTemplate = '<table class="cal-admin-event {COLOR}" cellspacing="0" border="0" cellpadding="0"><tbody>'
            .'<tr>'
                .'<td style="width:1%;">'.$this->language->getLanguageValue("cal_date").'</td>'
                .'<td style="width:1%;">{CAL_DATE}</td>'
                .'<td style="width:1%;">'.$this->language->getLanguageValue("cal_ort").'</td>'
                .'<td>{CAL_ORT}</td>'
                .'<td style="width:1%;" class="cal-nowrap">'.$this->language->getLanguageValue("cal_color").'</td>'
                .'<td style="width:1%;">{CAL_COLOR}</td>'
            .'</tr><tr>'
                .'<td>'.$this->language->getLanguageValue("cal_title").'</td>'
                .'<td colspan="5">{CAL_TITLE}</td>'
            .'</tr><tr>'
                .'<td colspan="6">'.$this->language->getLanguageValue("cal_inhalt").'<br />'
                .'{CAL_TEXT}</td>'
            .'</tr>'
        .'</tbody></table>';
    }

    function front_init() {
        global $syntax;
        $syntax->insert_jquery_in_head('jquery');
        $syntax->insert_jquery_in_head('jquery-ui');
        $syntax->insert_in_head('<link type="text/css" rel="stylesheet" href="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/addons/dialog/css/ui-lightness/jquery-ui-1.9.2.custom.min.css" />');
        $syntax->insert_in_head('<script type="text/javascript" src="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/addons/dialog/cal_dialog.js" charset="utf-8"></script>');
        $syntax->insert_in_head('<script type="text/javascript" src="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/addons/prevnext/prevnext.js" charset="utf-8"></script>');
    }

    private function help_get_Day($year,$month,$day,$is_day,$css = false) {
        if($is_day) {
            $title = "";
            $onclick = ' onclick="calDialog(this,\'&amp;db='.$this->db.'&amp;func=events\');return false;"';
            $css = "cal-day-normal";
            if(false !== ($holiday = $this->get_holiday($month,$day))) {
                $title = ' title="'.$holiday.'"';
                $css = "cal-day-holi";
            }
            $event = $this->get_From_To_Date_Cols($year."-".$month."-".$day,$year."-".$month."-".$day);
            if(count($event) > 0) {
                $date_format_title = $this->language->getLanguageValue("cal_date_format_title");
                $css .= " cal-day-event";
                $event_title = (count($event) > 1 ? $this->language->getLanguageValue("cal_events_title") : $this->language->getLanguageValue("cal_event_title"))." ".$this->format_Date($this->make_Proper_Date($year."-".$month."-".$day),$date_format_title);
                $event_link = $day;
                if($this->para["func"] == "month")
                    $event_link = (count($event) > 1 ? $this->language->getLanguageValue("cal_events_link") : $this->language->getLanguageValue("cal_event_link"));
                $day_link = '<a title="'.$event_title.'" class="'.$css.'" href="'.$this->get_Href($this->para["show_in"],$year."-".$month,"&amp;show_event=".$year."-".$month."-".$day).'"'.$onclick.'>'.$event_link.'</a>';
                if($this->para["func"] == "month")
                    $day_link = '<div class="'.$css.'"'.$title.'>'.$day.$day_link.'</div>';
            } else
                $day_link = '<div class="'.$css.'"'.$title.'>'.$day.'</div>';
        } else
            $day_link = '<div class="cal-day-hidden">'.$day.'</div>';
        return $day_link;
    }

    private function help_title_navi($year,$month) {

        $title = $year;
        if($this->in_year)
            $title = $this->lang["month"][($month * 1)];
        elseif($this->para["func"] != "year")
            $title = $this->lang[$this->para["func"]][($month * 1)]." ".$year;

        $navi = array("","","","");
        if($this->para["show_nav"])
            $navi = $this->help_get_Prev_Next_Nav($year,$month);

       $html = '<div>'
            .'<table width="100%" cellspacing="0" border="0" cellpadding="0">'
                .'<tr>'
                    .(($this->para["show_nav"] and !$this->in_year) ? '<td width="1%">'.$navi[0].'</td>' : "")
                    .(($this->para["show_nav"] and !$this->in_year and $this->para["func"] != "year") ? '<td width="1%">'.$navi[2].'</td>' : "")
                    .'<td><div class="cal-navi-title">'.$title.'</div></td>'
                    .(($this->para["show_nav"] and !$this->in_year and $this->para["func"] != "year") ? '<td width="1%">'.$navi[3].'</td>' : "")
                    .(($this->para["show_nav"] and !$this->in_year) ? '<td width="1%">'.$navi[1].'</td>' : "")
                .'</tr>'
            .'</table>'
        .'</div>';
        return $html;
    }

    private function help_get_Prev_Next_Nav($year,$month,$nav = false) {
        $tmp = array("","","","");

        if(!$nav and !is_array($nav) and count($nav) != 4)
            $nav = array("&lt;&lt;","&gt;&gt;","&lt;","&gt;");

        $onclick = ' onclick="showPrevNext(this,\'&amp;cols='.$this->para["cols"]
                                        .'&amp;db='.$this->db
                                        .'&amp;func='.$this->para["func"]
                                        .'&amp;show_in='.$this->para["show_in"]
                                        .'&amp;show_weeknr='.$this->para["show_weeknr"]
                        .'\',\'.cal-box\');return false;"';

        global $CatPage;
        # year
        $prev_date = ($year - 1)."-".$month;
        $next_date = ($year + 1)."-".$month;
        $tmp[0] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,'date='.$prev_date).'"'.$onclick.'>'.$nav[0].'</a>';
        $tmp[1] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,'date='.$next_date).'"'.$onclick.'>'.$nav[1].'</a>';
        # month
        $prev_date = ($month - 1 < 1 ? ($year - 1)."-12" : $year."-".($month - 1));
        $next_date = ($month + 1 > 12 ? ($year + 1)."-1" : $year."-".($month + 1));
        $tmp[2] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,'date='.$prev_date).'"'.$onclick.'>'.$nav[2].'</a>';
        $tmp[3] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,'date='.$next_date).'"'.$onclick.'>'.$nav[3].'</a>';
        return $tmp;
    }

    function template_list() {
        $events = $this->get_From_To_Date_Cols($this->para["from"],$this->para["to"]);

        $html = '<table class="cal-list" cellspacing="0" border="0" cellpadding="0">'
            .'<thead>';

        if(count($events) > 0) {
            $date_format_title = $this->language->getLanguageValue("cal_date_format_title");
            $last = end($events);
            reset($events);
            $first = $events[key($events)];
            $html .= '<tr>'
                    .'<th colspan="3">'.$this->language->getLanguageValue("cal_list_title",$this->format_Date($first,$date_format_title),$this->format_Date($last,$date_format_title)).'</th>'
                .'</tr><tr>'
                    .'<th style="width:1%;">'.$this->language->getLanguageValue("cal_date").'</th><th>'.$this->language->getLanguageValue("cal_title").'</th><th>'.$this->language->getLanguageValue("cal_ort").'</th>'
                .'</tr>';
        } else
            $html .= '<tr><th colspan="3">'.$this->language->getLanguageValue("cal_events_error").'</th></tr>';

        $html .= '</thead>'
                .'<tfoot>'
                    .'<tr>'
                        .'<td colspan="3">'.$this->language->getLanguageValue("cal_color")
                            .'<span class="cal-legend cal-color1">&nbsp;</span>'.$this->language->getLanguageValue("cal_color1")
                            .'<span class="cal-legend cal-color2">&nbsp;</span>'.$this->language->getLanguageValue("cal_color2")
                            .'<span class="cal-legend cal-color3">&nbsp;</span>'.$this->language->getLanguageValue("cal_color3")
                        .'</td>'
                    .'</tr>'
                .'</tfoot>';

        if(count($events) > 0) {
            $date_format_liste = $this->language->getLanguageValue("cal_date_format_liste");
            $string = '<tr class="{CAL_COLOR}">'
                        .'<td class="cal-nowrap">{CAL_DATE}</td>'
                        .'<td>{CAL_TITLE}</td><td>{CAL_ORT}</td>'
                    .'</tr>';
            $tr_month = "";
            $onclick = ' onclick="calDialog(this,\'&amp;db='.$this->db.'&amp;func=events\');return false;"';
            $html .= '<tbody>';
            $archiv = getRequestValue('archiv','get');
#            $archiv = false;
#            if(strlen($this->para["date"]) > 3)
#                $archiv = substr($this->para["date"],0,4);
            foreach($events as $pos => $date) {
                if($archiv !== false and $archiv != substr($date,0,4))
                    continue;

                if($tr_month != substr($date,0,7)) {
                    $tr_month = substr($date,0,7);
                    $html .= '<tr><td colspan="3" class="cal-list-title-month">'.$this->lang['month'][(substr($date,5,2) * 1)]." ".substr($date,0,4).'</td></tr>';
                }

                $link = '<a title="'.$this->language->getLanguageValue("cal_event_title")." ".$this->format_Date($date,$date_format_title).'" href="'.$this->get_Href($this->para["show_in"],substr($date,0,7),"&amp;show_event=".$date).'"'.$onclick.'>'."{CAL_DATE}".'</a>';

                $tmp = str_replace("{CAL_DATE}",$link,$string);

                $html .= str_replace($this->datenCols,$this->get_Daten_Pos($pos,$date_format_liste),$tmp);
            }
            $html .= '</tbody>';
        }
        $html .= '</table>';

        return $html;
    }

    function template_list_archiv() {
        global $CatPage;
        $html = "";
        $html .= '<table class="cal-list-archiv" cellspacing="0" border="0" cellpadding="0">';
        $html .= '<thead><tr><th>'.$this->language->getLanguageValue("cal_archive").'</th></tr></thead><tbody>';
        $link = array();
        $art = "archiv=";
        $archiv = getRequestValue('archiv','get');
#        $art = "date=";
#        $archiv = substr($this->para["date"],0,4);
        foreach($this->get_From_To_Date_Cols() as $pos => $date) {
            $year = substr($date,0,4);
            $css = "";
            if($archiv == $year)
                $css = ' class="cal-event-active"';
            $link[$year] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,$art.$year).'"'.$css.'>'.$year.'</a>';
        }
        if(count($link) < 0)
            return "";
        $html .= '<tr><td>'.implode('</td></tr><tr><td>',$link).'</td></tr>';

        $html .= '</tbody></table>';
        return $html;
    }

    function template_events() {
        if(!$this->para["show_event"] or strlen($this->para["show_event"]) < 8)
            return "";

        $string = '<tr class="{CAL_COLOR}">'
                    .'<td>'.$this->language->getLanguageValue("cal_title").'</td>'
                    .'<td>{CAL_TITLE}</td>'
                .'</tr><tr class="{CAL_COLOR}">'
                    .'<td>'.$this->language->getLanguageValue("cal_inhalt").'</td>'
                    .'<td>{CAL_TEXT}</td>'
                .'</tr><tr class="{CAL_COLOR}">'
                    .'<td>'.$this->language->getLanguageValue("cal_ort").'</td>'
                    .'<td>{CAL_ORT}</td>'
                .'</tr><tr class="{CAL_COLOR}">'
                    .'<td class="cal-events-first">'.$this->language->getLanguageValue("cal_date_event").'</td>'
                    .'<td>{CAL_DATE}</td>'
                .'</tr>';

        $events = array();
        $date_format_title = $this->language->getLanguageValue("cal_date_format_title");
        $date_format_event = $this->language->getLanguageValue("cal_date_format_event");
        foreach($this->get_From_To_Date_Cols($this->para["show_event"],$this->para["show_event"]) as $pos => $date) {
            $events[] = str_replace($this->datenCols,$this->get_Daten_Date($date,$date_format_event),$string);
        }
        $html = "";
        if(count($events) > 0) {
            $html .= '<table class="cal-events" cellspacing="0" border="0" cellpadding="0">';
            if(!$this->is_ajax) {
                $html .= '<thead>'
                    .'<tr>'
                        .'<th colspan="2">'
                            .(count($html) > 1  ? $this->language->getLanguageValue("cal_events_title") : $this->language->getLanguageValue("cal_event_title"))
                            ." "
                            .$this->format_Date($this->make_Proper_Date($this->para["show_event"]),$date_format_title)
                        .'</th>'
                    .'</tr>'
                .'</thead>';
            }
            $html .= '<tbody>'
                    .implode('<tr><td colspan="2"></td></tr>',$events)
                    .'<tr>'
                        .'<td colspan="2">'.$this->language->getLanguageValue("cal_color")
                            .'<span class="cal-legend cal-color1">&nbsp;</span>'.$this->language->getLanguageValue("cal_color1")
                            .'<span class="cal-legend cal-color2">&nbsp;</span>'.$this->language->getLanguageValue("cal_color2")
                            .'<span class="cal-legend cal-color3">&nbsp;</span>'.$this->language->getLanguageValue("cal_color3")
                        .'</td>'
                    .'</tr>'
                .'</tbody>'
            .'</table>';
            return $html;
        }
        return $this->language->getLanguageValue("cal_events_error");
    }

    function template_month($year = false,$month = false) {
        if($year === false and $month === false)
            list($year,$month,,) = explode("-",$this->make_Proper_Date($this->para["date"]));

        $nav = "month";
        $lang_day = "day";
        if($this->para["func"] == "month_small" or $this->para["func"] == "year") {
            $nav = "month_small";
            $lang_day = "day_small";
        }

        $html = '<table cellspacing="0" border="0" cellpadding="0" class="cal-'.$nav.($this->in_year ? "" : " cal-box").'">'
            .'<tbody>';

        $html .= '<tr>'
                .'<td colspan="'.($this->para["show_weeknr"] ? "8" : "7").'" class="cal-td-nav">'
            .$this->help_title_navi($year,$month).'</td>'
            .'</tr>';

        $html .= '<tr>';
        if($this->para["show_weeknr"])
            $html .= '<th class="cal-td cal-title-week-nr"><div>'.$this->lang['week_nr_title'].'</div></th>';
        foreach($this->lang['week_days'] as $day_lang) {
            $html .= '<th style="width:14.2857142857%;" class="cal-td"><div>'.$this->lang[$lang_day][$day_lang].'</div></th>';
        }
        $html .= '</tr>';
        foreach($this->get_WeeksDays($year,$month) as $week_nr => $days) {
            $html .= '<tr>';
            if($this->para["show_weeknr"])
                $html .= '<td class="cal-td cal-week-nr"><div>'.$week_nr.'</div></td>';
            foreach($days as $day => $is_show) {
               $html .= '<td style="width:14.2857142857%;" class="cal-td">'.$this->help_get_Day($year,$month,$day,$is_show).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>'
            .'</table>';
        return $html;
    }

    function template_month_small($year = false,$month = false) {
        return $this->template_month($year,$month);
    }

    function template_year() {
        list($year,$month,,,) = explode("-",$this->make_Proper_Date($this->para["date"]));
        $html = '<table cellspacing="0" border="0" cellpadding="0" class="cal-year cal-box">'
            .'<tbody>';

        $html .= '<tr>'
                .'<td colspan="'.$this->para["cols"].'" class="cal-td-nav">'
                    .$this->help_title_navi($year,$month)
                .'</td>'
            .'</tr>';

        $this->in_year = true;
        for($month = 1; $month <= 12; $month++) {
            $first = "";
            if($month % $this->para["cols"] == 1) {
                $first = " cal-year-td-first";
                $html .= "<tr>";
            }
            $html .= '<td class="cal-year-td'.$first.'">'.$this->template_month($year,$month).'</td>';
            if($month % $this->para["cols"] == 0)
                $html .= "</tr>";
        }
        $this->in_year = false;
        $html .= '</tbody>'
            .'</table>';
        return $html;
    }

    function template_admin_event($daten) {
        $events = $this->adminTemplate;
        $replace = array();
        foreach($this->datenCols as $i => $search) {
            if($i === 4) {
                $this->datenColAdmin[$i] = str_replace(' selected="selected"',"",$this->datenColAdmin[$i]);
                $replace[$i] = str_replace('value="'.$daten[$i].'"','value="'.$daten[$i].'" selected="selected"',$this->datenColAdmin[$i]);
                $events = str_replace('{COLOR}',$daten[$i],$events);
            } else
                $replace[$i] = str_replace($search,$daten[$i],$this->datenColAdmin[$i]);
        }
        return str_replace($this->datenCols,$replace,$events);
    }

    function template_admin_newevent() {
        $event = str_replace("{COLOR}","cal-admin-newevent",$this->adminTemplate);
        $event = str_replace($this->datenCols,$this->datenColAdmin,$event);
        return str_replace($this->datenCols,"",$event);
    }
}
?>