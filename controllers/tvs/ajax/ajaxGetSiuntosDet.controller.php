<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxGetSiuntosDetController extends controller {

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
        $SiuntaID = $this->getVar('SiuntaID');

       
        //nusiskaitom duomenys apie siunta
        $PackData = array();
        if($SiuntaID){
            $PackData = $this->tvsMod->getSiuntaDuomToTransp($SiuntaID);
            //$OpenManifest = $this->tvsMod->getVP_Manifest($this->VUserID);
        }//end if
        

        if($PackData['OK']!='OK'){ 
                //parsisiunciam klaidas
                $actionMessage = $this->tvsMod->getErrorArrayAsStr();
                $ErrorStatus = "NOTOK";
        }else{
                $actionMessage = ' ';
                $ErrorStatus = "OK";
        }


        
        $rezultArray['error']=$ErrorStatus;
        $rezultArray['actionMessage']=$actionMessage;
        $rezultArray['data']=$PackData['Duom'];
        

        //!!!!!! DEBUG
        //$this->var_dump($rezultArray, "rezultArray");//-----------------DEBUG

        echo "**--**";
        echo json_encode($rezultArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxGetSiuntosDetController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
