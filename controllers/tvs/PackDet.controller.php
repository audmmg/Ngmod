<?php

//error_reporting(E_ALL);
//
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '/../controller.php';

class PackDetController extends controller {


//**************************************************************************
 
 /*
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
*/

//****************************************************************************
//*************************************KONSTRUKTORIUS*************************
function __construct() {
    parent::__construct();
                
    if ($this->getVar('a')) {
        $this->action = $this->getVar('a');
    } else {
        $this->action = "det";
    }

    $this->packNr = $this->getVar('p');//pakuotes ID
    $this->SiuntaNr = $this->getVar('s');//Siuntos ID

    if($this->packNr){
        $this->SiuntaNr = "";
    }
    /*
    $this->PackingSlipID = $this->getVar('ps');//packingSlip ID
    $this->SalesOrderID = $this->getVar('so');//SalesOrder ID
    */

    //echo"<br><br><br>";var_dump($this->packNr);

    $this->sDuom = $this->getVar('sDuom');


        //permusam prioriteta pries POST ... GET svarbiau
        if($this->packNr){
            $this->sDuom['sPakuote']=$this->packNr;
        }

        if($this->SiuntaNr){ 
            $this->sDuom['sSiunta']=$this->SiuntaNr;
        }
        /*
        if($this->PackingSlipID){
            $this->sDuom['sPackingSlip']=$this->PackingSlipID;
        }
        if($this->SalesOrderID){
            $this->sDuom['sOrderID']=$this->SalesOrderID;
        }
        */

        //Jeigu yra pakuotės nr, tai neimam siuntos... ir taip surasim, tik painiavos bus vaziau
        if($this->sDuom['sPakuote']){
            $this->sDuom['sSiunta']='';
        }

    //$this->aktyvusTabas = ""; //$this->dizFormData['valdymas']['tabasAktyvusIDPavad'];//aktyvus tabas
    
 

    //jeigu aktyvus tabas nenustatytas, tai pasiimam is default nustatymu
    /*
    if (!$this->aktyvusTabas){
        for($i=0;$i<count($this->TABuFormavimas);$i++){
            if($this->TABuFormavimas[$i]['aktyvus']== 'TRUE'){
                $this->aktyvusTabas=$this->TABuFormavimas[$i]['IDpavadinimas'];
            }
        }
    }
    $this->tabai=$this->formatingTabs($this->TABuFormavimas, $this->aktyvusTabas);
    */

}//end construct


/*
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
*/
//****************************************************************************


	public function run($param = array()) {
        DEBUG::log($this->action, 'action');

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvs_mod = new tvs_mod();


        //!!!!!! DEBUG
        //$this->var_dump($this->TView, "this->TView <hr>$sql<hr> ");//-----------------DEBUG

        switch ($this->action) {

            case 'det':
//echo "<br><br><br>";
//var_dump($this->sDuom);

                $this->FullPackData = $this->tvs_mod->getFullPackData($this->sDuom);

                //nuskaitom SO/packingslipus/...
                $RefArray = $this->tvs_mod->getSOArrayBySiuntaUID($this->FullPackData['uid']);

                //!!!!!! DEBUG
                //$this->var_dump($RefArray, "RefArray <hr>$sql<hr> ");//-----------------DEBUG


                //nuskaitom failus is visu vietu, viska kas priklauso siuntai
                $this_PS_files_array_str=$this->tvs_mod->getSOFilesArray($RefArray);


                //!!!!!! DEBUG
                //$this->var_dump($this_PS_files_array_str, "this_PS_files_array_str <hr>$sql<hr> ");//-----------------DEBUG

                /*
                $this_PS_files_array = array();
                $this_PS_files_str = '';
                if($SOArray){
                    $idx = 0;
                    foreach ($SOArray as $key => $SOf) {
                        //susirasom i array priklausan2ius failus
                        $this_PS_files_array_tmp=$this->tvs_mod->getSOFilesArray($SOf,$idx);
                        $this_PS_files_array = array_merge($this_PS_files_array, $this_PS_files_array_tmp);
                        $idx = count($this_PS_files_array);
                        //var_dump($this_PS_files_array);
                    }//end foreach

                    if($this_PS_files_array){
                        foreach ($this_PS_files_array as $key => $ff) {
                            $this_PS_files_str .= '<a href="'.$ff['fpath'].'" target = "_file" class="aLink">'.$ff['fname'].'</a><br>';
                        }
                    }else{
                        $this_PS_files_str = ' ';
                    }
                }else{
                        $this_PS_files_str = ' ';
                }//end if
                
                */


                $this->FullPackData['FilesArray'] = $this_PS_files_array_str['fileArray'];
                $this->FullPackData['FilesStr'] = $this_PS_files_array_str['fileStr'];




                //!!!!!! DEBUG
                //$this->var_dump($this->FullPackData, "this->FullPackData <hr>$sql<hr> ");//-----------------DEBUG
                
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
        $custom_javascripts = array('jQuery/ui/jquery.datetimepicker.full','tvs/tvsPak');
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
      
        $this->viewer->assign('FullPackData', $this->FullPackData);

        
        //$this->viewer->assign('SelectedOrderData', $this->SelectedOrderData);


        $sessionID = SESSION::getUserID();
        $this->viewer->assign('sessionID', $sessionID);
        

        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);


        $this->viewer->assign('action',$this->action);
        $this->viewer->assign('packNr',$this->packNr);

        $this->viewer->assign('sDuom',$this->sDuom);


        // Load Viewer:
        $list_html = $this->viewer->fetch('tvs/packDet.tpl');
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
