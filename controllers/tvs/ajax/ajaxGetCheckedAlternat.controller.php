<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxGetCheckedAlternatController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct() {
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
        
        $LynID = $this->getVar('LynID');

        $LynArray = array();
        if($LynID){
            $rez=$this->tvsMod->getLynAlternat($LynID);
        }//end if


        

        //!!!!!! DEBUG
        $this->var_dump($rez, "--RezArray");//-----------------DEBUG

        if($rez){
            $RezArray['error']='OK';
            $RezArray['actionMessage']='Duomenys išsaugoti.';
            $RezArray['Duom']=$rez;
        }else{
            $RezArray['error']='NOTOK';
            $RezArray['actionMessage']='Saugant duomenys įvyko klaida!';
            $RezArray['Duom']= array();
        }

        echo $rez."**--**";
        echo json_encode($RezArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxGetCheckedAlternatController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
