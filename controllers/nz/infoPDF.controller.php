<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '../../controller.php';

class infoPDFController extends controller {


//**************************************************************************
 

 private $TABuFormavimas = array(

            0 => array(
                'pavadinimas'=>"Naujas užsakovo auditas",
                'IDpavadinimas'=>"tabBi",
                'sabl'=>"nz/auditas/new.tpl",
                'aktyvus'=>TRUE
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
    require_once ($root_pathU . "modules/nz/pretenzija.mod.php");
    $this->pretenzijaMod = new pretenzija_mod();
    
    require_once ($root_pathU . "modules/nz/asmenys.mod.php");
    $this->asmenysMod = new asmenys_mod();
    
    require_once ($root_pathU . "modules/nz/nzCerm.mod.php");
    $this->nzCermMod = new nzCerm_mod();

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

        //$this->nzArray = $this->nzMod->getNzArray();

        
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
        $custom_javascripts = array('nz', 'jr/dropzone','jr/nz','jr/chosen.jquery','jr/jquery.modal');
        // send variables to the main controller(will be passed to viewer in footer.tpl):
        $this->formJavascripts($form_javascripts);
        $this->customJavascripts($custom_javascripts);

        // list of CSS file:
        $form_css = array('tabs', 'forms');
        $custom_css = array('themes/base/jquery.ui.all', 'jr/nz', 'jr/dropzone', 'jr/font-awesome','jr/chosen','jr/jquery.modal');
        // send variables to the main controller(will be passed to viewer in header.tpl):
        $this->formCSS($form_css);
        $this->customCSS($custom_css);


        //HTML, JS kodas i HEADERi
        $this->headCode = "";
      
        $this->viewer->assign('RezArray', $this->Rez);
        


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////ASSIGNS END/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //jei gaunam save atliekam saugojimo veiksmus
        $data=$this->pretenzijaMod->getPretenzijaInfo($this->getVar('uid'));
        $client=$this->nzCermMod->getClientContacts($data['kliento_id']);
       
        //isverstos frazes turi pirmuma i pdf
        if(!empty($data['papildoma_informacija_lang']))$data['papildoma_informacija']=$data['papildoma_informacija_lang'];
        if(!empty($data['tyrimo_eiga_lang']))$data['tyrimo_eiga']=$data['tyrimo_eiga_lang'];
        if(!empty($data['isvada_lang']))$data['isvada']=$data['isvada_lang'];
        if(!empty($data['comment_lang']))$data['comment']=$data['comment_lang'];
        $this->pretenzijaMod->setPdfState($this->getVar('uid'));
        $this->viewer->assign('data',$data);
        $this->viewer->assign('jobs',$this->asmenysMod->asmenysList(1)); 
        $this->viewer->assign('url',parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        $userData['name'] = SESSION::getUserName();
        $userData['pareigos'] = SESSION::getUserPareigos();
        $this->viewer->assign('user',$userData);
        $this->viewer->assign('signature',COMMON::getRootFolder()."/img/signature/".SESSION::getUserID().".png");
        $this->viewer->assign('trans',$this->get_trans($client['CI_SaliesKodas']));
        
        
        $list_html = $this->viewer->fetch('nz/pretenzija/pdf.tpl');
        //echo $list_html;die;
        $ds="/";$storeFolder = 'uploads';$nr="pretenzija".$data['dokumento_nr'];
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr)) {
                mkdir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr, 0777, true);
        } 
        //echo "aaa";die;
        //echo COMMON::getRootFolder() . '/classes/MPDF/mpdf.php';
        require_once COMMON::getRootFolder() . '/classes/MPDF/mpdf.php';
        $mpdf=new mPDF();
        $mpdf->WriteHTML($list_html);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->Output(dirname(__FILE__) . "/../../uploads/pretenzija".$data['dokumento_nr']."/answer".$data['dokumento_nr'].".pdf", "F");
        //$mpdf->Output("Claim".$this->getVar('uid').".pdf", "I");die;
        $url=parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        header('Location: '.$url."?rc=pretenzijaEdit&rd=nz&uid=".$this->getVar('uid')."&nz_tipas=0");
        die;
        /*$this->viewer->clearAllAssign();

        parent::print_view($list_html, $headCode); //*/
    }
    
    public function get_trans($lang){
        if(strtolower($lang)=='lt'){
            $trans['ats']="Atsakymas į pretenziją"; 
            $trans['nr']="NŽ Nr.";
            $trans['data']="Data";
            $trans['uzsakovas']="Užsakovas";
            $trans['pretenzijos_nr']="Pretenzijos nr.";
            $trans['siunte']="Pretenziją siuntė";
            $trans['gauta']="Pretenzija gauta";
            $trans['pavadinimas']="Gaminio pavadinimas";
            $trans['uzsak_nr']="Užsakymo numeris/Partijos numeris";
            $trans['gam_nr']="Gaminio numeris/Gamybos data";
            $trans['apibudinimas']="Pretenzijos apibūdinimas";
            $trans['priezastis']="Neatitikties esminė priežastis";
            $trans['isvados']="Išvados dėl neatitikties";
            $trans['priimama']="Priimama";
            $trans['nesutinkama']="Nesutinkama";
            $trans['komentaras']="Komentaras";
            $trans['veiksmai']="Atlikti veiksmai";
            $trans['atsakingi']="Atsakingi asmenys";
            $trans['atlikimo_data']="Atlikimo data";
            $trans['statusas']="Statusas";
            $trans['atlikta']="Atlikta";
            $trans['vykdoma']="Vykdoma";   
            $trans['koregavimo']="Koregavimo ir prevenciniai veiksmai";  
            $trans['nagrinejo']="Pretenziją nagrinėjo";  
            $trans['parasas']="Parašas"; 
            $trans['eil']="Eil. Nr";   
        }   
        else{
            $trans['ats']="Answer to nonconformity"; 
            $trans['nr']="Auriko No.";
            $trans['data']="Answer date";
            $trans['uzsakovas']="Customer";
            $trans['pretenzijos_nr']="Nonconformity nr.";
            $trans['siunte']="Nonconformity received from";
            $trans['gauta']="Nonconformity received";
            $trans['pavadinimas']="Product name";
            $trans['uzsak_nr']="Batch number";
            $trans['gam_nr']="Product number/Production date";
            $trans['apibudinimas']="Nonconformity description";
            $trans['priezastis']="Cause of nonconformity";
            $trans['isvados']="Conclusion of nonconformity";
            $trans['priimama']="Accepted";
            $trans['nesutinkama']="Not accepted";
            $trans['komentaras']="Comment";
            $trans['veiksmai']="Actions taken";
            $trans['atsakingi']="Responsible person";
            $trans['atlikimo_data']="Due date";
            $trans['statusas']="Status";
            $trans['atlikta']="Done";
            $trans['vykdoma']="In progress";   
            $trans['koregavimo']="Corrective and preventative actions";  
            $trans['nagrinejo']="Nonconformity investigated by";  
            $trans['parasas']="Signature";    
            $trans['eil']="No.";        
        } 
        return $trans;
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
    $controller = new infoPDFController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
