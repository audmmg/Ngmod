<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
ob_start();
//$root_path = COMMON::getRootFolder();

$root_path = COMMON::getRootFolder();
require_once ($root_path . "controllers/controller.php");

class fileUploadController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError();

        //$smId=$this->getVar ('smId');

        //sukuriam samatu modeli
        $root_pathU = COMMON::getRootFolder();
        require_once ($root_pathU . "modules/nz/fileUpload.mod.php");
        $this->fileUpload = new fileUpload_mod();
        require_once ($root_pathU . "modules/nz/auditas.mod.php");
        $this->auditasMod = new auditas_mod();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $rezult=$this->fileUpload->upload("auditas".$this->auditasMod->getDocumentNr());
        return $rezult;
        /*if(count($rezultArray)>0){
            echo json_encode($rezultArray);
        }else{
            echo json_encode("");
        }*/
    }


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new fileUploadController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
