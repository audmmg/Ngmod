<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxKurjerIskvietimController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "classes/tvsups.php");
        $this->tvsUPSClass = new tvsups();


        $this->Sandelys = $_POST['Sandelys'];



    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        //nusiskaitom ar dar nera iskviestas siandien UPS kurjeris i ta gamyba
        $Numbers = $this->tvsMod->getTVSNumbers ();

        $siandien = date("Y-m-d");
        $YraIskvietimas = 'NaN';
        if($this->Sandelys == 'KEG'){
            if($Numbers['UPS_KurjerisKEG'] == 'Y' AND $Numbers['UPS_KurjerisKEGDate'] == $siandien){
                $YraIskvietimas = 'Y';
            }else{
                $YraIskvietimas = 'N';
            }
        }else if($this->Sandelys == 'KPG'){
            if($Numbers['UPS_KurjerisKPG'] == 'Y' AND $Numbers['UPS_KurjerisKPGDate'] == $siandien){
                $YraIskvietimas = 'Y';
            }else{
                $YraIskvietimas = 'N';
            }
        }else if($this->Sandelys == 'ETK'){
            if($Numbers['UPS_KurjerisETK'] == 'Y' AND $Numbers['UPS_KurjerisETKDate'] == $siandien){
                $YraIskvietimas = 'Y';
            }else{
                $YraIskvietimas = 'N';
            }
        }else{
            $YraIskvietimas = 'NaN';
            $rezultArray['actionMessage']="Nežinomas sandėlys į kurį iškviesti UPS kurjetrį."; //$actionMessage;
        }

        //Jeigu UPS kurjeris dar nebuvo iskviestas, tai kvieciam ji i tam tikra sandeli
        if($YraIskvietimas=='N'){
            // nusiskaitom UPS siuntas kurias reikia isvezti toje gamyboje
            $UPSSiuntosSiandienArray = $this->tvsMod->getUPSSiuntosSiandien ($this->Sandelys);

            //!!!!!! DEBUG
            $this->var_dump($UPSSiuntosSiandienArray, "UPSSiuntosSiandienArray MOD<hr>$qry<hr> ");//-----------------DEBUG


            if($UPSSiuntosSiandienArray){

                $response = $this->tvsUPSClass->PickupCreationRequest($UPSSiuntosSiandienArray, $siandien, $this->Sandelys);

                /*
                    $param['Sandelys'] = $this->Sandelys;
                    $param['SiuntuSk'] = $UPSSiuntosSiandien['SiuntuSk'];
                    $param['SiuntuSvoris'] = $UPSSiuntosSiandien['SiuntuSvoris'];
                    $param['PickupDate'] = date('Ymd');
                    $param['CloseTime'] = '1700'; //(17:00)
                    $param['ReadyTime'] = '0500'; //(05:00)
                */

                $rezultArray['error']=$response["error"];//$ErrorStatus;
                $rezultArray['actionMessage']=$response["actionMessage"]; //$actionMessage;
                $rezultArray['data']=array();

            }else{//end if
                $rezultArray['error']='NOTOK';//$ErrorStatus;
                $rezultArray['actionMessage']="Šiandien nėra UPS siuntų! Kurjeris NEiškviestas!"; //$actionMessage;
                $rezultArray['data']=array();
            }


            

            //apdorojam response duomenis



        }elseif($YraIskvietimas=='Y'){

            $rezultArray['error']="OK";//$ErrorStatus;
            $rezultArray['actionMessage']="UPS kurjeris jau buvo iškviestas!";
            $rezultArray['data']=array();

        }else{

            $rezultArray['error']="OK";//$ErrorStatus;
            $rezultArray['actionMessage']="Problema iškviečiant UPS kurjerį!";
            $rezultArray['data']=array();

        }


            //!!!!!! DEBUG
            $this->var_dump($rezultArray, "rezultArray UPS<hr>$qry<hr> ");//-----------------DEBUG


        echo "**--**";
        echo json_encode($rezultArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxKurjerIskvietimController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
