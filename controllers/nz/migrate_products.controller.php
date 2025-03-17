<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '../../controller.php';

class migrate_productsController extends controller {


//**************************************************************************
 

 private $TABuFormavimas = array(

            0 => array(
                'pavadinimas'=>"Pirmas Tab",
                'IDpavadinimas'=>"tabBi",
                'sabl'=>"nz/tab1.tpl",
                'aktyvus'=>TRUE
                ),
            1 => array(
                'pavadinimas'=>"Info",
                'IDpavadinimas'=>"tabInf",
                'sabl'=>"nz/tab2.tpl",
                'aktyvus'=>FALSE
                )

         );



//****************************************************************************
//*************************************KONSTRUKTORIUS*************************

function __construct() {
    parent::__construct();
          
    /* ************* TABU valdymas *********** */
    $this->aktyvusTabas = ""; 
    //jeigu aktyvus tabas nenustatytas, tai pasiimam is default nustatymu
    if (!$this->aktyvusTabas){
        for($i=0;$i<count($this->TABuFormavimas);$i++){
            if($this->TABuFormavimas[$i]['aktyvus']== 'TRUE'){
                $this->aktyvusTabas=$this->TABuFormavimas[$i]['IDpavadinimas'];
            }
        }
    }
    $this->tabai=$this->formatingTabs($this->TABuFormavimas, $this->aktyvusTabas);

    /* ********** pasiimam GET/ POST ************ */
    //$this->screen = $this->getVar('manoVar');


    /* *********** prijungiam modeli *************** */
    $root_pathU = COMMON::getRootFolder();
    require_once ($root_pathU . "modules/nz/nz.mod.php");
    $this->nzMod = new nz_mod();

    $root_pathU = COMMON::getRootFolder();
    //require_once ($root_pathU . "modules/nz/nzCerm.mod.php");
    //$this->nzCermMod = new nzCerm_mod();

    /* ********* pasiimam vartotojo duomenis ************ */
    /*
        $this->userUID = SESSION::getUserID();
        $this->userName = SESSION::getUserName();
    
        $userData['id']= SESSION::getUserID();
        $userData['name'] = SESSION::getUserName();
        $userData['pareigos'] = SESSION::getUserPareigos();
        $userData['login'] = SESSION::getUserLogin();
        $userData['language'] = SESSION::getUserInterfaceLanguage();
    */

       
}//end construct




//****************************************************************************

	public function run($param = array()) {
        //DEBUG::log($this->action, 'action');

        $this->nzArray = $this->nzMod->getNzArray();

        
        if(is_array($this->nzArray) AND count($this->nzArray)>0){
            foreach ($this->nzArray as $nzData) {
                $this->Rez[] = $nzData['uid'];
            }
        }//end if

        //!!!!!! DEBUG
        //$this->var_dump($this->menesioOEE, "this->menesioOEE ARRAY11<hr>$qry");//-----------------DEBUG


        $this->showHTML();
    }// end function run



//=================================================================================

public function showHTML() {

        // list of used javascript files:
        $form_javascripts = array('tabs' );
        $custom_javascripts = array('nz', 'jr/dropzone','jr/nz');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $form_css = array('tabs', 'forms');
        $custom_css = array('themes/base/jquery.ui.all', 'jr/nz', 'jr/dropzone', 'jr/font-awesome');
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);


        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->viewer->assign('RezArray', $this->Rez);
        


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////ASSIGNS END/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         
         echo "Migrating products...<br />";
         $this->nzMod->migrate_products();
         echo "Migrating products end...";die;
        
        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);
        $this->viewer->assign('url',parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        $this->viewer->assign('naujausi',$this->nzMod->getNaujausi());
        $this->viewer->assign('jobs',$this->nzMod->getMyJobs(SESSION::getDarbuotojasID()));
        $this->viewer->assign('visits',$this->nzMod->getMyVisits(SESSION::getUserName()));

        // Load Viewer:
        $list_html = $this->viewer->fetch('nz/nz.tpl');
        $this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //
    }


    /* NEKEISTI */
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



}// end class

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new migrate_productsController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
