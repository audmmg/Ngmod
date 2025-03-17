<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxChangeAutoPatikraController extends controller {

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
        $SiuntaID = $this->getVar('siuntaNr'); 

        $SiuntaDuom = $this->tvsMod->getSiuntaDuomFromTVS($SiuntaID);

        $NewAPBusena = 'N';//default
        if($SiuntaDuom){
            switch ($SiuntaDuom[0]['AutoPatikra']) {
                case 'N':
                    $NewAPBusena = 'OK';
                    break;
                case 'OK':
                    $NewAPBusena = 'NOK';
                    break;
                case 'NOK':
                    $NewAPBusena = 'N';
                    break;
                default:
                    $NewAPBusena = 'N';
                    break;
            }//end swith

        }else{
            $NewAPBusena = 'N';
            $actionMessage = 'Nėra siuntos duomenų.';
            $ErrorStatus = "NOTOK";

        }

        // var_dump ($SiuntaDuom);

        //echo "--------".$NewAPBusena."-----<Br>";
        $SiuntaRez = $this->tvsMod->changeAutoPatikraBusena($SiuntaID, $NewAPBusena);

        //var_dump ($SiuntaRez);

        if($SiuntaRez['OK']!='OK'){ 
                //parsisiunciam klaidas
                $actionMessage = $this->tvsMod->getErrorArrayAsStr();
                $ErrorStatus = "NOTOK";
        }else{
                $actionMessage = ' ';
                $ErrorStatus = "OK";
        }


        
        $rezultArray['error']=$ErrorStatus;
        $rezultArray['actionMessage']=$actionMessage;
        $rezultArray['SiuntaUpd']=$SiuntaRez['SiuntaUpd'];
        $rezultArray['NewAPBusena']=$SiuntaRez['NewAPBusena'];
        

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
    $controller = new ajaxChangeAutoPatikraController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
