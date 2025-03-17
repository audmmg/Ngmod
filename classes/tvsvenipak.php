<?php
ob_start();

$script_path = dirname(__FILE__);
$root_path = dirname($script_path);

require_once($script_path . '/data.php');
require_once($root_path . '/config.php');

require_once($root_path . '/classes/xml2array.php');
require_once($root_path . '/classes/tvsclass.php');
require_once($root_path . '/classes/TVSconfig.php');

if (@$_SESSION['debug']>0 || @Config::$DEBUG_MODE > 0 || true) {
    require_once($root_path . '/libs/FirePHPCore/FirePHP.class.php');
}

/**
 * Class TVS SHENKER
 *
 * @author Arnoldas Ramonas
 */
class tvsvenipak  extends tvsclass{
    //private static $useDebug = true;

    public $mode = 'notSet'; //test/live/notSet, veliau ateis is TVSconfig.php TVS_CONFIG::VENIPAK_MODE;
    //public $mode = 'live'; //test/live

    public $manifest="";
    //krovinio registracijos XML ir duomenys
    public $XML_KR ="";
    public $XML_KR_created = false;
    public $XML_KR_send_time = "";
    public $XML_KR_send_OK = false;
    public $XML_KR_send_user = "";
    public $XML_KR_result = "";

    //kurjerio iskvietimo XML ir duomenys
    public $XML_KI ="";
    public $XML_KI_created = false;
    public $XML_KI_send_time = "";
    public $XML_KI_send_OK = false;
    public $XML_KI_send_user = "";
    public $XML_KI_result = "";



    /* Pirminis demo acountas */

    /* senas demo
    private $VUserDemo = "aurikademo2";
    private $VPassDemo = "tk5dz30b0";
    private $VUserIDDemo = "57943";

    private $KrovinioUzsakLinkDemo = "http://demo.venipak.com/import/send.php";
    private $KurjerioIskvietLinkDemo = "http://demo.venipak.com/import/send.php";
    private $LipdukoSpausdLinkDemo = "http://demo.venipak.com/ws/print_label.php";
    private $ManifestoSpausdLinkDemo = "http://demo.venipak.com/ws/print_list.php";
    */

    /* naujas demo */
    /* paseno
    private $VUserDemo = "aurikademo";
    private $VPassDemo = "fy91d3pzp";
    private $VUserIDDemo = "17330";
    
    private $KrovinioUzsakLinkDemo = "httpass://venipak.uat.megodata.com/import/send.php";
    private $KurjerioIskvietLinkDemo = "httpass://venipak.uat.megodata.com/import/send.php";
    private $LipdukoSpausdLinkDemo = "httpass://venipak.uat.megodata.com/ws/print_label.php";
    private $ManifestoSpausdLinkDemo = "httpass://venipak.uat.megodata.com/ws/print_list.php";
    */


    /* naujausia demo */
    private $VUserDemo = "aurikademo3";
    private $VPassDemo = "2f0h275p4";
    private $VUserIDDemo = "17323";
    /* portalas httpass://venipak.uat.megodata.com/siunta2 */
    private $KrovinioUzsakLinkDemo = "http://venipak.uat.megodata.com/import/send.php";
    private $KurjerioIskvietLinkDemo = "http://venipak.uat.megodata.com/import/send.php";
    private $LipdukoSpausdLinkDemo = "http://venipak.uat.megodata.com/ws/print_label.php";
    private $ManifestoSpausdLinkDemo = "http://venipak.uat.megodata.com/ws/print_list.php";



    /* antrinis demo acountas */
    /*
    private $VUserDemo = "aurikademo3";
    private $VPassDemo = "2f0h275p4";
    private $VUserIDDemo = "17323";

    private $KrovinioUzsakLinkDemo = "http://venipak.uat.megodata.com/import/send.php";
    private $KurjerioIskvietLinkDemo = "http://venipak.uat.megodata.com/import/send.php";
    private $LipdukoSpausdLinkDemo = "http://venipak.uat.megodata.com/ws/print_label.php";

    */



    /* LIVE ACOUNT */
    private $VUserLive = "aurika2";
    private $VPassLive = "44pak445";
    private $VUserIDLive = "12229";
    
    private $KrovinioUzsakLinkLive = "https://go.venipak.lt/import/send.php";
    private $KurjerioIskvietLinkLive = "https://go.venipak.lt/import/send.php";
    private $LipdukoSpausdLinkLive = "https://go.venipak.lt/ws/print_label";
    private $ManifestoSpausdLinkLive = "https://go.venipak.lt/ws/print_list.php";
    





    private $VUser = "";
    private $VPass = "";
    private $VUserID = "";


    /*  action nuorodos */

    private $KrovinioUzsakLink = "";
    private $KurjerioIskvietLink = "";
    private $LipdukoSpausdLink = "";
    private $ManifestoSpausdLink = "";




    /* siuntejo duomenys */
    private $SenderName = "Aurika, UAB";
    private $SenderCompanyCode = "132878726";
    private $SenderCountryCode = "LT";
    private $SenderCity = "Kaunas";

    private $SenderAddressKEG = "Taikos pr. 129A";
    private $SenderAddressKPG = "Chemijos g. 29F";
    private $SenderAddressETK = "Jovarų g. 2A";

    private $SenderPostCodeKEG = "51127";
    private $SenderPostCodeKPG = "51333";
    private $SenderPostCodeETK = "47193";

    private $SenderContactPersonKEG = "Edita Kupčiūnienė";
    private $SenderContactPersonKPG = "Edita Kupčiūnienė";
    private $SenderContactPersonETK = "Edita Kupčiūnienė";

    private $SenderContactTelKEG = "+37068802736";
    private $SenderContactTelKPG = "+37068802736";
    private $SenderContactTelETK = "+37068802736";

    private $SenderContactMailKEG = "transportas@aurika.lt";
    private $SenderContactMailKPG = "transportas@aurika.lt";
    private $SenderContactMailETK = "transportas@aurika.lt";

    private $SenderAddress = '';
    private $SenderPostCode = '';
    private $SenderContactPerson = '';
    private $SenderContactTel = '';
    private $SenderContactMail = '';
    private $SenderDataSet = false;




    /* bendrieji duomenys, sablonai */
    private $actionLink = "";

    private $SiuntaData = array();

    /* parameters from form */
    public $sParam = array();



    /* ************* REZ *************** */
    
    public $rezKR_SiuntuNr = array();
    public $rezKI_OrderNr = "";


