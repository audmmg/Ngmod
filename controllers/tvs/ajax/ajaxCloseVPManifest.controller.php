<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxCloseVPManifestController extends controller {

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

        $manifest = $this->getVar('manifest');

        $manifestArray = array('KEG', 'KPG'); // turimi manifestu laukai DB
        if(in_array($manifest, $manifestArray)){
            $ErrorStatus = "OK";
           
            //nusiskaitom duomenys apie siunta
            $ManifestRez = $this->tvsMod->closeVPManifest($manifest);
            

            if($ManifestRez['OK']!='OK'){ 
                    //parsisiunciam klaidas
                    $actionMessage = $this->tvsMod->getErrorArrayAsStr();
                    $ErrorStatus = "NOTOK";
            }else{
                    $actionMessage = ' ';
                    $ErrorStatus = "OK";
            }


            
            $rezultArray['error']=$ErrorStatus;
            $rezultArray['actionMessage']=$actionMessage;
            $rezultArray['data']=$ManifestRez['Duom'];
        }else{
            $rezultArray['error']="NOTOK";
            $rezultArray['actionMessage']="KLAIDA: Nenurodyta kurį manifestą uždaryti!";
            $rezultArray['data']=" ";
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
    $controller = new ajaxCloseVPManifestController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
