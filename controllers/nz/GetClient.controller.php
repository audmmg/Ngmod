<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '../../controller.php';

class GetClientController extends controller {


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
    require_once ( $root_path. "modules/nz/nzCerm.mod.php");
    $this->nzCerm = new nzCerm_mod();

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

       


        $this->showHTML();
    }// end function run



//=================================================================================

public function showHTML() {

        $id =$this->getVar('clientid');
        $rezult=$this->nzCerm->getClientContacts($id);
        
        if(count($rezult)>0){
            $ret['name']=$rezult['CI_Pavadinimas'];
            $ret['contact']=$rezult['ASMENYS'][0]['CA_Vardas']." ".$rezult['ASMENYS'][0]['CA_Pavarde'];
            $select="<select id='change_other_contact'>";
            $select.="<option>Kontakto pasirinkimas</option>"; 
            foreach($rezult['ASMENYS'] as $asm){
                $select.="<option>".$asm['CA_Vardas']." ".$asm['CA_Pavarde']." ".$asm['CA_ElPastas']." ".$asm['CA_Telefonas']."</option>";    
            }
            $select.="</select>";
            if(count($rezult['ASMENYS'])>1)$ret['select']=$select;
            else $ret['select']="";
            
            echo json_encode($ret);die;
        }else{
            echo json_encode("");
        }
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
    $controller = new GetClientController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
