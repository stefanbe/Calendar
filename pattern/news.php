<?php if(!defined('IS_CMS')) die();

class news extends CalDate {

    # user daten {CAL_DATE} ist pflicht und muss als erstes im array sein
    public $datenCols = array("{CAL_DATE}","{CAL_TITEL}","{CAL_TEXT}","{CAL_FILE}");
    private $datenColAdmin = array();
    private $adminTemplate = NULL;

    function admin_init() {
        # das input feld für das datum muss name="event[CAL_DATE]" sein
        # alle name= müssen name="event[$this->datenCols]" ohne {} benant werden
        $this->datenColAdmin[] = '<input type="text" name="event[CAL_DATE]" value="{CAL_DATE}" size="16" maxlength="16" />';

        $this->datenColAdmin[] = '<input class="news-admin-input" type="text" name="event[CAL_TITEL]" value="{CAL_TITEL}" size="100" maxlength="255" />';

        $this->datenColAdmin[] = '<textarea name="event[CAL_TEXT]" cols="5" rows="6">{CAL_TEXT}</textarea>';

        global $specialchars;

        $tmp_array = getDirAsArray(CONTENT_DIR_REL,"dir","natcasesort");
        $select = '';
        foreach($tmp_array as $cat) {
            $select .= '<optgroup label="'.$specialchars->rebuildSpecialChars($cat, false, false).'">';
            foreach(getDirAsArray(CONTENT_DIR_REL.$cat,array(EXT_PAGE,EXT_HIDDEN),"natcasesort") as $page) {
                $page = substr($page,0,-EXT_LENGTH);
                $select .= '<option value="'.FILE_START.$cat.':'.$page.FILE_END.'">'.$specialchars->rebuildSpecialChars($page, false, false).'</option>';
            }
            $select .= '</optgroup>';
        }
        $this->datenColAdmin[] = '<select class="catpageselect" name="event[CAL_FILE]">'
            .$select
            .'</select>';

        $this->adminTemplate = '<table class="news-admin-event{ADMIN}" cellspacing="0" border="0" cellpadding="0"><tbody>'
            .'<tr>'
                .'<td style="width:1%;">'.$this->language->getLanguageValue("news_date").'</td>'
                .'<td style="width:1%;">{CAL_DATE}</td>'
                .'<td style="width:1%;">'.$this->language->getLanguageValue("news_file").'</td>'
                .'<td><div class="news-admin-select">{CAL_FILE}</div></td>'
            .'</tr><tr>'
                .'<td>'.$this->language->getLanguageValue("news_title").'</td>'
                .'<td colspan="3">{CAL_TITEL}</td>'
            .'</tr><tr>'
                .'<td>'.$this->language->getLanguageValue("news_anleser").'</td>'
                .'<td colspan="3">{CAL_TEXT}</td>'
            .'</tr>'
        .'</tbody></table>';
    }

    function template_list() {
        global $CatPage, $specialchars;

        $string = '<tr>'
                    .'<td class="news-date">{CAL_DATE}</td>'
                    .'<td>{LINK}</td>'
                .'</tr><tr>'
                    .'<td colspan="2"><hr></td>'
                .'</tr>';
        $html = "";
        $date_format = $this->language->getLanguageValue("news_date_format_liste");
        foreach($this->get_From_To_Date_Cols($this->para["from"],$this->para["to"]) as $pos => $date) {
            $daten = $this->get_Daten_Pos($pos,$date_format);
            if(strlen($daten[1]) > 1)
                $daten[1] = '<span class="news-link-title">'.$daten[1].'</span><br />';
            if(strlen($daten[2]) > 1)
                $daten[2] = '<span class="news-link-text">'.$daten[2].'</span><br />';
            list($cat,$page) = $CatPage->split_CatPage_fromSyntax($daten[3]);
            $link = '<a href="'.$CatPage->get_Href($cat,$page).'">'."{CAL_TITEL}{CAL_TEXT}".'</a>';

            $string_tmp = str_replace("{LINK}",$link,$string);
            $html .= str_replace($this->datenCols,$daten,$string_tmp);
        }
        if(strlen($html) > 10)
            return '<table class="news-list" cellspacing="0" border="0" cellpadding="0"><tbody>'.$html.'</tbody></table>';
        return $this->language->getLanguageValue("news_event_error");
    }

    function template_list_small() {
        global $CatPage;

        $html = "";

        $date_format = $this->language->getLanguageValue("news_date_format_new");
        foreach($this->get_From_To_Date_Cols($this->para["from"],$this->para["to"]) as $pos => $date) {
            $daten = $this->get_Daten_Pos($pos,$date_format);
            list($cat,$page) = $CatPage->split_CatPage_fromSyntax($daten[3]);
            $html .= '<li><a href="'.$CatPage->get_Href($cat,$page).'"><span class="news-nowrap news-list-small-date">'.$daten[0].'</span><span class="news-list-small-news">'.$daten[1].'</span></a></li>';
        }
        if(strlen($html) < 10)
            $html = '<li>'.$this->language->getLanguageValue("news_events_error").'</li>';
        return '<table class="news-list-small" cellspacing="0" border="0" cellpadding="0"><thead><tr><th>'.$this->language->getLanguageValue("news_news").'</th></tr></thead><tbody><tr><td><ul>'.$html.'</ul></td></tr></tbody></table>';
    }

    function template_list_archiv() {
        global $CatPage;
        $html = "";
        $html .= '<table class="news-list-archiv" cellspacing="0" border="0" cellpadding="0">';
        $html .= '<thead><tr><th>'.$this->language->getLanguageValue("news_archive").'</th></tr></thead><tbody>';
        $link = array();
        foreach($this->get_From_To_Date_Cols() as $pos => $date) {
            $year = substr($date,0,4);
            $css = "";
            if(substr($this->para['from'],0,4) == $year)
                $css = ' class="news-active"';
            $link[$year] = '<a href="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST,'from='.$year.'&to='.$year).'"'.$css.'>'.$year.'</a>';
        }
        if(count($link) < 0)
            return "";
        $html .= '<tr><td>'.implode('</td></tr><tr><td>',$link).'</td></tr>';

        $html .= '</tbody></table>';
        return $html;
    }

    function template_admin_event($daten) {
        $replace = array();
        $this->datenColAdmin[3] = str_replace(' class="catpageselect"','',$this->datenColAdmin[3]);
        foreach($this->datenCols as $i => $search) {
            if($i === 3) {
                $this->datenColAdmin[$i] = str_replace(' selected="selected"',"",$this->datenColAdmin[$i]);
                $replace[$i] = str_replace('value="'.$daten[$i].'"','value="'.$daten[$i].'" selected="selected"',$this->datenColAdmin[$i]);
            } else
                $replace[$i] = str_replace($search,$daten[$i],$this->datenColAdmin[$i]);
        }
        $events = str_replace("{ADMIN}","",$this->adminTemplate);
        return str_replace($this->datenCols,$replace,$events);
    }

    function template_admin_newevent() {
        $event = str_replace("{ADMIN}"," news-admin-newevent mo-td-content-width",$this->adminTemplate);
        $event = str_replace($this->datenCols,$this->datenColAdmin,$event);
        return str_replace($this->datenCols,"",$event);
    }
}
?>