<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';

class transpController extends controller {


//**************************************************************************
 
 private $TABuFormavimas = array(
            0 => array(
                'pavadinimas'=>"Užsakymai",
                'IDpavadinimas'=>"tabKa",
                'sabl'=>"tvs/transpListView.tpl",
                'aktyvus'=>TRUE
                ),
           1 => array(
                'pavadinimas'=>"Info",
                'IDpavadinimas'=>"tabKaNew",
                'sabl'=>"tvs/transpInfo.tpl",
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

                //Nustatom pradines paieskos reiksmes
                if(!$this->sDuom['sShiped']){
                    $this->sDuom['sShiped']='All';//default
                }   

                if(!$this->sDuom['sInvoiced']){
                    $this->sDuom['sInvoiced']='Y';//default
                }   

                //nustatom pagal koki lauka rusiuoti
                if(!$this->sDuom['sRusiavimas']){
                    $this->sDuom['sRusiavimas']='A.vrzv_dat';//default rusiavimas pagal lyn__ref
                    $this->sDuom['sRusiavimasAscDesc']='DESC';//rusiavimo kryptis A-Z Z-A
                }   

                if(!$this->sDuom['sDatNuo'] OR $this->sDuom['sDatNuo']>date("Y:m:d H:i:s")){
                    $this->sDuom['sDatNuo'] = date("Y-m-d 00:00:00");
                }
                if(!$this->sDuom['sDatIki'] OR $this->sDuom['sDatIki']>date("Y:m:d H:i:s")){
                    $this->sDuom['sDatIki'] = date("Y-m-d 23:59:59");
                }
                if($this->sDuom['sDatNuo'] > $this->sDuom['sDatIki']){
                    $this->sDuom['sDatNuo'] = $this->sDuom['sDatIki'];
                }

                if(!$SearchDuom['sRusiavimas']){
                    $SearchDuom['sRusiavimas']="TR_ExpShipDate";
                    $SearchDuom['sRusiavimasAscDesc']='DESC';
                }


                $this->orderList = $this->tvs_mod->getOrderTranspList($this->sDuom);
                //!!!!!! DEBUG
                //$this->var_dump($this->TView, "this->TView <hr>$sql<hr> ");//-----------------DEBUG
                
                if($this->is_ID($this->SelectedOrderUID)){
                    $this->SelectedOrderData = $this->tvs_mod->getOrderData($this->SelectedOrderUID);

                    //$this->fileArray = $this->newGamMod->getDocFileArray($this->SelectedNewGamData);
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
        $custom_javascripts = array('jQuery/ui/jquery.datetimepicker.full','tvs/tvsTransp');
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
        //$this->viewer->assign('fileArray',$this->fileArray);

        // Load Viewer:
        $list_html = $this->viewer->fetch('tvs/transp.tpl');
        $this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //
    }

}// end class

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new transpController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
