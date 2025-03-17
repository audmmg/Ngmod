<?php
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();

$gmod_root_path = dirname(__FILE__) . '/../../../'; //kelias iki nGmod sistemos root
require_once $gmod_root_path . "common.php";


//$root_path = dirname(__FILE__) ;

/*BArkodo generavimui */
require_once $gmod_root_path .  'vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;

/*PDF generavimui */
require_once ($gmod_root_path . "classes/DOMPDF/vendor/autoload.php");
use Dompdf\Dompdf;
use Dompdf\Options;

require_once ($gmod_root_path . "classes/PDF/vendor/autoload.php");
require_once ($gmod_root_path . "controllers/controller.php");

require_once ($gmod_root_path. "classes/session.php");

session_name ("CERMCalc");
session_start();

header("Set-Cookie: PHPSESSID=" . session_id() . "; path=/");
class ajaxCAT_PDFController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError();

        $this->UzsUid = $this->getVar('uuid');

        //jeigu nera nei vieno UID tai nieko nespausdinam ir iseinam
        if(!$this->UzsUid){
                $duom['error']='NOTOK';
                $duom['errorMsg']="Nėra duomenų apie siuntą.";
            echo "**--**";
            echo json_encode($duom);
            die("");//nutraukiam scripto darba
        }//

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/CATpdf.mod.php");
        $this->catPDFMod = new CATpdf_mod();

        var_dump ($this->UzsUid);

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {



        /* ********************* GENERUOJAM BARKODA ******************* */


        $data = $this->UzsUid; // Duomenys, kuriuos norite koduoti

        // HTML formato barkodas
        $generatorHTML = new BarcodeGeneratorHTML();
        echo 'aaaaa<h3>Code-128 Barkodas (HTML):</h3>';
        $barkodeHTML =  $generatorHTML->getBarcode($data, $generatorHTML::TYPE_CODE_128,3,50);
        echo "<hr>" . $barkodeHTML . "<hr>";

        // PNG formato barkodas
$generatorPNG = new BarcodeGeneratorPNG();
$barcode = $generatorPNG->getBarcode($data, $generatorPNG::TYPE_CODE_128,3,50);

        // Išsaugome PNG failą
        file_put_contents('code128.png', $barcode);
        //echo '<h3>Code-128 Barkodas (PNG):</h3>';
        //echo '<img src="code128.png" alt="Barkodas">';        




        /* ***************** KURIAM LIPDUKA ***************** */

        $this->rez = $this->catPDFMod->sukurtiLipduka($this->UzsUid, $barkodeHTML);
        //var_dump ($this->rez);
        
        if(!$this->rez)
        {
            $this->addErrorArray($this->catPDFMod->getErrorArray());
            $this->modError = $this->catPDFMod->getMessageArrayAsStr();
            return $this->modError;
        }





        
        //ob_end_clean();
        $options = new Options;
        $options->setChroot(__DIR__);
        $options->setIsRemoteEnabled(true);
        $options->set('isHtml5ParserEnabled', true);

        //var_dump($this->rez);
        if(empty($this->uid2))
            {$this->uid2=0;}
        $dompdf = new Dompdf($options);
        //$dompdf->setPaper("A6", "landscape");//portrait
        // Nustatykite lapo dydį (148x104 mm) ir orientaciją (landscape)
        $customPaper = array(0, 0, 104 * 2.83465, 148 * 2.83465); // Paverčiame milimetrus į taškus (1 mm ≈ 2.83465 pt)
        $dompdf->setPaper($customPaper, 'landscape');        

        $html = mb_convert_encoding($this->rez, 'HTML-ENTITIES', 'UTF-8');

        $dompdf->loadHtml($html);
        $dompdf->render();

        //$dompdf->addInfo("Title", "An Example PDF"); // "add_info" in earlier versions of Dompdf

        /**
         * Send the PDF to the browser
         */
        //$dompdf->stream("invoice.pdf", ["Attachment" => 0]);
        $output = $dompdf->output();
        //var_dump ($output);
        $root_path = COMMON::getRootFolder();
        $ReturnFileName='CAT_'.$this->UzsUid.'.pdf';
        $FileNameWithPath=$root_path . 'uploads/tvsLabel/'.$ReturnFileName;
        
        var_dump($FileNameWithPath);
        $FileRez = file_put_contents($FileNameWithPath, $output);//grazina file size (bite) arba false;
        //var_dump ($FileRez);

        //$FileName = "pasiulimas_86_87.pdf"; ///CIA TIK TESTAVIMUI
        $duom['duom']['FileName']=$ReturnFileName;

        if($duom['duom']['FileName'] AND $Klaida!="Y"){
            $duom['error']='OK';
            $duom['errorMsg']="";
        }else{
            $duom['error']='NOTOK';
            $duom['errorMsg']=$errorMsg;
        }

        

        echo "**--**";
        echo json_encode($duom);
    }//end function


////////////////////////////////////////////////////////////////////////
}//end class


if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxCAT_PDFController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>