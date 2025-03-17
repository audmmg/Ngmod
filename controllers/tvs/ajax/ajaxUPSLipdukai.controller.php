<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxUPSLipdukaiController extends controller {

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

        $SiuntaUID = $this->getVar('SID');

        if($SiuntaUID){


                    $sParam = array();
                    $root_path = COMMON::getRootFolder();
                    require_once ($root_path . "classes/tvsups.php");
                    $this->tvsups = new tvsups('test', $SiuntaUID, $sParam);

                    
                    $sugeneruotasPdf = $this->tvsups->ajaxCreateLabelPDF($SiuntaUID); //is atsiustu GIF lipduku generuojam viena PDF lipduka (is daug gif lipduku)

                    //$rezDuom = $this->tvsvenipak->PrintManifest($ManNr);


                    if(!$sugeneruotasPdf){
                        $ErrorStatus['OK'] = 'NOTOK';
                        $actionMessage = $this->tvsups->getErrorsAsHtml ();
                    }else{//end if
                        $ErrorStatus['OK'] = 'OK';
                        $actionMessage = "";
                        //$createOK['ERROR'] = '';
                    }


            
            $rezultArray['error']=$ErrorStatus['OK'];
            $rezultArray['actionMessage']=$actionMessage;
            $rezultArray['file']['lipdukas']=$sugeneruotasPdf['file'];
            $rezultArray['file']['vaztarastis']='';
        }else{
            $rezultArray['error']='NOTOK';
            $rezultArray['actionMessage']='Nepasirinktas manifestas';
            $rezultArray['file']['lipdukas']='';
            $rezultArray['file']['vaztarastis']='';


        }
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
    $controller = new ajaxUPSLipdukaiController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
