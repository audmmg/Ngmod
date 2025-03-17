<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");



class ajaxCreateLabelController extends controller {

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
        $this->packNr = $this->getVar('packNr'); //(jeigu viena lipduka tai grazins cia)
        //$this->manifestNr = $this->getVar('manifestNr'); //(jeigu visus lipdukus, tai cia)
        $this->siuntaNr = $this->getVar('siuntaNr'); //(jeigu visus lipdukus, tai cia)
        $this->vezejas = $this->getVar('vezejas');
        
        switch ($this->vezejas) {
            case 'VENIPAK':
                    if($this->packNr){
                        $SiuntaUID="";
                        $sParam = array();
                        $root_path = COMMON::getRootFolder();
                        require_once ($root_path . "classes/tvsvenipak.php");
                        $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);
                        $this->tvsvenipak->clearErrors (); //isvalom klaidas, nes jungiames be SiuntosUID

                        $rezDuom = $this->tvsvenipak->createLabel ($this->packNr);
                    }else{
                        $actionMessage = 'Nėra siuntos numerio.';
                        $ErrorStatus = "NOTOK";

                    }
                
                break;
            case 'VENIPAKALL':
                    if($this->siuntaNr){//siuo atveju cia 
                        $siuntaNr =$this->siuntaNr; //siuo atveju cia 
                        $SiuntaUID="";

                        $sParam = array();
                        $root_path = COMMON::getRootFolder();
                        require_once ($root_path . "classes/tvsvenipak.php");
                        $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);
                        $this->tvsvenipak->clearErrors (); //isvalom klaidas, nes jungiames be SiuntosUID
                        
                        //$rezDuom = $this->tvsvenipak->createLabelAll ($siuntaNr);//sita fun irgi veikia, tik cia pakeiciam ja i Siuntos lipduku spausdinima
                        $rezDuom = $this->tvsvenipak->createLabelSiunta($siuntaNr);

                    }else{

                        $actionMessage = 'Nėra manifesto numerio.';
                        $ErrorStatus = "NOTOK";

                    }
                
                break;
            
            default:
                // nieko nedarom
                $actionMessage = 'Nenurodytas vežėjas.';
                $ErrorStatus = "NOTOK";

                break;
        }

        if($rezDuom['OK']!='OK'){ 
                //parsisiunciam klaidas
                $actionMessage = $this->tvsvenipak->getErrorsAsHtml();
                $ErrorStatus = "NOTOK";
        }else{
                $actionMessage = ' ';
                $ErrorStatus = "OK";
        }


        
        $rezultArray['error']=$ErrorStatus;
        $rezultArray['actionMessage']=$actionMessage;
        $rezultArray['data']=$rezDuom['File'];
        

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
    $controller = new ajaxCreateLabelController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
