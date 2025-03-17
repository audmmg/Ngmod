<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxDetSiuntaGreenController extends controller {

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
        $SiuntaID = $this->getVar('SID');

        if($SiuntaID){
            $AllSiuntaData = $this->tvsMod->detSiuntaGreenData($SiuntaID);
            if($AllSiuntaData['OK']!='OK' OR $this->tvsMod->countError()>0){
                $this->addErrorArray($this->tvsMod->getErrorArray ());
                $AllSiuntaData['OK'] = 'NOTOK';
            }else{
                $AllSiuntaData['OK'] = 'OK';
            }
        }else{//end if
            $AllSiuntaData['OK'] = 'NOTOK';
            $AllSiuntaData['Comment'] = 'Nežinomas siuntos numeris.';
        }
       

        /*
        if($SiuntaID){
            //pasiima siuntos duomenys is _TMS_Pak
            $this->FullPackData = $this->tvs_mod->getFullPackData($this->sDuom);

            //nuskaitom SO/packingslipus/...
            $RefArray = $this->tvs_mod->getSOArrayBySiuntaUID($this->FullPackData['uid']);
            //!!!!!! DEBUG
            //$this->var_dump($RefArray, "RefArray <hr>$sql<hr> ");//-----------------DEBUG


            //nuskaitom failus is visu vietu, viska kas priklauso siuntai
            $this_PS_files_array_str=$this->tvs_mod->getSOFilesArray($RefArray);
            //!!!!!! DEBUG
            //$this->var_dump($this_PS_files_array_str, "this_PS_files_array_str <hr>$sql<hr> ");//-----------------DEBUG

        }else{//end if
            $DelRez['OK'] = 'NOTOK';
            $DelRez['Comment'] = 'Nežinomas siuntos numeris.';
        }
        */

        //!!!!!! DEBUG
        //$this->var_dump($DelRez, "DelRez");//-----------------DEBUG


        //$this->FullPackData['FilesArray'] = $this_PS_files_array_str['fileArray'];
        //$this->FullPackData['FilesStr'] = $this_PS_files_array_str['fileStr'];

        //$AllSiuntaData['ERROR']['Error'] = 'OK';
        //$AllSiuntaData['ERROR']['ErrorComments'] = "it's OK";

        echo "**--**";
        echo json_encode($AllSiuntaData);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxDetSiuntaGreenController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