    function __construct( $mode, $SiuntaUID, $sParam) {//mode = 'test'; //test/live
        parent::__construct();

        $this->mode = TVS_CONFIG::VENIPAK_MODE;

        //20200212 - imam mode taip kaip susetupinta claseje, o ne taip kaip paduodama kuriant objekta
        $mode = $this->mode;

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        $this->SiuntaUID = $SiuntaUID;
        $this->sParam = $sParam;

        

        if($mode=='live'){
            $this->mode = $mode;
            
            $this->VUser = $this->VUserLive;
            $this->VPass = $this->VPassLive;
            $this->VUserID = $this->VUserIDLive;

            $this->KrovinioUzsakLink = $this->KrovinioUzsakLinkLive;
            $this->KurjerioIskvietLink = $this->KurjerioIskvietLinkLive;
            $this->LipdukoSpausdLink = $this->LipdukoSpausdLinkLive;
            $this->ManifestoSpausdLink = $this->ManifestoSpausdLinkLive;
            

            echo "********* $mode=live ************<br>";

        }elseif($mode=='test'){
            $this->mode = $mode;

            $this->VUser = $this->VUserDemo;
            $this->VPass = $this->VPassDemo;
            $this->VUserID = $this->VUserIDDemo;

            
            $this->KrovinioUzsakLink = $this->KrovinioUzsakLinkDemo;
            $this->KurjerioIskvietLink = $this->KurjerioIskvietLinkDemo;
            $this->LipdukoSpausdLink = $this->LipdukoSpausdLinkDemo;
            $this->ManifestoSpausdLink = $this->ManifestoSpausdLinkDemo;


            echo "********* $mode=test ************<br>";

        }else{
            $this->$mode = 'notSet';
            $this->setSenderData ('Clear');
            $this->KrovinioUzsakLink = '';
            $this->KurjerioIskvietLink = '';
            $this->LipdukoSpausdLink = '';
            $this->ManifestoSpausdLink = '';

            echo "********* $mode=NOTSET ************<br>";
        }

        //keiciam siuntejo Persona
        $this->SenderContactPerson = $this->sParam['det_IsvAtsakingas'];
        $this->SenderContactTel = $this->sParam['det_IsvAtsakingasTel'];
        $this->SenderContactMail = $this->sParam['det_IsvAtsakingasEmail'];


        //pasiimam/atsidarom manifesta jeigu tai naujai registruojama siunta
        // bet jeigu tai jau uzregistruota siunta (pvz pries spausdinant lipdukus), tai manifesta reikia nusiskaitineti is DB kur info prie tos siuntos
        $Sandelys = 'NaN';
        if(strtoupper($this->mode) == 'TEST'){
            $this->manifest = $this->tvsMod->generateVP_Manifest($this->VUserID, 'TEST');

            if($this->sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                $Sandelys = 'KEG';
            }else if($this->sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                $Sandelys = 'KPG';
            }else if($this->sParam['sVP_ManifestKEGKPG']=='ETKMAN'){
                $Sandelys = 'ETK';
            }

        }else{
            if($this->sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                //echo "<br>*****VV11111****<br>";
                $this->manifest = $this->tvsMod->generateVP_Manifest($this->VUserID, 'KEG');
                $Sandelys = 'KEG';
            }else if($this->sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                $this->manifest = $this->tvsMod->generateVP_Manifest($this->VUserID, 'KPG');
                //echo "<br>*****VV2222****<br>";
                $Sandelys = 'KPG';
            }else if($this->sParam['sVP_ManifestKEGKPG']=='ETKMAN'){
                $this->manifest = $this->tvsMod->generateVP_Manifest($this->VUserID, 'ETK');
                //echo "<br>*****VV2222****<br>";
                $Sandelys = 'ETK';
            }
        }//end else

        if(!$this->manifest){
                $Error['message'] = $this->tvsMod->getErrorArrayAsStr();
                $Error['code'] = "TAD-0011"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
                //echo "<br>*****VV333****<br>";
        }
        

//______________________________________
        //pasiimam duomenys is TVS_Siuntos, TVS)Pack lenteliu
        if($Sandelys == 'ETK'){
            $SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTranspETK($SiuntaUID, $Sandelys);
        }else{
            $SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID, $Sandelys);
        }

        //echo "******VVVV101010****";
        var_dump($SiuntaDataRez);

        if($SiuntaDataRez['OK']=='OK' AND ($this->mode == 'live' OR $this->mode == 'test')){
            //echo "<br>*****VV444****<br>";
            $this->SiuntaData = $SiuntaDataRez['Duom'];

            //siuntejo adresas
            if($this->SiuntaData['Sandelys']=='KEG' OR $this->SiuntaData['Sandelys']=='KPG' OR $this->SiuntaData['Sandelys']=='ETK' OR $this->SiuntaData['Sandelys']=='ETK1'){
                $this->setSenderData ($this->SiuntaData['Sandelys']);
                //echo "<br>*****VV555****<br>";
            }else{
                $this->setSenderData ('Clear');
                //echo "<br>*****VV666****<br>";
            }

            //paruosiam duomenys
            if(!$this->SiuntaData['GavejasID']){
                //jeigu nera gavejo, tai nurodom, kad gavejas yra klientas
                $this->SiuntaData['GavName']=$this->SiuntaData['ClientName'];
                //echo "<br>*****VV777****<br>";
            }



            //pildom adresus
            $addrRez = $this->setAddress ($this->SiuntaData, 'CONSIGNEE');

            var_dump($addrRez);


            //pildom kiekius ir pakuotes
            $this->setPackBasic ($this->SiuntaData);

            var_dump($this->setPackBasic);

            /*
                tswd – delivery the same working day
                nwd – delivery next working day
                nwd10– delivery next working day till 10:00
                nwd12 – delivery next working day till 12:00
                nwd8_14 – delivery next working day 8:00-14:00
                nwd14_17 – delivery next working day 14:00-17:00
                nwd18_22 – delivery next working day 18:00-22:00
                nwd18a – delivery next working day after 18:00
                sat– delivery on saturday
                If this field is left blank „nwd“ is assigned
            */            



            /*
            if($addrRez){
                //var_dump($this->address);
            }else{
                // nieko
            }
            */


            /*
            $this->tvs['weight']=''; //'5.6' - svoris kg
            $this->tvs['volume']=''; // '0.01' turis m2 
            $this->tvs['pallets']=''; // 1 -paleciu kiekis
            $this->tvs['date_y']=''; // '2019' - metai
            $this->tvs['date_m']=''; // '11' - menuo
            $this->tvs['date_d']=''; // '30' -menesio diena

            $this->tvs['hour_from']=''; //'14' - laikas valandomis
            $this->tvs['min_from']=''; //'10' - laikas minutemis

            $this->tvs['hour_to']=''; //'16' - laikas valandomis
            $this->tvs['min_to']='';// '45' - laikas minutemis

            $this->tvs['comment']='';//Pastaba iki 50 simbolių
            $this->tvs['spp']=''; //2 boxes
            $this->tvs['doc_no']=''; //'HF423'
            */
        }else{//end if
                $Error['message'] = "Nėra duomenų apie siuntą ".$SiuntaUID." ";
                $Error['code'] = "TAD-0001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
                echo "*****Nėra duomenų apie siuntą******";

        }

        //jeigu ivyko klaidu, tai pranesam, kad kazkas blogai
        /* PERKELIAM tai i kontroleri
        if($this->haveErrors()>0){
            $createOK['OK'] = 'NOTOK';
            $createOK['ERROR'] = $this->getErrorsAsHtml ();
        }else{//end if
            $createOK['OK'] = 'OK';
            $createOK['ERROR'] = '';
        }
        */

