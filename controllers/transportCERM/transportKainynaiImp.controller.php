<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';

class transportKainynaiImpController extends controller {


//**************************************************************************
 
 private $TABuFormavimas = array(
            0 => array(
                'pavadinimas'=>"Kainynai",
                'IDpavadinimas'=>"tabKa",
                'sabl'=>"transportasCERM/dizKainosView.tpl",
                'aktyvus'=>TRUE
                ),
           1 => array(
                'pavadinimas'=>"Naujas kainynas",
                'IDpavadinimas'=>"tabKaNew",
                'sabl'=>"transportasCERM/dizKainosNew.tpl",
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

    $this->SelectedKainUID = $this->getVar('sk');


    $this->aktyvusTabas = ""; //$this->dizFormData['valdymas']['tabasAktyvusIDPavad'];//aktyvus tabas
    
    //jeigu buvo failo ikelimas tai aktyvus tabas bus "Naujas kainynas"
    if($this->action=="in"){
        $this->aktyvusTabas="tabKaNew";
    }//end if

    if($this->action=="UP"){
        $this->aktyvusTabas="tabKaNew";
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


        $this->TView = $this->getVar('TView');
        //!!!!!! DEBUG
        //$this->var_dump($this->TView, "this->TView <hr>$sql<hr> ");//-----------------DEBUG

        switch ($this->action) {

            case 'UP':
                $this->uplUID = $this->getVar('uid');
                $this->uplKainynasData = $this->transportMod->getKainynasData($this->uplUID);
                $this->KainInForma();
                break;
            case 'in':
                $insRezArray = $this->KainInsert();
                if($insRezArray['OK']=="OK"){
                    $this->insRezKainynas = $this->transportMod->getKainynasData($insRezArray['kainynoUID']);
                    $this->InOK = $insRezArray['OK'];
                    $this->InOKUID = $insRezArray['kainynoUID'];
                }else{
                    $this->InOK = $insRezArray['NOTOK'];
                    $this->InOKUID = null;
                    
                }
                $this->KainInForma();
                break;
            case 'del':
                $this->delUID = $this->getVar('uid');
                if($this->is_ID($this->delUID)){
                    $this->delRezKainynas = $this->transportMod->delKainynas($this->delUID);
                }//end if UID
                $this->addErrorArray($this->transportMod->getErrorArray());
                $this->addMessageArray($this->transportMod->getMessageArray());
                $this->KainInForma();
                break;
            case 'filt':

                $this->KainInForma();
                break;

            default:
                $this->KainInForma();
        }
    }// end function run

//===============================AJAX==================================================

//iveda nauja arba updatina kainyna
private function KainInsert() {



        $insRezArray = $this->transportMod->kainynasInsert();
        
        $this->addErrorArray($this->transportMod->getErrorArray());
        $this->addMessageArray($this->transportMod->getMessageArray());

        return $insRezArray;
}//END KainInsert







//=================================================================================

    public function KainInForma() {


        // list of used javascript files:
        $form_javascripts = array('tabs');
        $custom_javascripts = array('transportCERM/transpCERM');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $form_css = array('tabs', 'forms');
        $custom_css = array('themes/base/jquery.ui.all');
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);

        //$messageDiv=$this->getMessageArrayAsStr();
        //$errorDiv=$this->getErrorArrayAsStr();

        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->viewer->assign('dizFormaData', $this->dizFormaData);

        //sukuriam  modeli
        /*
        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/diz/dizForma.mod.php");
        $this->dizFormaModelis = new dizForma_mod();
        */


        //$this->dizFormaDBData = $this->transportasC->dizFormaDBData($this->dizFormaUID);
        //$this->dizFormaDBData = "LABAS";
        $this->viewer->assign('FormaDBData', $this->dizFormaDBData);


        $sessionID = SESSION::getUserID();

        $this->viewer->assign('sessionID', $sessionID);

        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);



        //surenkam duomenis pirmam TABui

        //ziurim ar reikia kazka rinkti
        
        $this->viewer->assign('TView', $this->TView);
        $where="";
        if(is_array($this->TView) AND count($this->TView)>0){
            if($this->TView['vDate']){
                if($where==""){
                    $where=" WHERE (DateIved>='".$this->TView['vDate']." 00:00:00' AND DateIved<='".$this->TView['vDate']." 23:59:59') ";
                }else{
                    $where.=" AND (DateIved>='".$this->TView['vDate']." 00:00:00' AND DateIved<='".$this->TView['vDate']." 23:59:59') ";
                }//end else
            }//end if
            if($this->TView['vVezejas']){
                if($where==""){
                    $where=" WHERE Imone='".$this->TView['vVezejas']."' ";
                }else{
                    $where.=" AND Imone='".$this->TView['vVezejas']."' ";
                }//end else
            }//end if
            if($this->TView['vSalis']){
                if($where==""){
                    $where=" WHERE Salis='".$this->TView['vSalis']."' ";
                }else{
                    $where.=" AND Salis='".$this->TView['vSalis']."' ";
                }//end else
            }//end if
            if($this->TView['vBudas']){
                if($where==""){
                    $where=" WHERE Budas='".$this->TView['vBudas']."' ";
                }else{
                    $where.=" AND Budas='".$this->TView['vBudas']."' ";
                }//end else
            }//end if
            if($this->TView['vImEx']){
                if($where==""){
                    $where=" WHERE ExIm='".$this->TView['vImEx']."' ";
                }else{
                    $where.=" AND ExIm='".$this->TView['vImEx']."' ";
                }//end else
            }//end if
        }//end if
        $this->KainynList =  $this->transportMod->getKainynaiList ($where);
        //paimam visu kainynu lista        
        $this->viewer->assign('KainynList', $this->KainynList);

        //paruosiam paieskos listus
        $vVezejai =  $this->transportMod->getVezejaiArray ();
        $this->viewer->assign('vVezejai', $vVezejai);

        $vSalys =  $this->transportMod->getSalysArray ();
        $this->viewer->assign('vSalys', $vSalys);

        $vBudai =  $this->transportMod->getBudasArray ();
        $this->viewer->assign('vBudai', $vBudai);

        $vImExai =  $this->transportMod->getExImArray ();
        $this->viewer->assign('vImExai', $vImExai);

        
        if(!$this->SelectedKainUID){
            // NEISVEDAM NIEKO $this->SelectedKainUID=$this->KainynList['0']['uid'];
        }

        if($this->is_ID($this->SelectedKainUID)){
            $this->viewer->assign('SelectedKainUID', $this->SelectedKainUID);
            //paimam vieno kainyno duomenis
            $this->SelectedKainData=$this->transportMod->getKainynasData ($this->SelectedKainUID);
            //!!!!!! DEBUG
            //$this->var_dump($this->SelectedKainData, "this->SelectedKainData zzz ");//-----------------DEBUG

            $this->viewer->assign('SelectedKainData', $this->SelectedKainData);
        }

        //po inserto isvedam kainyna
        if($this->action == 'in'){
            $this->viewer->assign('action',$this->action);
            $this->viewer->assign('insKainynas',$this->insRezKainynas);
            $this->viewer->assign('InOK',$this->InOK);
            $this->viewer->assign('InOKUID',$this->InOKUID);
        }

        //po inserto isvedam kainyna
        if($this->action == 'UP'){
            $this->viewer->assign('action',$this->action);
            $this->viewer->assign('uplKainynasData',$this->uplKainynasData  );
        }

      
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
