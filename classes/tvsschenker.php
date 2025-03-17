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
 * DUOMENŲ ĮVEDIMO APRAŠYMAS: https://conf.aurika.lt/pages/viewpage.action?pageId=70303881
 *
 * @author Arnoldas Ramonas
 */
class tvsschenker  extends tvsclass{
    //private static $useDebug = true;

    public $mode = 'notSet'; //test/live/notSet, veliau ateis is TVSconfig.php TVS_CONFIG::SCHENKER_MODE;


    //private $AccessKeyDemo = '5f6391d7-67ea-41e8-a6d7-271092fa9cec'; // senas demo KEY pagal laiska apie pakeitimus del 10/12 val
    private $AccessKeyDemo = '550e7680-c908-40ec-878f-8fd9771a73e6'; // senas/naudojamas demo KEY 

    private $AccessKeyLive = '3339e71f-b4b8-4bed-930f-485bb95248cd';
    private $AccessKey = '';

    /* DEMO acountas */
    /* prisijungimas prie demo sistemos stebejimui
        https://eschenker-fat.dbschenker.com/nges-portal/secured/#!/booking/my-bookings

    */
    private $SCHuserDemo = "";
    private $SCHpassDemo = "";
    private $SCHKrovinioUzsakLinkDemo = "https://eschenker-fat.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl";
    private $SCHLipdukoSpausdLinkDemo = "https://eschenker-fat.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl";


    /* LIVE ACOUNT */
    /*
        Prisijungimai prie sistemos (stebejimui)
        https://eschenker.dbschenker.com/nges-portal/secured/#!/booking/my-bookings
        transportas@aurika.lt
        Aurika123+
    */
    private $SCHuserLive = "aurika2";
    private $SCHpassLive = "44pak445";
    private $SCHKrovinioUzsakLinkLive = "https://eschenker.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl";
    private $SCHLipdukoSpausdLinkLive = "https://eschenker.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl";

    
    /* DARBINIS */
    private $SCHUser = "";
    private $SCHPass = "";
    private $SCHKrovinioUzsakLink = "";
    private $SCHLipdukoSpausdLink = "";

    private $actionLink = "";

    /* siuntejo duomenys */
    private $SenderName = "Aurika, UAB";
    private $SenderCompanyCode = "132878726";
    private $SenderCountryCode = "LT";
    private $SenderCity = "Kaunas";


    private $SenderAddressIDKEG = "AURIKAUAB";
    private $SenderAddressIDKPG = "AURIKAUAB1";
    private $SenderAddressIDETK = "AURIKAUAB2";
    private $SenderAddressID = '';

    private $SenderAddressKEG = "Taikos pr. 129A";
    private $SenderAddressKPG = "Chemijos g. 29F";
    private $SenderAddressETK = "Jovarų g. 2A";
    private $SenderAddress = '';

    private $SenderPostCodeKEG = "51127";
    private $SenderPostCodeKPG = "51333";
    private $SenderPostCodeETK = "47193";
    private $SenderPostCode = '';

    private $SenderContactPersonKEG = "AURIKA shipping department";//"Edita Kupčiūnienė";
    private $SenderContactPersonKPG = "AURIKA shipping department";//"Edita Kupčiūnienė";
    private $SenderContactPersonETK = "AURIKA shipping department";//"Edita Kupčiūnienė";
    private $SenderContactPerson = '';

    private $SenderContactTelKEG = "+37068802736";
    private $SenderContactTelKPG = "+37068802736";
    private $SenderContactTelETK = "+37068802736";
    private $SenderContactTel = '';

    private $SenderContactMailKEG = "transportas@aurika.lt";
    private $SenderContactMailKPG = "transportas@aurika.lt";
    private $SenderContactMailETK = "transportas@aurika.lt";
    private $SenderContactMail = '';

    private $SHIPPERDataSet = false;
    private $PICKUPDataSet = false;
    private $CONSIGNEEDataSet = false;
    private $DELIVERYDataSet = false;


    

    private $tvs = array();

    //krovinio registracijos XML ir duomenys
    public $SCHXML = '';
    public $SCHXML_created = false;
    public $SCHXML_result = "";

    //private $address = array();
    public $addres = array();

    public $SiuntaData = array();

    /* parameters from form */
    public $sParam = array();


    public $PacksIsSet = false;
    public $DataIsSet = false;// jeigu $PaksIsSet PacksIsSet ==false tai ir $DataIsSe==false

    /* ************* REZ *************** */
    
    public $rezKR_SiuntuNr = array();
    public $rezKI_OrderNr = "";


    public $Neutralumas = '';// ''-nera neutralumo, 'LS'- yra neutralumas; 2LS - dvigubas neutralumas





    function __construct() {//mode = 'test'; //test/live
        parent::__construct();

        $this->mode = TVS_CONFIG::SCHENKER_MODE;

        //echo "---SCHENKER---<Br>";

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();


        if($this->mode=='live'){
            
            $this->AccessKey = $this->AccessKeyLive;
            $this->SCHuser = $this->SCHuserLive;
            $this->SCHpass = $this->SCHpassLive;

            $this->SCHKrovinioUzsakLink = $this->SCHKrovinioUzsakLinkLive;
            $this->SCHLipdukoSpausdLink = $this->SCHLipdukoSpausdLinkLive;
            

            //echo "********* $mode=live ************<br>";

        }elseif($this->mode=='test'){

            $this->AccessKey = $this->AccessKeyDemo;
            $this->SCHuser = $this->SCHuserDemo;
            $this->SCHpass = $this->SCHpassDemo;

            $this->SCHKrovinioUzsakLink = $this->SCHKrovinioUzsakLinkDemo;
            $this->SCHLipdukoSpausdLink = $this->SCHLipdukoSpausdLinkDemo;


            //echo "********* $mode=test ************<br>";

        }else{
            $this->mode = 'notSet';
            $this->AccessKey = '';
            $this->SCHuser = '';
            $this->SCHpass = '';

            $this->SCHKrovinioUzsakLink = '';
            $this->SCHLipdukoSpausdLink = '';

            //echo "********* $mode=NOTSET ************<br>";
        }

        if ($this->AccessKey){
            $returnData = $this->mode;

        }else{
            $returnData = '';
            $Error['message'] = 'Įvyko registravimo klaida';
            $Error['code'] = "TRE-2001"; //t-transport AD-address data
            $Error['group'] = "RE"; //AD - address data error
            $this->addError ($Error);

        }


        //var_dump($this->AccessKey);
        //echo "<HR>";

       return $returnData;

    }//end function 







    function setSiuntaData($SiuntaUID, $sParam) {//siuntos UID DB ir sParam - duomenys is WEB formos
        
        //$this->WSDLLinkTest = 'https://eschenker-fat.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl';
        //ECHO "<br><br>---SETINAM DUOMENIS -|".$SiuntaUID."|---<br><br>";
        //var_dump($sParam);



        $this->SiuntaUID = $SiuntaUID;
        $this->sParam = $sParam;


        //keiciam siuntejo Persona
        $this->SenderContactPerson = $this->sParam['det_IsvAtsakingas'];
        $this->SenderContactTel = $this->sParam['det_IsvAtsakingasTel'];
        $this->SenderContactMail = $this->sParam['det_IsvAtsakingasEmail'];


        $Sandelys = 'NaN';
        if($this->sParam['sSCH_SandelysKEGKPG']=='KEG'){
            $this->sandelys = 'KEG';
        }else if($this->sParam['sSCH_SandelysKEGKPG']=='KPG'){
            $this->sandelys = 'KPG';
        }else if($this->sParam['sSCH_SandelysKEGKPG']=='ETK' OR $this->sParam['sSCH_SandelysKEGKPG']=='ETK1'){
            $this->sandelys = 'ETK';
        }else{
            $Error['message'] = 'Nenustatyta iš kurio sandėlio bus siunta';
            $Error['code'] = "TAD-2011"; //t-transport AD-address data
            $Error['group'] = "AD"; //AD - address data error
            $this->addError ($Error);
        }



        //pasiimam duomenys is TVS_Siuntos, TVS_Pack lenteliu
        $SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID, $Sandelys);

        