        //return $createOK;

    }//end function 


    public function run($action, $param = array()){

        switch ($action) {
            case 'KR': //krovinio registravimas
                $aRez = $this->runKR($param);
                break;
            case 'KI': //kurjerio iskvietimas
                $aRez = $this->runKI($param);
                break;
            case 'LS': //Lipduko spausdinimas
                # code...
                break;
            
            default:
                # code...
                break;
        }

        return $aRez;
    }//end function




    private function runKR ($param=array()){ //krovinio registravimas

        //FORMUOJAM XML
        $KR_rez = $this->generateXMLKR ($param);

        //echo "<br>----000---<br>";
        var_dump($KR_rez);

        if($KR_rez == 'ERROR'){
            $this->XML_KR_created = false;
            //echo $this->getErrorsAsHtml ('ALL');
            //echo "<br>----111---";
            $pavyko = false;
            $Error['message'] = "Nepavyko sugeneruoti MANIFEST nr";
            $Error['code'] = "TKR-0110"; //t-transport KI - kurjerio iskvietimas
            $Error['group'] = "KR"; //KI - kirjerio iskvietimas
            $this->addError ($Error);

            return $pavyko;
        }//end if
        //echo "<br>----333---";


        //SIUNCIAM XML
        $pavyko = false;//pradine reiksme
        if ($this->XML_KR_created){
            if($this->VUser AND $this->VPass AND $this->VUserID ){
                //echo "<br>----444---";
                if($this->KrovinioUzsakLink){
                    //echo "<br>----555---";
                        $auth_data = array(
                            'user'=> $this->VUser,
                            'pass'=> $this->VPass,
                            'xml_text'=> $this->XML_KR
                        );

                        //$ActionLink =  "http://demo.venipak.com/import/send.php";



                        /*
                        echo "<hr>".$this->KrovinioUzsakLink."<hr>";
                        var_dump($auth_data);
                        echo "<hr>";
                        */
                        $rez = $this->connectSend($auth_data, $this->KrovinioUzsakLink);
                        
                        $this->XML_KR_result = $rez['response'];

                        var_dump($this->XML_KR_result);

                    $objXML = new xml2Array();
                    //suformuojam parsinta XML i array (lieka objekte ir grazina)
                    $xmlRezArray = $objXML->parse($this->XML_KR_result);

                    //var_dump($xmlRezArray);

                    // pasigerinam grazinama Rezultatu array
                    $xmlCheckArray = $objXML->checkAnswer ('KR');

                    //echo "<br>************CHECK REZ************";
                    //var_dump($xmlCheckArray);

                    if($xmlCheckArray['isAnswer']===true){//ar atejo XML atsakymo tipo
                        //echo "<br>----666---";
                        $this->XML_KR_send_time = date("Y-m-d H:i:s");
                        $this->XML_KR_send_OK = true;
                        $this->XML_KR_send_userUID = SESSION::getUserID();
                        $this->XML_KR_send_user = SESSION::getUserName();

                        if($xmlCheckArray['AnswerType']=='ok'){//ar atsakymas, kad viskas OK ar kad klaida
                            //echo "<br>----777---";
                            if(count($xmlCheckArray['AnswerDataArray'])>0){//jeigu yra bent vienas siuntos numeris
                                //echo "<br>----888---";
                                $this->rezKR_SiuntuNr = $xmlCheckArray['AnswerDataArray']; //(Array)


                                $pavyko = true;
                            }else{
                                $Error['message'] = "Neužregistruota nei viena pakuotė";
                                $Error['code'] = "TKR-0103"; //t-transport KR - krovinio registravimas
                                $Error['group'] = "KR"; //KI - kirjerio iskvietimas
                                $this->addError ($Error);
                                $pavyko = false; 
                            }
                        }elseif($xmlCheckArray['AnswerType']=='error'){
                            
                            if(is_array($xmlCheckArray['AnswerErrorsArray'])){
                                $i = 1;
                                foreach ($xmlCheckArray['AnswerErrorsArray'] as $key => $gerror) {
                                    $pavyko = false; 
                                    $Error['message'] = "[".$gerror['CODE']."] ".$gerror['ERRORTEXT']." ";
                                    $Error['code'] = "TKR-0210-".$i; //t-transport KR - krovinio registravimas
                                    $Error['group'] = "KR"; //KI - kirjerio iskvietimas
                                    $this->addError ($Error);
                                    $i++;
                                    //echo "<br>**-";
                                    //var_dump($this->errorArray);
                                }
                            }else{//end if
                                $pavyko = false; 
                                $Error['message'] = "Įvyko nežinoma klaida registruojant siuntą";
                                $Error['code'] = "TKR-0201"; //t-transport KR
                                $Error['group'] = "KR"; //KI - kirjerio iskvietimas
                                $this->addError ($Error);
                            }

                        }else{
                            $Error['message'] = "Nesuprantamas atsakymas iš VENIPAK sistemos";
                            $Error['code'] = "TKR-0102"; //t-transport KR
                            $Error['group'] = "KR"; //KI - kirjerio iskvietimas
                            $this->addError ($Error);
                            $pavyko = false; 
                        }
                    }else{
                        $Error['message'] = "Nėra atsakymo iš VENIPAK sistemos";
                        $Error['code'] = "TKR-0101"; //t-transport KR
                        $Error['group'] = "KR"; //KI - kirjerio iskvietimas
                        $this->addError ($Error);

                        $pavyko = false; 
                    }
                      

                }else{//end if
                    $Error['message'] = "Bloga užklausos nuoroda";
                    $Error['code'] = "TKR-0003"; //t-transport KR - krovinio registracijos
                    $Error['group'] = "KR"; //KR - krovinio registracijos error
                    $this->addError ($Error);
                    $pavyko = false;

                }//end else

            }else{//end if
                $Error['message'] = "Nėra prisijungimo duomenų";
                $Error['code'] = "TKR-0002"; //t-transport KR - krovinio registracijos
                $Error['group'] = "KR"; //KR - krovinio registracijos error
                $this->addError ($Error);
                $pavyko = false;

            }//end else


        }else{//end if
            $Error['message'] = "Neparuoštas XML krovinio registracijai ";
            $Error['code'] = "TKR-0001"; //t-transport KR - krovinio registracijos
            $Error['group'] = "KR"; //KR - krovinio registracijos error
            $this->addError ($Error);
            $pavyko = false;
        }//end else

        echo "PAVYKO: ". $pavyko."<br><hr>";
        return $pavyko;
    }//end function



    private function runKI ($param=array()){ //kurjerio iskvietimas

        //FORMUOJAM XML
        $KI_rez = $this->generateXMLKI ();

        //echo "<hr>";var_dump($this->XML_KI); echo"<hr>";

        if($KI_rez == 'ERROR'){
            $this->XML_KI_created = false;
            //echo $this->getErrorsAsHtml ('ALL');

            $pavyko = false;

            return $pavyko;
        }//end if



        //SIUNCIAM XML
        $pavyko = false;
        if ($this->XML_KI_created){
            if($this->VUser AND $this->VPass AND $this->VUserID ){

                if($this->KurjerioIskvietLink){

                        $auth_data = array(
                            'user'=> $this->VUser,
                            'pass'=> $this->VPass,
                            'xml_text'=> $this->XML_KI
                        );

                        //$ActionLink =  "http://demo.venipak.com/import/send.php";

                        //var_dump($auth_data);
                        //var_dump($this->KurjerioIskvietLink);

                        $rez = $this->connectSend($auth_data, $this->KurjerioIskvietLink);
                        
                        $this->XML_KI_result = $rez['response'];

                        //echo "<hr>".$this->XML_KI_result;

                        $objXML = new xml2Array();
                        //$arrOutput = $objXML->parse($strYourXML);
                        //suformuojam parsinta XML i array (lieka objekte ir grazina)
                        $xmlRezArray = $objXML->parse($this->XML_KI_result);

                        //echo "<br>************REZ************";
                        //var_dump($xmlRezArray);
                        // pasigerinam grazinama Rezultatu array
                        $xmlCheckArray = $objXML->checkAnswer ('KI');
                        //echo "<br>************CHECK REZ************";
                        //var_dump($xmlCheckArray);

                        if($xmlCheckArray['isAnswer']===true){
                            $this->XML_KI_send_time = date("Y-m-d H:i:s");
                            $this->XML_KI_send_OK = true;
                            $this->XML_KI_send_userUID = SESSION::getUserID();
                            $this->XML_KI_send_user = SESSION::getUserName();
                            
                            if($xmlCheckArray['AnswerType']=='ok'){
                                if($xmlCheckArray['AnswerDataArray']['KI_Order']){
                                    $this->rezKI_OrderNr = $xmlCheckArray['AnswerDataArray']['KI_Order'];


                                    $pavyko = true;
                                }else{
                                    $Error['message'] = "Negautas Orderio numeris iš VENIPAK sistemos";
                                    $Error['code'] = "TKI-0103"; //t-transport KI - kurjerio iskvietimas
                                    $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                                    $this->addError ($Error);
                                    $pavyko = false; 
                                }
                            }elseif($xmlCheckArray['AnswerType']=='error'){
                                
                                if(is_array($xmlCheckArray['AnswerErrorsArray'])){
                                    foreach ($xmlCheckArray['AnswerErrorsArray'] as $key => $gerror) {
                                        $pavyko = false; 
                                        $Error['message'] = "[".$gerror['CODE']."] ".$gerror['ERRORTEXT']." ";
                                        $Error['code'] = "TKI-0210"; //t-transport KI - kurjerio iskvietimas
                                        $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                                        $this->addError ($Error);

                                    }
                                }else{//end if
                                    $pavyko = false; 
                                    $Error['message'] = "Įvyko nežinoma klaida registruojant kurjerio iškvietimą";
                                    $Error['code'] = "TKI-0201"; //t-transport KI - kurjerio iskvietimas
                                    $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                                    $this->addError ($Error);
                                }

                            }else{
                                $Error['message'] = "Nesuprantamas atsakymas iš VENIPAK sistemos";
                                $Error['code'] = "TKI-0102"; //t-transport KI - kurjerio iskvietimas
                                $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                                $this->addError ($Error);
                                $pavyko = false; 
                            }
                        }else{
                            $Error['message'] = "Nėra atsakymo iš VENIPAK sistemos";
                            $Error['code'] = "TKI-0101"; //t-transport KI - kurjerio iskvietimas
                            $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                            $this->addError ($Error);

                            $pavyko = false; 
                        }

                        //parse result
                        /*
                        if($rez['pavyko']===true){

                            //parsinam
                            $pavyko = true;    

                        }//end if
                        */
                        

                }else{//end if
                    $Error['message'] = "Bloga užklausos nuoroda";
                    $Error['code'] = "TKI-0003"; //t-transport KI - kurjerio iskvietimas
                    $Error['group'] = "KI"; //KI - kirjerio iskvietimas
                    $this->addError ($Error);
                    $pavyko = false;

                }//end else

            }else{//end if
                $Error['message'] = "Nėra prisijungimo duomenų";
                $Error['code'] = "TKI-0002"; //t-transport KR - krovinio registracijos
                $Error['group'] = "KI"; //KR - krovinio registracijos error
                $this->addError ($Error);
                $pavyko = false;

            }//end else


        }else{//end if
            $Error['message'] = "Neparuoštas XML krovinio registracijai ";
            $Error['code'] = "TKI-0001"; //t-transport KR - krovinio registracijos
            $Error['group'] = "KI"; //KR - krovinio registracijos error
            $this->addError ($Error);
            $pavyko = false;
        }//end else

        return $pavyko;
    }//end function





    private function connectSend($auth_data, $Link){

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
            curl_setopt($curl, CURLOPT_URL, $Link);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $XML_result = curl_exec($curl);

            //var_dump($XML_result);

            if(!$XML_result){
                    $Error['message'] = "Prisijungimo prie VENIPAK klaida";
                    $Error['code'] = "TCN-0001"; //CN - prisijungimo klaida
                    $Error['group'] = "CN"; //CN - prisijungimo klaida
                    $this->addError ($Error);
                    $pavyko = false;
            }else{
                $pavyko = true;
            }
            curl_close($curl);

            $rez['pavyko']=$pavyko;
            $rez['response']=$XML_result;

        return $rez;
    }//end function


    private function setSenderData ($gamyba){

        switch ($gamyba) {
            case 'KEG':
                    $this->SenderAddress = $this->SenderAddressKEG;
                    $this->SenderPostCode = $this->SenderPostCodeKEG;
                    $this->SenderContactPerson = $this->SenderContactPersonKEG;
                    $this->SenderContactTel = $this->SenderContactTelKEG;
                    $this->SenderContactMail = $this->SenderContactMailKEG;
                    $this->SenderDataSet = true;
                break;

            case 'KPG':
                    $this->SenderAddress = $this->SenderAddressKPG;
                    $this->SenderPostCode = $this->SenderPostCodeKPG;
                    $this->SenderContactPerson = $this->SenderContactPersonKPG;
                    $this->SenderContactTel = $this->SenderContactTelKPG;
                    $this->SenderContactMail = $this->SenderContactMailKPG;
                    $this->SenderDataSet = true;
                 break;
            case 'ETK':
            case 'ETK1':
                    $this->SenderAddress = $this->SenderAddressETK;
                    $this->SenderPostCode = $this->SenderPostCodeETK;
                    $this->SenderContactPerson = $this->SenderContactPersonETK;
                    $this->SenderContactTel = $this->SenderContactTelETK;
                    $this->SenderContactMail = $this->SenderContactMailETK;
                    $this->SenderDataSet = true;
                 break;
            
            default:
                    $this->SenderAddress = '';
                    $this->SenderPostCode = '';
                    $this->SenderContactPerson = '';
                    $this->SenderContactTel = '';
                    $this->SenderContactMail = '';
                    $this->SenderDataSet = false;
                break;
        }//end switch

    }//end function




    /* generuoja krovinio registracijos XML 
        - ziurim ar nera siandien atidaryto manifesto, jeigu nera atidaryto arba jis atidarytas ne siandien tai generuojam nauja
        - jeigu yra atidarytas siandienos manifestas tai nusiskaitom ji
    */
    public function generateXMLKR ($param=array()){




        $yraKlaidu = 0;

        /* Manifesto generavima perkelem i construktoriu
        // duomenys manifesto generavimui
        $dat =date("ymd");
        $ManifestNr = $this->tvsMod->getTVSNr ('VP_manifest');
        if($ManifestNr =="ERROR"){
                $Error['message'] = "Nepavyko sugeneruoti manifesto numerio.";
                $Error['code'] = "TPD-1010"; //t-transport AD-address data
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR111";
                return 'ERROR';
        }
        */
        



        //formuojam pilna manifesto numeri
        //$this->manifest = $this->VUserID.$dat.$ManifestNr;


        //Generuojam ShipmentCode numeri
        //$shipment_code = $this->tvsMod->getTVSNr ('VP_shipmentCode');
        //$dat =date("ymd"); -yra auksciau
        $shipment_code = 'S'.$dat.str_pad($this->SiuntaData['uid'], 4, "0", STR_PAD_LEFT);



        //Sena verisja
        //$doc_no = 'D'.$dat.str_pad($this->SiuntaData['uid'], 4, "0", STR_PAD_LEFT);
        
        //Nauja versija (su PackingSlipu)        
        $PS_array_tmp = $this->SiuntaData['KEY'];
        //var_dump($PS_array_tmp);
        if($PS_array_tmp AND is_array($PS_array_tmp)){
            $firstPackingSlip = $PS_array_tmp[0]['PackSlipID'];
            $kiekPackingSlipu = count($PS_array_tmp);
        }
        //jeigu turim packingSlip tai pirma is ju priskiriam, jeigu ju yra daugiau, tai pridedam gale raide X
        if($firstPackingSlip){
            $doc_no = 'PS'.$firstPackingSlip;// Cia negeneruojam o rasom PackingSlip numeri
            //jeigu i siunta ieina daugiau kaip vienas packingslipas pridedam X
            if($kiekPackingSlipu AND $kiekPackingSlipu>1){
                $doc_no .= 'X';
            }
        }else{
            //jeigu neturim packing Slipu, tai kaip anksciau duteikiam generuojama numeri
            $doc_no = 'D'.$dat.str_pad($this->SiuntaData['uid'], 4, "0", STR_PAD_LEFT);// Cia negeneruojam o rasom PackingSlip numeri
        }
        

        


        

        //KLAIDU TIKRINIMAS
        //$this->manifest = '57943191204138'; //tik testavimui
        //echo "<hr>----".$this->manifest."---<br>";
        if(!$this->manifest OR strlen($this->manifest)!=14){
                $Error['message'] = "Nėra teisingo manifesto numerio.";
                $Error['code'] = "TPD-1025"; //
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR222";
                return 'ERROR';
        }


        if($this->address['CONSIGNEE']['addressSet']===false){
                $Error['message'] = "Nenustatytas gavėjo adresas.";
                $Error['code'] = "TPD-1011"; //
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR222";
                return 'ERROR';
        }


        if($this->SenderDataSet===false){
                $Error['message'] = "Nenustatytas siuntėjo adresas.";
                $Error['code'] = "TPD-1015"; //
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR222";
                return 'ERROR';
        }

        if(!$shipment_code){
                $Error['message'] = "Nepavyko sugeneruoti 'Shipment_code'.";
                $Error['code'] = "TPD-1012"; //
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR333";
                $this->shipment_code = "";
                return 'ERROR';
        }else{
            $this->shipment_code = $shipment_code;
        }


         if($this->PacksIsSet===false){
                $Error['message'] = "Nėra siuntos duomenų.";
                $Error['code'] = "TPD-1016"; //
                $Error['group'] = "PD";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR333";
                return 'ERROR';
        }


        var_dump($param);
        var_dump($this->sParam['Det_ArIPastomata']);

        $rezXML = '
        <?xml version="1.0" encoding="UTF-8"?>
        <description type="1">
            <manifest title="'.$this->manifest.'">
                <shipment>
                    <consignee>
                        <name>'.$this->address['CONSIGNEE']['name1'].'</name>
                        <company_code>'.$this->address['CONSIGNEE']['company_code'].'</company_code>
                        <country>'.$this->address['CONSIGNEE']['countryCode'].'</country>
                        <city>'.$this->address['CONSIGNEE']['city'].'</city>
                        <address>'.$this->address['CONSIGNEE']['street1'].' '.$this->address['CONSIGNEE']['street2'].'</address>
                        <post_code>'.$this->address['CONSIGNEE']['postalCode'].'</post_code>
                        <contact_person>'.$this->address['CONSIGNEE']['contactPerson'].'</contact_person>
                        <contact_tel>'.$this->address['CONSIGNEE']['contactPersonTel'].'</contact_tel>
                        <contact_email>'.$this->address['CONSIGNEE']['contactPersonEmail'].'</contact_email>
                    </consignee>
                    <sender>
                        <name>'.$this->SenderName.'</name>
                        <company_code>'.$this->SenderCompanyCode.'</company_code>
                        <country>'.$this->SenderCountryCode.'</country>
                        <city>'.$this->SenderCity.'</city>
                        <address>'.$this->SenderAddress.'</address>
                        <post_code>'.$this->SenderPostCode.'</post_code>
                        <contact_person>'.$this->SenderContactPerson.'</contact_person>
                        <contact_tel>'.$this->SenderContactTel.'</contact_tel>
                        <contact_email>'.$this->SenderContactMail.'</contact_email>
                    </sender>
                    <attribute>
                        <shipment_code>'.$shipment_code.'</shipment_code>
        ';
        //ziurim pristatymo tipa
        //echo "<br>----".$param['det_PristatytiIki']."----<br>";
        switch ($param['sVP_PristatytiIki']) {
            case '10:00':
                    $rezXML .='<delivery_type>nwd10</delivery_type>
                    ';
                break;
            case '12:00':
                    $rezXML .='<delivery_type>nwd12</delivery_type>
                    ';
                break;
            
            default:
                $rezXML .='<delivery_type>nwd</delivery_type>
                ';
                break;
        }

        //ziurim ar ne express
        if($param['sVP_Express']=='express'){
            $rezXML .='<delivery_mode>1</delivery_mode>';
        }else{
            $rezXML .='<delivery_mode>0</delivery_mode>';
        }


        $rezXML .= '
                        
                        <return_doc>0</return_doc>
                        <return_doc_consignee>
                            <name>'.$this->SenderName.'</name>
                            <company_code>'.$this->SenderCompanyCode.'</company_code>
                            <country>'.$this->SenderCountryCode.'</country>
                            <city>'.$this->SenderCity.'</city>
                            <address>'.$this->SenderAddressKEG.'</address>
                            <post_code>'.$this->SenderPostCodeKEG.'</post_code>
                            <contact_person>'.$this->SenderContactPersonKEG.'</contact_person>
                            <contact_tel>'.$this->SenderContactTelKEG.'</contact_tel>
                        </return_doc_consignee>
                        <cod>0</cod>
                        <cod_type>EUR</cod_type>
                        <doc_no>'.$doc_no.'</doc_no>
                        <comment_door_code> </comment_door_code>
                        <comment_office_no> </comment_office_no>
                        <comment_warehous_no> </comment_warehous_no>
        ';

        if($param['Det_ArIPastomata']=='PICKUP'){
            $rezXML .= '
                        <comment_call>0</comment_call>
            ';
        }else{
            $rezXML .= '
                        <comment_call>1</comment_call>
            ';
        }


        //ziurim ar ne 4Rankos
        if($param['sVP_4Rankos']=='Y'){
            $rezXML .='<four_hands>1</four_hands>';
        }else{
            $rezXML .='<four_hands>0</four_hands>';
        }

        $rezXML .= '                        
                    </attribute>
        ';

        //echo "<br>CIA-77";
        var_dump($this->packs);
        if ($this->packs){
            $p=1;
            
            $savePackArray = array();
            foreach ($this->packs['PacksArray'] as $key => $packel) {
                for ($i=1; $i <= $packel['Kiekis']; $i++) { 
                    $pack_no="";
                    $packDBNr = $this->tvsMod->getTVSNr ('VP_pack');
                    $pack_no = 'V'.$this->VUserID.'E'.$packDBNr;

                    $pack_doc_no = $doc_no.'_'.str_pad($p, 2, "0", STR_PAD_LEFT);

                    $savePackArray[$pack_no]['pack_no']=$pack_no;
                    $savePackArray[$pack_no]['pack_doc_no']=$pack_doc_no;
                    $savePackArray[$pack_no]['Svoris']=$packel['Svoris'];
                    $savePackArray[$pack_no]['Turis']=$packel['Turis'];
                    $savePackArray[$pack_no]['Plotis']=$packel['Plotis'];
                    $savePackArray[$pack_no]['Ilgis']=$packel['Ilgis'];
                    $savePackArray[$pack_no]['Aukstis']=$packel['Aukstis'];
                    $savePackArray[$pack_no]['Tipas']=$packel['Tipas'];
                    


                    $rezXML .= '
                        <pack>
                            <pack_no>'.$pack_no.'</pack_no>
                            <doc_no>'.$pack_doc_no.'</doc_no>
                            <weight>'.$packel['Svoris'].'</weight>
                            <volume>'.$packel['Turis'].'</volume>
                        </pack>
                    ';
                    $p++;
                }//end for
            }//end foreach

            $this->packs['PacksArrayFull']=$savePackArray;
            //var_dump($this->packs);
        }//end if
        $rezXML .= '
                </shipment>
            </manifest>
        </description>
        ';

        var_dump($rezXML);

        $this->XML_KR = $rezXML;
        $this->XML_KR_created = true;

        return 'OK';
    }//end function



