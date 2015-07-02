<?php if(!defined('IS_CMS')) die();

class CalDaten {
    private $daten;
    public $datenCols;
    public $lang;
    public $language;
    private $file;
    public $min_Y;
    public $max_Y;
    public $holiday;
    public $para;
    public $db;
    public $is_ajax;

    function __construct($pattern) {
        if(defined('IS_ADMIN') and IS_ADMIN) {
            global $ADMIN_CONF;
            $tmp = $ADMIN_CONF->get("language");
        } else {
            global $CMS_CONF;
            $tmp = $CMS_CONF->get("cmslanguage");
        }

        if(is_file(PLUGIN_DIR_REL."Calendar/lang/date_".$tmp.".php"))
            $this->lang = include(PLUGIN_DIR_REL."Calendar/lang/date_".$tmp.".php");
        elseif(is_file(PLUGIN_DIR_REL."Calendar/lang/date_deDE.php"))
            $this->lang = include(PLUGIN_DIR_REL."Calendar/lang/date_deDE.php");
        else
            die();

        $this->language = new Language(PLUGIN_DIR_REL."Calendar/pattern/".$pattern."/lang_".$tmp.".txt");
    }

    function CalDaten_init($db) {
        $this->db = $db;
        $this->file = PLUGIN_DIR_REL."Calendar/dbs/".$this->db."_db.php";

        $this->loadDaten();
    }

    function loadDaten() {
        if(!is_file($this->file)) {
            $this->daten = array();
            return true;
        }
        if(false === ($conf = @file_get_contents($this->file)))
            die("Fatal Error Can't read file: ".basename($this->file));
        global $page_protect_search;
        $conf = str_replace($page_protect_search,"",$conf);
        $conf = trim($conf);
        $conf = unserialize($conf);

        if(!is_array($conf))
            die("Fatal Error 2");

        $this->daten = $conf;

        unset($conf);
        $this->make_From_Daten_min_max_Year();
        return true;
    }

    private function saveDaten() {
        if(!defined('IS_ADMIN') or !IS_ADMIN)
            return false;
        $tmp = $this->daten;
        $this->daten = $this->sort_Col($this->get_Col(0));

        global $page_protect;
        $conf = $page_protect.serialize($this->daten);
        if(false === (@file_put_contents($this->file,$conf,LOCK_EX))) {
            $this->daten = $tmp;
            $this->make_From_Daten_min_max_Year();
            return false;
        }
        $this->make_From_Daten_min_max_Year();
        return true;
    }

    private function sort_Col($col_array) {
        sort($col_array);
        array_multisort($this->daten,$col_array, SORT_STRING);
        return array_reverse($this->daten,true);
    }

    # $daten muss ein array[pos] = array( event daten )
    # wenn pos = new neuer eintrag
    function saveEvent($event) {
        if(!defined('IS_ADMIN') or !IS_ADMIN)
            return false;
        $date = @key($event);
        if(!is_array($event[$date]))
            return false;
        $event[$date][0] = $this->make_Proper_Date($event[$date][0]);
        $tmp = $this->get_Col(0);
        # prüfen obs das datum schonn gibt
        if($date === "new" and !in_array($event[$date][0],$tmp)) {
            $this->daten[] = $event[$date];
            return $this->saveDaten();
        }
        $date = $this->make_Proper_Date($date);
        if($date !== "new" and in_array($date,$tmp)) {
            if(false === ($pos = array_search($date,$tmp)))
                return false;
            if($date != $event[$date][0] and in_array($date,$tmp) and !in_array($event[$date][0],$tmp)) {
                unset($this->daten[$pos]);
                $this->daten[] = $event[$date];
                return $this->saveDaten();
            # nur der inhalt hat sich geöndert
            } elseif($this->daten[$pos][0] == $event[$date][0]) {
                $this->daten[$pos] = $event[$date];
                return $this->saveDaten();
            }
        }
        return false;
    }

