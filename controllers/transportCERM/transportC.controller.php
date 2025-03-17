<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';

class transportCController extends controller {


//**************************************************************************
 
 private $TABuFormavimas = array(
            0 => array(
                'pavadinimas'=>"Kainynai",
                'IDpavadinimas'=>"tabKS",
                'sabl'=>"transportasCERM/KainosPaieskaForm.tpl",
                'aktyvus'=>TRUE
                ),
           1 => array(
                'pavadinimas'=>"Pastaba",
                'IDpavadinimas'=>"tabKSPastaba",
                'sabl'=>"transportasCERM/KainosPastaba.tpl",
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
        $this->action = "";
    }



    
    $this->aktyvusTabas = ""; //$this->dizFormData['valdymas']['tabasAktyvusIDPavad'];//aktyvus tabas
    

    //jeigu buvo paspausta IESKOTI pasiimam paieskos formos duomenis
    if($this->action=="P"){
        $this->TSearch = $this->getVar('TSearch');
        $this->TSearch['sSvoris']=str_replace(",",".",$this->TSearch['sSvoris']);

        //!!!!!! DEBUG
        //$this->var_dump($this->TSearch, "TSearch -- TSearch<hr> ");//-----------------DEBUG

    }//end if
                
    

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
        require_once ($root_path . "modules/transportCERM/transportC.mod.php");
        $this->transportMod = new transportC_mod();


        switch ($this->action) {

            case 'P':
                $this->searchRez = $this->transportMod->searchTransport ($this->TSearch);

                $this->addErrorArray($this->transportMod->getErrorArray());
                $this->addMessageArray($this->transportMod->getMessageArray());
                
                break;
            case 'nenaudojama':

                break;

            default:
                
        }
        $this->KainInForma();
    }// end function run

//==========FUNKCIJOS=======================================================================

//==========ISVEDIMAS=======================================================================

    public function KainInForma() {


        // list of used javascript files:
        $form_javascripts = array('tabs');
        $custom_javascripts = array('transportCERM/transpCERM');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $form_css = array('tabs', 'forms');
        $custom_css = array('themes/base/jquery.ui.all', 'demos');
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);

        //$messageDiv=$this->getMessageArrayAsStr();
        //$errorDiv=$this->getErrorArrayAsStr();

        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->sSalys = $this->transportMod->getSalysArray ();
        $this->viewer->assign('sSalys', $this->sSalys);
        //!!!!!! DEBUG
        //$this->var_dump($this->sSalys, "sSalys -- sSalys<hr> ");//-----------------DEBUG


        $this->sBudai = $this->transportMod->getBudasArray ();
        $this->viewer->assign('sBudai', $this->sBudai);
        //!!!!!! DEBUG
        //$this->var_dump($this->sBudai, "sBudai -- sBudai<hr> ");//-----------------DEBUG

        //!!!!!! DEBUG
        //$this->var_dump($this->TSearch, "TSearch -- TSearch<hr> ");//-----------------DEBUG

        $this->viewer->assign('TSearch', $this->TSearch);


        //sukuriam  modeli
        /*
        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/diz/dizForma.mod.php");
        $this->dizFormaModelis = new dizForma_mod();
        */

        if(is_array($this->searchRez['Duom']) AND $this->searchRez['OK']=='OK'){
            $this->viewer->assign('searchRez', $this->searchRez['Duom']);
            //!!!!!! DEBUG
            //$this->var_dump($this->searchRez, "searchRez -- searchRez<hr> ");//-----------------DEBUG
        }//end if

        $sessionID = SESSION::getUserID();

        $this->viewer->assign('sessionID', $sessionID);

        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);


        // Load Viewer:
        $list_html = $this->viewer->fetch('transportasCERM/dizKainos.tpl');
        $this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //
    }

}// end class

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new transportKainynaiImpController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