//generuoja kurjerio iskvietimo XML
private function generateXMLKI (){


        $yraKlaidu = 0;

        

        //KLAIDU TIKRINIMAS
        if($this->address['CONSIGNEE']['addressSet']===false){
                $Error['message'] = "Nenustatytas gavėjo adresas.";
                $Error['code'] = "TKI-1011"; //KI-Kurjerio iskvietimas
                $Error['group'] = "KI";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR222";
                return 'ERROR';
        }


        if($this->SenderDataSet===false){
                $Error['message'] = "Nenustatytas siuntėjo adresas.";
                $Error['code'] = "TKI-1015"; //KI-Kurjerio iskvietimas
                $Error['group'] = "KI";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR222";
                return 'ERROR';
        }

        if($this->PacksIsSet===false){
                $Error['message'] = "Nėra siuntos duomenų.";
                $Error['code'] = "TKI-1016"; //
                $Error['group'] = "KI";
                $this->addError ($Error);
                $yraKlaidu = 1;
                //echo "ERROR333";
                return 'ERROR';
        }

        //var_dump($this->sParam);

        list($sParamMetai, $sParamMenuo, $sParamDiena) = explode("-", $this->sParam['sIsvData']);
        list($sParamValNuo, $sParamMinNuo) = explode(":", $this->sParam['sIsvLaikasNuo']);
        list($sParamValIki, $sParamMinIki) = explode(":", $this->sParam['sIsvLaikasIki']);

        $doc_no = $this->tvsMod->getTVSNr('VP_kurjer_doc');

        //$this->sParam['det_KurjerioDescription']=str_replace($this->searchChar, $this->replaceChar, $this->sParam['det_KurjerioDescription']);
        $this->sParam['det_KurjerioDescription'] = (htmlspecialchars_decode($this->sParam['det_KurjerioDescription'],ENT_QUOTES) == $this->sParam['det_KurjerioDescription']) ? htmlspecialchars($this->sParam['det_KurjerioDescription'],ENT_QUOTES) : $this->sParam['det_KurjerioDescription'];

        $rezXML = '
        <?xml version="1.0" encoding="UTF-8"?>
            <description type="3">
                <sender>
                    <name>'.$this->SenderName.'</name>
                    <company_code>'.$this->SenderCompanyCode.'</company_code>
                    <country>'.$this->SenderCountryCode.'</country>
                    <city>'.$this->SenderCity.'</city>
                    <address>'.$this->SenderAddress.'</address>
                    <post_code>'.$this->SenderPostCode.'</post_code>
                    <contact_person>'.$this->SenderContactPerson.'</contact_person>
                    <contact_tel>'.$this->SenderContactTel.'</contact_tel>
                    <contact_email>'.$this->SenderContactMail.'</contact_email>
                </sender>
                <consignee>
                    <name>'.$this->address['CONSIGNEE']['name1'].'</name>
                    <company_code>'.$this->address['CONSIGNEE']['company_code'].'</company_code>
                    <country>'.$this->address['CONSIGNEE']['countryCode'].'</country>
                    <city>'.$this->address['CONSIGNEE']['city'].'</city>
                    <address>'.$this->address['CONSIGNEE']['street1'].' '.$this->address['CONSIGNEE']['street2'].'</address>
                    <post_code>'.$this->address['CONSIGNEE']['postalCode'].'</post_code>
                    <contact_person>'.$this->address['CONSIGNEE']['contactPerson'].'</contact_person>
                    <contact_tel>'.$this->address['CONSIGNEE']['contactPersonTel'].'</contact_tel>
                    <contact_email>'.$this->address['CONSIGNEE']['contactPersonEmail'].'</contact_email>
                </consignee>
                <weight>'.$this->packs['SvorisSum'].'</weight>
                <volume>'.$this->packs['SUMTuris'].'</volume>
                <pallets>'.$this->packs['SUMPalets'].'</pallets>
                <date_y>'.$sParamMetai.'</date_y>
                <date_m>'.$sParamMenuo.'</date_m>
                <date_d>'.$sParamDiena.'</date_d>
                <hour_from>'.$sParamValNuo.'</hour_from>
                <min_from>'.$sParamMinNuo.'</min_from>
                <hour_to>'.$sParamValIki.'</hour_to>
                <min_to>'.$sParamMinIki.'</min_to>
                <comment>'.$this->sParam['det_KurjerioDescription'].'</comment>
                <spp>'.$this->packs['SUM_Spp'].'</spp>
                <doc_no>'.$doc_no.'</doc_no>
            </description> 
        ';


        $this->XML_KI = $rezXML;
        $this->XML_KI_created = true;

        return 'OK';


}//end function


