<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';
require_once($script_path . '/../../classes/TVSconfig.php');

class sandelysController extends controller {


//**************************************************************************
 
 private $TABuFormavimas = array(
            0 => array(
                'pavadinimas'=>"Užsakymai",
                'IDpavadinimas'=>"tabKa",
                'sabl'=>"tvs/sandelysListView.tpl",
                'aktyvus'=>TRUE
                ),
           1 => array(
                'pavadinimas'=>"Info",
                'IDpavadinimas'=>"tabKaNew",
                'sabl'=>"tvs/sandelysInfo.tpl",
                'aktyvus'=>FALSE
                )
         );


//****************************************************************************
//*************************************KONSTRUKTORIUS*************************
function __construct() {
    parent::__construct();
                
    if ($this->getVar('a')) {
        $this->action = $this->getVar('a');
    } else {
        $this->action = "list";
    }

    $this->SelectedOrderUID = $this->getVar('soi');
    $this->sDuom = $this->getVar('sDuom');
    $this->CB = $this->getVar('CB');
    $this->sDuom['CB'] = $this->CB;

    //jeigu nera filtro, tai parenkam standartini
    /*
    if(!$this->sDuom OR !is_array($this->sDuom) OR count($this->sDuom)<=0){
        $this->sDuom['sData']=2;// tik dvieju darbo dienu
        //$this->sDuom['sDataKuri']="NG";// atrinkines pagal NewGam data
        $this->sDuom['sDataKuri']="JOB";// atrinkines pagal JOB data
        
        if(SESSION::getUserID()=='942'){ //jeigu tai "Lina Slegute"
            $this->sDuom['sData']=2;// 7 paskutines dienos
            $this->sDuom['sO2n']=1;// tik tuos kurie NEturi specifikacijos
            $this->sDuom['sDataKuri']="NG";// atrinkines pagal NewGam data
        }
        if(SESSION::getUserID()=='465'){ //jeigu tai "Suminskas"
            $this->sDuom['sData']=2;// 7 paskutines dienos
            //$this->sDuom['sO3n']=1;// tik tuos kurie NEturi JOB
            $this->sDuom['sDataKuri']="JOB";// atrinkines pagal JOB data
        }
    }//end if
    */

        //!!!!!! DEBUG
        //$this->var_dump($this->sDuom, "this->sDuom <hr>$sql<hr> ");//-----------------DEBUG


    $this->aktyvusTabas = ""; //$this->dizFormData['valdymas']['tabasAktyvusIDPavad'];//aktyvus tabas
    
    //TABo parinkimas pagal ACRIONa
    /*
    if($this->action=="in"){
        $this->aktyvusTabas="tabKaNew";
    }//end if

    if($this->action=="UP"){
        $this->aktyvusTabas="tabKaNew";
    }//end if
    */
                
    

    //jeigu aktyvus tabas nenustatytas, tai pasiimam is default nustatymu
    if (!$this->aktyvusTabas){
        for($i=0;$i<count($this->TABuFormavimas);$i++){
            if($this->TABuFormavimas[$i]['aktyvus']== 'TRUE'){
                $this->aktyvusTabas=$this->TABuFormavimas[$i]['IDpavadinimas'];
            }
        }
    }

    //$this->dizFormData['valdymas']['tabasAktyvusIDPavad']=$aktyvusTabas;
    $this->tabai=$this->formatingTabs($this->TABuFormavimas, $this->aktyvusTabas);

    //var_dump($this->tabai);
        
}//end construct



public function formatingTabs ($TABuArray, $aktyvusTabas){
        if(!$aktyvusTabas){
            return $TABuArray; //jeigu nezinomas aktyvus tabas tai grazinam defaultini array
        }else{
            for ($i=0; $i<count($TABuArray); $i++){
                if ($TABuArray[$i]['IDpavadinimas']==$aktyvusTabas){
                    $TABuArray[$i]['aktyvus']= TRUE;
                }else{
                    $TABuArray[$i]['aktyvus']= FALSE;
                }
            }//end for
            return $TABuArray;
        }//end else

}//end function
//****************************************************************************


