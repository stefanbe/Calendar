<?php if(!defined('IS_CMS')) die();

class Calendar extends Plugin {

    private $adminLang;

    function getContent($value) {
        global $lang, $CMS_CONF;
        $this->adminLang = new Language(PLUGIN_DIR_REL."Calendar/lang/admin_".$CMS_CONF->get("cmslanguage").".txt");

        $value_array = $this->makeUserParaArray($value);
        $pattern_function = false;
        if(isset($value_array[0]) and strpos($value_array[0],"admin_") === false) {
            $pattern_function = "template_".trim($value_array[0]);
        }

        global $specialchars;
        if(false !== ($get_ajax = getRequestValue('func','get'))
                and false !== ($db = getRequestValue('db','get'))
                and $this->settings->keyExists($specialchars->replaceSpecialChars($db,false))) {
            $db = $specialchars->replaceSpecialChars($db,false);
            $pattern = $this->settings->get($db);
            $get_ajax = "template_".$get_ajax;
        } elseif(isset($value_array["db"])
                and $this->settings->keyExists($specialchars->replaceSpecialChars($value_array["db"],false))) {
            $db = $specialchars->replaceSpecialChars($value_array["db"],false);
            $pattern = $this->settings->get($db);
        } else
            return $this->adminLang->getLanguageHtml("del_db_error");

        if(!is_file($this->PLUGIN_SELF_DIR."pattern/".$pattern.".php"))
            return $this->adminLang->getLanguageHtml("pattern_error");

        require_once($this->PLUGIN_SELF_DIR."datenClass.php");
        require_once($this->PLUGIN_SELF_DIR."dateClass.php");
        require_once($this->PLUGIN_SELF_DIR."pattern/".$pattern.".php");

        if(class_exists($pattern,false)) {
            if(($pattern_function and !in_array($pattern_function,get_class_methods($pattern)))
                    or ($get_ajax and !in_array($get_ajax,get_class_methods($pattern))))
                return $this->adminLang->getLanguageHtml("pattern_error");

            $tmpl_ob = new $pattern($pattern);
            $tmpl_ob->CalDaten_init($db);
            if(method_exists($tmpl_ob,'front_init'))
                $tmpl_ob->front_init();
            $tmpl_ob->CalDate_init($value_array);

            $tmpl_ob->is_ajax = false;
            global $syntax;
            if($get_ajax) {
                $tmpl_ob->is_ajax = true;
                echo $syntax->convertContent($tmpl_ob->$get_ajax(), false);
                exit;
            }
            if(is_file($this->PLUGIN_SELF_DIR.'pattern/'.$pattern.'.css')) {
                $syntax->insert_in_head('<style type="text/css"> @import "'.$this->PLUGIN_SELF_URL.'pattern/'.$pattern.'.css"; </style>');
            }
            return $tmpl_ob->$pattern_function();
        }
        return $this->adminLang->getLanguageHtml("pattern_error");
    }

    function getConfig() {
        if(IS_ADMIN and !$this->settings->get("plugin_replace_catpagefile")) {
            $this->settings->set("plugin_replace_catpagefile","dbs/*_db.php");
        }

        $config["--admin~~"] = array(
            "buttontext" => $this->adminLang->getLanguageHtml("database_button"),
            "description" => $this->adminLang->getLanguageValue("database_text"),
            "datei_admin" => "admin/index.php"
            );

        $config['--template~~'] = ''
        .'<div class="mo-in-li-l">'.$this->adminLang->getLanguageValue("strtotime_text").'</div>'
        .'<div class="mo-in-li-r"><input name="teststrtotime" type="button" value="'.$this->adminLang->getLanguageHtml("strtotime_button").'" onclick="dialog_test_strtotime(\'\')" /></div>'
        .'<script language="Javascript" type="text/javascript">/*<![CDATA[*/'
            .'function dialog_test_strtotime(para) {'
                .'$.get("'.URL_BASE.PLUGIN_DIR_NAME.'/Calendar/admin/test_strtotime.php"+para,'
                    .'function(data) {'
                        .'$("#message_strtotime").html(data);'
                        .'if(para.length < 1) {'
                            .'dialog_multi.dialog({'
                                .'title: "'.$this->adminLang->getLanguageValue("dialog_title").'",'
                                .'open: function(event, ui) {'
                                    .'dialog_multi.dialog().html("'.str_replace("/","\/",addslashes($this->adminLang->getLanguageValue("dialog_html"))).'");'
                                .'},'
                                .'buttons: [{'
                                    .'text: "'.$this->adminLang->getLanguageValue("dialog_button_test").'",'
                                    .'click: function() {'
                                        .'dialog_test_strtotime("?strtotime="+$(\'input[name="strtotime"]\').val());'
                                    .'}'
                                .'},{'
                                    .'text: "'.$this->adminLang->getLanguageValue("dialog_button_close").'",'
                                    .'click: function() { dialog_multi.dialog("close"); }'
                                .'}]});'
                                .'dialog_multi.dialog("open");'
                            .'}'
                .'},"html");'
            .'}'
            .'$(function() {'
                .'$(\'input[name="teststrtotime"]\').closest(".js-config").find(".js-save-plugin").eq(0).remove();'
            .'});'
        .'/*]]>*/</script>';

        return $config;
    }

    function getInfo() {
        global $ADMIN_CONF;
        $tmp = $ADMIN_CONF->get("language");
        $this->adminLang = new Language($this->PLUGIN_SELF_DIR."lang/admin_".$tmp.".txt");

        $help = $this->adminLang->getLanguageValue("info_text",$tmp,$this->PLUGIN_SELF_URL);

        foreach(getDirAsArray($this->PLUGIN_SELF_DIR."pattern/","dir") as $dir) {
            $file = false;
            if(is_file($this->PLUGIN_SELF_DIR."pattern/".$dir."/info_".$tmp.".html"))
                $file = $this->PLUGIN_SELF_DIR."pattern/".$dir."/info_".$tmp.".html";
            elseif(is_file($this->PLUGIN_SELF_DIR."pattern/".$dir."/info_deDE.html"))
                $file = $this->PLUGIN_SELF_DIR."pattern/".$dir."/info_deDE.html";
            if($file and false !== ($txt = @file_get_contents($file)))
                $help .= $this->adminLang->getLanguageValue("info_text_pattern",$dir,$txt);
        }

        $info = array(
            // Plugin-Name + Version
            "<b>Calendar</b> ".$this->adminLang->getLanguageValue("info_revision","9"),
            // moziloCMS-Version
            "2.0",
            // Kurzbeschreibung nur <span> und <br /> sind erlaubt
            $help,
            // Name des Autors
            "stefanbe",
            // Download-URL
            array("http://www.mozilo.de/forum/index.php?action=media","Templates und Plugins"),
            // Platzhalter für die Selectbox in der Editieransicht 
            array('{Calendar|}' => $this->adminLang->getLanguageValue("info_description"))
        );
        return $info;
    }
}

?>