public function createLabel ($packNr, $LabelFormat = "100 X 150"){ // $LabelFormat = "100 X 150" or "A4"

    $arOK = "NOTOK";
    $LabelFile = "";
    if($packNr){


        //$code = '12345131029001'; //Sender's shipment bill number (manifestas) (12345131029001) 

        //$this->VUser = $this->VUserLive;
        //$this->VPass = $this->VPassLive;
        //$this->VUserID = $this->VUserIDLive;

        $auth_data = array(
            'user'=> $this->VUser,
            'pass'=> $this->VPass,
            'pack_no'=> $packNr,
            'type' => $LabelFormat

        );
        $ActionLink =  $this->LipdukoSpausdLink."";
 echo "<hr>+++";
 var_dump($auth_data);
 echo "\n<br>------------------<br>\n";
 echo $ActionLink."------------<br>\n";
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
        curl_setopt($curl, CURLOPT_URL, $ActionLink);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($curl);

        echo "REZULT:\n<br>";
        var_dump($result);

        if(!$result OR strpos($result, 'Error:') === true  OR strlen($result)<200){
                $arOK = "NOTOK";
                $Error['message'] = "Nepavyko sukurti siuntos lipduko.";
                $Error['code'] = "LA-0001"; //
                $Error['group'] = "LA";
                $this->addError ($Error);

        }else{
                $output_file = "lab_".$packNr.".pdf";
                $output_path = "../../../uploads/tvsLabel/";
                $LabelFile = $this->savePdf($result, $output_path, $output_file);

                //tikrinti ar susikure failas
                if(file_exists ( $output_path.$output_file ) ){
                    $arOK = "OK";
                    //irasom i DB
                    $saveDataRez = $this->tvsMod->saveLabelFileToDB($packNr, $output_file);

                    if($saveDataRez!='OK'){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko išsaugoti lipduko PDF .";
                        $Error['code'] = "LA-0003"; //
                        $Error['group'] = "LA";
                        $this->addError ($Error);

                    }

                }else{
                    $arOK = "NOTOK";
                    $Error['message'] = "Nepavyko sukurti siuntos lipduko.";
                    $Error['code'] = "LA-0002"; //
                    $Error['group'] = "LA";
                    $this->addError ($Error);

                }//end else

        }//end else

        curl_close($curl);
    }else{
        $arOK = "NOTOK-1";
    }

    $rezDuom['OK']=$arOK;
    $rezDuom['File']=$LabelFile;

    return $rezDuom;
}//end function