    function deleteEvent($date) {
        if(!defined('IS_ADMIN') or !IS_ADMIN)
            return false;
        if($date != $this->make_Proper_Date($date))
            return false;
        $tmp = $this->get_Col(0);
        if(false !== ($pos = array_search($date,$tmp))) {
            unset($this->daten[$pos]);
            return $this->saveDaten();
        }
    }

    function deleteOldEvents($date) {
        if(!defined('IS_ADMIN') or !IS_ADMIN)
            return false;
        if($date != $this->make_Proper_Date($date))
            return false;
        $del = false;
        foreach($this->daten as $pos => $daten) {
            if($date > $daten[0]) {
                $del = true;
                unset($this->daten[$pos]);
            }
        }
        if($del)
            return $this->saveDaten();
    }

    function get_Daten_Pos($pos = 0,$date_format = false) {
        $tmp = array_fill(0,(count($this->datenCols)),"");
        if(array_key_exists($pos, $this->daten)) {
            $tmp = $this->daten[$pos];
            if($date_format)
                $tmp[0] = $this->format_Date($tmp[0],$date_format);
        }
        return $tmp;
    }

    function get_Daten_Date($date,$date_format = false) {
        $tmp = array_fill(0,(count($this->datenCols)),"");
        if(false !== ($pos = array_search($date,$this->get_Col(0)))) {
            $tmp = $this->daten[$pos];
            if($date_format)
                $tmp[0] = $this->format_Date($tmp[0],$date_format);
        }
        return $tmp;
    }

    function format_Date($date,$date_format = "Y-m-d-H-i") {
        list($year,$month,$day,$hour,$minute) = explode("-",$date);
        $date = date($date_format,mktime(($hour * 1),($minute * 1),0,($month * 1),($day * 1),$year));
        if(strpos($date_format,"l") !== false)
            $date = strtr($date,$this->lang["l"]);
        if(strpos($date_format,"D") !== false)
            $date = strtr($date,$this->lang["D"]);
        if(strpos($date_format,"F") !== false)
            $date = strtr($date,$this->lang["F"]);
        if(strpos($date_format,"M") !== false)
            $date = strtr($date,$this->lang["M"]);
        return $date;
    }

    function get_Daten_Array() {
        if(is_array($this->daten))
            return $this->daten;
        return array();
    }

    function get_Col($col) {
        $cols = array();
        foreach($this->daten as $pos => $row) {
            if(array_key_exists($col, $row))
                $cols[$pos] = $row[$col];
        }
        return $cols;
    }

    function get_Col_Search($col,$search) {
        $cols = array();
        foreach($this->daten as $pos => $row) {
            if(array_key_exists($col, $row)) {
                if(strpos(strtolower($search),strtolower($row[$col])) !== false)
                    $cols[$pos] = $row[$col];
            }
        }
        return $cols;
    }

    function get_AllCol_Search($search,$noarray = true) {
        $cols = array();
        foreach($this->daten as $pos => $row) {
            foreach($row as $cellpos => $cell) {
                if(strpos(strtolower($search),strtolower($cell)) !== false) {
                    if($noarray) {
                        $cols[$pos] = $cell;
                        break;
                    } else
                        $cols[$pos][$cellpos] = $cell;
                }
            }
        }
        return $cols;
    }

    # die reihenfolge ist jahr monat tag stunde minute sekunde
    # trennzeichen können alles auser zahlen sein auch mehrere zeichen
    # monat, tag, stunde, minute, sekunde können 1stelig sein
    # wenn das jahr nicht 4stellig ist wird es zum actuellen jahrhunder addiert
    # Return ist immer: jahr-monat-tag-stunde-minute
    # alles was bei $date nicht angegeben wird wird mit dem actuelen Datum/Zeit ersetzt
    # auser $min_max ist "max" oder "min"
    # bei min wird das kleinste jahr aus den daten genommen und der rest von 01-01-00-00
    # bei max wird das gröste jahr aus den daten genommen und der rest von 12-maxTage-23-59
    function make_Proper_Date($date = "",$min_max = false) {
        $date = $this->make_Proper_DateStr($date);
        if($min_max == "min")
            $tmp = $this->min_Y."-01-01-00-00";
        elseif($min_max == "max") {
            $tmp = $date.substr($this->max_Y."-12",min(strlen($date),4));
            $tmp = $this->max_Y."-12-".date("t",strtotime($tmp))."-23-59";
        } else
            $tmp = date("Y-m-d-H-i");
        return $date.substr($tmp,strlen($date));
    }

