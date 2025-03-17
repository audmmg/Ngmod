<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//ini_set("display_errors", 1);

$script_path = dirname(__FILE__);
require_once $script_path . '../../controller.php';

class listPretenzijaController extends controller {


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
       
        if($_POST['save']==1){
            $this->pretenzijaMod->savePretenzija($_POST['p']);
            if(!empty($_POST['p']['why1'])){
                $this->pretenzijaMod->saveRootCause($_POST['p']);  
            }
            if($_POST['p']['pdf'] == 1){
                $this->generateRootCausePdf();  
            }
        }
        
        $this->viewer->assign('controllerErrorsArray', $this->getErrorArray());
        $this->viewer->assign('tabai',$this->tabai);
        $this->viewer->assign('aktyvusTab',$this->aktyvusTabas);
        $this->viewer->assign('url',parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        $this->viewer->assign('today',date("Y-m-d"));
        if($_POST['export']==1){
            $f=$_POST['filter'];
            $f['per_page']=9999999;
            $f['page']=1;
            $pretenzijos=$this->pretenzijaMod->getAllPretenzijos(0,$f);    
        }
        else{
            $pretenzijos=$this->pretenzijaMod->getAllPretenzijos(0,$_POST['filter']);    
        }
        
        if(empty($pretenzijos['filter']['reason'])){$pretenzijos['filter']['reason']=[];}
        $this->viewer->assign('pretenzijos',$pretenzijos['list']);
        
        $this->viewer->assign('pa',$pretenzijos['pn']);
        $this->viewer->assign('vi',$pretenzijos['vn']);
        $this->viewer->assign('is',$pretenzijos['in']);
        $this->viewer->assign('bendri',$pretenzijos['bn']);
        
        $this->viewer->assign('pages',$pretenzijos['page']);
        $this->viewer->assign('filter',$pretenzijos['filter']);
        $this->viewer->assign('filternames',$pretenzijos['filternames']);
        $this->viewer->assign('filterpriezastys',$pretenzijos['filterpriezastys']);
        $this->viewer->assign('counted',$pretenzijos['counted']);
        // Load Viewer:
        $list_html = $this->viewer->fetch('nz/pretenzija/list.tpl');
        $this->viewer->clearAllAssign();
        if($_POST['export']==1){
            //var_dump($pretenzijos['list']);die;
        $this->viewer->assign('pretenzijos',$pretenzijos['list']);
        $html = $this->viewer->fetch('nz/pretenzija/export.tpl');
        header("Content-type: application/x-msdownload; charset=utf-8"); 
        header("Content-Disposition: attachment; filename=exported.xls"); 
        header("Pragma: no-cache"); 
        header("Expires: 0"); 
        echo $html;
        die;            
        }

        parent::print_view($list_html, $headCode); //
    }
    
    public function generateRootCausePdf(){
            error_reporting(0);
            ini_set('display_errors', 0);
            $this->viewer->assign('url',parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
            $this->viewer->assign('today',date("Y-m-d h:i:s"));   
            $this->viewer->assign('created_by',SESSION::getUserID()); 
            $data=$this->pretenzijaMod->getPretenzijaInfo($_POST['p']['uid']); 
            $data['asmenys']=$this->asmenysMod->asmenysList(1);
            if(!empty($data['asmenys']) && count($data['asmenys'])>0){
                foreach($data['asmenys'] as $k=>$itm){
                    $data['asmenys'][$k]['files']= $this->asmenysMod->getUploadedFiles($data['dokumento_nr'],$itm['user_id'],1);   
                    $data['asmenys'][$k]['images']= $this->asmenysMod->getUploadedImages($data['dokumento_nr'],$itm['user_id'],1);   
                }
            }
            //var_dump($data);die;
            $this->viewer->assign('info',$data); 
            $this->viewer->assign('post',$_POST['p']); 
        
        
        $list_html = $this->viewer->fetch('nz/pretenzija/rootcause/pdf.tpl');
        //echo $list_html;die;
        $ds="/";$storeFolder = 'uploads';$nr="pretenzija".$data['dokumento_nr'];
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr)) {
                mkdir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr, 0777, true);
        } 
        //echo "aaa";die;
        //echo COMMON::getRootFolder() . '/classes/MPDF/mpdf.php';
        
        require_once COMMON::getRootFolder() . '/classes/MPDF/mpdf.php';
        $mpdf=new mPDF('utf-8', 'A4-L');
        $mpdf->allow_charset_conversion=true;
        $mpdf->charset_in='UTF-8';
        $mpdf->WriteHTML($list_html);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->Output(dirname(__FILE__) . "/../../uploads/pretenzija".$data['dokumento_nr']."/rootcause".$data['dokumento_nr'].".pdf", "F");        
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
    $controller = new listPretenzijaController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}