public function createLabelSiunta ($siuntaNr){

    $arOK = "NOTOK";
    $LabelFile = "";
    if($siuntaNr){

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        $PakuotesPrieSiuntosArray = $this->tvsMod->getPakuotesPrieSiuntos($siuntaNr);

        if($PakuotesPrieSiuntosArray){
                $code = ""; //Sender's shipment bill number (manifestas) (12345131029001) 

                $auth_data_tmp = array(
                    'user'=> $this->VUser,
                    'pass'=> $this->VPass,
                    'pack_no'=> $PakuotesPrieSiuntosArray,
                    'type' => $LabelFormat

                );
                $auth_data = $auth_data = http_build_query($auth_data_tmp);

                $ActionLink =  $this->LipdukoSpausdLink;

var_dump($auth_data);
var_dump($ActionLink);


                $curl = curl_init();

                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
                curl_setopt($curl, CURLOPT_URL, $ActionLink);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

                $result = curl_exec($curl);

                //echo "----<hr>".$result."<hr>----";
                /*
                if(strpos($result, 'Error:') === true){
                    echo "***TRUE***";
                }else{
                    echo "***FALSE: ".strpos($result, 'Error:')."***";
                }
                */

                if(!$result OR strpos($result, 'Error:') === true OR strlen($result)<200){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko sukurti siuntos lipdukų.";
                        $Error['code'] = "LA-0201"; //
                        $Error['group'] = "LA";
                        $this->addError ($Error);

                }else{
                        $output_file = "SL_".$siuntaNr.".pdf";
                        $output_path = "../../../uploads/tvsLabel/";
                        $LabelFile = $this->savePdf($result, $output_path, $output_file);

                        //TODO tikrinti ar susikure failas
                        if(file_exists ( $output_path.$output_file ) ){
                            $arOK = "OK";
                            //echo "<br>SAUGOM: ".$output_file."<br>";
                            //irasom i DB
                            //$saveDataRez = $this->tvsMod->saveLabelAllFileToDB($Manifest, $output_file);
                            $saveDataRez = $this->tvsMod->saveLabelSiuntaFileToDB($siuntaNr, $output_file);

                            if($saveDataRez!='OK'){
                                $arOK = "NOTOK";
                                $Error['message'] = "Nepavyko išsaugoti lipdukų PDF .";
                                $Error['code'] = "LA-0203"; //
                                $Error['group'] = "LA";
                                $this->addError ($Error);

                            }

                        }else{
                            $arOK = "NOTOK";
                            $Error['message'] = "Nepavyko sukurti siuntos lipdukų.";
                            $Error['code'] = "LA-0202"; //
                            $Error['group'] = "LA";
                            $this->addError ($Error);

                        }//end else

                }//end else

                curl_close($curl);
        }else{
            $arOK = "NOTOK-2";
            $LabelFile="";
        }
    }else{
        $arOK = "NOTOK-1";
        $LabelFile = "";
    }

    $rezDuom['OK']=$arOK;
    $rezDuom['File']=$LabelFile;

    return $rezDuom;
}//end function




