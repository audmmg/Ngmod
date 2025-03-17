<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxGetTrackInfoController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "classes/tvsvenipak.php");
        $this->tvsVenipakClass = new tvsvenipak('live', '', array());

        $this->TrackingNr = $_POST['TrackingNr'];
        if(substr($this->TrackingNr, 0, 1)=="V"){
            $this->WhoTrNr = "VENIPAK";
        }else{
            $this->WhoTrNr = "NaN";
        }

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $trackingRezStr = "";
        if($this->TrackingNr){
            if($this->WhoTrNr == "VENIPAK"){
                $trackingInfoRezArray = $this->tvsVenipakClass->getTrackingInfo ($this->TrackingNr);
                $trackingInfo = $trackingInfoRezArray['TrackingInfo'];
            }else{

            }
        }//end if




        $rezultArray['error']="OK";//$ErrorStatus;
        $rezultArray['actionMessage']=""; //$actionMessage;
        $rezultArray['data']['TRInfo']=$trackingInfo;
        

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
    $controller = new ajaxGetTrackInfoController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
