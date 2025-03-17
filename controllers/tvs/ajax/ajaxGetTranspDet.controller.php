<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxGetTranspDetController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $ErrorStatus = "OK";
        $LynRefArrayTmp = $this->getVar('Lyn');

        //issiparsinam grynus packing slip ID

        $LynRefArray = array();
        if($LynRefArrayTmp){
            //$PackingSlipNetvarkytiArray = explode(",", $PackingSlipArrayStr);
            foreach ($LynRefArrayTmp as $key => $PSID) {
                $LynRefArray[]= substr($PSID, 4);
            }

        }//end if

        //var_dump($LynRefArray);

        
        //begam per visus PSilpus ir susirenkam info
        $rezArray = array();
        if($LynRefArray){
            $PackData = $this->tvsMod->getPackLinData($LynRefArray);
        }//end if
        

        //!!!!!! DEBUG
        //$this->var_dump($rezultArray, "rezultArray");//-----------------DEBUG

        echo "**--**";
        echo json_encode($PackData);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxGetTranspDetController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