public function createLabelAll ($Manifest){

    $arOK = "NOTOK";
    $LabelFile = "";
    if($Manifest){


        $code = $Manifest; //Sender's shipment bill number (manifestas) (12345131029001) 

        $auth_data = array(
            'user'=> $this->VUser,
            'pass'=> $this->VPass,
            'code'=> $code,
            'type' => '100 X 150'

        );
        $ActionLink =  $this->LipdukoSpausdLink;


        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
        curl_setopt($curl, CURLOPT_URL, $ActionLink);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($curl);

        //echo "----<hr>".$result."<hr>----";
        if(strpos($result, 'Error:') === true){
            echo "***TRUE***";
        }else{
            echo "***FALSE: ".strpos($result, 'Error:')."***";
        }

        if(!$result OR strpos($result, 'Error:') === true OR strlen($result)<200){
                $arOK = "NOTOK";
                $Error['message'] = "Nepavyko sukurti siuntos lipdukų.";
                $Error['code'] = "LA-0201"; //
                $Error['group'] = "LA";
                $this->addError ($Error);

        }else{
                $output_file = "Alab_".$code.".pdf";
                $output_path = "../../../uploads/tvsLabel/";
                $LabelFile = $this->savePdf($result, $output_path, $output_file);

                //TODO tikrinti ar susikure failas
                if(file_exists ( $output_path.$output_file ) ){
                    $arOK = "OK";
                    //irasom i DB
                    $saveDataRez = $this->tvsMod->saveLabelAllFileToDB($Manifest, $output_file);

                    if($saveDataRez!='OK'){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko išsaugoti lipdukų PDF .";
                        $Error['code'] = "LA-0203"; //
                        $Error['group'] = "LA";
                        $this->addError ($Error);

                    }

                }else{
                    $arOK = "NOTOK";
                    $Error['message'] = "Nepavyko sukurti siuntos lipdukų.";
                    $Error['code'] = "LA-0202"; //
                    $Error['group'] = "LA";
                    $this->addError ($Error);

                }//end else

        }//end else

        curl_close($curl);
    }else{
        $arOK = "NOTOK-1";
    }

    $rezDuom['OK']=$arOK;
    $rezDuom['File']=$LabelFile;

    return $rezDuom;
}//end function



public function PrintManifest($ManifestNr){


    $arOK = "NOTOK";
    if($ManifestNr){


        $code = $ManifestNr; //Sender's shipment bill number (manifestas) (12345131029001) 

        $auth_data = array(
            'user'=> $this->VUser,
            'pass'=> $this->VPass,
            'code'=> $code
        );
        $ActionLink =  $this->ManifestoSpausdLink;

//var_dump($auth_data);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
        curl_setopt($curl, CURLOPT_URL, $ActionLink);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($curl);

        //echo "----<hr>".$result."<hr>----";
        if(strpos($result, 'Error:') === true){
            //echo "***TRUE***";
        }else{
            //echo "***FALSE: ".strpos($result, 'Error:')."***";
        }

        if(!$result OR strpos($result, 'Error:') === true OR strlen($result)<200){
            //echo "***NEPAEJO ***";
                $arOK = "NOTOK";
                $Error['message'] = "Nepavyko sukurti manifesto ataskaitos";
                $Error['code'] = "MA-0301"; //
                $Error['group'] = "MA";
                $this->addError ($Error);

        }else{
            //echo "***PAEJO ***";

                //echo "FILE:". $pathFile."------------";
                /*
                header('Content-type: ' . 'application/octet-stream');
                header('Content-Disposition: ' . 'attachment; filename=manPDF.PDF');
                echo $result;    
                */
                
                //$arOK = 'OK';
                
                //$output_file = "Alab_".$code.".pdf";
                //$output_path = "../../../uploads/tvsLabel/";
                $output_file = "Mnf_".$ManifestNr.".PDF";
                $output_path = "../../../uploads/tvsLabel/";
                $pathFile  =  $output_path.$output_file;

                $LabelFile = $this->savePdf($result, $output_path, $output_file);

                //TODO tikrinti ar susikure failas
                if(file_exists ( $pathFile ) ){
                    $arOK = "OK";

                    /*
                    //irasom i DB
                    $saveDataRez = $this->tvsMod->saveLabelAllFileToDB($Manifest, $output_file);

                    if($saveDataRez!='OK'){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko išsaugoti manifesto PDF .";
                        $Error['code'] = "MA-0303"; //
                        $Error['group'] = "MA";
                        $this->addError ($Error);

                    }
                    */

                }else{
                    $arOK = "NOTOK";
                    $Error['message'] = "Nepavyko sukurti manifesto pdf.";
                    $Error['code'] = "MA-0202"; //
                    $Error['group'] = "MA";
                    $this->addError ($Error);

                }//end else
                

        }//end else

        curl_close($curl);
    }else{
                $arOK = "NOTOK-1";
                $Error['message'] = "Nėra manifesto numerio..";
                $Error['code'] = "MA-0302"; //
                $Error['group'] = "MA";
                $this->addError ($Error);

    }

    $rezDuom['OK']=$arOK;
    $rezDuom['File']=$output_file;

    //var_dump($rezDuom);

    return $rezDuom;


}//end function




