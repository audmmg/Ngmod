<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxGetZaliavosController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError();

        //$smId=$this->getVar ('smId');

        //sukuriam samatu modeli
        $root_path = COMMON::getRootFolder();
        require_once ( $root_path. "/modules/samata1/samata1Det.mod.php");
        $this->samataModelis = new samata1Det_mod();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $search_str = strtolower($this->getVar('term'));

        $a = strtolower($this->getVar('a'));
       
        $rezultArray = array();

        if($a=='list'){
            //pasiimam medziagu lista autokomplitui
            $rezultArray = $this->samataModelis->ajaxGetZaliava($search_str);
        }else if($a=='det'){
            
            $PrekeID = $this->getVar('PrekeID');
            $uid = $this->getVar('uid');

            //jeigu turim ID
            if($this->is_ID($uid)){
                //pasiimam reikalingus duomenis pasirinkus konkrecia medziaga
                $zalData = $this->samataModelis->ajaxGetZaliavaDet($uid);

                 if($this->is_ID($PrekeID)){
                    //pasiimam medziagos kaina is MS SQL
                    $zalKaina = $this->samataModelis->ajaxGetZaliavaKaina($PrekeID);

                    //pasiimam plociu lista
                    $zalPlociaiArray = $this->samataModelis->ajaxGetZaliavaPlociai($PrekeID);

                }

            }

            $rezultArray['data'] = $zalData;
            $rezultArray['kainaLt'] =$zalKaina;
            $rezultArray['plociaiArray'] =$zalPlociaiArray;
        }else{
            $rezultArray=array(
                    Preke=>'aaa',
                    uid=>'123',
                    NomNr=>'000'
                );
        }

        //$rezultArray['testUid']=$uid;
        if(count($rezultArray)>0){
            echo json_encode($rezultArray);
        }else{
            echo json_encode("");
        }
    }


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxGetZaliavosController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
