<?php
error_reporting(E_ALL);
function array_merge_recursive_distinct () {
  $arrays = func_get_args();
  $base = array_shift($arrays);
  if(!is_array($base)) $base = empty($base) ? array() : array($base);
  foreach($arrays as $append) {
    if(!is_array($append)) $append = array($append);
    foreach($append as $key => $value) {
      if(!array_key_exists($key, $base) and !is_numeric($key)) {
        $base[$key] = $append[$key];
        continue;
      }
      if(is_array($value) or is_array($base[$key])) {
        $base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
      } else if(is_numeric($key)) {
        if(!in_array($value, $base)) $base[] = $value;
      } else {
        $base[$key] = $value;
      }
    }
  }
  return $base;
}
class Converter {
    
    /*
     * 
     * The main converter class. Converts Citizens 1.x npc-profiles.yml to Citizens 2.x saves.yml
     * 
     * Version 2.0
     * 
     * Written by AgentKid
     * 
     */
    
    public $inputFile;
    public $outputFile;
    public $skippedArray=array();
    public $skipped=0;
    public $converted=0;
    public $ID;
    private $inputData;
    private $outputData;
    
    function __construct($inputFileLocation){
        $this->inputFile = $inputFileLocation;
        $this->ID = rand(1000000000000, 9999999999999);
        $fileHandle = fopen("/usr/share/nginx/www/citizens/converter/input/".$this->ID.".npc-profiles.yml", 'w');
        fwrite($fileHandle,file_get_contents($inputFileLocation));
        fclose($fileHandle);
    }
    function getYML(){
        $this->inputData = yaml_parse_file($this->inputFile);
        if($this->inputData==false){
            var_dump(file_get_contents($this->inputFile));
            var_dump($this->inputData);
            die('Invalid YML file! Debug:'.$this->ID);
        }
    }
    function convert($traderMode="none"){
        $npcNumber = 0;
        $yml = $this->inputData;
        $count = count($yml);
        $output = array();
        while($npcNumber!=$count){
            if(!isset($yml[$npcNumber]["basic"]["name"]) || !isset($yml[$npcNumber]["basic"]["location"])){
                $skip = true;
                $count = $count+1;
                $this->skipped = $this->skipped+1;
                array_push($this->skippedArray, $npcNumber);
            }
            $explodedLocation = explode(',',$yml[$npcNumber]["basic"]["location"]);
            if($skip!=true){
                if(isset($yml[$npcNumber]["basic"]["color"]) && $yml[$npcNumber]["basic"]["color"]!="f"){
                    $yml[$npcNumber]["basic"]["name"] = "§" . $yml[$npcNumber]["basic"]["color"] . $yml[$npcNumber]["basic"]["name"];
                }
                $push = array(
                    "npc" => array(
                        "This" => "line can be ignored/removed.",
                        $npcNumber => array(
                            "name" => $yml[$npcNumber]["basic"]["name"],
                            "traits" => array(
                                "age" => array(
                                    "age" => 0,
                                    "locked" => "true",
                                ),
                                "owner" => $yml[$npcNumber]["basic"]["owner"],
                                "lookclose" => array(
                                    'enabled' => $yml[$npcNumber]["basic"]["look-when-close"],
                                    'range' => '10.0',
                                    'realistic-looking' => "true"
                                ),
                                "location" => array(
                                    "world" => $explodedLocation[0],
                                    "x" => $explodedLocation[1],
                                    "y" => $explodedLocation[2],
                                    "z" => $explodedLocation[3],
                                    "yaw" => $explodedLocation[4],
                                    "pitch" => $explodedLocation[5],
                                ),
                                "type" => "PLAYER",
                                "spawned" => "true",
                                "text" => array(
                                    "talk-close" => $yml[$npcNumber]["basic"]["talk-when-close"],
                                    "random-talker" => "true",
                                )
                            )
                        )
                    )
                );
                $text = explode(';',$yml[$npcNumber]["basic"]["text"]);
                $textNumber = 0;
                $pushText = array();
                if($text[0]==""){ $textNumber=1; }
                while($textNumber!=count($text)){
                    $pushText = array(
                        "npc" => array(
                            $npcNumber => array(
                                "traits" => array(
                                    "text" => array(
                                        $textNumber => $text[$textNumber]
                                    )
                                )
                            )
                        )
                    );
                    $textNumber = $textNumber+1;
                    $push = array_merge_recursive_distinct($push, $pushText);
                }
                $waypointNumber = 0;
                while($waypointNumber!=count($yml[$npcNumber]["basic"]["waypoints"])){
                    $waypointInfo = explode(',',$yml[$npcNumber]["basic"]["waypoints"][$waypointNumber]["location"]);
                    $pointpush = array(
                        "npc" => array(
                            $npcNumber => array(
                                "traits" => array(
                                    "waypoints" => array(
                                        "waypoints" => array(
                                            "This" => "line can be ignored/removed.",
                                            $waypointNumber => array(
                                                "location" => array(
                                                    "world" => $waypointInfo[0],
                                                    "x" => $waypointInfo[1],
                                                    "y" => $waypointInfo[2],
                                                    "z" => $waypointInfo[3],
                                                    "yaw" => $waypointInfo[4],
                                                    "pitch" => $waypointInfo[5]
                                                ),
                                                "delay" => 0
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    );
                    $waypointNumber=$waypointNumber+1;
                    $push = array_merge_recursive_distinct($push, $pointpush);
                }
                $items = explode(",",$yml[$npcNumber]["basic"]["items"]);
                array_pop($items);
                $itemNumber=0;
                $handEmpty=true;
                foreach($items as $item){
                    $openitem = explode(":",$item);
                    switch($itemNumber){
                        case 0:
                            $type = "hand";
                            break;
                        case 1:
                            $type = "helmet";
                            break;
                        case 2:
                            $type = "chestplate";
                            break;
                        case 3:
                            $type = "leggings";
                            break;
                        case 4:
                            $type = "boots";
                            break;
                    }
                    if($openitem[0]!==0){
                        if($type='hand'){ $handEmpty=false; }
                        $itempush = array(
                            "npc" => array(
                                $npcNumber => array(
                                    "traits" => array(
                                        "equipment" => array(
                                            $type => array(
                                                "id" => $openitem[0],
                                                "amount" => "1"
                                            )
                                        )
                                    )
                                )
                            )
                        );
                        $push = array_merge_recursive_distinct($push, $itempush);
                        if($openitem[1]!=0){
                            $itempush = array(
                                "npc" => array(
                                    $npcNumber => array(
                                        "traits" => array(
                                            "equipment" => array(
                                                $type => array(
                                                    "data" => $openitem[1]
                                                )
                                            )
                                        )
                                    )
                                )
                            );
                            $push = array_merge_recursive_distinct($push, $itempush);
                        }
                    }
                    $itemNumber=$itemNumber+1;
                }
            }
            switch($traderMode){
                case "CitiTraders":
                    $invs = explode(",",$yml[$npcNumber]["basic"]["inventory"]);
                    array_pop($invs);
                    $invNumber=0;
                    // Clear out the air first, then check and see if 
                    if((count($invs)>1&&!$handEmpty) || (count($invs)>2&&$handEmpty)){
                        $invPush = array(
                            "npc" => array(
                                $npcNumber => array(
                                    "traits" => array(
                                        "stockroom" => array(
                                            "enableRightClick" => "true",
                                            "enableLeftClick" => "true"
                                        ),
                                        "wallet" => array(
                                            "type" => "PRIVATE",
                                            "amount" => $yml[$npcNumber]["basic"]["balance"],
                                            "account" => ""
                                        )
                                    )
                                )
                            )
                        );
                        $push = array_merge_recursive_distinct($push, $invPush);
                        foreach($invs as $invItem){
                            if(strpos($invItem, 'AIR')!==0){
                                $invItem = explode("/", $invItem);
                                $invPush = array(
                                    "npc" => array(
                                        $npcNumber => array(
                                            "traits" => array(
                                                "stockroom" => array(
                                                    "inv" => array(
                                                        "This" => " line can be removed.",
                                                        $invNumber => array(
                                                            "id" => $invItem[0],
                                                            "amount" => $invItem[1],
                                                            "data" => $invItem[2],
                                                            "mdata" => "0"
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                );
                                $push = array_merge_recursive_distinct($push, $invPush);
                            }
                        }
                    }
                    break;
                case "DtlTraders":
                    
                    break;
                }
            $output = array_merge_recursive_distinct($output,$push);
            $npcNumber = $npcNumber+1;
            $skip = false;
        }
        $this->converted = $npcNumber;
        $this->outputData = $output;
    }
    function getOutputInYML(){
        $fileName = $this->ID . ".saves.yml";
        $fileLocation = "/usr/share/nginx/www/citizens/converter/output/" . $fileName;
        $webLocation = "http://citizensnpcs.com/converter/output/" . $fileName;
        $datedel = date('jS \of F Y h:i A', time() + (24 * 60 * 60));
        $warn = "# This file was converted by the Araeosia Citizens 1.x -> 2.x converter.\n# It will be accessible from " . $webLocation . " until the " . $datedel . " EST.\n# Download this file to your server and place it in the ./plugins/Citizens folder and name it 'saves.yml'.\n# Converter by Agent Kid. http://araeosia.com/";
        $newyml = yaml_emit($this->outputData, YAML_ANY_ENCODING, YAML_CRLN_BREAK);
        $newyml = $warn . $newyml;
        $newyml = str_replace('---', "", $newyml);
        $newyml = str_replace('...', "", $newyml);
        $newyml = str_replace("\r\n          This: ' line can be removed.'", "", $newyml);
        $newyml = str_replace("\r\n          This: line can be ignored/removed.", "", $newyml);
        $newyml = str_replace("\\xA7", "§", $newyml);
        $newyml = str_replace("\r\n  This: line can be ignored/removed.", "", $newyml);
        $newyml = str_replace('"y":', "y:", $newyml);
        $newyml = trim($newyml);
        $FileHandle = fopen($fileLocation, 'w') or die("Can't open file location!");
        fwrite($FileHandle,$newyml);
        fclose($FileHandle);
        $this->outputFile = $fileLocation;
        return $webLocation;
    }
    function getOutputInSQL(){
        return false;
    }
}
include('assets/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->setTemplateDir('/usr/share/nginx/www/citizens/converter/assets/templates');
$smarty->setCompileDir('/usr/share/nginx/www/citizens/converter/assets/Smarty/templates_c');
$smarty->setCacheDir('/usr/share/nginx/www/citizens/converter/assets/Smarty/cache');
$smarty->setConfigDir('/usr/share/nginx/www/citizens/converter/assets/Smarty/configs');
$debug = $_GET['debug'];
$page = 'index';
$errors = null;
if($_GET['p']=='dl'){ $page='dl'; }
if(isset($_FILES['file']) || $debug){
    $page = 'done';
    if($debug){
        $convertFile = "/usr/share/nginx/www/citizens/converter/test1.yml";
    } elseif ($_FILES["file"]["error"] > 0){
        foreach($_FILES["file"]["error"] as $error){
            $errors = $errors.$error;
        }
        $smarty->assign('error', $errors);
        $smarty->assign('page', 'home.tpl');
        $smarty->display('index.tpl');
        exit;
    } elseif ($_FILES["file"]["type"] != "application/octet-stream") {
        $smarty->assign('error', 'Invalid file!');
        $smarty->assign('page', 'home.tpl');
        $smarty->display('index.tpl');
        exit;
    } elseif ($_FILES["file"]["size"]>5000000){
        $smarty->assign('error', 'File too large!');
        $smarty->assign('page', 'home.tpl');
        $smarty->display('index.tpl');
        exit;
    } else {
        $convertFile = $_FILES["file"]["tmp_name"];
    }
    $Converter = new Converter($convertFile);
    $Converter->getYML();
    $Converter->convert($_POST["traderMode"]);
    $webLocation = $Converter->getOutputInYML();
    if($debug){
        echo "<pre>";
        var_dump($Converter);
        echo "</pre>";
    }
}
switch($page){
    case "index":
        $smarty->assign('page', 'home.tpl');
        $smarty->display('index.tpl');
        break;
    case "done":
        $smarty->assign('id', $Converter->ID);
        $smarty->assign('converted', $Converter->converted);
        $smarty->assign('skipped', $Converter->skipped);
        $smarty->assign('skippedString', implode(', ', $Converter->skippedArray));
        $smarty->assign('date', date('jS \of F Y h:i A', time() + (24 * 60 * 60)));
        $smarty->assign('webLocation', $webLocation);
        $smarty->assign('page', 'done.tpl');
        $smarty->display('index.tpl');
        break;
    case "dl":
        $ID = $_GET['id'];
        $fileLocation = '/usr/share/nginx/www/citizens/converter/output/'.$ID.".saves.yml";
        if (file_exists($fileLocation)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=saves.yml');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileLocation));
            ob_clean();
            flush();
            readfile($fileLocation);
            exit;
        }else{
            $smarty->assign('error', 'We couldn\'t find the file you requested. Try converting it again.');
            $smarty->assign('page', 'home.tpl');
            $smarty->display('index.tpl');
        }
        break;
}

?>