<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';
require_once($script_path . '/../../classes/TVSconfig.php');

class siuntosController extends controller {


//**************************************************************************
 
 private $TABuFormavimas = array(
            0 => array(
                'pavadinimas'=>"Užsakymai",
                'IDpavadinimas'=>"tabKa",
                'sabl'=>"tvs/siuntosListView.tpl",
                'aktyvus'=>TRUE
                ),
           1 => array(
                'pavadinimas'=>"Info",
                'IDpavadinimas'=>"tabKaNew",
                'sabl'=>"tvs/siuntosInfo.tpl",
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

    $this->sDuom = $this->getVar('sDuom');

    if(!$this->sDuom['sSiuntaRegDate']){
        $this->sDuom['sSiuntaRegDate'] = date("Y-m-d");
    }

    $this->aktyvusTabas = ""; //$this->dizFormData['valdymas']['tabasAktyvusIDPavad'];//aktyvus tabas
    
 

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

                $this->siuntosList = $this->tvs_mod->getSiuntosList($this->sDuom);
                

                //Renkam duomenys VENIPAKUI DET
                $this->OpenVPManifestKPG = $this->tvs_mod->getVP_Manifest('KPG');
                $this->OpenVPManifestKEG = $this->tvs_mod->getVP_Manifest('KEG');



                //Renkam duomenys SCHENKER DET




                //!!!!!! DEBUG
                //$this->var_dump($this->siuntosList, "this->siuntosList <hr>$sql<hr> ");//-----------------DEBUG
                
                /*
                if($this->is_ID($this->SelectedOrderUID)){
                    $this->SelectedOrderData = $this->tvs_mod->getSiuntosData($this->SelectedOrderUID);

                    //$this->fileArray = $this->newGamMod->getDocFileArray($this->SelectedNewGamData);
                }
                */

                $this->OutputForma();
                break;
            default:
                $this->OutputForma();
        }
    }// end function run

//===============================AJAX==================================================





//=================================================================================

    public function OutputForma() {


        // list of used javascript files:
        $form_javascripts = array('tabs');
        $custom_javascripts = array('jQuery/ui/jquery.datetimepicker.full','tvs/tvsTransp','jr/dropzone');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $custom_css = array('themes/base/jquery.ui.all', 'styleTvs', 'jquery.datetimepicker','jr/dropzone');
        $form_css = array('tabs', 'forms');
        
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);

        //$messageDiv=$this->getMessageArrayAsStr();
        //$errorDiv=$this->getErrorArrayAsStr();

        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->viewer->assign('siuntosList', $this->siuntosList);

        
        //$this->viewer->assign('SelectedOrderData', $this->SelectedOrderData);


        $sessionID = SESSION::getUserID();

        $this->viewer->assign('sessionID', $sessionID);
        //$this->viewer->assign('CB', $this->CB);
        

        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);
        $this->viewer->assign('OpenVPManifestKPG',$this->OpenVPManifestKPG);
        $this->viewer->assign('OpenVPManifestKEG',$this->OpenVPManifestKEG);
        
        $this->CheckVPmode = TVS_CONFIG::VENIPAK_MODE;
        $this->viewer->assign('CheckVPmode',$this->CheckVPmode);



        $this->viewer->assign('action',$this->action);
        //$this->viewer->assign('SelectedOrderUID',$this->SelectedOrderUID);
        $this->viewer->assign('sDuom',$this->sDuom);
        //$this->viewer->assign('fileArray',$this->fileArray);

        // Load Viewer:
        $list_html = $this->viewer->fetch('tvs/siuntos.tpl');
        $this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //
    }

}// end class

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new siuntosController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
