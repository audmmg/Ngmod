<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");
//require_once ("../../classes/tvsschenker.php");

class ajaxSchenkerController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "classes/tvsschenker.php");
        $this->Schenker = new tvsschenker();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $ErrorStatus = "OK";
        
        $Det_PackingSlip = $this->getVar('Det_PackingSlip');
        //$PackingSlipArray = explode(",", $Det_PackingSlip);
        

        $this->Schenker->tvsVarDump();

        $this->Schenker->fillByPackingSlipNr();




        //$rez=$this->tvsMod->savePakuote($PakArray);

        //!!!!!! DEBUG
        //$rez = $this->var_dump($PakArray, "PakArray");//-----------------DEBUG

        if($rez=='OK'){
            $RezArray['error']='OK';
            $RezArray['actionMessage']='Duomenys išsaugoti.';
        }else{
            $RezArray['error']='NOTOK';
            $RezArray['actionMessage']='Saugant duomenys įvyko klaida!';
        }

        echo $rez."**--**";
        echo json_encode($RezArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

/*
if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxSchenkerController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
*/

    $controller = new ajaxSchenkerController();
    $controller->run();


?>
