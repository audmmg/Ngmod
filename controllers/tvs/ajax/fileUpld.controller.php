<?php
///////////////////////////////////////////////////
// VEIKSMAS atspausdina lipdukus 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class fileUpldController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct() {
        parent::__construct();
        parent::clearError();

        //$smId=$this->getVar ('smId');

        //sukuriam samatu modeli
        $root_pathU = COMMON::getRootFolder();
        require_once ($root_pathU . "modules/tvs/fileUpld.mod.php");
        $this->fileUpload = new fileUpld_mod();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        /*
        if($param['subdir']){
            $subdir=$param['subdir'];// subdir in folder "upload"
        }else{
            $subdir="";
        }
        */

        $subdir = $this->getVar('subdir');
        $kam = $this->getVar('kam');
        $SiuntaID = $this->getVar('SiuntaID');

        
        if($SiuntaID AND is_numeric($SiuntaID)){
            
            //echo "====".$subdir."===".$kam."===".$SiuntaID."===<br>";
            
            
            $rezultArray=$this->fileUpload->upload($subdir, $SiuntaID, $kam );
            //var_dump($rezultArray);

            $rezult['file']=$rezultArray['File'];
            $rezult['fileUID']=$rezultArray['FileUID'];
            $rezult['error']=$rezultArray['error'];
            $rezult['responseText']=$subdir." ".$rezultArray['responseText'];
            
            
            if($rezult['file']){
                //viskas OK
                //$rezult['error']='OK';
            }else{
                $rezult['file']='NaN';
                $rezult['fileUID']='';
                $rezult['error']='NOTOK';
                $rezult['responseText']="Duomenų perdavimo klaida";

                echo json_encode($rezult);
            }
        }else{
                $rezult['file']='NaN';
                $rezult['fileUID']='';
                $rezult['error']='NOTOK';
                $rezult['responseText']="Nežinomas siuntos numeris";
                echo json_encode($rezult);
        }
        
        echo"**--**";
        echo json_encode($rezult);
        return " ";
    }


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new fileUpldController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
