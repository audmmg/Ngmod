<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxGetTrNtrController extends controller {

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


        $this->SiuntaID = $_POST['SiuntaID'];



    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $trackingNrStr = "";
        if($this->SiuntaID){
            $TrNrArray = $this->tvsMod->GetSavedTrackingNumber($this->SiuntaID);

            if($TrNrArray){

                foreach ($TrNrArray as $key => $value) {
                    if($value['VezejasReal'] == "VENIPAK"){
                        $trackingNrStr .= "<a href='#' id='TRN_".$value['TrackingNr']."' class='TRNumber'>".$value['TrackingNr']."</a><br>";
                    }elseif($value['VezejasReal'] == "SCHENKER"){
                        $trackingNrStr .= $value['TrackingNr']."<br>";
                    }elseif($value['VezejasReal'] == "UPS"){
                        $trackingNrStr .= "<a href='#' id='TRN_".$value['TrackingNr']."' class='TRNumber'>".$value['TrackingNr']."</a><br>";
                    }
                }//end foreach
            }//end if
        }//end if
//var_dump($TrNrArray);
        $this->TrackingNr = $TrNrArray[0]['TrackingNr'];
        $this->WhoTrNr = $TrNrArray[0]['VezejasReal'];

        /*
        if(substr($this->TrackingNr, 0, 1)=="V"){
            $this->WhoTrNr = "VENIPAK";
        }else{
            $this->WhoTrNr = "NaN";
        }
        */


        $trackingRezStr = "";
        if($this->TrackingNr){
            if($this->WhoTrNr == "VENIPAK"){
                $trackingInfoRezArray = $this->tvsVenipakClass->getTrackingInfo ($this->TrackingNr);
                $trackingInfo = $trackingInfoRezArray['TrackingInfo'];
            }elseif($this->WhoTrNr == "SCHENKER"){
                $trackingInfoRezArray = array();
                 

                    $trackingInfo = "
                            <table id='TrackingTable' style='width:600px;'>
                                <tr>
                                    <th>Nuoroda</th>
                                </tr>
                                <tr>
                                    <td style='width:400px;'>Peržiūrėti SCHENKER sistemoje:<br><a href='https://eschenker.dbschenker.com/app/tracking-public/?refNumber=".$this->TrackingNr."' class='' style='width:300px;' target='_blank'>https://eschenker.dbschenker.com/app/tracking-public/?refNumber=".$this->TrackingNr."&</a></td>
                                </tr>
                            </table>";
            }elseif($this->WhoTrNr == "UPS"){
                    $trackingInfo = "
                            <table id='TrackingTable' style='width:600px;'>
                                <tr>
                                    <th>Nuoroda</th>
                                </tr>
                                <tr>
                                    <td style='width:400px;'>Peržiūrėti UPS sistemoje:<br><a href='https://www.ups.com/track?loc=lt_LT&tracknum=".$this->TrackingNr."' class='' style='width:300px;' target='_blank'>https://www.ups.com/track?loc=lt_LT&tracknum=".$this->TrackingNr."&</a></td>
                                </tr>
                            </table>";

            }else{

            }
        }//end if


        $rezultArray['error']="OK";//$ErrorStatus;
        $rezultArray['actionMessage']=""; //$actionMessage;
        $rezultArray['data']['TRNR']=$trackingNrStr;
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
    $controller = new ajaxGetTrNtrController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