    # macht aus allen 1steligen ein 0 davor
    # und macht das jahr 4steleig
    function make_Proper_DateStr($date = "") {
        if(strlen($date) >= 1) {
            # alles was keine zahl ist mit nur 1nem - ersetzen
            $date = preg_replace('/[^\d]+/','-',$date);
            $tmp = explode('-',$date);
            # ist das jahr ist nicht 4stelig zum actuellen jahrhundert addieren
            if(strlen($tmp[0]) < 4)
                $tmp[0] = sprintf("%-'04s",substr(date("Y"),0,2)) + $tmp[0];
            # überall wo 1stelige angaben sind mit führender 0 ersetzen
            $date = vsprintf(substr("%04d-%02d-%02d-%02d-%02d",0,((count($tmp) * 5) - 1)), $tmp);
        }
        return $date;
    }

    function make_MaxLen($maxlen = 3) {
        $reverse = false;
        if($maxlen < 0)
            $reverse = true;
        $maxlen = trim($maxlen,'-');
        $from_to = array("","");
        if(isset($this->daten[0]))
            $from_to[1] = $this->daten[key($this->daten)][0];
        foreach($this->daten as $event) {
            $maxlen--;
            if($maxlen <= 0) {
                $from_to[0] = $event[0];
                break;
            }
        }
        if($reverse)
            $from_to = array_reverse($from_to);
        return $from_to;
    }

    function make_Proper_FromTo($from_to = "") {
        if(!$from_to)
            return array($this->make_Proper_Date("","min"),$this->make_Proper_Date("","max"));

        $from_to = explode("|",$from_to);
        $from = trim($from_to[0]);
        $to = "";
        if(isset($from_to[1]))
            $to = trim($from_to[1]);

        return array($this->help_Proper_FromTo($from,"min"),$this->help_Proper_FromTo($to,"max"));
    }

    private function help_Proper_FromTo($str,$min_max) {
        if($str == "max" or $str == "min")
            return $this->make_Proper_Date("",$str);
        if(!$str or false === ($timestamp = strtotime($str)))
            return $this->make_Proper_Date("",$min_max);
        return date("Y-m-d-H-i",$timestamp);
    }

    function make_From_Daten_min_max_Year() {
        $this->min_Y = date("Y");
        $this->max_Y = $this->min_Y;
        if(count($this->daten) > 0) {
            $this->max_Y = substr($this->daten[(count($this->daten) - 1)][0],0,4);
            $this->min_Y = substr($this->daten[0][0],0,4);
        }
    }

    function get_From_To_Date_Cols($from = "",$to = "",$col = 0) {
        $cols = array();
        $from_search = $this->make_Proper_Date($from,"min");
        $to_search = $this->make_Proper_Date($to,"max");
        $revese = false;
        if($from > $to) {
            $revese = true;
            $from_search = $this->make_Proper_Date($to,"min");
            $to_search = $this->make_Proper_Date($from,"max");
        }
        if(strlen($from) < 1) $from = false;
        if(strlen($to) < 1) $to = false;
        foreach($this->daten as $pos => $row) {
            if($from and $from_search > $row[0])
                break;
            if($to and $to_search < $row[0])
                continue;
            $cols[$pos] = $row[$col];
        }
        if($revese)
            return array_reverse($cols, true);
        return $cols;
    }
}
?>