        if($SiuntaDataRez['OK']=='OK' AND ($this->mode == 'live' OR $this->mode == 'test')){
            $this->SiuntaData = $SiuntaDataRez['Duom'];
            unset($SiuntaDataRez);
            //var_dump($this->SiuntaData);


            $this->setSenderAurikaAdresas  ('Clear');
            $this->setPICKUPAdresa  ('Clear');

            //nustato DEFAULT AURIKOS KAIP SIUNTEJO ADRESA
            if($this->SiuntaData['NeutralumasKod']==''){//jeigu nera LS ar 2LS
                if($this->SiuntaData['Sandelys']=='KEG' OR $this->SiuntaData['Sandelys']=='KPG' OR $this->SiuntaData['Sandelys']=='ETK' OR $this->SiuntaData['Sandelys']=='ETK1'){
                    $this->setSenderData ($this->SiuntaData['Sandelys']);
                }
            }

            //ECHO "SENDER SET:<br>";
            //var_dump($this->SHIPPERDataSet);
            //echo "<hr>";

            //paruosiam duomenys
            if(!$this->SiuntaData['GavejasID']){
                //jeigu nera gavejo, tai nurodom, kad gavejas yra klientas
                $this->SiuntaData['GavName']=$this->SiuntaData['ClientName'];
            }

            if($this->SiuntaData['NeutralumasKod']=='LS' OR $this->SiuntaData['NeutralumasKod']=='2LS'){
                $this->tvs['neutralShipping']=1;
            }else{
                $this->tvs['neutralShipping']=0;
            }


            //pildom adresus
            //$addrRez = $this->setConsigneeAddress ($this->SiuntaData);
            $addrRez = $this->setAddresses ($this->SiuntaData);

            //ECHO "Consignee SET:<br>";
            //var_dump($this->addres);
            //echo "<hr>";


            $this->RequestID = date("YmdHis").substr(md5(mt_rand()), 0, 4); //date + random string


            $this->tvs['handlingInstructions']='';//pradinis nustatymas, veliau keisis

            $this->tvs['incoterm']='DAP';
            $this->tvs['incotermLocation']='Inc-location';
            //TAG20210721
            switch ($this->sParam['sSCH_Premium']) {
                case 'premium':
                    $this->tvs['productCode']='44';
                    break;
                default:
                    $this->tvs['productCode']='43';
                    break;
            }



            switch ($this->sParam['sSCH_PristatytiIki']) {
                case '10':
                    $this->tvs['productCode']='44';// jeigu iki tam tikros valandos tai visada 44
                    $this->tvs['productOption']='55';
                    break;
                case '12': /* rasom 12 bet SCHENKERIUI tai yra 13 */
                    $this->tvs['productCode']='44';// jeigu iki tam tikros valandos tai visada 44
                    $this->tvs['productOption']='56';
                    break;
                default:
                    $this->tvs['productOption']='';
                    break;
            }


            //$sParam['sSCH_liftas']

            $this->tvs['measurementType']='METRIC';
            $this->tvs['cargoDescription']='';

            $this->tvs['cargoInsurance']['value']=''; //skaicius EUR'AIS
            $this->tvs['cargoInsurance']['currency']='EUR';

            $this->tvs['cashOnDelivery']['value']=''; //skaicius EUR'AIS
            $this->tvs['cashOnDelivery']['currency']='EUR';


            $this->tvs['customsClearance']='false';

            $this->tvs['grossWeight']='0'; // Suminis svoris
            $this->tvs['indoorDelivery']='true';

            $this->tvs['pickupDates']['pickUpDateFrom']=date("Y-m-d\TH:i:sP");
            $this->tvs['pickupDates']['pickUpDateTo']=date("Y-m-d\TH:i:sP");

            $this->tvs['reference']['number']="SCH_".$this->SiuntaData['KEY'][0]['PackSlipID']."_".$this->SiuntaData['uid'];// LAH7400123
            $this->tvs['reference']['id']="SHIPPER_REFERENCE_NUMBER"; // SHIPPER_REFERENCE_NUMBER arba CONSIGNEE_REFERENCE_NUMBER

            if($this->sParam['sSCH_Lift']=='lift'){
                $this->tvs['handlingInstructions'] .=' MUST TAIL-LIFT DELIVERY';// reikalingas liftas
            }else{
                // $this->tvs['handlingInstructions']='';
            }


            $this->tvs['specialCargo']='false';
            $this->tvs['specialCargoDescription']='';

            $this->tvs['valueOfGoods']['value']=""; //optional
            $this->tvs['valueOfGoods']['currency']="";//optional


            $this->tvs['wayBillNumber']='';
            $this->tvs['serviceType']='D2D';
            $this->tvs['incotermDestinationType']='CON';


            //TODO, reikia aukciau klaidu tikrinimo, bet dabar sakom, kad viskas OK
            $this->DataIsSet = true;//veliau gali pasikeisti priklausomai nuo kitu setinimu.


            //Pildom pakuociu duomenys
            $this->setPackBasic ($this->SiuntaData);

            /*
            $this->tvs['shippingInformation']['shipmentPosition']['dgr']='true';
            $this->tvs['shippingInformation']['shipmentPosition']['length']='';
            $this->tvs['shippingInformation']['shipmentPosition']['width']='';
            $this->tvs['shippingInformation']['shipmentPosition']['height']='';
            $this->tvs['shippingInformation']['shipmentPosition']['volume']='';
            $this->tvs['shippingInformation']['shipmentPosition']['grossWeight']='';
            $this->tvs['shippingInformation']['shipmentPosition']['marksAndNumbers']='';

            $this->tvs['shippingInformation']['shipmentPosition']['packageType']='EP';
            $this->tvs['shippingInformation']['shipmentPosition']['pieces']='';
            $this->tvs['shippingInformation']['shipmentPosition']['stackable']='';

            $this->tvs['shippingInformation']['grossWeight']='';//bendras svoris
            $this->tvs['shippingInformation']['volume']='';//bendras turis
            */

            //var_dump($this->tvs);
            //echo "<hr>";
            ///var_dump($this->packs);
            //echo "<hr>";
            //var_dump($this->PacksIsSet);
            //echo "<hr>";

        }else{//end if
                $Error['message'] = "Nėra išsaugota duomenų apie siuntą.";
                $Error['code'] = "TDS-2001"; //
                $Error['group'] = "DS"; //DS - data save error
                $this->addError ($Error);
                $this->DataIsSet = false;
        }


        //jeigu visi kiti duomenys paruosti, tai skaitom, kad bendri siuntuos domenys paruosti
        if($this->DataIsSet===true AND $this->PacksIsSet === true AND $this->SHIPPERDataSet===true AND $this->CONSIGNEEDataSet===true){
            $this->DataIsSet = true;
        }