	public function run($param = array()) {
        DEBUG::log($this->action, 'action');

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvs_mod = new tvs_mod();


        //!!!!!! DEBUG
        //$this->var_dump($this->TView, "this->TView <hr>$sql<hr> ");//-----------------DEBUG


        switch ($this->action) {

            case 'list':

                //pasiimam kas dirba, kad apribotume kai kurias funkcijas, pvz trinti uzsakymus gali tik Edita ir Gintare ir as
                //20220128 (Editos laiskas 2022-01-28 08:50)
                $userID = $_SESSION['user_id'];
                $galiTrintiSiuntasArray = array('501', '858', '1107', '48', '1295', '937', '1410', '1177', '527', '1271', '627', '1052','1429','635', '1252', '1668');
                if(in_array($userID, $galiTrintiSiuntasArray)){
                    $this->galiTrintiSiuntas='Y';
                }else{
                    $this->galiTrintiSiuntas='N';
                }


                if(!$this->sDuom['sDatNuo'] OR $this->sDuom['sDatNuo']>date("Y:m:d")){
                    //$this->sDuom['sDatNuo'] = date("Y-m-d 00:00:00");
                    $this->sDuom['sDatNuo'] = date("Y-m-d");
                }
                if(!$this->sDuom['sDatIki'] OR $this->sDuom['sDatIki']>date("Y:m:d")){
                    //$this->sDuom['sDatIki'] = date("Y-m-d H:i:s");
                    $this->sDuom['sDatIki'] = date("Y-m-d");
                }
                if($this->sDuom['sDatNuo'] > $this->sDuom['sDatIki']){
                    $this->sDuom['sDatNuo'] = $this->sDuom['sDatIki'];
                }

                $this->orderList = $this->tvs_mod->getOrderList($this->sDuom);
                //!!!!!! DEBUG
                //$this->var_dump($this->orderList, "this->TView <hr>$sql<hr> ");//-----------------DEBUG
                
                if($this->is_ID($this->SelectedOrderUID)){
                    $this->SelectedOrderData = $this->tvs_mod->getOrderData($this->SelectedOrderUID);

                    //$this->fileArray = $this->newGamMod->getDocFileArray($this->SelectedNewGamData);
                }

                $dateSiandien = date("Y-m-d");
                $this->ManifestList = $this->tvs_mod->getManifestList($dateSiandien);


                $this->Numbers = $this->tvs_mod->getTVSNumbers ();
                if($this->Numbers['UPS_KurjerisKEG']=='Y' AND substr($this->Numbers['UPS_KurjerisKEGDate'],0,10)==$dateSiandien){
                    $this->Numbers['UPS_KurjerisKEGIskviestas'] = 'Y';
                }else{
                    $this->Numbers['UPS_KurjerisKEGIskviestas'] = 'N';
                }

                if($this->Numbers['UPS_KurjerisKPG']=='Y' AND substr($this->Numbers['UPS_KurjerisKPGDate'],0,10)==$dateSiandien){
                    $this->Numbers['UPS_KurjerisKPGIskviestas'] = 'Y';
                }else{
                    $this->Numbers['UPS_KurjerisKPGIskviestas'] = 'N';
                }


                $this->OutputForma();
                break;
            default:
                $this->OutputForma();
        }
    }// end function run

//===============================AJAX==================================================

/*
//iveda nauja arba updatina kainyna
private function KainInsert() {



        $insRezArray = $this->transportMod->kainynasInsert();
        
        $this->addErrorArray($this->transportMod->getErrorArray());
        $this->addMessageArray($this->transportMod->getMessageArray());

        return $insRezArray;
}//END KainInsert

*/





//=================================================================================

    public function OutputForma() {


        // list of used javascript files:
        $form_javascripts = array('tabs');
        $custom_javascripts = array('jQuery/ui/jquery.datetimepicker.full','tvs/tvs');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $custom_css = array('themes/base/jquery.ui.all', 'styleTvs', 'jquery.datetimepicker');
        $form_css = array('tabs', 'forms');
        
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);

        //$messageDiv=$this->getMessageArrayAsStr();
        //$errorDiv=$this->getErrorArrayAsStr();

        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->viewer->assign('orderList', $this->orderList);

        
        $this->viewer->assign('SelectedOrderData', $this->SelectedOrderData);


        $sessionID = SESSION::getUserID();

        $this->viewer->assign('sessionID', $sessionID);
        //$this->viewer->assign('CB', $this->CB);
        

        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);


        $this->viewer->assign('action',$this->action);
        $this->viewer->assign('SelectedOrderUID',$this->SelectedOrderUID);
        $this->viewer->assign('sDuom',$this->sDuom);
        $this->viewer->assign('ManifestList',$this->ManifestList);
        $this->viewer->assign('Numbers',$this->Numbers);
        

        $this->CheckVPmode = TVS_CONFIG::VENIPAK_MODE;
        $this->viewer->assign('CheckVPmode',$this->CheckVPmode);

        $this->CheckSCHPmode = TVS_CONFIG::SCHENKER_MODE;
        $this->viewer->assign('CheckSCHPmode',$this->CheckSCHPmode);

        $this->CheckUPSmode = TVS_CONFIG::UPS_MODE;
        $this->viewer->assign('CheckUPSmode',$this->CheckUPSmode);

        $this->viewer->assign('galiTrintiSiuntas',$this->galiTrintiSiuntas);

        // Load Viewer:
        $list_html = $this->viewer->fetch('tvs/sandelys.tpl');
        $this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //
    }

}// end class

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new sandelysController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
