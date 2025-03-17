<?php
///////////////////////////////////////////////////
// Spausdina VENIPAK ir UPS manifesta
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxPrintManifestController extends controller {

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

        $ManCode = $this->getVar('ManNr');

        list($ManVezejas, $ManNr) = explode("_", $ManCode);

        if($ManVezejas == 'VNP'){
            if($ManNr){


                    $SiuntaUID="";
                    $sParam = array();
                    $root_path = COMMON::getRootFolder();
                    require_once ($root_path . "classes/tvsvenipak.php");
                    $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);

                    //$rezDuom = $this->tvsvenipak->PrintManifest($ManNr);
                    $this->tvsvenipak->clearErrors(); //isvalom errorus, nes registruojames be siuntos, tai iskarto bus erroras, bet jis siuo atveju neidomus

                    $rezDuom = $this->tvsvenipak->PrintManifest($ManNr);


                    if($this->tvsvenipak->haveErrors()>0){
                        $ErrorStatus['OK'] = 'NOTOK';
                        $actionMessage = $this->tvsvenipak->getErrorsAsHtml ();
                    }else{//end if
                        $ErrorStatus['OK'] = 'OK';
                        $actionMessage = "";
                        //$createOK['ERROR'] = '';
                    }


            
                $rezultArray['error']=$ErrorStatus['OK'];
                $rezultArray['actionMessage']=$actionMessage;
                $rezultArray['file']=$rezDuom['File'];
            }else{
                $rezultArray['error']='NOTOK';
                $rezultArray['actionMessage']='Nepasirinktas manifestas';
                $rezultArray['file']='';

            }
        }elseif($ManVezejas == 'UPS'){

                    $sParam = array();
                    $root_path = COMMON::getRootFolder();
                    require_once ($root_path . "classes/tvsups.php");
                    $this->tvsups = new tvsups();

                    //$rezDuom = $this->tvsups->PrintManifest($ManNr);
                    //$this->tvsups->clearErrors(); //isvalom errorus, nes registruojames be siuntos, tai iskarto bus erroras, bet jis siuo atveju neidomus

                    $rezFile = $this->tvsups->PrintManifest($ManNr);


                    if($this->tvsups->haveErrors()>0){
                        $ErrorStatus['OK'] = 'NOTOK';
                        $actionMessage = $this->tvsups->getErrorsAsHtml ();
                    }else{//end if
                        $ErrorStatus['OK'] = 'OK';
                        $actionMessage = "";
                        //$createOK['ERROR'] = '';
                    }


            
                $rezultArray['error']=$ErrorStatus['OK'];
                $rezultArray['actionMessage']=$actionMessage;
                $rezultArray['file']=$rezFile['file'];
                $rezultArray['fileWithPath']=$rezFile['fileWidthPath'];
        }//end elseif


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
    $controller = new ajaxPrintManifestController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
