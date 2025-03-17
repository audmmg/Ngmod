<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxParinktiVezejaController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();
        /*
        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "classes/tvsvenipak.php");
        $this->tvsVenipakClass = new tvsvenipak('live', '', array());
        */



    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $this->Param['ClientID'] = $_POST['ClientID'];
        $this->Param['VezejasStr'] = $_POST['VezejasStr'];
        $this->Param['Det_SaliesKodas'] = $_POST['Det_SaliesKodas'];
        $this->Param['GalutinisBrutto'] = $_POST['GalutinisBrutto'];
        $this->Param['TranzitoLaikas'] = $_POST['TranzitoLaikas'];
        $this->Param['Express'] = $_POST['Express'];
        $this->Param['ShipmentFromStr']= $_POST['isSandelio'];
        $this->pakuotes = $_POST['pakuotes'];

        //!!!!!! DEBUG
        //$this->var_dump($this->pakuotes, "pakuotes");//-----------------DEBUG


        if($this->pakuotes){
            $this->paksArray = $this->tvsMod->parsePacksStrToArray ($this->pakuotes);
        }else{
            $this->paksArray = array();
        }

        //!!!!!! DEBUG
        //$this->var_dump($this->paksArray, "paksArray");//-----------------DEBUG

        $PakuociuSk = 0;
        $PaleciuSk = 0;
        $PakTipas='NaN';
        if($this->paksArray){
            foreach ($this->paksArray as $key => $pak) {
                if($pak['pTipas']=="PK" OR $pak['pTipas']=="DD" OR $pak['pTipas']=="RD"){
                    $PakuociuSk = $PakuociuSk + $pak['pKiekis'];  
                    if($PakTipas!='P') {//Jeigu yra bent viena palete tai nekeiciam tipo
                        $PakTipas='D';//Dezute arba Pakuote, bet ne Palete. Jeigu yra bent viena palete tai nekeiciam tipo
                    }
                }else if ($pak['pTipas']=="EP" OR $pak['pTipas']=="MP"){
                    $PaleciuSk = $PakuociuSk + $pak['pKiekis']; 
                    $PakTipas='P';//jeigu yra bent viena palete, tai tada tipas yra palete
                }
            }//end foreach
        }//end if

        $this->Param['PakuociuSk']=$PakuociuSk;
        $this->Param['PaleciuSk']=$PaleciuSk;
        $this->Param['PakTipas']=$PakTipas;

        //!!!!!! DEBUG
        //$this->var_dump($Param, "Param");//-----------------DEBUG

        $this->NewVezejas = $this->tvsMod->ParinktiVezeja($this->Param);

        //!!!!!! DEBUG
        //$this->var_dump($this->NewVezejas, "NewVezejas");//-----------------DEBUG




        $rezultArray['error']="OK";//$ErrorStatus;
        $rezultArray['actionMessage']=""; //$actionMessage;
        $rezultArray['NewVezejas']=$this->NewVezejas;
        //$rezultArray['NewVezejas']='UPS';// Testavimui
        

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
    $controller = new ajaxParinktiVezejaController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