public function getTrackingInfo ($trackingNr){ 

    $arOK = "NOTOK";
    if($trackingNr){

    $trackingInfoURL = "https://go.venipak.lt/ws/tracking?code=".$trackingNr."&type=1";
    $json = file_get_contents($trackingInfoURL);
    //$obj = json_decode($json);
    var_dump ($json);

    $json = trim($json);
    var_dump($json);
    $json = substr($json, 1); //nuimam nereikalingus simbolius priekyje
    $json = substr($json, 0, -1); //nuimam nereikalingus simbolius gale
    $TrackingArrayTmp = explode("\"\n\"", $json);
    if($TrackingArrayTmp){
        $j=0;
        foreach ($TrackingArrayTmp as $keyA => $valueA) {
            $TrackingArray[$j]= explode("\",\"", $valueA);
            $j++;
        }
        
    }

    //var_dump ($TrackingArray);


    if($TrackingArray){
        $RezTracking = "<table id='TrackingTable' >";
        foreach ($TrackingArray as $keyAr => $valueAr) {
            if($keyAr==0){
                $RezTracking .= "
                    <tr>
                        <th>".$valueAr[0]."</th>
                        <th>".$valueAr[1]."</th>
                        <th>".$valueAr[2]."</th>
                        <th>".$valueAr[3]."</th>
                        <th>".$valueAr[4]."</th>
                    </tr>
                ";
            }else{
                $RezTracking .= "
                    <tr>
                        <td><b>".$valueAr[0]."</b></td>
                        <td>".$valueAr[1]."</td>
                        <td>".$valueAr[2]."</td>
                        <td><b>".$valueAr[3]."</b></td>
                        <td>".$valueAr[4]."</td>
                    </tr>
                ";
            }//end if
        }//end foreach
        $RezTracking .= "
                    <tr>
                        <td colspan='4' style='width:400px;'>Peržiūrėti VENIPAK sistemoje:<br><a href='https://venipak.lt/tracking/track/".$trackingNr."' class='' style='width:300px;' target='_blank'>https://venipak.lt/tracking/track/".$trackingNr."&</a></td>
                    </tr>
                </table>";
    }else{
        $RezTracking = "Informacijos nėra.";
    }
/*
        $auth_data = array(
            'user'=> $this->VUser,
            'pass'=> $this->VPass,
            'pack_no'=> $packNr,
            'type' => $LabelFormat

        );
        $ActionLink =  $this->LipdukoSpausdLink."";
 echo "<hr>+++";
 var_dump($auth_data);
 echo "\n<br>------------------<br>\n";
 echo $ActionLink."------------<br>\n";
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
        curl_setopt($curl, CURLOPT_URL, $ActionLink);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($curl);

        echo "REZULT:\n<br>";
        var_dump($result);

        if(!$result OR strpos($result, 'Error:') === true  OR strlen($result)<200){
                $arOK = "NOTOK";
                $Error['message'] = "Nepavyko sukurti siuntos lipduko.";
                $Error['code'] = "LA-0001"; //
                $Error['group'] = "LA";
                $this->addError ($Error);

        }else{
                $output_file = "lab_".$packNr.".pdf";
                $output_path = "../../../uploads/tvsLabel/";
                $LabelFile = $this->savePdf($result, $output_path, $output_file);

                //tikrinti ar susikure failas
                if(file_exists ( $output_path.$output_file ) ){
                    $arOK = "OK";
                    //irasom i DB
                    $saveDataRez = $this->tvsMod->saveLabelFileToDB($packNr, $output_file);

                    if($saveDataRez!='OK'){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko išsaugoti lipduko PDF .";
                        $Error['code'] = "LA-0003"; //
                        $Error['group'] = "LA";
                        $this->addError ($Error);

                    }

                }else{
                    $arOK = "NOTOK";
                    $Error['message'] = "Nepavyko sukurti siuntos lipduko.";
                    $Error['code'] = "LA-0002"; //
                    $Error['group'] = "LA";
                    $this->addError ($Error);

                }//end else

        }//end else

        curl_close($curl);

        */
    }else{
        $arOK = "NOTOK-1";
    }





    $rezDuom['OK']=$arOK;
    $rezDuom['TrackingInfo']=$RezTracking;
    $rezDuom['TrackingArray']=$TrackingArray;

    return $rezDuom;
}//end function





function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $base64_string ) );

    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
}   


function savePdf($pdf_string, $output_path, $output_file) {
    // open the output file for writing
    $kelias = $output_path.$output_file;
    $ifp = fopen( $kelias, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    //$data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, $pdf_string );

    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
}   

























/* ********************NEBAIGTOS FUNKCIJOS ********************* */


    public function preForm__OFF ($siuntaData){

        $gavejas = '';
        $gavejoAdr = '';
        $gavejoMiestas = '';

        if($siuntaData['GavName'] AND $siuntaData['GavGatve']){
            $gavejas = $siuntaData['GavName'];
            $gavejoAdr = $siuntaData['GavGatve'];
            $Country  = $siuntaData['GavCountry'];
            $RajonasMiestas = $siuntaData['GavRajonasMiestas'];
            $LandRef = $siuntaData['GavLandRef'];
            $PostCode = $siuntaData['GavPostCode'];
            $Tel = $siuntaData['GavTel'];
            $Email = $siuntaData['GavEmail'];
        }else{
            $gavejas = $siuntaData['ClientName'];
            $gavejoAdr = $siuntaData['ClientGatve'];
            $Country  = $siuntaData['ClientCountry'];
            $RajonasMiestas = $siuntaData['ClientRajonasMiestas'];
            $LandRef = $siuntaData['ClientLandRef'];
            $PostCode = $siuntaData['ClientPostCode'];
            $Tel = $siuntaData['ClientTel'];
            $Email = $siuntaData['ClientEmail'];
        }

        $form = "
        <div id='schenkerPreForm' class='schenkerPreForm'>
            <table class=''>
                <tr>
                    <th>Siuntos Nr</th>
                    <td><input type='text' id='SCH_siuntaID' value='".$siuntaData['uid']."' class=''></td>
                </tr>
                <tr>
                    <th>Siuntėjo adresas:</th>
                    <td>
                        <select id='SCH_siuntejoAdr' class=''>
                            <option value='AurTaika'>Aurika Taika</option>
                            <option value='AurChemija'>Aurika Chemija</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Gavėjo adresas:</th>
                    <td>
                        <td><input type='text' id='SCH_gavejoAdr' value='".$siuntaData['uid']."' class=''></td>
                    </td>
                </tr>
            </table>
        </div>
        ";


        return $form;
    }//end function






    public function fillByPackingSlipNr__OFF(){

        $SiuntaUID = 46;
        $rez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);

        
    }







    
   
}//end class