            //var_dump($this->DataIsSet);
            //echo "<hr>";

    }//end function 






    public function setPackBasic ($SiuntaData){

        $this->PacksIsSet = false;

        //tikrinam duomenis
        if ($SiuntaData){
            //echo "<br>CIA999";
            
            if (!$SiuntaData['SvorisSum']){
                $this->PacksIsSet=false;
                $Error['message'] = "Nenurodytas suminis svoris.";
                $Error['code'] = "TPD-2007"; //t-transport AD-address data
                $Error['group'] = "PD"; //PD - pack data error
                $this->addError ($Error);
            }
            


            //parsinam pakuotes
            $PaksArray = array();
            $i = 0;
            $PaksStr = $SiuntaData['Pakuotes'];
            $paksBigArray = explode("::", $PaksStr);
            if($paksBigArray){
                //echo "<br>CIA1010";
                foreach ($paksBigArray as $key => $pakuoteStr) {
                    if($pakuoteStr){
                        list($pKiekis, $pTipas, $pMatmenysStr, $pPakioSvoris) = explode("|", $pakuoteStr);
                        list($pPlotis, $pIlgis, $pAukstis) = explode("x", $pMatmenysStr);
                        if ($pKiekis AND $pTipas AND $pPlotis AND $pIlgis AND $pAukstis){
                            //echo "<br>CIA1111";


                            $PaksArray['PACK'][$i]['Kiekis'] = $pKiekis;
                            $PaksArray['PACK'][$i]['Plotis'] = round($pPlotis,2);
                            $PaksArray['PACK'][$i]['Ilgis'] = round($pIlgis,2);
                            $PaksArray['PACK'][$i]['Aukstis'] = round($pAukstis,2);
                            $PaksArray['PACK'][$i]['Turis'] = round($pPlotis/100*$pIlgis/100*$pAukstis/100,2);// mato vnt 
                            $PaksArray['PACK'][$i]['Svoris'] = round($pPakioSvoris,2);
                            if($PaksArray['PACK'][$i]['Turis']==0 AND $PaksArray['PACK'][$i]['Svoris']>0){
                                $PaksArray['PACK'][$i]['Turis']=0.01;
                            }

                            $PaksArray['PACK'][$i]['GrossSvoris'] = round($pPakioSvoris,2);
                            switch ($pTipas) {
                                case 'PK':
                                    $PaksArray['PACK'][$i]['Tipas'] = 'PK';
                                    break;
                                case 'EP':
                                    $PaksArray['PACK'][$i]['Tipas'] = 'EP';
                                    break;
                                case 'MP':
                                    $PaksArray['PACK'][$i]['Tipas'] = 'XP';
                                    break;
                                case 'RD':
                                    $PaksArray['PACK'][$i]['Tipas'] = 'RO';
                                    break;
                                case 'DD':
                                    $PaksArray['PACK'][$i]['Tipas'] = 'BX';
                                    break;
                                
                                default:
                                    $PaksArray['PACK'][$i]['Tipas'] = 'BX';
                                    break;
                            }
                            

                            $PaksArray['SUMKiekis'] += $PaksArray['PACK'][$i]['Kiekis'];//pakuociu/paleciu kiekis
                            $PaksArray['SUMPlotis'] += $PaksArray['PACK'][$i]['Plotis']*$PaksArray['PACK'][$i]['Kiekis'];
                            $PaksArray['SUMIlgis'] += $PaksArray['PACK'][$i]['Ilgis']*$PaksArray['PACK'][$i]['Kiekis'];
                            $PaksArray['SUMAukstis'] += $PaksArray['PACK'][$i]['Aukstis']*$PaksArray['PACK'][$i]['Kiekis'];
                            $PaksArray['SUMTuris'] += $PaksArray['PACK'][$i]['Turis']*$PaksArray['PACK'][$i]['Kiekis'];
                            $PaksArray['SUMSvoris'] += $PaksArray['PACK'][$i]['GrossSvoris']*$PaksArray['PACK'][$i]['Kiekis'];
                            if($pTipas=='EP' OR $pTipas=='MP'){//jeigu europalete arba maza palete tai skaiciuojam
                                $PaksArray['SUMPalets'] += $pKiekis; //paleciu kiekis
                            }
                            if($pTipas=='EP'){//jeigu europalete palete tai skaiciuojam
                                $PaksArray['SUM_EUPalets'] += $pKiekis; //paleciu kiekis
                            }
                            if($pTipas=='MP'){//jeigu maza palete tai skaiciuojam
                                $PaksArray['SUM_NonEUPalets'] += $pKiekis; //paleciu kiekis
                            }
                            if($pTipas=='DD'){//jeigu dezute
                                $PaksArray['SUM_Boxes'] += $pKiekis; //paleciu kiekis
                            }
                            if($pTipas=='PK'){//jeigu dezute
                                $PaksArray['SUM_Paks'] += $pKiekis; //paleciu kiekis
                            }
                            if($pTipas=='RD'){//jeigu dezute
                                $PaksArray['SUM_Rolls'] += $pKiekis; //paleciu kiekis
                            }

                            $i++;
                            //echo "<br>***01***";
                        }else{
                            $this->PacksIsSet=false;
                            //echo "<br>CIA1212";
                            $Error['message'] = "Blogai įvesti pakuotės matmenys.";
                            $Error['code'] = "TPD-2101"; //t-transport AD-address data
                            $Error['group'] = "PD"; //AD - address data error
                            $this->addError ($Error);

                        }//end else
                    }//end if
                }//end foreach

                if(!$PaksArray['SUMPalets']){
                    $PaksArray['SUMPalets']=0;
                }

                //rasom komenta apie pakuotes 
                $PaksArray['SUM_Spp'] = "";
                if($PaksArray['SUM_EUPalets']>0){
                    $PaksArray['SUM_Spp'] .= $PaksArray['SUM_EUPalets'].' Euro pallets ';
                }
                if($PaksArray['SUM_NonEUPalets']>0){
                    $PaksArray['SUM_Spp'] .= $PaksArray['SUM_NonEUPalets'].' Pallets ';
                }
                if($PaksArray['SUM_Boxes']>0){
                    $PaksArray['SUM_Spp'] .= $PaksArray['SUM_Boxes'].' Boxes ';
                }
                if($PaksArray['SUM_Paks']>0){
                    $PaksArray['SUM_Spp'] .= $PaksArray['SUM_Paks'].' Packages ';
                }
                if($PaksArray['SUM_Rolls']>0){
                    $PaksArray['SUM_Spp'] .= $PaksArray['SUM_Rolls'].' Rolls ';
                }

            }//end 

            $pakSkaicius = $i;
            if($pakSkaicius==0){
                $this->PacksIsSet=false;
                $Error['message'] = "Nėra duomenų apie pakuotes";
                $Error['code'] = "TPD-2008"; //t-transport AD-address data
                $Error['group'] = "PD"; //PD - pack data error
                $this->addError ($Error);
            }

            /*
            //tikrinam pakuociu duomenis
            if (!$SiuntaData['length']){
                $Error['message'] = "Nenustatytas pakuotės ilgis";
                $Error['code'] = "TPD-0001"; //t-transport AD-address data
                $Error['group'] = "PD"; //PD - pack data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['width']){
                $Error['message'] = "Nenustatytas pakuotės plotis";
                $Error['code'] = "TPD-0002"; //t-transport AD-address data
                $Error['group'] = "PD"; 
                $this->addError ($Error);
            }
            if (!$SiuntaData['height']){
                $Error['message'] = "Nenustatytas pakuotės aukštis";
                $Error['code'] = "TPD-0003"; //t-transport AD-address data
                $Error['group'] = "PD";
                $this->addError ($Error);
            }
            if (!$SiuntaData['weight']){
                $Error['message'] = "Nenustatytas pakuotės svoris";
                $Error['code'] = "TPD-0004"; //t-transport AD-address data
                $Error['group'] = "PD";
                $this->addError ($Error);
            }
            if (!$SiuntaData['packageType']){
                $Error['message'] = "Nenustatytas pakuotės tipas";
                $Error['code'] = "TPD-0005"; //t-transport AD-address data
                $Error['group'] = "PD";
                $this->addError ($Error);
            }
            if (!$SiuntaData['pieces']){
                $Error['message'] = "Nenustatytas pakuočių kiekis";
                $Error['code'] = "TPD-0006"; //t-transport AD-address data
                $Error['group'] = "PD";
                $this->addError ($Error);
            }
            */

            //echo "<br>***02***";
            if($this->haveErrors ('PD')==0){
                //echo "<br>***03***";
                //echo "<br>CIA0002";
                $this->packs['SvorisSum']=round($SiuntaData['SvorisSum'],2);

                $this->packs['PacksArray']=$PaksArray['PACK'];
                $this->packs['SUMKiekis']=$PaksArray['SUMKiekis'];
                $this->packs['SUMPlotis']=$PaksArray['SUMPlotis'];
                $this->packs['SUMIlgis']=$PaksArray['SUMIlgis'];
                $this->packs['SUMAukstis']=$PaksArray['SUMAukstis'];
                $this->packs['SUMTuris']=round($PaksArray['SUMTuris'], 2);
                $this->packs['SUMSvoris']=round($PaksArray['SUMSvoris'], 2);
                $this->packs['SUM_Spp']=$PaksArray['SUM_Spp'];

                $this->packs['SUMPalets']=$PaksArray['SUMPalets'];
                $this->packs['SUM_EUPalets']=$PaksArray['SUM_EUPalets'];
                $this->packs['SUM_NonEUPalets']=$PaksArray['SUM_NonEUPalets'];
                $this->packs['SUM_Boxes']=$PaksArray['SUM_Boxes'];
                $this->packs['SUM_Paks']=$PaksArray['SUM_Paks'];
                $this->packs['SUM_Rolls']=$PaksArray['SUM_Rolls'];



                $this->PacksIsSet=true;

                $this->SiuntaData['PAKS'] = $this->packs;

                //$this->packs= $this->pack;
                //var_dump($this->packs);
                $returnRez = true;
            }else{//end if
                //echo "<br>***04***";
                $this->PacksIsSet=false;
                $returnRez = false;
            }



        }else{//end if $addressData
                //echo "<br>***05***";
                $this->PacksIsSet=false;
                $Error['message'] = "Nėra jokių duomenų apie pakuotę";
                $Error['code'] = "TAD-2010"; //t-transport AD-address data
                $Error['group'] = "PD"; 
                $this->addError ($Error);

                //echo "<br>CIA888";
                $returnRez = false;
                
        }

        //priskiriam adresa


        return $returnRez; //boolen
    }//end function








    public function generateSCHXML ($param=array()){

        $pavyko = true;

        /*
            $this->AccessKey = $this->AccessKeyLive;
            $this->SCHuser = $this->SCHuserLive;
            $this->SCHpass = $this->SCHpassLive;
            $this->SCHKrovinioUzsakLink = $this->SCHKrovinioUzsakLinkLive;
            $this->SCHLipdukoSpausdLink = $this->SCHLipdukoSpausdLinkLive;
        */

        //Generuojam requestID
        $micro_Time=microtime(true)*10000;
        $reqID = 'SC'.$micro_Time;

        $form = '
<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                                <Body>
                                    <getBookingRequestLand xmlns="http://www.schenker.com/Booking/v1_1">
                                        <in xmlns="">
                                            <applicationArea>
                                               <accessKey>'.$this->AccessKey.'</accessKey>
                                               <requestID>'.$reqID.'</requestID>
                                            </applicationArea>
                                            <bookingLand submitBooking="true">
                                                <barcodeRequest start_pos="1" separated="false">A6</barcodeRequest>
';

//SHIPPER
if($this->SHIPPERDataSet){

$form .= '

                                                <address>
                                                    <contactPerson>
                                                        <email>'.$this->addres['SHIPPER']['contactPerson']['email'].'</email>
                                                    </contactPerson>
                                                    <name1>'.$this->addres['SHIPPER']['name1'].'</name1>
                                                    <customerAddressIdentifier>'.$this->addres['SHIPPER']['customerAddressIdentifier'].'</customerAddressIdentifier>
                                                    <email>'.$this->addres['SHIPPER']['email'].'</email>
                                                    <industry>CONSUMER</industry>
                                                    <locationType>PHYSICAL</locationType>
                                                    <personType>COMPANY</personType>
                                                    <phone>'.$this->addres['SHIPPER']['mobilePhone'].'</phone>
                                                    <postalCode>'.$this->addres['SHIPPER']['postalCode'].'</postalCode>
                                                    <stateCode></stateCode>
                                                    <stateName></stateName>
                                                    <schenkerAddressId>'.$this->addres['SHIPPER']['schenkerAddressId'].'</schenkerAddressId>
                                                    <street>'.$this->addres['SHIPPER']['street'].'</street>
                                                    <city>'.$this->addres['SHIPPER']['city'].'</city>
                                                    <countryCode>'.$this->addres['SHIPPER']['countryCode'].'</countryCode>
                                                    <type>SHIPPER</type>
                                                </address>
';
}else{
    $Error['message'] = "Nenustatytas siuntėjo adresas";
    $Error['code'] = "TAD-2051"; //t-transport AR-address data
    $Error['group'] = "AD"; //PD - pack data error
    $this->addError ($Error);
    $pavyko = fasle;

}


//PICKUP
if($this->PICKUPDataSet){
$form .= '

                                                <address>
                                                    <contactPerson>
                                                        <email>'.$this->addres['PICKUP']['contactPerson']['email'].'</email>
                                                    </contactPerson>
                                                    <name1>'.$this->addres['PICKUP']['name1'].'</name1>
                                                    <customerAddressIdentifier>'.$this->addres['PICKUP']['customerAddressIdentifier'].'</customerAddressIdentifier>
                                                    <email>'.$this->addres['PICKUP']['email'].'</email>
                                                    <industry>CONSUMER</industry>
                                                    <locationType>PHYSICAL</locationType>
                                                    <personType>COMPANY</personType>
                                                    <phone>'.$this->addres['PICKUP']['mobilePhone'].'</phone>
                                                    <postalCode>'.$this->addres['PICKUP']['postalCode'].'</postalCode>
                                                    <stateCode></stateCode>
                                                    <stateName></stateName>
                                                    <schenkerAddressId>'.$this->addres['PICKUP']['schenkerAddressId'].'</schenkerAddressId>
                                                    <street>'.$this->addres['PICKUP']['street'].'</street>
                                                    <city>'.$this->addres['PICKUP']['city'].'</city>
                                                    <countryCode>'.$this->addres['PICKUP']['countryCode'].'</countryCode>
                                                    <type>PICKUP</type>
                                                </address>
';
}else{

}


//CONSIGNEE
if($this->CONSIGNEEDataSet){
$form .= '
                                                <address>
                                                    <contactPerson>
                                                        <email>'.$this->addres['CONSIGNEE']['contactPerson']['email'].'</email>
                                                    </contactPerson>
                                                    <name1>'.$this->addres['CONSIGNEE']['name1'].'</name1>
                                                    <customerAddressIdentifier>'.$this->addres['CONSIGNEE']['customerAddressIdentifier'].'</customerAddressIdentifier>
                                                    <email>'.$this->addres['CONSIGNEE']['email'].'</email>
                                                    <industry>CONSUMER</industry>
                                                    <locationType>PHYSICAL</locationType>
                                                    <personType>COMPANY</personType>
                                                    <phone>'.$this->addres['CONSIGNEE']['mobilePhone'].'</phone>
                                                    <postalCode>'.$this->addres['CONSIGNEE']['postalCode'].'</postalCode>
                                                    <stateCode></stateCode>
                                                    <stateName></stateName>
                                                    <schenkerAddressId>'.$this->addres['CONSIGNEE']['schenkerAddressId'].'</schenkerAddressId>
                                                    <street>'.$this->addres['CONSIGNEE']['street'].'</street>
                                                    <city>'.$this->addres['CONSIGNEE']['city'].'</city>
                                                    <countryCode>'.$this->addres['CONSIGNEE']['countryCode'].'</countryCode>
                                                    <type>CONSIGNEE</type>
                                               </address>
';
}else{
    $Error['message'] = "Nenustatytas gavėjo adresas";
    $Error['code'] = "TAD-2052"; //t-transport AR-address data
    $Error['group'] = "AD"; //PD - pack data error
    $this->addError ($Error);
    $pavyko = fasle;

}



            
//DELIVERY
// Delivery naudojamas tada, kai gavejas yra viena imone, o gavimo adresas kitoks... tada siuncia i DELIVERY o saskaitos eina CONSIGNEE
//if($this->SiuntaData['NeutralumasKod']=='LS' OR $this->SiuntaData['NeutralumasKod']=='2LS'){

if($this->DELIVERYDataSet AND $this->tvs['neutralShipping']==1){
$form .= '
                                                <address>
                                                    <contactPerson>
                                                        <email>'.$this->addres['DELIVERY']['contactPerson']['email'].'</email>
                                                    </contactPerson>
                                                    <name1>'.$this->addres['DELIVERY']['name1'].'</name1>
                                                    <email>'.$this->addres['DELIVERY']['email'].'</email>
                                                    <industry>CONSUMER</industry>
                                                    <locationType>PHYSICAL</locationType>
                                                    <personType>COMPANY</personType>
                                                    <phone>'.$this->addres['DELIVERY']['mobilePhone'].'</phone>
                                                    <postalCode>'.$this->addres['DELIVERY']['postalCode'].'</postalCode>
                                                    <stateCode></stateCode>
                                                    <stateName></stateName>
                                                    <street>'.$this->addres['DELIVERY']['street'].'</street>
                                                    <city>'.$this->addres['DELIVERY']['city'].'</city>
                                                    <countryCode>'.$this->addres['DELIVERY']['countryCode'].'</countryCode>
                                                    <type>DELIVERY</type>
                                               </address>
';
}else{
    /*
    $Error['message'] = "Nenustatytas gavėjo adresas";
    $Error['code'] = "TAD-2052"; //t-transport AR-address data
    $Error['group'] = "AD"; //PD - pack data error
    $this->addError ($Error);
    $pavyko = fasle;
    */
}




$form .= '
                                               <incoterm>'.$this->tvs['incoterm'].'</incoterm>
                                               <incotermLocation>'.$this->tvs['incotermLocation'].'</incotermLocation>
                                               <productCode>'.$this->tvs['productCode'].'</productCode>';

if($this->tvs['productOption']){
    $form .= '
                                               <productOption>'.$this->tvs['productOption'].'</productOption>
    ';
}                                               

$form .= '                                               
                                               <measurementType>'.$this->tvs['measurementType'].'</measurementType>
                                               <customsClearance>'.$this->tvs['customsClearance'].'</customsClearance>
';


$form .= '
                                               <grossWeight>'.$this->packs['SvorisSum'].'</grossWeight>
                                               <indoorDelivery>'.$this->tvs['indoorDelivery'].'</indoorDelivery>
                                               <pickupDates>
                                                    <pickUpDateFrom>'.$this->tvs['pickupDates']['pickUpDateFrom'].'</pickUpDateFrom>
                                                    <pickUpDateTo>'.$this->tvs['pickupDates']['pickUpDateTo'].'</pickUpDateTo>
                                               </pickupDates>
                                               <reference>
                                                    <number>'.$this->tvs['reference']['number'].'</number>
                                                    <id>'.$this->tvs['reference']['id'].'</id>
                                               </reference>
                                               <handlingInstructions>'.$this->tvs['handlingInstructions'].'</handlingInstructions>
                                               <neutralShipping>'.$this->tvs['neutralShipping'].'</neutralShipping>
                                               <specialCargo>'.$this->tvs['specialCargo'].'</specialCargo>
';

$form .= '                                               
                                               <serviceType>D2D</serviceType>

                                               <shippingInformation>
';

if($this->packs['PacksArray']){
    foreach ($this->packs['PacksArray'] as $key => $pakis) {
        
        $tmpTuris = $pakis['Turis'] * $pakis['Kiekis'];
        $tmpSvoris = $pakis['GrossSvoris'] * $pakis['Kiekis'];
    
$form .= '
                                                        <shipmentPosition>
                                                            <dgr>false</dgr>
                                                            <cargoDesc></cargoDesc>
                                                            <length>'.$pakis['Ilgis'].'</length>
                                                            <width>'.$pakis['Plotis'].'</width>
                                                            <height>'.$pakis['Aukstis'].'</height>
                                                            <volume>'.$tmpTuris.'</volume>
                                                            <grossWeight>'.$tmpSvoris.'</grossWeight>
                                                            <packageType>'.$pakis['Tipas'].'</packageType>
                                                            <pieces>'.$pakis['Kiekis'].'</pieces>
                                                            <stackable>false</stackable>
                                                        </shipmentPosition>
';

    }//end foreach
}//end if

$form .= '                                                        
                                                    <grossWeight>'.$this->packs['SvorisSum'].'</grossWeight>
                                                    <volume>'.$this->packs['SUMTuris'].'</volume>
                                               </shippingInformation>
                                               <express>false</express>
                                               <foodRelated>false</foodRelated>
                                               <heatedTransport>false</heatedTransport>
                                               <homeDelivery>false</homeDelivery>
                                               <measureUnit>PIECES</measureUnit>
                                               <ownPickup>false</ownPickup>
                                               <pharmaceuticals>false</pharmaceuticals>
                                            </bookingLand>
                                        </in>
                                    </getBookingRequestLand>
                                </Body>
</Envelope>
        ';


        $this->SCHXML = $form;

        if($pavyko===true){
            $this->SCHXML_created = true;
        }else{
            $this->SCHXML_created = false;
        }

        var_dump($this->SCHXML);


        return $pavyko;
    }//end function









    private function setSenderAurikaAdresas ($gamyba){

        switch ($gamyba) {
            case 'KEG':
                    //address
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['name2']="";
                    $this->addres['SHIPPER']['customerAddressIdentifier']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['fax']="";
                    $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                    $this->addres['SHIPPER']['locationType']="PHYSICAL";
                    $this->addres['SHIPPER']['mobilePhone']=$this->SenderContactTelKEG;
                    $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['SHIPPER']['phone']="+37037363666";
                    $this->addres['SHIPPER']['poBox']="POBOX";
                    $this->addres['SHIPPER']['postalCode']="51127";
                    $this->addres['SHIPPER']['stateCode']="";
                    $this->addres['SHIPPER']['stateName']="";
                    $this->addres['SHIPPER']['preferredLanguage']="LT";
                    $this->addres['SHIPPER']['schenkerAddressId']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['street']="Taikos pr. 129A";
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                break;

            case 'KPG':
                    //address
                    /* Cia visais atvejais KEG, nes saskaitos eina ten */
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['name2']="";
                    $this->addres['SHIPPER']['customerAddressIdentifier']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['fax']="";
                    $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                    $this->addres['SHIPPER']['locationType']="PHYSICAL";
                    $this->addres['SHIPPER']['mobilePhone']=$this->SenderContactTelKEG;
                    $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['SHIPPER']['phone']="+37037363666";
                    $this->addres['SHIPPER']['poBox']="POBOX";
                    $this->addres['SHIPPER']['postalCode']="51127";
                    $this->addres['SHIPPER']['stateCode']="";
                    $this->addres['SHIPPER']['stateName']="";
                    $this->addres['SHIPPER']['preferredLanguage']="LT";
                    $this->addres['SHIPPER']['schenkerAddressId']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['street']="Taikos pr. 129A";
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                 break;
            case 'ETK':
            case 'ETK1':
                    //address
                    /* Cia visais atvejais KEG, nes saskaitos eina ten */
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['name2']="";
                    $this->addres['SHIPPER']['customerAddressIdentifier']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['fax']="";
                    $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                    $this->addres['SHIPPER']['locationType']="PHYSICAL";
                    $this->addres['SHIPPER']['mobilePhone']=$this->SenderContactTelKEG;
                    $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['SHIPPER']['phone']="+37037363666";
                    $this->addres['SHIPPER']['poBox']="POBOX";
                    $this->addres['SHIPPER']['postalCode']="51127";
                    $this->addres['SHIPPER']['stateCode']="";
                    $this->addres['SHIPPER']['stateName']="";
                    $this->addres['SHIPPER']['preferredLanguage']="LT";
                    $this->addres['SHIPPER']['schenkerAddressId']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['street']="Taikos pr. 129A";
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                 break;
            
            default:
                    $this->addres['SHIPPER']['contactPerson']['email']="";
                    $this->addres['SHIPPER']['name1']="";
                    $this->addres['SHIPPER']['name2']="";
                    $this->addres['SHIPPER']['customerAddressIdentifier']="";
                    $this->addres['SHIPPER']['email']="";
                    $this->addres['SHIPPER']['fax']="";
                    $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                    $this->addres['SHIPPER']['locationType']="PHYSICAL";
                    $this->addres['SHIPPER']['mobilePhone']="";
                    $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['SHIPPER']['phone']="";
                    $this->addres['SHIPPER']['poBox']="POBOX";
                    $this->addres['SHIPPER']['postalCode']="";
                    $this->addres['SHIPPER']['stateCode']="";
                    $this->addres['SHIPPER']['stateName']="";
                    $this->addres['SHIPPER']['preferredLanguage']="";
                    $this->addres['SHIPPER']['schenkerAddressId']="";
                    $this->addres['SHIPPER']['street']="";
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="";
                    $this->addres['SHIPPER']['countryCode']="";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = false;

                break;
        }//end switch

    }//end function





    private function setPICKUPAdresa ($gamyba){

        switch ($gamyba) {
            case 'KEG':
                    //address
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['PICKUP']['name1']=$this->SenderContactPersonKEG;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']=$this->SenderAddressIDKEG;
                    $this->addres['PICKUP']['email']=$this->SenderContactMailKEG;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelKEG;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']="+37037363666";
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']="51127";
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']=$this->SenderAddressIDKEG;
                    $this->addres['PICKUP']['street']="Taikos pr. 129A, warehouse";
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                break;

            case 'KPG':
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailKPG;
                    $this->addres['PICKUP']['name1']=$this->SenderContactPersonKPG;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']=$this->SenderAddressIDKPG;
                    $this->addres['PICKUP']['email']=$this->SenderContactMailKPG;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelKPG;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']="+37037363666";
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']="51333";
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']=$this->SenderAddressIDKPG;
                    $this->addres['PICKUP']['street']="Chemijos g. 29F";
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                 break;
            case 'ETK':
            case 'ETK1':
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailETK;
                    $this->addres['PICKUP']['name1']=$this->SenderContactPersonETK;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']=$this->SenderAddressIDETK;
                    $this->addres['PICKUP']['email']=$this->SenderContactMailETK;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelETK;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']="+37037363666";
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeETK; // = "47193";
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']=$this->SenderAddressIDETK;
                    $this->addres['PICKUP']['street']=$this->SenderAddressETK; // = "Jovarų g. 2A"
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                 break;
            
            default:
                    $this->addres['PICKUP']['contactPerson']['email']='';
                    $this->addres['PICKUP']['name1']='';
                    $this->addres['PICKUP']['name2']='';
                    $this->addres['PICKUP']['customerAddressIdentifier']='';
                    $this->addres['PICKUP']['email']='';
                    $this->addres['PICKUP']['fax']='';
                    $this->addres['PICKUP']['industry']='';
                    $this->addres['PICKUP']['locationType']='';
                    $this->addres['PICKUP']['mobilePhone']='';
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']='';
                    $this->addres['PICKUP']['poBox']='';
                    $this->addres['PICKUP']['postalCode']='';
                    $this->addres['PICKUP']['stateCode']='';
                    $this->addres['PICKUP']['stateName']='';
                    $this->addres['PICKUP']['preferredLanguage']='';
                    $this->addres['PICKUP']['schenkerAddressId']='';
                    $this->addres['PICKUP']['street']='';
                    $this->addres['PICKUP']['street2']='';
                    $this->addres['PICKUP']['city']='';
                    $this->addres['PICKUP']['countryCode']='';
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = false;

                break;
        }//end switch

    }//end function




    /*  Nustatom consignee adresa
        Adreso duomenys imam is _TMSSiuntos lenteles, ten jau turi buti geri adresai 
        jeigu darysim kita moduli, tai reiks prideti eiluciu kur duomenys is lenteles keicia duomenys is HTML lauku

        !!! GALI BUTI NEBENAUDOJAMA IR PAKEISTA I setAddresses ()
    */
    public function setConsigneeAddress___ ($SiuntaData){

        $this->CONSIGNEEDataSet = false;
        //tikrinam duomenis
        if ($SiuntaData){


                    /* sanitaizinam pavadinimus */
                    //$SiuntaData['GavName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['GavName']);
                    $SiuntaData['GavName'] = (htmlspecialchars_decode($SiuntaData['GavName'],ENT_QUOTES) == $SiuntaData['GavName']) ? htmlspecialchars($SiuntaData['GavName'],ENT_QUOTES) : $SiuntaData['GavName'];

                    //$SiuntaData['ClientName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['ClientName']);
                    $SiuntaData['ClientName'] = (htmlspecialchars_decode($SiuntaData['ClientName'],ENT_QUOTES) == $SiuntaData['ClientName']) ? htmlspecialchars($SiuntaData['ClientName'],ENT_QUOTES) : $SiuntaData['ClientName'];

                    //$SiuntaData['Miestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Miestas']);
                    $SiuntaData['Miestas'] = (htmlspecialchars_decode($SiuntaData['Miestas'],ENT_QUOTES) == $SiuntaData['Miestas']) ? htmlspecialchars($SiuntaData['Miestas'],ENT_QUOTES) : $SiuntaData['Miestas'];

                    //$SiuntaData['Delivery_street']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_street']);
                    $SiuntaData['Delivery_street'] = (htmlspecialchars_decode($SiuntaData['Delivery_street'],ENT_QUOTES) == $SiuntaData['Delivery_street']) ? htmlspecialchars($SiuntaData['Delivery_street'],ENT_QUOTES) : $SiuntaData['Delivery_street'];

                    //$SiuntaData['street2']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['street2']);
                    $SiuntaData['street2'] = (htmlspecialchars_decode($SiuntaData['street2'],ENT_QUOTES) == $SiuntaData['street2']) ? htmlspecialchars($SiuntaData['street2'],ENT_QUOTES) : $SiuntaData['street2'];

                    //$SiuntaData['Delivery_contact']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_contact']);
                    $SiuntaData['Delivery_contact'] = (htmlspecialchars_decode($SiuntaData['Delivery_contact'],ENT_QUOTES) == $SiuntaData['Delivery_contact']) ? htmlspecialchars($SiuntaData['Delivery_contact'],ENT_QUOTES) : $SiuntaData['Delivery_contact'];


                    if (!$SiuntaData['ClientName']){
                        $Error['message'] = "Nenustatytas įmonės pavadinimas";
                        $Error['code'] = "TAD-2001"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    if (!$SiuntaData['Delivery_street']){
                        $Error['message'] = "Nenustatyta adreso gatvė";
                        $Error['code'] = "TAD-2003"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    if (!$SiuntaData['Miestas']){
                        $Error['message'] = "Nenustatytas adreso miestas";
                        $Error['code'] = "TAD-2031"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    if (!$SiuntaData['SaliesKodas']){
                        $Error['message'] = "Nenustatytas šalies kodas";
                        $Error['code'] = "TAD-2005"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    if (!$SiuntaData['PostKodas']){
                        $Error['message'] = "Nenustatytas pašto kodas";
                        $Error['code'] = "TAD-2002"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    /*
                    if (!$SiuntaData['type'] OR ($SiuntaData['type']!='SHIPPER' AND $SiuntaData['type']!='CONSIGNEE')){
                        $Error['message'] = "Nenustatytas siuntėjo ar gavėjo tipas (SHIPPER or CONSIGNEE)";
                        $Error['code'] = "TAD-0006"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }
                    */


                    $klaiduSk = $this->haveErrors ('AD');
                    if($klaiduSk==0){

                        /* basic address duomenys */
                        //$this->address[$SiuntaData['type']]['addressSet']=true;
                        //$this->addres['CONSIGNEE']['type'] = 'CONSIGNEE';
                        $this->addres['CONSIGNEE']['contactPerson']['email'] = trim ($SiuntaData['Delivery_contact_email']);
                        $this->addres['CONSIGNEE']['name1'] = trim ($SiuntaData['ClientName']);
                        $this->addres['CONSIGNEE']['name2']="";
                        $this->addres['CONSIGNEE']['street'] = trim ($SiuntaData['Delivery_street']);
                        $this->addres['CONSIGNEE']['city'] = trim ($SiuntaData['Miestas']);
                        $this->addres['CONSIGNEE']['countryCode'] = trim ($SiuntaData['SaliesKodas']);
                        $this->addres['CONSIGNEE']['postalCode'] = trim ($SiuntaData['PostKodas']);

                        $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                        $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                        $this->addres['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
                        $this->addres['CONSIGNEE']['poBox']="POBOX";

                        /* additional address duomenys */
                        $this->addres['CONSIGNEE']['email'] = trim ($SiuntaData['Delivery_email']);
                        $this->addres['CONSIGNEE']['mobilePhone'] = trim ($SiuntaData['Delivery_contact_phone']." ".$SiuntaData['Delivery_phone']);
                        $this->addres['CONSIGNEE']['phone'] = trim ($SiuntaData['Delivery_phone']);
                        //$this->addres['CONSIGNEE']['PreferredLanguage'] = $SiuntaData['Delivery_phone'];

                        /*
                        K $this->addres['CONSIGNEE']['contactPerson']['email']=$this->sParam['det_ContactPersonMail'];
                        K ----- $this->sParam['det_ContactPerson'];
                        $this->addres['CONSIGNEE']['name1']=$this->sParam['ClientName'];
                        $this->addres['CONSIGNEE']['name2']="";
                        $this->addres['CONSIGNEE']['customerAddressIdentifier']='';//$SiuntaData['AdresasID'];//cia gavejo adresas bet ne kliento
                        +$this->addres['CONSIGNEE']['email']=$SiuntaData['ClientEmail'];
                        +$this->addres['CONSIGNEE']['fax']=$SiuntaData['ClientTelFax'];
                        +$this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                        +$this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                        K $this->addres['CONSIGNEE']['mobilePhone']='';
                        +$this->addres['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
                        +$this->addres['CONSIGNEE']['phone']=$SiuntaData['ClientTel'];
                        +$this->addres['CONSIGNEE']['poBox']="POBOX";
                        +$this->addres['CONSIGNEE']['postalCode']=$SiuntaData['ClientPostCode'];
                        K $this->addres['CONSIGNEE']['stateCode']="";
                        K $this->addres['CONSIGNEE']['stateName']="";
                        K $this->addres['CONSIGNEE']['preferredLanguage']="";
                        K $this->addres['CONSIGNEE']['schenkerAddressId']="";
                        K $this->addres['CONSIGNEE']['street']=$SiuntaData['ClientGatve'];
                        K $this->addres['CONSIGNEE']['street2']="";
                        K $this->addres['CONSIGNEE']['city']=$SiuntaData['ClientRajonasMiestas'];
                        K $this->addres['CONSIGNEE']['countryCode']=$SiuntaData['ClientLandRef'];
                        +$this->addres['CONSIGNEE']['type']="CONSIGNEE";
                        */


                        /*
                        $this->address[$SiuntaData['type']]['name1']=$SiuntaData['GavName'];
                        $this->address[$SiuntaData['type']]['name2']=$SiuntaData['ClientName'];
                        $this->address[$SiuntaData['type']]['company_code']=$SiuntaData['company_code'];// !!!!!!!!!!!!!!!!! nera kodo
                        $this->address[$SiuntaData['type']]['countryCode']=$SiuntaData['SaliesKodas'];
                        $this->address[$SiuntaData['type']]['city']=$SiuntaData['Miestas'];
                        $this->address[$SiuntaData['type']]['street1']=$SiuntaData['Delivery_street'];
                        $this->address[$SiuntaData['type']]['street2']=$SiuntaData['street2'];//!!!!!!!!!!!!nera gatves2
                        $this->address[$SiuntaData['type']]['stateCode']=$SiuntaData['stateCode'];//!!!!!!!!!!  nera StateCode
                        $this->address[$SiuntaData['type']]['stateName']=$SiuntaData['stateName'];//!!!!!!!!!!  nera StateName
                        $this->address[$SiuntaData['type']]['postalCode']=$SiuntaData['PostKodas'];    
                        */

                        /*
                        $this->address[$SiuntaData['type']]['contactPerson']=$SiuntaData['Delivery_contact'];
                        if($SiuntaData['Delivery_contact_phone']){
                            $this->address[$SiuntaData['type']]['contactPersonTel']=$SiuntaData['Delivery_contact_phone'];
                        }else{
                            $this->address[$SiuntaData['type']]['contactPersonTel']=$SiuntaData['Delivery_phone'];
                        }
                        if($SiuntaData['Delivery_contact_email']){
                            $this->address[$SiuntaData['type']]['contactPersonEmail']=$SiuntaData['Delivery_contact_email'];
                        }else{
                            $this->address[$SiuntaData['type']]['contactPersonEmail']=$SiuntaData['Delivery_email'];
                        }
                        $this->address[$SiuntaData['type']]['email']=$SiuntaData['Delivery_email'];
                        $this->address[$SiuntaData['type']]['phone']=$SiuntaData['Delivery_phone'];
                        */

                       

                        $this->CONSIGNEEDataSet = true;
                        $returnRez = true;
                    }else{//end if
                        $returnRez = false;
                    }

            

        }else{//end if $addressData
                $Error['message'] = "Nėra jokių duomenų apie adresą";
                $Error['code'] = "TAD-1001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
                $returnRez = false;
        }

        //var_dump($this->errorArray);
        //priskiriam adresa


        return $returnRez; //boolen
    }//end function



    public function setAddresses ($SiuntaData){

        var_dump($SiuntaData);

        $this->CONSIGNEEDataSet = false;
        $this->SHIPPERDataSet = false;
        $this->DELIVERYDataSet = false;
        $this->PICKUPDataSet = false;

        //tikrinam duomenis
        if ($SiuntaData){

            /* sanitaizinam pavadinimus */
            /* senas kodas */
            //$SiuntaData['GavName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['GavName']);
            $SiuntaData['GavName'] = (htmlspecialchars_decode($SiuntaData['GavName'],ENT_QUOTES) == $SiuntaData['GavName']) ? htmlspecialchars($SiuntaData['GavName'],ENT_QUOTES) : $SiuntaData['GavName'];

            //$SiuntaData['GavGatve']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['GavGatve']);
            $SiuntaData['GavGatve'] = (htmlspecialchars_decode($SiuntaData['GavGatve'],ENT_QUOTES) == $SiuntaData['GavGatve']) ? htmlspecialchars($SiuntaData['GavGatve'],ENT_QUOTES) : $SiuntaData['GavGatve'];

            //$SiuntaData['GavRajonasMiestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['GavRajonasMiestas']);
            $SiuntaData['GavRajonasMiestas'] = (htmlspecialchars_decode($SiuntaData['GavRajonasMiestas'],ENT_QUOTES) == $SiuntaData['GavRajonasMiestas']) ? htmlspecialchars($SiuntaData['GavRajonasMiestas'],ENT_QUOTES) : $SiuntaData['GavRajonasMiestas'];
            
            
            //$SiuntaData['ClientName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['ClientName']);
            $SiuntaData['ClientName'] = (htmlspecialchars_decode($SiuntaData['ClientName'],ENT_QUOTES) == $SiuntaData['ClientName']) ? htmlspecialchars($SiuntaData['ClientName'],ENT_QUOTES) : $SiuntaData['ClientName'];

            //$SiuntaData['Miestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Miestas']);
            $SiuntaData['Miestas'] = (htmlspecialchars_decode($SiuntaData['Miestas'],ENT_QUOTES) == $SiuntaData['Miestas']) ? htmlspecialchars($SiuntaData['Miestas'],ENT_QUOTES) : $SiuntaData['Miestas'];

            //$SiuntaData['Delivery_street']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_street']);
            $SiuntaData['Delivery_street'] = (htmlspecialchars_decode($SiuntaData['Delivery_street'],ENT_QUOTES) == $SiuntaData['Delivery_street']) ? htmlspecialchars($SiuntaData['Delivery_street'],ENT_QUOTES) : $SiuntaData['Delivery_street'];

            //$SiuntaData['street2']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['street2']);
            $SiuntaData['street2'] = (htmlspecialchars_decode($SiuntaData['street2'],ENT_QUOTES) == $SiuntaData['street2']) ? htmlspecialchars($SiuntaData['street2'],ENT_QUOTES) : $SiuntaData['street2'];

            //$SiuntaData['Delivery_contact']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_contact']);
            $SiuntaData['Delivery_contact'] = (htmlspecialchars_decode($SiuntaData['Delivery_contact'],ENT_QUOTES) == $SiuntaData['Delivery_contact']) ? htmlspecialchars($SiuntaData['Delivery_contact'],ENT_QUOTES) : $SiuntaData['Delivery_contact'];

            //$SiuntaData['ClientGatve']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['ClientGatve']);
            $SiuntaData['ClientGatve'] = (htmlspecialchars_decode($SiuntaData['ClientGatve'],ENT_QUOTES) == $SiuntaData['ClientGatve']) ? htmlspecialchars($SiuntaData['ClientGatve'],ENT_QUOTES) : $SiuntaData['ClientGatve'];

            //$SiuntaData['ClientRajonasMiestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['ClientRajonasMiestas']);
            $SiuntaData['ClientRajonasMiestas'] = (htmlspecialchars_decode($SiuntaData['ClientRajonasMiestas'],ENT_QUOTES) == $SiuntaData['ClientRajonasMiestas']) ? htmlspecialchars($SiuntaData['ClientRajonasMiestas'],ENT_QUOTES) : $SiuntaData['ClientRajonasMiestas'];

            
            

            /* naujas kodas */
            //gavejas
            //$SiuntaData['Gavejas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Gavejas']);
            $SiuntaData['Gavejas'] = (htmlspecialchars_decode($SiuntaData['Gavejas'],ENT_QUOTES) == $SiuntaData['Gavejas']) ? htmlspecialchars($SiuntaData['Gavejas'],ENT_QUOTES) : $SiuntaData['Gavejas'];

            //$SiuntaData['Adresas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Adresas']);
            $SiuntaData['Adresas'] = (htmlspecialchars_decode($SiuntaData['Adresas'],ENT_QUOTES) == $SiuntaData['Adresas']) ? htmlspecialchars($SiuntaData['Adresas'],ENT_QUOTES) : $SiuntaData['Adresas'];

            //$SiuntaData['Delivery_street']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_street']);
            $SiuntaData['Delivery_street'] = (htmlspecialchars_decode($SiuntaData['Delivery_street'],ENT_QUOTES) == $SiuntaData['Delivery_street']) ? htmlspecialchars($SiuntaData['Delivery_street'],ENT_QUOTES) : $SiuntaData['Delivery_street'];

            //$SiuntaData['Miestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Miestas']);
            $SiuntaData['Miestas'] = (htmlspecialchars_decode($SiuntaData['Miestas'],ENT_QUOTES) == $SiuntaData['Miestas']) ? htmlspecialchars($SiuntaData['Miestas'],ENT_QUOTES) : $SiuntaData['Miestas'];

            //siuntejas
            //$SiuntaData['SiuntejoName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['SiuntejoName']);
            $SiuntaData['SiuntejoName'] = (htmlspecialchars_decode($SiuntaData['SiuntejoName'],ENT_QUOTES) == $SiuntaData['SiuntejoName']) ? htmlspecialchars($SiuntaData['SiuntejoName'],ENT_QUOTES) : $SiuntaData['SiuntejoName'];

            //$SiuntaData['SiuntejoAdrF']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['SiuntejoAdrF']);
            $SiuntaData['SiuntejoAdrF'] = (htmlspecialchars_decode($SiuntaData['SiuntejoAdrF'],ENT_QUOTES) == $SiuntaData['SiuntejoAdrF']) ? htmlspecialchars($SiuntaData['SiuntejoAdrF'],ENT_QUOTES) : $SiuntaData['SiuntejoAdrF'];

            //$SiuntaData['SiuntejoStreet']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['SiuntejoStreet']);
            $SiuntaData['SiuntejoStreet'] = (htmlspecialchars_decode($SiuntaData['SiuntejoStreet'],ENT_QUOTES) == $SiuntaData['SiuntejoStreet']) ? htmlspecialchars($SiuntaData['SiuntejoStreet'],ENT_QUOTES) : $SiuntaData['SiuntejoStreet'];

            //$SiuntaData['SiuntejoMiestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['SiuntejoMiestas']);
            $SiuntaData['SiuntejoMiestas'] = (htmlspecialchars_decode($SiuntaData['SiuntejoMiestas'],ENT_QUOTES) == $SiuntaData['SiuntejoMiestas']) ? htmlspecialchars($SiuntaData['SiuntejoMiestas'],ENT_QUOTES) : $SiuntaData['SiuntejoMiestas'];

            
            //$SiuntaData['Siuntejo_contact']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Siuntejo_contact']);
            $SiuntaData['Siuntejo_contact'] = (htmlspecialchars_decode($SiuntaData['Siuntejo_contact'],ENT_QUOTES) == $SiuntaData['Siuntejo_contact']) ? htmlspecialchars($SiuntaData['Siuntejo_contact'],ENT_QUOTES) : $SiuntaData['Siuntejo_contact'];


            //$SiuntaData['DilerioName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['DilerioName']);
            $SiuntaData['DilerioName'] = (htmlspecialchars_decode($SiuntaData['DilerioName'],ENT_QUOTES) == $SiuntaData['DilerioName']) ? htmlspecialchars($SiuntaData['DilerioName'],ENT_QUOTES) : $SiuntaData['DilerioName'];

            //$SiuntaData['DilerioAdrF']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['DilerioAdrF']);
            $SiuntaData['DilerioAdrF'] = (htmlspecialchars_decode($SiuntaData['DilerioAdrF'],ENT_QUOTES) == $SiuntaData['DilerioAdrF']) ? htmlspecialchars($SiuntaData['DilerioAdrF'],ENT_QUOTES) : $SiuntaData['DilerioAdrF'];

            //$SiuntaData['DilerioStreet']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['DilerioStreet']);
            $SiuntaData['DilerioStreet'] = (htmlspecialchars_decode($SiuntaData['DilerioStreet'],ENT_QUOTES) == $SiuntaData['DilerioStreet']) ? htmlspecialchars($SiuntaData['DilerioStreet'],ENT_QUOTES) : $SiuntaData['DilerioStreet'];

            //$SiuntaData['DilerioMiestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['DilerioMiestas']);
            $SiuntaData['DilerioMiestas'] = (htmlspecialchars_decode($SiuntaData['DilerioMiestas'],ENT_QUOTES) == $SiuntaData['DilerioMiestas']) ? htmlspecialchars($SiuntaData['DilerioMiestas'],ENT_QUOTES) : $SiuntaData['DilerioMiestas'];

            //$SiuntaData['Dilerio_contact']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Dilerio_contact']);
            $SiuntaData['Dilerio_contact'] = (htmlspecialchars_decode($SiuntaData['Dilerio_contact'],ENT_QUOTES) == $SiuntaData['Dilerio_contact']) ? htmlspecialchars($SiuntaData['Dilerio_contact'],ENT_QUOTES) : $SiuntaData['Dilerio_contact'];

            

            //$SiuntaData['Delivery_comment']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_comment']);
            $SiuntaData['Delivery_comment'] = (htmlspecialchars_decode($SiuntaData['Delivery_comment'],ENT_QUOTES) == $SiuntaData['Delivery_comment']) ? htmlspecialchars($SiuntaData['Delivery_comment'],ENT_QUOTES) : $SiuntaData['Delivery_comment'];


            //Tikrinam klaidas
            if (!$SiuntaData['Gavejas']){
                $Error['message'] = "Nenustatytas gavėjo pavadinimas";
                $Error['code'] = "TAD-2001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Delivery_street']){
                $Error['message'] = "Nenustatytas gavėjo adresas";
                $Error['code'] = "TAD-2003"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Miestas']){
                $Error['message'] = "Nenustatytas gavėjo miestas";
                $Error['code'] = "TAD-2031"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['SaliesKodas']){
                $Error['message'] = "Nenustatytas gavėjo šalies kodas";
                $Error['code'] = "TAD-2005"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['PostKodas']){
                $Error['message'] = "Nenustatytas gavėjo pašto kodas";
                $Error['code'] = "TAD-2002"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            /*
            if (!$SiuntaData['PostKodas']){
                $Error['message'] = "Nenustatytas gavėjo pašto kodas";
                $Error['code'] = "TAD-2006"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            */
            /*
            if (!$SiuntaData['Det_Delivery_phone'] AND !$SiuntaData['Det_Delivery_contact_phone']){
                $Error['message'] = "Nenurodytas gavžėjo telefonas";
                $Error['code'] = "TAD-2007"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            */
            if($SiuntaData['NeutralumasKod']=='LS' OR $SiuntaData['NeutralumasKod']=='2LS'){//LS neutralumas ARBA 2LS dvigubas neutralumas
                if (!$SiuntaData['SiuntejoName']){
                    $Error['message'] = "Nenustatytas siuntėjo pavadinimas";
                    $Error['code'] = "TAD-2201"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoStreet']){
                    $Error['message'] = "Nenustatyta siuntėjo adresas";
                    $Error['code'] = "TAD-2203"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoMiestas']){
                    $Error['message'] = "Nenustatytas siuntėjo miestas";
                    $Error['code'] = "TAD-2231"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoSaliesKodas']){
                    $Error['message'] = "Nenustatytas siuntėjo šalies kodas";
                    $Error['code'] = "TAD-2205"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoPostKodas']){
                    $Error['message'] = "Nenustatytas siuntėjo pašto kodas";
                    $Error['code'] = "TAD-2202"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }

                if($SiuntaData['NeutralumasKod']=='2LS'){//2LS dvigubas neutralumas

                    if (!$SiuntaData['DilerioName']){
                        $Error['message'] = "Nenustatytas tarpininko (dilerio) pavadinimas";
                        $Error['code'] = "TAD-2301"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }

                }//end if NEUTRALUMAS 2LS

            }//end if neutralumas LS OR 2LS


            /* ************************ nustatinejam adresus (new) **************************** */

            $klaiduSk = $this->haveErrors ('AD');
            //jeigu neturim klaidu su adresu tai setinam adresus
            if($klaiduSk==0){

                    //ivedam duomenys
                    if($SiuntaData['NeutralumasKod']=='2LS'){//2LS dvigubas neutralumas
                        /* 2LS GAVEJAS */
                        /* 2LS SIUNTEJAS */
                        /* 2LS DILERIS */
                        /* 2LS PICKUP */

                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim ($SiuntaData['Gavejas']);
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                if($SiuntaData['Delivery_contact_phone']){
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
                                }else{
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_phone']);
                                }
                                $this->addres['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['CONSIGNEE']['phone']=trim ($SiuntaData['Delivery_phone']);
                                $this->addres['CONSIGNEE']['poBox']="POBOX";
                                $this->addres['CONSIGNEE']['postalCode']=trim ($SiuntaData['PostKodas']);
                                $this->addres['CONSIGNEE']['stateCode']="";
                                $this->addres['CONSIGNEE']['stateName']="";
                                $this->addres['CONSIGNEE']['preferredLanguage']='';
                                $this->addres['CONSIGNEE']['schenkerAddressId']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['street']=trim ($SiuntaData['Delivery_street']);
                                $this->addres['CONSIGNEE']['street2']="";
                                $this->addres['CONSIGNEE']['city']=trim ($SiuntaData['Miestas']);
                                $this->addres['CONSIGNEE']['countryCode']=trim ($SiuntaData['SaliesKodas']);
                                $this->addres['CONSIGNEE']['type']="CONSIGNEE";                    
                                $this->CONSIGNEEDataSet = true;


                                $this->addres['SHIPPER']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                $this->addres['SHIPPER']['name1']=trim ($SiuntaData['SiuntejoName']);
                                $this->addres['SHIPPER']['name2']="";
                                $this->addres['SHIPPER']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['email']=trim ($SiuntaData['Siuntejo_email']);
                                $this->addres['SHIPPER']['fax']="";
                                $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                                $this->addres['SHIPPER']['locationType']="PHYSICAL";
                                $this->addres['SHIPPER']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['SHIPPER']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                $this->addres['SHIPPER']['poBox']="POBOX";
                                $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                $this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                $this->setPICKUPAdresa ($SiuntaData['Sandelys']);



                                /* !!! jeigu irasom siuos duomenis, tai siam adresui ir siuncia (2LS), seip duomenimis uzpildo teisingai*/
                                /*
                                $this->addres['DELIVERY']['contactPerson']['email']=$SiuntaData['Dilerio_contact_email'];
                                $this->addres['DELIVERY']['name1']=$SiuntaData['DilerioName'];
                                $this->addres['DELIVERY']['name2']="";
                                $this->addres['DELIVERY']['customerAddressIdentifier']=$SiuntaData['DilerioAdresasID'];
                                $this->addres['DELIVERY']['email']=$SiuntaData['Dilerio_email'];
                                $this->addres['DELIVERY']['fax']="";
                                $this->addres['DELIVERY']['industry']="AUTOMOTIVE";
                                $this->addres['DELIVERY']['locationType']="PHYSICAL";
                                $this->addres['DELIVERY']['mobilePhone']=$SiuntaData['Dilerio_contact_phone'];
                                $this->addres['DELIVERY']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['DELIVERY']['phone']=$SiuntaData['Dilerio_phone'];
                                $this->addres['DELIVERY']['poBox']="POBOX";
                                $this->addres['DELIVERY']['postalCode']=$SiuntaData['DilerioPostKodas'];
                                $this->addres['DELIVERY']['stateCode']="";
                                $this->addres['DELIVERY']['stateName']="";
                                $this->addres['DELIVERY']['preferredLanguage']='';
                                $this->addres['DELIVERY']['schenkerAddressId']='';
                                $this->addres['DELIVERY']['street']=$SiuntaData['DilerioStreet'];
                                $this->addres['DELIVERY']['street2']="";
                                $this->addres['DELIVERY']['city']=$SiuntaData['DilerioMiestas'];
                                $this->addres['DELIVERY']['countryCode']=$SiuntaData['DilerioSaliesKodas'];
                                $this->addres['DELIVERY']['type']="DELIVERY"; 
                                */                   
                                $this->DELIVERYDataSet = false;


                    }elseif($SiuntaData['NeutralumasKod']=='LS'){//LS-neutralumas
                        /* LS GAVEJAS */
                        /* LS SIUNTEJAS */
                        /* LS PICKUP */

                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim ($SiuntaData['Gavejas']);
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                //$this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
                                if($SiuntaData['Delivery_contact_phone']){
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
                                }else{
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_phone']);
                                }
                                $this->addres['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['CONSIGNEE']['phone']=trim ($SiuntaData['Delivery_phone']);
                                $this->addres['CONSIGNEE']['poBox']="POBOX";
                                $this->addres['CONSIGNEE']['postalCode']=trim ($SiuntaData['PostKodas']);
                                $this->addres['CONSIGNEE']['stateCode']="";
                                $this->addres['CONSIGNEE']['stateName']="";
                                $this->addres['CONSIGNEE']['preferredLanguage']='';
                                $this->addres['CONSIGNEE']['schenkerAddressId']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['street']=trim ($SiuntaData['Delivery_street']);
                                $this->addres['CONSIGNEE']['street2']="";
                                $this->addres['CONSIGNEE']['city']=trim ($SiuntaData['Miestas']);
                                $this->addres['CONSIGNEE']['countryCode']=trim ($SiuntaData['SaliesKodas']);
                                $this->addres['CONSIGNEE']['type']="CONSIGNEE";                    
                                $this->CONSIGNEEDataSet = true;

                                $this->addres['SHIPPER']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                $this->addres['SHIPPER']['name1']=trim ($SiuntaData['SiuntejoName']);
                                $this->addres['SHIPPER']['name2']="";
                                $this->addres['SHIPPER']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['email']=trim ($SiuntaData['Siuntejo_email']);
                                $this->addres['SHIPPER']['fax']="";
                                $this->addres['SHIPPER']['industry']="AUTOMOTIVE";
                                $this->addres['SHIPPER']['locationType']="PHYSICAL";
                                $this->addres['SHIPPER']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                $this->addres['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['SHIPPER']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                $this->addres['SHIPPER']['poBox']="POBOX";
                                $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                $this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                $this->setPICKUPAdresa ($SiuntaData['Sandelys']);


                                $this->addres['DELIVERY']['contactPerson']['email']='';
                                $this->addres['DELIVERY']['name1']='';
                                $this->addres['DELIVERY']['name2']='';
                                $this->addres['DELIVERY']['customerAddressIdentifier']='';
                                $this->addres['DELIVERY']['email']='';
                                $this->addres['DELIVERY']['fax']='';
                                $this->addres['DELIVERY']['industry']="AUTOMOTIVE";
                                $this->addres['DELIVERY']['locationType']="PHYSICAL";
                                $this->addres['DELIVERY']['mobilePhone']='';
                                $this->addres['DELIVERY']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['DELIVERY']['phone']='';
                                $this->addres['DELIVERY']['poBox']="POBOX";
                                $this->addres['DELIVERY']['postalCode']='';
                                $this->addres['DELIVERY']['stateCode']='';
                                $this->addres['DELIVERY']['stateName']='';
                                $this->addres['DELIVERY']['preferredLanguage']='';
                                $this->addres['DELIVERY']['schenkerAddressId']='';
                                $this->addres['DELIVERY']['street']='';
                                $this->addres['DELIVERY']['street2']='';
                                $this->addres['DELIVERY']['city']='';
                                $this->addres['DELIVERY']['countryCode']='';
                                $this->addres['DELIVERY']['type']="DELIVERY";                    
                                $this->DELIVERYDataSet = false;


                    }else{//nera neutralumo


                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim ($SiuntaData['Gavejas']);
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                //$this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
                                if($SiuntaData['Delivery_contact_phone']){
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
                                }else{
                                    $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_phone']);
                                }
                                $this->addres['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['CONSIGNEE']['phone']=trim ($SiuntaData['Delivery_phone']);
                                $this->addres['CONSIGNEE']['poBox']="POBOX";
                                $this->addres['CONSIGNEE']['postalCode']=trim ($SiuntaData['PostKodas']);
                                $this->addres['CONSIGNEE']['stateCode']="";
                                $this->addres['CONSIGNEE']['stateName']="";
                                $this->addres['CONSIGNEE']['preferredLanguage']='';
                                $this->addres['CONSIGNEE']['schenkerAddressId']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['street']=trim ($SiuntaData['Delivery_street']);
                                $this->addres['CONSIGNEE']['street2']="";
                                $this->addres['CONSIGNEE']['city']=trim ($SiuntaData['Miestas']);
                                $this->addres['CONSIGNEE']['countryCode']=trim ($SiuntaData['SaliesKodas']);
                                $this->addres['CONSIGNEE']['type']="CONSIGNEE";                    
                                $this->CONSIGNEEDataSet = true;

                                // Jeigu SHIPER - AURIKA
                                $this->setSenderAurikaAdresas ($SiuntaData['Sandelys']);

                                //address
                                $this->setPICKUPAdresa ($SiuntaData['Sandelys']);


                                $this->addres['DELIVERY']['contactPerson']['email']='';
                                $this->addres['DELIVERY']['name1']='';
                                $this->addres['DELIVERY']['name2']='';
                                $this->addres['DELIVERY']['customerAddressIdentifier']='';
                                $this->addres['DELIVERY']['email']='';
                                $this->addres['DELIVERY']['fax']='';
                                $this->addres['DELIVERY']['industry']="AUTOMOTIVE";
                                $this->addres['DELIVERY']['locationType']="PHYSICAL";
                                $this->addres['DELIVERY']['mobilePhone']='';
                                $this->addres['DELIVERY']['personType']="COMPANY"; // COMPANY, PERSON
                                $this->addres['DELIVERY']['phone']='';
                                $this->addres['DELIVERY']['poBox']="POBOX";
                                $this->addres['DELIVERY']['postalCode']='';
                                $this->addres['DELIVERY']['stateCode']='';
                                $this->addres['DELIVERY']['stateName']='';
                                $this->addres['DELIVERY']['preferredLanguage']='';
                                $this->addres['DELIVERY']['schenkerAddressId']='';
                                $this->addres['DELIVERY']['street']='';
                                $this->addres['DELIVERY']['street2']='';
                                $this->addres['DELIVERY']['city']='';
                                $this->addres['DELIVERY']['countryCode']='';
                                $this->addres['DELIVERY']['type']="DELIVERY";                    
                                $this->DELIVERYDataSet = false;

                    }//if else neutralumai
                $returnRez = true;
            }else{//end if
                $returnRez = false;
            }

        }else{//end if $addressData
                $Error['message'] = "Nėra jokių duomenų apie adresą";
                $Error['code'] = "TAD-1001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
                $returnRez = false;
        }

        //var_dump($this->errorArray);
        //priskiriam adresa


        return $returnRez; //boolen
    }//end function






    /* apdirbam ir issisaugom pdf lipdukus */
    private function base64_to_jpeg($base64_string, $output_file) {
        if($base64_string AND $output_file){
            // open the output file for writing

            //$output_file = "SL_".$siuntaNr.".pdf";
            $output_path = $root_path."../../../uploads/tvsLabel/";
            $output_path_file = $output_path.$output_file;

            $ifp = fopen( $output_path_file, 'wb' ); 

            // split the string on commas
            // $data[ 0 ] == "data:image/png;base64"
            // $data[ 1 ] == <actual base64 string>
            $data = explode( ',', $base64_string );

            // we could add validation here with ensuring count( $data ) > 1
            fwrite( $ifp, base64_decode( $base64_string ) );

            // clean up the file resource
            fclose( $ifp ); 


                //tikrinti ar susikure failas
                if(file_exists ( $output_path.$output_file ) ){
                    $arOK = "OK";
                    //irasom i DB
                    
                    /*                    
                    $saveDataRez = $this->tvsMod->saveLabelFileToDB($packNr, $output_file);
                    if($saveDataRez!='OK'){
                        $arOK = "NOTOK";
                        $Error['message'] = "Nepavyko išsaugoti lipduko PDF .";
                        $Error['code'] = "LA-2003"; //
                        $Error['group'] = "LA";
                        $this->addError ($Error);
                        $output_file="";
                    }
                    */

                }else{
                    $arOK = "NOTOK";
                    $Error['message'] = "Nepavyko sukurti siuntos lipduko.";
                    $Error['code'] = "LA-2002"; //
                    $Error['group'] = "LA";
                    $this->addError ($Error);
                    $output_file = "";
                }//end else

        }else{
            $arOK = "NOTOK";
            $Error['message'] = "Trūksta duomenų sukurti PDF lipduką.";
            $Error['code'] = "LA-2003"; //
            $Error['group'] = "LA";
            $this->addError ($Error);
            $output_file = "";
        }

        return $output_file; 
    }//end function



    /* siunciam XML SCHENKERiui */
    private function connectSend(){

        //$url = $Link; //'https://eschenker-fat.dbschenker.com/webservice/bookingWebServiceV1_1?wsdl';

        //echo "<HR>SIUNCIAMAS XML :<br>";
        var_dump($this->SCHXML);


        $ch = curl_init($this->SCHKrovinioUzsakLink);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml','charset=UTF-8'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->SCHXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);


        //echo "<HR>SOURCE XML response:<br>";
        var_dump($output);

        if($output){
            if( strpos($output, '500 Internal Server Error') !== false ){
                $resperror = 3;//3- klaida response registruojant siunta
                $bookingId = '';
                $Error['message'] = "Gauta klaida 500 Internal Server Error";
                $Error['code'] = "TSX-2055"; //t-transport SX-send XML
                $Error['group'] = "SX"; //SX-send XML error
                $this->addError ($Error);

            }else{
                    $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $output);
                    $xml = new SimpleXMLElement($response);
                    $body = $xml->xpath('//soapBody')[0];
                    $RespArray = json_decode(json_encode((array)$body), TRUE); 

                    //echo "<HR>ARRAY response:<br>";
                    var_dump($RespArray);


                    //parsinam XMLA
                    if(isset($RespArray['ns2getBookingResponse']['out'])){        
                        if (is_array($RespArray['ns2getBookingResponse']['out'])){//jeigu grizo geras atsakymas, be klaidu
                                    $resperror = 1;//1- nera klaidos
                                    $respar = $RespArray['ns2getBookingResponse']['out'];
                                    $requestID = $respar['applicationArea']['requestID'];
                                    $bookingId = $respar['bookingId'];
                                    //var_dump($resp);

                                    //PDFas stringe
                                    $pdfBarcodeString = $respar['barcodeDocument'];

                                    //echo "<Br><hr>".base64_decode($pdfBarcodeString);
                                    //Saugom lipduku faila
                                    if($pdfBarcodeString){
                                        $output_file = "SCH_".$this->SiuntaData['uid'].".pdf";
                                        //issaugotas failas su visu keliu
                                        $BarcodeFile = $this->base64_to_jpeg($pdfBarcodeString, $output_file);
                                        if($this->haveErrors('LA')>0){
                                            $resperror = 2;//2- klaida saugant PDF
                                            //ivyko klaida saugant PDF
                                        }
                                    }else{
                                        $output_file = "";
                                        $BarcodeFile = "";
                                        $resperror = 2;//2- klaida saugant PDF
                                        $Error['message'] = "Nepavyko išsaugoti pdf failo";
                                        $Error['code'] = "TSX-2001"; //t-transport SX-send XML
                                        $Error['group'] = "SX"; //SX-send XML error
                                        $this->addError ($Error);
                                    }

                                    /*
                                    echo "
                                        <a href = '".$BarcodeFile."'>file</a>
                                    ";

                                    echo "<HR>";
                                    */

                        }else{

                            $resperror = 3;//3- klaida response registruojant siunta
                            $bookingId = '';
                            $Error['message'] = "Nežinoma klaida registruojant siuntą.";
                            $Error['code'] = "TSX-2051"; //t-transport SX-send XML
                            $Error['group'] = "SX"; //SX-send XML error
                            $this->addError ($Error);
                            
                        }
                    }elseif(isset($RespArray['soapFault'])){

                        $resperror = 3;//3- klaida response registruojant siunta
                        $respar = $RespArray['soapFault'];
                        $bookingId = '';

                        $errorComment = $respar['faultstring'];
                        $Error['message'] = "Klaida iš SCHENKER registruojant siuntą: ".$errorComment;
                        $Error['code'] = "TSX-2052"; //t-transport SX-send XML
                        $Error['group'] = "SX"; //SX-send XML error
                        $this->addError ($Error);

                    }else{
                        $resperror = 3;//3- klaida response registruojant siunta
                        $bookingId = '';
                        $Error['message'] = "Nežinoma klaida registruojant siuntą.";
                        $Error['code'] = "TSX-2053"; //t-transport SX-send XML
                        $Error['group'] = "SX"; //SX-send XML error
                        $this->addError ($Error);

                    }
            }//end else internal error
        }else{
                $resperror = 3;//3- klaida response registruojant siunta
                $bookingId = '';
                $Error['message'] = "Negautas atsakymas iš SCHENKER.";
                $Error['code'] = "TSX-2054"; //t-transport SX-send XML
                $Error['group'] = "SX"; //SX-send XML error
                $this->addError ($Error);

        }

        $rez['RESPONSE_XML'] = $output;
        $rez['RESPONSE_ARRAY'] = $respar;
        $rez['RESPONSE_REQUEST_ID'] = $requestID;
        $rez['RESPONSE_BOOKING_ID'] = $bookingId;
        $rez['RESPONSE_ERROR'] = $resperror;
        $rez['RESPONSE_PDF'] = $BarcodeFile;// be kelio
        return $rez;
    }//end function




    /* pasiruosimas XML siuntimui, siuntimas ir rezultatu apdorojimas */
    public function sendXML ($param=array()){

        //FORMUOJAM XML
        //$KR_rez = $this->SCHXML_created;

        //SIUNCIAM XML
        //$pavyko = false;//pradine reiksme
        if ($this->SCHXML_created){
            if($this->AccessKey){
                //echo "<br>----444---";
                if($this->SCHKrovinioUzsakLink){
                    //bandom registruoti
                    $XML_Rez = $this->connectSend();
                    

                    $this->XML_RESPONSE_XML = $XML_Rez['RESPONSE_XML'];
                    $this->RESPONSE_ARRAY = $XML_Rez['RESPONSE_ARRAY'];
                    $this->RESPONSE_REQUEST_ID = $rez['RESPONSE_REQUEST_ID'];
                    $this->RESPONSE_BOOKING_ID = $XML_Rez['RESPONSE_BOOKING_ID'];
                    $this->RESPONSE_ERROR = $XML_Rez['RESPONSE_ERROR'];//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                    $this->RESPONSE_PDF = $XML_Rez['RESPONSE_PDF'];//jeigu yra failo pavadinimas ir RESPONSE_ERROR=1 tai OK

                    if($this->RESPONSE_ERROR==1){//ar atejo geras XML atsakymas
                        $this->XML_RESPONSE_TIME = date("Y-m-d H:i:s");
                        $this->XML_result_send_userUID = SESSION::getUserID();
                        $this->XML_result_send_user = SESSION::getUserName();

                        //Irasom i DB
                        /*
                        echo "<br>
                            Booking ID: ".$this->RESPONSE_BOOKING_ID."<br>
                            <a href='../../uploads/tvsLabel/".$this->RESPONSE_PDF."'>print PDF</a>

                            <br>
                        ";
                        */

                    }else{
                        //Klaidos jau apdorotos auksciau funkcijoje
                    }
                      

                }else{//end if
                    $Error['message'] = "Bloga užklausos nuoroda";
                    $Error['code'] = "TSX-2080"; 
                    $Error['group'] = "SX"; 
                    $this->addError ($Error);
                    echo $Error['message'];
                }//end else

            }else{//end if
                $Error['message'] = "Nėra prisijungimo duomenų";
                $Error['code'] = "TSX-2081"; 
                $Error['group'] = "SX"; 
                $this->addError ($Error);
                echo $Error['message'];
            }//end else


        }else{//end if
            $Error['message'] = "Neparuoštas XML krovinio registracijai ";
            $Error['code'] = "TSX-2082";
            $Error['group'] = "SX";
            $this->addError ($Error);

            echo $Error['message'];
        }//end else

        /*
        echo "<HR>RESPONSE<hr>";
        var_dump($this->RESPONSE_ARRAY);
        var_dump($this->RESPONSE_BOOKING_ID);
        var_dump($this->RESPONSE_ERROR);
        var_dump($this->RESPONSE_PDF);
        */
        if($this->RESPONSE_ERROR ==1){
            //echo "<a href='uploads/tvsLabel/".$this->RESPONSE_PDF."'>print PDF</a>";
        }else{
            //echo "<br> NO PDF";
        }

        return true;
    }//end function
























    public function fillByPackingSlipNr___(){

        $SiuntaUID = 46;
        $rez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);

        
    }










    public function tvsVarDump (){

        echo "<HR>";
        var_dump($this->addres);
        echo "<HR>";
        var_dump($this->tvs);
        echo "<HR>";

    }//end function

    
   
}//end class
