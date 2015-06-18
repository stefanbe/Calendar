<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

if(!is_dir(PLUGIN_DIR_REL."Calendar/dbs/")) {
    $error = mkdirMulti(PLUGIN_DIR_REL."Calendar/dbs/");
    if($error !== true)
        return returnMessage(false, $error);
}

require_once(PLUGIN_DIR_REL."Calendar/datenClass.php");
require_once(PLUGIN_DIR_REL."Calendar/dateClass.php");

class CalAdmin {
    private $dbs = array();
    private $db = false;
    private $patterns = array();
    private $pattern = false;
    private $tmpl_ob = false;
    private $calmessages;
    private $to_scroll = false;
    private $language;

    function CalAdmin($settings) {
        global $specialchars;
        global $ADMIN_CONF;
        $this->language = new Language(PLUGIN_DIR_REL."Calendar/lang/admin_".$ADMIN_CONF->get("language").".txt");
        foreach(scandir(PLUGIN_DIR_REL."Calendar/dbs/") as $file) {
            if($file[0] != "." and strpos($file,"_db.php") !== false) {
                $tmp = substr($file,0,-7);
                if($settings->keyExists($tmp)
                        and is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/pattern/'.$settings->get($tmp).'.php'))
                    $this->dbs[] = $tmp;
            }
        }
        foreach(scandir(PLUGIN_DIR_REL."Calendar/pattern/") as $file) {
            if($file[0] != "." and strpos($file,".php") !== false) {
                $tmp = substr($file,0,-4);
                    if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/pattern/'.$tmp.'.php'))
                        $this->patterns[] = $tmp;
            }
        }
        foreach($settings->toArray() as $tmp_db => $tmp) {
            if($tmp_db == "active" or $tmp_db == "plugin_replace_catpagefile")
                continue;
            if(!is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$tmp_db.'_db.php'))
                $settings->delete($tmp_db);
        }
        sort($this->dbs);
        sort($this->patterns);

        $tmp = $specialchars->replaceSpecialChars(getRequestValue("cal","get"),false);
        if($tmp and $settings->keyExists($tmp)) {
            $this->db = $tmp;
            $this->pattern = $settings->get($tmp);
        } elseif(count($this->dbs) > 0 and $settings->keyExists($this->dbs[0])) {
            $this->db = $this->dbs[0];
            $this->pattern = $settings->get($this->dbs[0]);
        } elseif(count($this->patterns) > 0)
            $this->pattern = $this->patterns[0];

        if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/pattern/'.$this->pattern.'.php')) {
            require_once(PLUGIN_DIR_REL."Calendar/pattern/".$this->pattern.".php");
            $this->tmpl_ob = new $this->pattern();
            if($this->db) {
                $this->tmpl_ob->CalDaten_init($this->db);
                if(method_exists($this->tmpl_ob,'admin_init'))
                    $this->tmpl_ob->admin_init();
            }
        } else {
            return returnMessage(false, "No Pattern Find");
        }
    }

    function getAdminContent() {
        # post anfrage
        if(false !== getRequestValue('ispost',"post")) {
            # es gibt nee miteilung
            if(strlen($this->calmessages) > 10)
                $_SESSION['calmessages'] = $this->calmessages;
            if($this->to_scroll)
                $_SESSION['caltoscroll'] = $this->to_scroll;
            $curent_db = '';
            if($this->db)
                $curent_db = '&cal='.$this->db;
            $url = $_SERVER['HTTP_HOST'].str_replace("&amp;","&",PLUGINADMIN_GET_URL).$curent_db;
            # damit beim browserreload nicht wieder die daten gesendet werden senden wir eine get anfrage
            header("Location: http://$url");
            exit;
        }
        # es gab nee miteilung jetzt ausgeben da keine post anfrage
        if(isset($_SESSION['calmessages']) and strlen($_SESSION['calmessages']) > 10) {
            global $message;
            $message .= $_SESSION['calmessages'];
            unset($_SESSION['calmessages']);
        }
        if(isset($_SESSION['caltoscroll']) and $_SESSION['caltoscroll']) {
            $this->to_scroll = $_SESSION['caltoscroll'];
            unset($_SESSION['caltoscroll']);
        }

        $this->insertHead();
        $html = '<div class="ui-widget-content ui-corner-all ui-state-highlight" style="margin:.2em;margin-right:1.2em;padding:.2em .4em">'
                .$this->Calendar_makeAdminNewDB()
                .$this->Calendar_makeAdminDelDB()
                .'</div>';
        if($this->db) {
            $html .= '<div class="ui-tabs ui-widget ui-widget-content ui-corner-all mo-ui-tabs" style="position:relative;margin-right:1em;">'
                .$this->Calendar_makeAdminSubMenu()
                .'<div class="plugins mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">'
                .$this->Calendar_makeAdminNewEvent()
                .$this->Calendar_makeAdminEvents()
                .'</div>'
                .'</div>';
        }
        return $html;
    }

    function Calendar_makeAdminNewDB() {
        $html = '<form name="newdb" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
            .'<input type="hidden" name="ispost" value="true" />'
            .$this->language->getLanguageValue("new_text",'<input type="text" name="newdb" value="" />')
            .'<select name="newpattern">';
        foreach($this->patterns as $pattern) {
            $html .= '<option value="'.$pattern.'">'.$pattern.'</option>';
        }
        $html .= '</select>'
            .' <input type="submit" value="'.$this->language->getLanguageHtml("new_button").'" />'
            .'</form>';
        return $html;
    }

    function Calendar_makeAdminDelDB() {
        return '<form name="deldb" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
            .'<input type="hidden" name="ispost" value="true" />'
            .'<input type="hidden" name="deldb" value="'.$this->db.'" />'
            .'<input style="float:right" type="submit" value="'.$this->language->getLanguageHtml("delete_button").'" />'
            .'</form><br style="clear:both" />';
    }

    function Calendar_makeAdminSubMenu() {
        global $specialchars;
        $submenu = '<ul class="mo-menu-tabs ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';
        foreach($this->dbs as $subnav) {
            $cssaktiv = " mo-ui-state-hover";
            if($this->db == $subnav)
                $cssaktiv = " ui-tabs-selected ui-state-active";
            $submenu .= '<li class="ui-state-default ui-corner-top'.$cssaktiv.'">'.'<a href="'.PLUGINADMIN_GET_URL.'&amp;cal='.$subnav.'"><b>'.$specialchars->rebuildSpecialChars($subnav, false, false).'</b></a>'.'</li>';
        }
        $submenu .= '</ul>';
        return $submenu;
    }

    function Calendar_makeAdminNewEvent() {
        # neues event
        return '<ul class="mo-ul">'
            .'<li class="mo-li ui-widget-content ui-corner-all">'
                .'<form name="event-form-new" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
                    .'<input type="hidden" name="ispost" value="true" />'
                    .'<div class="align-right ui-state-default ui-corner-top mo-li-head-tag-no-ul mo-li-head-tag mo-tag-height-from-icon mo-middle">'
                        .'<span class="mo-bold" style="float:left">'."Neuer Eintrag".'</span>'
                        .'<button type="submit" name="admin_event_new" value="true" class="ca-admin-button mo-icons-icon mo-icons-save">&nbsp;</button>'
                    .'</div>'
                    .'<div class="ui-widget-content ui-corner-all ui-state-highlight" style="margin:.4em;">'
                        .$this->tmpl_ob->template_admin_newevent()
                    .'</div>'
                .'</form>'
            .'</li>'
        .'</ul>';
    }

    function Calendar_makeAdminEvents() {
        # alle events
        $cols = $this->tmpl_ob->get_Col(0);
        if(count($cols) < 1)
            return NULL;
        $delete_inputs = "";

        $html = '<ul class="mo-ul ev-search">';
        foreach($cols as $pos => $date) {
            $scroll = "";
            if($this->to_scroll === $date)
                $scroll = " to-scroll";
            $html .= '<li class="mo-li ui-widget-content ui-corner-all ev-search-item'.$scroll.'">'
                .'<span style="display:none;" class="ev-search-name">'.$date.'</span>'
                .'<form name="event-form-'.$pos.'" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
                    .'<input type="hidden" name="ispost" value="true" />'
                    .'<div class="align-right ui-state-default ui-corner-top ui-helper-clearfix mo-li-head-tag-no-ul mo-li-head-tag mo-tag-height-from-icon mo-middle">'
                        .'<button type="submit" name="admin_event_save" value="'.$date.'" class="ca-admin-button mo-icons-icon mo-icons-save">&nbsp;</button>'
                        .'<button type="submit" name="admin_event_delete_button" value="'.$date.'" class="ca-admin-button mo-icons-icon mo-icons-delete">&nbsp;</button>'
                        .'<input type="checkbox" class="mo-checkbox js-event-del" name="admin_event_delete['.$pos.']" value="'.$date.'" />'
                    .'</div>'
                    .'<div class="ui-widget-content ui-corner-all" style="margin:.4em;">'
                        .$this->tmpl_ob->template_admin_event(str_replace("<br />","\n",$this->tmpl_ob->get_Daten_Pos($pos)))
                    .'</div>'
                .'</form>'
            .'</li>';
            $delete_inputs .= '<input type="hidden" name="event['.$date.']" value="false" />';
        }
        $html .= '</ul>';
        # events löschen wird mit jq ins filter div verschoben
        $html .= '<form name="event-form-delete" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
            .'<input type="hidden" name="ispost" value="true" />'
            .'<input type="hidden" name="admin_event_delete_all" value="true" />'
            .$delete_inputs
            .'<input style="float:right" type="checkbox" class="mo-checkbox" id="cal-select-all" />'
            .'<button style="float:right" type="submit" value="true" name="admin_event_delete_button" class="ca-admin-button mo-icons-icon mo-icons-delete">&nbsp;</button>'# button submit
        .'</form>';
/*
        $html .= '<form name="event-form-delete-old" action="'.PLUGINADMIN_GET_URL.'&amp;cal='.$this->db.'" method="post">'
            .'<input type="hidden" name="ispost" value="true" />'
            .'<input type="hidden" name="admin_event_delete_old_events" value="true" />'
            .'<input style="float:right;margin-right:2em;" type="submit" value="'.$this->language->getLanguageHtml("delete_old_button",date("Y-m-d")).'" />'
        .'</form>';
*/
        return $html;
    }

    function Calendar_AdminDel($event) {
        foreach($event as $date => $del) {
            if($del == "true") {
                if(!$this->tmpl_ob->deleteEvent($date))
                    return returnMessage(false,$this->language->getLanguageValue("delete_events_error",$date));
            }
        }
        return NULL;
    }

    function Calendar_AdminSave($event,$save,$new) {
        $save_daten = array();
        $pos = $save;
        if($new)
            $pos = "new";
        foreach($this->tmpl_ob->datenCols as $index) {
            $index = substr($index,1,-1);
            if(array_key_exists($index, $event))
                $save_daten[$pos][] = str_replace(array("\r\n","\r","\n"),"<br />",$event[$index]);
            else
                $save_daten[$pos][] = false;
        }
        if(!$this->tmpl_ob->saveEvent($save_daten))
            return returnMessage(false, $this->language->getLanguageValue("save_event_error",$this->tmpl_ob->db."_db.php"));
        $this->to_scroll = $save_daten[$pos][0];
        return NULL;
    }

    function insertHead() {
        global $PLUGIN_ADMIN_ADD_HEAD;
        $PLUGIN_ADMIN_ADD_HEAD[] = '<link type="text/css" rel="stylesheet" href="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/admin/admin_plugin.css" />';

        $PLUGIN_ADMIN_ADD_HEAD[] = '<link type="text/css" rel="stylesheet" href="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/pattern/'.$this->pattern.'.css" />';

        $PLUGIN_ADMIN_ADD_HEAD[] = '<link type="text/css" rel="stylesheet" href="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/addons/timepicker/jquery-ui-timepicker-addon.css" />';
        $PLUGIN_ADMIN_ADD_HEAD[] = '<script type="text/javascript" src="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/addons/timepicker/jquery-ui-timepicker-addon.js"></script>';
        $PLUGIN_ADMIN_ADD_HEAD[] = '<script type="text/javascript">/*<![CDATA[*/'
            .'var mo_date_timepicker = {
                closeText: "'.$this->tmpl_ob->lang['timepicker']['closeText'].'",
                prevText: "'.$this->tmpl_ob->lang['timepicker']['prevText'].'",
                nextText: "'.$this->tmpl_ob->lang['timepicker']['nextText'].'",
                monthNames: ["'.implode('","',$this->tmpl_ob->lang["month"]).'"],
                monthNamesShort: ["'.implode('","',$this->tmpl_ob->lang["month_small"]).'"],
                dayNames: ["'.implode('","',$this->tmpl_ob->lang["day"]).'"],
                dayNamesShort: ["'.implode('","',$this->tmpl_ob->lang["day_small"]).'"],
                dayNamesMin: ["'.implode('","',$this->tmpl_ob->lang["day_small"]).'"],
                weekHeader: "'.$this->tmpl_ob->lang['timepicker']['weekHeader'].'",
                firstDay: '.$this->tmpl_ob->lang['timepicker']['firstDay'].',
                isRTL: '.$this->tmpl_ob->lang['timepicker']['isRTL'].',
                timeText: "'.$this->tmpl_ob->lang['timepicker']['timeText'].'",
                hourText: "'.$this->tmpl_ob->lang['timepicker']['hourText'].'",
                minuteText: "'.$this->tmpl_ob->lang['timepicker']['minuteText'].'",
                secondText: "'.$this->tmpl_ob->lang['timepicker']['secondText'].'"'
            .'};'
        .'/*]]>*/</script>';

        $PLUGIN_ADMIN_ADD_HEAD[] = '<script type="text/javascript">/*<![CDATA[*/'
                            .'var cal_filter_text = "'."Datums ".getLanguageValue("filter_text").'";'
                            .'var cal_error_date_empty = "'.$this->language->getLanguageValue("error_date_empty").'";'
                            .'var cal_error_date_exists = "'.$this->language->getLanguageValue("error_date_exists").'";'
                            .'var cal_error_del_no_selectet = "'.$this->language->getLanguageValue("error_del_no_selectet").'";'
                            .'var del_db_title = "'.$this->language->getLanguageValue("del_db_title").'";'
                            .'var del_event_title = "'.$this->language->getLanguageValue("del_event_title").'";'
                            .'/*]]>*/</script>';

        if($this->db)
            $PLUGIN_ADMIN_ADD_HEAD[] = '<script type="text/javascript" src="'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/admin/cal_admin.js"></script>';
    }

    function isPost($settings) {
        global $specialchars;

        $newpattern = getRequestValue("newpattern","post");
        $newdb = getRequestValue("newdb","post");
        if($newpattern !== false or $newdb !== false) {
            if($newdb == "active") {
                $this->calmessages .= returnMessage(false, $this->language->getLanguageValue("new_db_error_active"));
                return;
            }
            $newdb = $specialchars->replaceSpecialChars($newdb,false);
            if(!is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$newdb."_db.php")) {
                global $page_protect;
                $new = $page_protect.serialize(array());
                $settings->set($newdb,$newpattern);
                mo_file_put_contents(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$newdb."_db.php",$new);
                $this->db = $newdb;
            } else {
                if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$newdb."_db.php") and !$settings->keyExists($newdb)) {
                    $settings->set($newdb,$newpattern);
                    $this->db = $newdb;
                } else
                    $this->calmessages .= returnMessage(false, $this->language->getLanguageValue("new_db_error"));
            }
        }

        $deldb = getRequestValue("deldb","post");
        if($deldb !== false) {
            $deldb = $specialchars->replaceSpecialChars($deldb,false);
            if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$deldb."_db.php")) {
                $settings->delete($deldb);
                unlink(BASE_DIR.PLUGIN_DIR_NAME.'/Calendar/dbs/'.$deldb."_db.php");
            } else
                $this->calmessages .= returnMessage(false, $this->language->getLanguageValue("del_db_error"));
        }

        $delete = getRequestValue("admin_event_delete_all","post",false);
        $save = getRequestValue("admin_event_save","post",false);
        $new = getRequestValue("admin_event_new","post",false);

        if($delete !== false or $save !== false or $new !== false) {
            $event = getRequestValue("event","post",false);
            if(is_array($event)) {
                if($delete !== false)
                    if(strlen($delete) == 16)
                        $event = array($delete => "true");
                    $this->calmessages .= $this->Calendar_AdminDel($event);
                if($save !== false or $new !== false)
                    $this->calmessages .= $this->Calendar_AdminSave($event,$save,$new);
            } else
                $this->calmessages .= returnMessage(false, $this->language->getLanguageValue("post_event_data_error"));
        }

        $delete_old_events = getRequestValue("admin_event_delete_old_events","post",false);
        if($delete_old_events !== false) {
            if(false === $this->tmpl_ob->deleteOldEvents(date("Y-m-d")."-00-00"))
                $this->calmessages .= returnMessage(false,$this->language->getLanguageValue("save_event_error",$this->db));
        }

    }
} # end class

$CalAdmin = new CalAdmin($plugin->settings);
$CalAdmin->isPost($plugin->settings);
return $CalAdmin->getAdminContent();
?>
