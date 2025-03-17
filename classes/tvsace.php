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
 * Class TVS UPS

    * TEST SO 308910
 *
 * @author Arnoldas Ramonas
 */
class tvsace  extends tvsclass{
    //private static $useDebug = true;

    public $mode = 'notSet'; //test/live/notSet, veliau ateis is TVSconfig.php TVS_CONFIG::SCHENKER_MODE;


    //private $AccessKeyDemo = '550e7680-c908-40ec-878f-8fd9771a73e6';
    //private $AccessKeyLive = '3339e71f-b4b8-4bed-930f-485bb95248cd';
    //private $AccessKey = '';

    /* DEMO acountas */
    /* prisijungimas prie demo sistemos stebejimui
        https://eschenker-fat.dbschenker.com/nges-portal/secured/#!/booking/my-bookings

    */

    private $ACEKrovinioUzsakLinkLive = "http://portal.ace.ee:9092/api/v2/acerest/_table/order?fields=*"; //linkas toks pat tiek live tiek demo
    private $ACEKrovinioUzsakLinkDemo = "http://portal.ace.ee:9092/api/v2/acerest/_table/order?fields=*"; //linkas toks pat tiek live tiek demo
    //private $ACEKrovinioUzsakLink = "http://portal.ace.ee:9092/api/v2/acerest/_table/order?fields=*";
    private $ACEKrovinioUzsakRezimLive = "0";// LIVE rezimas
    private $ACEKrovinioUzsakRezimDemo = "1";// TEST rezimas


    private $UPSKurjerioIskvietimasLive =  "https://onlinetools.ups.com/ship/v1707/pickups"; //(LIVE)
    private $UPSKurjerioIskvietimasDemo =  "https://wwwcie.ups.com/ship/v1707/pickups"; //(TEST)
    private $UPSKurjerioIskvietimas = "";
    
/*
    private $header_data[]="Content-Type:application/json";
    private $header_data[]="Accept:application/json";
    private $header_data[]="Username:Aurika";
    private $header_data[]="Password:Siuntos0323";
    private $header_data[]="transId:1234567";
    private $header_data[]="Accept:application/json";
    private $header_data[]="AccessLicenseNumber:DD83A0C55C662D3D";
*/
    private $method='POST';

    /* siuntejo duomenys */
    private $SenderName = "Aurika, UAB";
    private $SenderCompanyCode = "132878726";
    private $SenderCountryCode = "LT";
    private $SenderCity = "Kaunas";


    private $SenderAddressIDKEG = "AURIKAUAB";
    private $SenderAddressIDKPG = "AURIKAUAB1";
    private $SenderAddressIDETK = "AURIKAUAB1";
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
    public $UPSXML = '';
    public $UPSXML_created = false;
    public $UPSXML_result = "";

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

        $this->mode = TVS_CONFIG::ACE_MODE;

        //echo "---SCHENKER---<Br>";

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();


        if($this->mode=='live'){
            
            $this->ACEKrovinioUzsakLink = $this->ACEKrovinioUzsakLinkLive;
            $this->ACEKrovinioUzsakRezim = $this->ACEKrovinioUzsakRezimLive;

        }elseif($this->mode=='test'){

            $this->ACEKrovinioUzsakLink = $this->ACEKrovinioUzsakLinkDemo;
            $this->ACEKrovinioUzsakRezim = $this->ACEKrovinioUzsakRezimDemo;

        }else{
            $this->mode = 'notSet';

            $this->UPSKrovinioUzsakLink = '';
        }


        //var_dump($this->AccessKey);
        //echo "<HR>";

       return $returnData;

    }//end function 







    function setSiuntaData($SiuntaUID, $sParam) {//siuntos UID DB ir sParam - duomenys is WEB formos
        

        $this->SiuntaUID = $SiuntaUID;
        $this->sParam = $sParam;


        //keiciam siuntejo Persona
        $this->SenderContactPerson = $this->sParam['det_IsvAtsakingas'];
        $this->SenderContactTel = $this->sParam['det_IsvAtsakingasTel'];
        $this->SenderContactMail = $this->sParam['det_IsvAtsakingasEmail'];


        $this->Sandelys = 'NaN';
        if($this->sParam['sACE_SandelysKEGKPG']=='KEG'){
            $this->sandelys = 'KEG';
        }else if($this->sParam['sACE_SandelysKEGKPG']=='KPG'){
            $this->sandelys = 'KPG';
        }else if($this->sParam['sACE_SandelysKEGKPG']=='ETK' OR $this->sParam['sACE_SandelysKEGKPG']=='ETK1'){
            $this->sandelys = 'ETK';
        }else{
            $Error['message'] = 'Nenustatyta iš kurio sandėlio bus siunta';
            $Error['code'] = "TAD-U2011"; //t-transport AD-address data
            $Error['group'] = "AD"; //AD - address data error
            $this->addError ($Error);
        }



        //pasiimam duomenys is TVS_Siuntos, TVS_Pack, TVS_keys lenteliu
        //$SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID, $this->Sandelys);
        if($this->sandelys == 'ETK'){
            $SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTranspETK($SiuntaUID, $this->Sandelys);
        }else{
            $SiuntaDataRez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID, $this->Sandelys);    
        }


        echo "<hr>*********SiuntaDataRez*********<br>";
        var_dump($SiuntaDataRez);

        if($SiuntaDataRez['OK']=='OK' AND ($this->mode == 'live' OR $this->mode == 'test')){

            $this->SiuntaData = $SiuntaDataRez['Duom'];
            unset($SiuntaDataRez);

            //!!!!!!!!!!!----------- TIK TESTAVIMUI, paskui reikia istrinti
            //$this->SiuntaData['NeutralumasKod'] = ''; 

            if($this->SiuntaData['NeutralumasKod'] == ''){
                    
                    
                    //var_dump($this->SiuntaData);

                    //isvalom SHIPPER duomenis, nes Clear
                    $this->setSenderAurikaAdresas  ('Clear');
                    /*
                            $this->addres['SHIPPER']['contactPerson']['email']='';
                            $this->addres['SHIPPER']['name1']='';
                            $this->addres['SHIPPER']['name2']="";
                            $this->addres['SHIPPER']['customerAddressIdentifier']='';
                            .....
                    */            

                    //isvalom PICKUP duomenis, nes kaip sandeli paduodam clear
                    $this->setPICKUPAurikaAdresa  ('Clear');



                    //nustato DEFAULT AURIKOS KAIP SIUNTEJO ADRESA
                    
                    if($this->SiuntaData['NeutralumasKod']==''){//jeigu nera LS ar 2LS
                        if($this->SiuntaData['Sandelys']=='KEG' OR $this->SiuntaData['Sandelys']=='KPG' OR $this->SiuntaData['Sandelys']=='ETK' OR $this->SiuntaData['Sandelys']=='ETK1'){
                            //$this->setSenderAurikaAdresas ($this->sParam['sUPS_SandelysKEGKPG']);
                            $this->setSenderAurikaAdresas ($this->SiuntaData['Sandelys']);
                        }
                    }
                    

                    ECHO "SENDER SET:<br>";
                    var_dump($this->SHIPPERDataSet);
                    echo "<hr>";

                    //paruosiam duomenys
                    if(!$this->SiuntaData['GavejasID']){
                        //jeigu nera gavejo, tai nurodom, kad gavejas yra klientas
                        $this->SiuntaData['GavName']=$this->SiuntaData['ClientName'];
                    }

                    /* CIAS negali buti neutralumo */
                    //$this->tvs['neutralShipping']=0;
                    /*
                    if($this->SiuntaData['NeutralumasKod']=='LS' OR $this->SiuntaData['NeutralumasKod']=='2LS'){
                        $this->tvs['neutralShipping']=1;
                    }else{
                        $this->tvs['neutralShipping']=0;
                    }
                    */

                    //pildom adresus
                    //$addrRez = $this->setConsigneeAddress ($this->SiuntaData);
                    $addrRez = $this->setAddresses ($this->SiuntaData);

                    //ECHO "Consignee SET:<br>";
                    //var_dump($this->addres);
                    //echo "<hr>";


                    $this->RequestID = date("YmdHis").substr(md5(mt_rand()), 0, 4); //date + random string

                    $this->tvs['ServiceCode']=$sParam['sACE_ServiceID'];

                    /*
                    switch ($this->sParam['sSCH_PristatytiIki']) {
                        case '10:00':
                            $this->tvs['productOption']='55';
                            break;
                        case '13:00':
                            $this->tvs['productOption']='56';
                            break;
                        default:
                            $this->tvs['productOption']='';
                            break;
                    }
                    */


                    //$sParam['sSCH_4ranku']
                    //$sParam['sSCH_liftas']

                    //TODO, reikia aukciau klaidu tikrinimo, bet dabar sakom, kad viskas OK
                    $this->DataIsSet = true;//veliau gali pasikeisti priklausomai nuo kitu setinimu.


                    //Pildom pakuociu duomenys
                    $this->setPackBasic ($this->SiuntaData);

            }else{

                /* jau galima UPS su LS ir 2LS pagal Gintares (transportas@aurika.lt) laiska 2020-10-02 10:49
                $Error['message'] = "Su UPS negalima siųsti LS ir 2LS siunta.";
                $Error['code'] = "TDS-U2081"; //
                $Error['group'] = "DS"; //DS - data save error
                $this->addError ($Error);
                $this->DataIsSet = false;
                */


                    //var_dump($this->SiuntaData);

                    //isvalom SHIPPER duomenis, nes Clear
                    $this->setSenderAurikaAdresas  ('Clear');

                    //isvalom PICKUP duomenis, nes kaip sandeli paduodam clear
                    $this->setPICKUPAurikaAdresa  ('Clear');


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

                    ECHO "Consignee SET:<br>";
                    var_dump($this->addres);
                    echo "<hr>";


                    $this->RequestID = date("YmdHis").substr(md5(mt_rand()), 0, 4); //date + random string

                    $this->tvs['ServiceCode']=$sParam['sACE_ServiceID'];

                    /*
                    switch ($this->sParam['sSCH_PristatytiIki']) {
                        case '10:00':
                            $this->tvs['productOption']='55';
                            break;
                        case '13:00':
                            $this->tvs['productOption']='56';
                            break;
                        default:
                            $this->tvs['productOption']='';
                            break;
                    }
                    */


                    //$sParam['sSCH_4ranku']
                    //$sParam['sSCH_liftas']

                    //TODO, reikia aukciau klaidu tikrinimo, bet dabar sakom, kad viskas OK
                    $this->DataIsSet = true;//veliau gali pasikeisti priklausomai nuo kitu setinimu.


                    //Pildom pakuociu duomenys
                    $this->setPackBasic ($this->SiuntaData);



            }
        }else{//end if
                $Error['message'] = "Nėra išsaugota duomenų apie siuntą.";
                $Error['code'] = "TDS-U2001"; //
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
        return $this->DataIsSet;

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

                            for ($j=0; $j < $pKiekis; $j++) { //begam per vienodas dezutes/paletes ir surasom kaip atskirtus vienetus, nes bus kiekvienai atskiras lipdukas ir tracking numeris
                                # code...
                            
                                    $PaksArray['PACK'][$i]['Kiekis'] = 1; //$pKiekis;//siuo atveju 1, nes kiekviena pakuote/palete imam kaip atskira vieneta o ne vienodu dezuciu kieki
                                    $PaksArray['PACK'][$i]['Plotis'] = round($pPlotis,2);
                                    $PaksArray['PACK'][$i]['Ilgis'] = round($pIlgis,2);
                                    $PaksArray['PACK'][$i]['Aukstis'] = round($pAukstis,2);
                                    $PaksArray['PACK'][$i]['Turis'] = round($pPlotis/100*$pIlgis/100*$pAukstis/100,2);// mato vnt 
                                    $PaksArray['PACK'][$i]['Svoris'] = round($pPakioSvoris,2);
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
                                    $PaksArray['SUMPlotis'] += $PaksArray['PACK'][$i]['Plotis'];//*$PaksArray['PACK'][$i]['Kiekis'];
                                    $PaksArray['SUMIlgis'] += $PaksArray['PACK'][$i]['Ilgis'];//*$PaksArray['PACK'][$i]['Kiekis'];
                                    $PaksArray['SUMAukstis'] += $PaksArray['PACK'][$i]['Aukstis'];//*$PaksArray['PACK'][$i]['Kiekis'];
                                    $PaksArray['SUMTuris'] += $PaksArray['PACK'][$i]['Turis'];//*$PaksArray['PACK'][$i]['Kiekis'];
                                    $PaksArray['SUMSvoris'] += $PaksArray['PACK'][$i]['GrossSvoris'];//*$PaksArray['PACK'][$i]['Kiekis'];
                                    if($pTipas=='EP' OR $pTipas=='MP'){//jeigu europalete arba maza palete tai skaiciuojam
                                        $PaksArray['SUMPalets'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }
                                    if($pTipas=='EP'){//jeigu europalete palete tai skaiciuojam
                                        $PaksArray['SUM_EUPalets'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }
                                    if($pTipas=='MP'){//jeigu maza palete tai skaiciuojam
                                        $PaksArray['SUM_NonEUPalets'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }
                                    if($pTipas=='DD'){//jeigu dezute
                                        $PaksArray['SUM_Boxes'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }
                                    if($pTipas=='PK'){//jeigu dezute
                                        $PaksArray['SUM_Paks'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }
                                    if($pTipas=='RD'){//jeigu dezute
                                        $PaksArray['SUM_Rolls'] += $PaksArray['PACK'][$i]['Kiekis']; //paleciu kiekis
                                    }

                                    $i++;
                                //echo "<br>***01***";
                            }//end for
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








    public function generateACEXML ($param=array()){

        $pavyko = true;

        //Generuojam requestID
        $micro_Time=microtime(true)*10000;
        $reqID = 'SC'.$micro_Time;


        /*
        if(!$this->addres['CONSIGNEE']['phone']){
            $this->addres['CONSIGNEE']['phone'] = '-';
        }
        */
        //ekiciam telefona visada i "-" ... Editos laiskas 20200930 09:52
        //$this->addres['CONSIGNEE']['phone'] = '-';

//var_dump($this->addres);


switch ($this->sParam['sACE_ServiceID']) {
            case 'aceSpeed':
                $thisProduct = 7;
                break;
            case 'aceSpeed10':
                $thisProduct = 2;
                break;
            case 'aceSpeed12':
                $thisProduct = 1;
                break;
            
            default:
                $thisProduct = 7;
                break;
        }        

$JSON_ace = '

[{
    "OrderType": "R",
    "CustomerOrderRef": "'.$this->sParam['uid'].'",
    "Product": "'.$thisProduct.'",
    "OrderedBy": "Arnoldas Ramonas",
    "OrderedEmail": "arnas@aurika.lt",
    "OrderedPhone": "+370 652 25224",
    "OrderedMobile": "+370 652 25224",


    "TermOfDelivery": "DDP",

    ';

if($this->addres['SHIPPER']['mobilePhone']){
    $phoneSHIPPER = $this->addres['SHIPPER']['mobilePhone'];
}else if ($this->addres['SHIPPER']['phone']){
    $phoneSHIPPER = $this->addres['SHIPPER']['phone'];
}else{
    $phoneSHIPPER = '-';
}

if($this->addres['SHIPPER']['contactPerson']['mail']){
    $emailSHIPPER = $this->addres['SHIPPER']['contactPerson']['mail'];
}else if ($this->addres['SHIPPER']['email']){
    $emailSHIPPER = $this->addres['SHIPPER']['email'];
}else{
    $emailSHIPPER = '-';
}


$JSON_ace .= '
    
    "ShipperName": "'.$this->addres['SHIPPER']['name1'].'",
    "ShipperAddress": "'.$this->addres['SHIPPER']['street'].'",
    "ShipperCity": "'.$this->addres['SHIPPER']['city'].'",
    "ShipperPostCode": "'.$this->addres['SHIPPER']['postalCode'].'",
    "ShipperCountry": "'.$this->addres['SHIPPER']['countryCode'].'",
    "ShipperContact": "'.$this->addres['SHIPPER']['contactPerson']['name'].'",
    "ShipperPhone": "'.$phoneSHIPPER.'",
    "ShipperEmail": "'.$emailSHIPPER.'",



    "PickupName": "Gatavos produkcijos sandėlys",
    "PickupAddress": "'.$this->addres['PICKUP']['street'].'",
    "PickupCity": "'.$this->addres['PICKUP']['city'].'",
    "PickupPostCode": "'.$this->addres['PICKUP']['postalCode'].'",
    "PickupCountry": "'.$this->addres['PICKUP']['countryCode'].'",
    "PickupContact": "'.$this->addres['PICKUP']['contactPerson']['name'].'",
    "PickupPhone": "'.$this->addres['PICKUP']['phone'].'",
    "PickupEmail": "'.$this->addres['PICKUP']['email'].'",

    "LoadTimeFrom": "",
    "LoadTimeTo": "",
    "LoadDate": "'.$this->sParam['sACE_IsvData'].'",
    "LoadDateType": "2",
    "LoadNotes": "",
    "LoadRef": "",
';
if($this->sParam['sACE_Lifts']=='lift'){
    $JSON_ace .= '
        "TailLiftPU": "1",
    ';
}else{
    $JSON_ace .= '
        "TailLiftPU": "0",
    ';
}

if($this->addres['CONSIGNEE']['mobilePhone']){
    $phone = $this->addres['CONSIGNEE']['mobilePhone'];
}else if ($this->addres['CONSIGNEE']['phone']){
    $phone = $this->addres['CONSIGNEE']['phone'];
}else{
    $phone = '+';
}

if($this->addres['CONSIGNEE']['contactPerson']['mail']){
    $email = $this->addres['CONSIGNEE']['contactPerson']['mail'];
}else if ($this->addres['CONSIGNEE']['email']){
    $email = $this->addres['CONSIGNEE']['email'];
}else{
    $email = '-';
}

var_dump ($this->addres['CONSIGNEE']);

$JSON_ace .= '
    

    "ConsigneeName": "'.$this->addres['CONSIGNEE']['name1'].'",
    "ConsigneeAddress": "'.$this->addres['CONSIGNEE']['street'].'",
    "ConsigneeCity": "'.$this->addres['CONSIGNEE']['city'].'",
    "ConsigneePostCode": "'.$this->addres['CONSIGNEE']['postalCode'].'",
    "ConsigneeCountry": "'.$this->addres['CONSIGNEE']['countryCode'].'",
    "ConsigneeContact": "'.$this->addres['CONSIGNEE']['contactPerson']['name'].'",
    "ConsigneePhone": "'.$phone.'",
    "ConsigneeEmail": "'.$email.'",

    "DeliveryName": "",
    "DeliveryAddress": "",
    "DeliveryCity": "",
    "DeliveryIndex": "",
    "DeliveryCountry": "",
    "DeliveryContact": "",
    "DeliveryPhone": "",
    "DeliveryEmail": "",

    "UnLoadTimeFrom": "",
    "UnLoadTimeTo": "",
    "UnLoadDate": "",
    "UnLoadDateType": "2",
    "UnloadNotes": "",
    "UnLoadRef": "",

';
if($this->sParam['sACE_Lifts']=='lift'){
    $JSON_ace .= '
        "TailLiftDE": "1",
    ';
}else{
    $JSON_ace .= '
        "TailLiftDE": "0",
    ';
}
$JSON_ace .= '

    "InsuranceValue": 0,
    "InsuranceCurrency": "",
    "Test": '.$this->ACEKrovinioUzsakRezim.',

';


if($this->packs['PacksArray']){
    $JSON_ace .= '
        "orderlines_by_OrderID":[
    ';

    $pakNo = 1;

    var_dump($this->packs['PacksArray']);

    foreach ($this->packs['PacksArray'] as $key => $pakis) {
        
        if ($pakNo > 1){
            $JSON_ace .= ', ';
        }
        //$tmpTuris = $pakis['Turis'] * $pakis['Kiekis'];
        //$tmpSvoris = $pakis['GrossSvoris'] * $pakis['Kiekis'];

        if($pakis['Kiekis']>0){
            //for ($i=1; $i <= $pakis['Kiekis']; $i++) {  NEREIKIA, nes auksciau surasem kiekviena dezute/palete kaip atskira o ne grupe vienodu

                $JSON_ace .= '
                        {
                            
                ';
                if($pakis['Tipas']=='EP' OR $pakis['Tipas']=='MP' ){// jeigu paletes tai rasom prie UNIT
                    $JSON_ace .= '
                            "Unit": '.$pakis['Kiekis'].',
                            "Pcs": "",
                    ';
                }else{ //Jeigu dezutes tai rasom prie PIC
                    $JSON_ace .= '
                            "Unit": "",
                            "Pcs": '.$pakis['Kiekis'].',
                    ';
                }

                $SumThisLineSvoris = $pakis['Svoris'] * $pakis['Kiekis'];
                $SumThisLineTuris = $pakis['Turis'] * $pakis['Ilgis'] * $pakis['Aukstis'];
                $JSON_ace .= '
                            "UnitW": '.$pakis['Svoris'].',
                            "GrW": '.$SumThisLineSvoris.',
                            "Notes": "",
                            "LDM": 0,
                            "Vol": '.$pakis['Turis'].',
                            "L": '.$pakis['Ilgis'].',
                            "W": '.$pakis['Plotis'].',
                            "H": '.$pakis['Aukstis'].',
                            "Commodity": ""
                        }     
                ';   

                $pakNo++;// reikalingas del kablelio padejimo reikalingoje vietoje
            //}//end for
        }//end if

    }//end foreach
    $JSON_ace .= '
            ]
            ,
        "ordersscc_by_OrderID": [{
        "SSCC": "137977"
    }]
}]

    ';

}//end if








        $this->ACEXML = $JSON_ace;

        if($pavyko===true){
            $this->ACEXML_created = true;
        }else{
            $this->ACEXML_created = false;
        }

        var_dump($this->ACEXML);


        return $pavyko;
    }//end function









    private function setSenderAurikaAdresas ($gamyba){

        switch ($gamyba) {
            case 'KEG':
                    //address
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderName;
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
                    $this->addres['SHIPPER']['street']=$this->SenderAddressKEG;
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                break;

            case 'KPG':
                    //address
                    /* Cia visais atvejais KEG, nes saskaitos eina ten */
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderName;
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
                    $this->addres['SHIPPER']['street']=$this->SenderAddressKEG;
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                 break;
            case 'ETK1':
            case 'ETK':
                    //address
                    /* Cia visais atvejais KEG, nes saskaitos eina ten */
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['name1']=$this->SenderName;
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
                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKEG;
                    $this->addres['SHIPPER']['stateCode']="";
                    $this->addres['SHIPPER']['stateName']="";
                    $this->addres['SHIPPER']['preferredLanguage']="LT";
                    $this->addres['SHIPPER']['schenkerAddressId']=$this->SenderAddressIDKEG;
                    $this->addres['SHIPPER']['street']=$this->SenderAddressKEG;
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                 break;
            
            default:
                    $this->addres['SHIPPER']['contactPerson']['name']="";
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




    /* UPSui atsaukiam sia funkcija nes LS ir 2LS atvejais cia formuojama idomiai, kombinuojant duomenis is siuntejo ir Aurikos duomenu 
    pagal Ginteres laiska (transportas@aurika.lt) 2020-10-02 10:49  punktas 5
    */
    private function setPICKUPAurikaAdresa ($gamyba){ 

        switch ($gamyba) {
            case 'KEG':
                    //address
                    $this->addres['PICKUP']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['PICKUP']['name1']=$this->SenderName;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']='';
                    $this->addres['PICKUP']['email']=$this->SenderContactMailKEG;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelKEG;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']=$this->SenderContactTelKEG;
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKEG;
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']="";
                    $this->addres['PICKUP']['street']=$this->SenderAddressKEG;
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                break;

            case 'KPG':
                    $this->addres['PICKUP']['contactPerson']['name']=$this->SenderContactPersonKPG;
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailKPG;
                    $this->addres['PICKUP']['name1']=$this->SenderName;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']="";
                    $this->addres['PICKUP']['email']=$this->SenderContactMailKPG;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelKPG;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']=$this->SenderContactTelKPG;
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKPG;
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']="";
                    $this->addres['PICKUP']['street']=$this->SenderAddressKPG;
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                 break;
            case 'ETK':
            case 'ETK1':
                    $this->addres['PICKUP']['contactPerson']['name']=$this->SenderContactPersonETK;
                    $this->addres['PICKUP']['contactPerson']['email']=$this->SenderContactMailETK;
                    $this->addres['PICKUP']['name1']=$this->SenderName;
                    $this->addres['PICKUP']['name2']="";
                    $this->addres['PICKUP']['customerAddressIdentifier']="";
                    $this->addres['PICKUP']['email']=$this->SenderContactMailETK;
                    $this->addres['PICKUP']['fax']="";
                    $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                    $this->addres['PICKUP']['locationType']="PHYSICAL";
                    $this->addres['PICKUP']['mobilePhone']=$this->SenderContactTelETK;
                    $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                    $this->addres['PICKUP']['phone']=$this->SenderContactTelETK;
                    $this->addres['PICKUP']['poBox']="POBOX";
                    $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeETK;
                    $this->addres['PICKUP']['stateCode']="";
                    $this->addres['PICKUP']['stateName']="";
                    $this->addres['PICKUP']['preferredLanguage']="LT";
                    $this->addres['PICKUP']['schenkerAddressId']="";
                    $this->addres['PICKUP']['street']=$this->SenderAddressETK;
                    $this->addres['PICKUP']['street2']="";
                    $this->addres['PICKUP']['city']="Kaunas";
                    $this->addres['PICKUP']['countryCode']="LT";
                    $this->addres['PICKUP']['type']="PICKUP";                    
                    $this->PICKUPDataSet = true;
                 break;
            
            default:
                    $this->addres['PICKUP']['contactPerson']['name']='';
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



    public function setAddresses ($SiuntaData){

        //var_dump($SiuntaData);

        $this->CONSIGNEEDataSet = false;
        $this->SHIPPERDataSet = false;
        $this->DELIVERYDataSet = false;
        $this->PICKUPDataSet = false;

        //tikrinam duomenis
        if ($SiuntaData){

            /* sanitaizinam pavadinimus */
            /* senas kodas */

            /*UPSui nekeiciam

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

            */
            

            /* naujas kodas */
            //gavejas
            //$SiuntaData['Gavejas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Gavejas']);

            /* UPS nedarom keitimo
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
            */


            //Tikrinam klaidas
            if (!$SiuntaData['Gavejas']){
                $Error['message'] = "Nenustatytas gavėjo pavadinimas";
                $Error['code'] = "TAD-U2001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Delivery_street']){
                $Error['message'] = "Nenustatytas gavėjo adresas";
                $Error['code'] = "TAD-U2003"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Miestas']){
                $Error['message'] = "Nenustatytas gavėjo miestas";
                $Error['code'] = "TAD-U2031"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['SaliesKodas']){
                $Error['message'] = "Nenustatytas gavėjo šalies kodas";
                $Error['code'] = "TAD-U2005"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['PostKodas']){
                $Error['message'] = "Nenustatytas gavėjo pašto kodas";
                $Error['code'] = "TAD-U2002"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            /*
            if (!$SiuntaData['PostKodas']){
                $Error['message'] = "Nenustatytas gavėjo pašto kodas";
                $Error['code'] = "TAD-U2006"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            */
            /*
            if (!$SiuntaData['Det_Delivery_phone'] AND !$SiuntaData['Det_Delivery_contact_phone']){
                $Error['message'] = "Nenurodytas gavžėjo telefonas";
                $Error['code'] = "TAD-U2007"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            */
            if($SiuntaData['NeutralumasKod']=='LS' OR $SiuntaData['NeutralumasKod']=='2LS'){//LS neutralumas ARBA 2LS dvigubas neutralumas

                /*
                    $Error['message'] = "UPS siunta su neutralumu (LS ir 2LS) negalima!";
                    $Error['code'] = "TAD-U2333"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                */

                /*
                if (!$SiuntaData['SiuntejoName']){
                    $Error['message'] = "Nenustatytas siuntėjo pavadinimas";
                    $Error['code'] = "TAD-U2201"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoStreet']){
                    $Error['message'] = "Nenustatyta siuntėjo adresas";
                    $Error['code'] = "TAD-U2203"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoMiestas']){
                    $Error['message'] = "Nenustatytas siuntėjo miestas";
                    $Error['code'] = "TAD-U2231"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoSaliesKodas']){
                    $Error['message'] = "Nenustatytas siuntėjo šalies kodas";
                    $Error['code'] = "TAD-U2205"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }
                if (!$SiuntaData['SiuntejoPostKodas']){
                    $Error['message'] = "Nenustatytas siuntėjo pašto kodas";
                    $Error['code'] = "TAD-U2202"; //t-transport AD-address data
                    $Error['group'] = "AD"; //AD - address data error
                    $this->addError ($Error);
                }

                if($SiuntaData['NeutralumasKod']=='2LS'){//2LS dvigubas neutralumas

                    if (!$SiuntaData['DilerioName']){
                        $Error['message'] = "Nenustatytas tarpininko (dilerio) pavadinimas";
                        $Error['code'] = "TAD-U2301"; //t-transport AD-address data
                        $Error['group'] = "AD"; //AD - address data error
                        $this->addError ($Error);
                    }

                }//end if NEUTRALUMAS 2LS
                */

            }//end if neutralumas LS OR 2LS


            /* ************************ nustatinejam adresus (new) **************************** */

            $klaiduSk = $this->haveErrors ('AD');
            //jeigu neturim klaidu su adresu tai setinam adresus
            if($klaiduSk==0){

var_dump($SiuntaData['NeutralumasKod']);

//TEST SO 312447 LS
                    //ivedam duomenys
                    if($SiuntaData['NeutralumasKod']=='2LS'){//2LS dvigubas neutralumas
                        /* 2LS GAVEJAS */
                        /* 2LS SIUNTEJAS */
                        /* 2LS DILERIS */
                        /* 2LS PICKUP */
                                if($SiuntaData['Delivery_contact']){
                                    $Delivery_contact_name = $SiuntaData['Delivery_contact'];
                                }else{
                                    $Delivery_contact_name = '-'; //$SiuntaData['Delivery_contact_email'];
                                }
                                $this->addres['CONSIGNEE']['contactPerson']['name']=trim ($Delivery_contact_name);
                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim (substr($SiuntaData['Gavejas'], 0, 35));
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
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


                                if($SiuntaData['Siuntejo_contact']){
                                    $Siuntejo_contactt_name = $SiuntaData['Siuntejo_contact'];
                                }else{
                                    $Siuntejo_contact_name = '-'; //$SiuntaData['Delivery_contact_email'];
                                }
                                $this->addres['SHIPPER']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                $this->addres['SHIPPER']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                $this->addres['SHIPPER']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
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
                                /*
                                if($SiuntaData['Sandelys']=='KEG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKEG;
                                }elseif($SiuntaData['Sandelys']=='KPG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKPG;
                                }else{
                                    $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                }
                                */
                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                $this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                //$this->addres['SHIPPER']['countryCode']='LT';// visada LT nes turi sutapti su "ShipperNumber" kodo priskirto saliai (kitaip ismeta klaida: 120120 )
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                $this->setPICKUPAurikaAdresa ($SiuntaData['Sandelys']);

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


                    }elseif($SiuntaData['NeutralumasKod']=='LS'){//LS-neutralumas
                        /* LS GAVEJAS */
                        /* LS SIUNTEJAS */
                        /* LS PICKUP */

                                if($SiuntaData['Delivery_contact']){
                                    $Delivery_contact_name = $SiuntaData['Delivery_contact'];
                                }else{
                                    $Delivery_contact_name = '-'; //$SiuntaData['Delivery_contact_email'];
                                }
                                $this->addres['CONSIGNEE']['contactPerson']['name']=trim ($Delivery_contact_name);
                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim (substr($SiuntaData['Gavejas'], 0, 35));//trim ($SiuntaData['Gavejas']);
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
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


                                if($SiuntaData['Siuntejo_contact']){
                                    $Siuntejo_contactt_name = $SiuntaData['Siuntejo_contact'];
                                }else{
                                    $Siuntejo_contact_name = '-'; //$SiuntaData['Delivery_contact_email'];
                                }
                                $this->addres['SHIPPER']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                $this->addres['SHIPPER']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                $this->addres['SHIPPER']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
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
                                /*
                                if($SiuntaData['Sandelys']=='KEG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKEG;
                                }elseif($SiuntaData['Sandelys']=='KPG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKPG;
                                }else{
                                    $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                }
                                */

                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                $this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                //$this->addres['SHIPPER']['countryCode']='LT';// visada LT nes turi sutapti su "ShipperNumber" kodo priskirto saliai (kitaip ismeta klaida: 120120 )
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                $this->setPICKUPAurikaAdresa ($SiuntaData['Sandelys']);


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

                                if($SiuntaData['Delivery_contact']){
                                    $Delivery_contact_name = $SiuntaData['Delivery_contact'];
                                }else{
                                    $Delivery_contact_name = '-'; //$SiuntaData['Delivery_contact_email'];
                                }
                                $this->addres['CONSIGNEE']['contactPerson']['name']=trim ($Delivery_contact_name);
                                $this->addres['CONSIGNEE']['contactPerson']['email']=trim ($SiuntaData['Delivery_contact_email']);
                                $this->addres['CONSIGNEE']['name1']=trim (substr($SiuntaData['Gavejas'], 0, 35));//trim ($SiuntaData['Gavejas']);
                                $this->addres['CONSIGNEE']['name2']="";
                                $this->addres['CONSIGNEE']['customerAddressIdentifier']=trim ($SiuntaData['AdresasID']);
                                $this->addres['CONSIGNEE']['email']=trim ($SiuntaData['Delivery_email']);
                                $this->addres['CONSIGNEE']['fax']='';
                                $this->addres['CONSIGNEE']['industry']="AUTOMOTIVE";
                                $this->addres['CONSIGNEE']['locationType']="PHYSICAL";
                                $this->addres['CONSIGNEE']['mobilePhone']=trim ($SiuntaData['Delivery_contact_phone']);
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
                                //$this->setSenderAurikaAdresas ($SiuntaData['Sandelys']);
                                $this->setSenderAurikaAdresas ('KEG'); // visada KEG is kurio sandelio besiustumem, nes cia administracijos adresas

                                //address
                                $this->setPICKUPAurikaAdresa ($SiuntaData['Sandelys']);


                                $this->addres['DELIVERY']['contactPerson']['name']='';
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
                echo "<br><bR>KLAIDA PRISKIRIANT ADRESUS-----<br>";
                var_dump($this->errorArray);
            }

        }else{//end if $addressData
                $Error['message'] = "Nėra jokių duomenų apie adresą";
                $Error['code'] = "TAD-1001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
                $returnRez = false;
        }

        var_dump($this->addres);
        //priskiriam adresa


        return $returnRez; //boolen
    }//end function





    /* pasiruosimas XML siuntimui, siuntimas ir rezultatu apdorojimas */
    public function sendXML ($SiuntaData=array()){

//var_dump($SiuntaData);

        $method='POST';
        //$url =  "https://onlinetools.ups.com/ship/v1/shipments?additionaladdressvalidation=city";
        //$url =  "https://wwwcie.ups.com/ship/{version}/shipments";
        $url = $this->ACEKrovinioUzsakLink;

        /*
        $header_data[]="Content-Type:application/json";
        $header_data[]="Accept:application/json";
        $header_data[]="Username:Aurika";
        $header_data[]="Password:Siuntos0323";
        $header_data[]="transId:1234567";
        $header_data[]="Accept:application/json";
        $header_data[]="AccessLicenseNumber:DD83A0C55C662D3D";
        */

        $header_data[]="Content-Type: application/json;";
        //$header_data[]="Accept:application/json";
        //$header_data[]="Username:Editak";
        //$header_data[]="Password:Taikos129A";
        $header_data[]='Authorization:Basic YXVyaWthQGF1cmlrYS5sdDprYWpzZGhHREY1NDMjIw==';
        $header_data[]='X-DreamFactory-Api-Key:d45d2badff2a27e460c52a7c4a65afc5fa281123eb3a568c7546edb630d0f27c';




        echo"<br><br><br>ACEXML-JSON<hr>";
        var_dump($this->ACEXML);
        echo"<br><br>111111<br>URL: $url<br> URL2: ".$this->ACEKrovinioUzsakLink."<hr>";


        $resp = $this->CallAPI($method, $url, $header_data, $this->ACEXML);
        //echo"<br><br>222222<br><hr>";
        var_dump($resp);
        //echo"<br><br><br><hr>";
        //$response = file_get_contents('http://example.com/path/to/api/call?param1=5');
        $respArray = json_decode($resp);
        //$response = new SimpleXMLElement($response);
        var_dump($respArray);
        //echo"<br><br>333333<br><hr>";

        //ar geras responsas
        $this->RESPONSE_ERROR = 0;//0-nezinom, 1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
        $this->RESPONSE_ERROR_ARRAY = array();
        $this->RESPONSE_ERROR_TEXT = '';
        $this->XML_RESPONSE_XML =  $resp;
        $this->XML_RESPONSE_COMMENT =  '';
        $this->RESPONSE_ARRAY = $respArray;
        $this->RESPONSE_REQUEST_ID = '';
        $this->RESPONSE_PDF_ARRAY = array();

        $this->XML_RESPONSE_TIME = date("Y-m-d H:i:s");
        $this->XML_result_send_userUID = SESSION::getUserID();
        $this->XML_result_send_user = SESSION::getUserName();



        if(is_array($respArray->error)){

/* ERROR PVZ

object(stdClass)[2]
  public 'error' => 
    object(stdClass)[1]
      public 'context' => null
      public 'message' => string 'Field 'Product' must be a valid integer.' (length=40)
      public 'code' => int 400
      public 'trace' => 
        array (size=70)
          0 => string '0 /home/administrator/dreamfactory-2.1.0-4/apps/dreamfactory/htdocs/vendor/dreamfactory/df-core/src/Resources/BaseDbTableResource.php(2027):
            ....

*/
            $errorArray = $respArray->errors;
            $this->RESPONSE_ERROR = "3";//yra klaidu
            $this->RESPONSE_ERROR_ARRAY = $errorArray;
            $this->RESPONSE_ShipmentIdentificationNumber = '';
            

            $this->RESPONSE_ERROR_TEXT = "[".$errorArray->code."] ".$errorArray->message;
            $Error['message'] = "[".$errorArray->code."] ".$errorArray->message;
            $Error['code'] = "TRG-AC109"; //t-transport RG-registracijos error
            $Error['group'] = "RG"; //RG siuntos registracijos error
            $this->addError ($Error);

            
            var_dump($resperr);
        } else if(is_array($respArray->resource)){//success

            $respA = $respArray->resource[0];//pasiimam pirma elementa, nes manau, kad daugiau ju ir nebus musu atveju

/* RESPONSE PVZ:


Jeigu gaunam 'ACERef' tai reiskia, kad viskas OK (testavimo rezime gaunam uzrasa test)

object(stdClass)[15]
  public 'resource' => 
    array (size=1)
      0 => 
        object(stdClass)[13]
          public 'OrderID' => int 5030841
          public 'OrderGUID' => string '024ee05c333711ecb7df000c29e523b2' (length=32)
          public 'CustomerID' => int 0
          public 'CustomerGUID' => string 'fe3dc7115eae71ba151473d318f5352f' (length=32)
          public 'CustomerName' => null
          public 'ACERef' => string 'test' (length=4) 
          public 'CustomerOrderRef' => string '' (length=0)
          public 'CustRefNr' => null
          public 'Transport' => null
          public 'Product' => int 7
          public 'ProductName' => null
          public 'OrderedBy' => string 'Arnoldas Ramonas' (length=16)
          public 'OrderedEmail' => string 'arnas@aurika.lt' (length=15)
          public 'OrderedPhone' => string '+370 652 25224' (length=14)
          public 'OrderedMobile' => string '+370 652 25224' (length=14)
          public 'SpecialAgreement' => null
          public 'TermOfDelivery' => string 'DDP' (length=3)
          public 'ToDLocation' => null
          public 'ShipperID' => null
          public 'ShipperName' => string 'b+b Automations- und Steuerungstech' (length=35)
          public 'ShipperAddress' => string 'Eichenstrasse 38a' (length=17)
          public 'ShipperCity' => string 'Oberzent' (length=8)
          public 'ShipperIndex' => null
          public 'ShipperPostCode' => string '64760' (length=5)
          public 'ShipperCountry' => string 'DE' (length=2)
          public 'ShipperContact' => string '-' (length=1)
          public 'ShipperPhone' => string '+49606847891-0' (length=14)
          public 'ShipperEmail' => string '-' (length=1)
          public 'CoverName' => null
          public 'CoverAddress' => null
          public 'CoverCity' => null
          public 'CoverIndex' => null
          public 'CoverPostCode' => null
          public 'CoverCountry' => null
          public 'CoverContact' => null
          public 'CoverPhone' => null
          public 'CoverEmail' => null
          public 'PickupID' => null
          public 'PickupName' => string 'Gatavos produkcijos sandėlys' (length=29)
          public 'PickupAddress' => string 'Taikos pr. 129A' (length=15)
          public 'PickupCity' => string 'Kaunas' (length=6)
          public 'PickupIndex' => null
          public 'PickupPostCode' => string '51127' (length=5)
          public 'PickupCountry' => string 'LT' (length=2)
          public 'PickupContact' => string 'AURIKA shipping department' (length=26)
          public 'PickupPhone' => string '+37068802736' (length=12)
          public 'PickupEmail' => string 'transportas@aurika.lt' (length=21)
          public 'LoadTimeFrom' => string '' (length=0)
          public 'LoadTimeTo' => string '' (length=0)
          public 'LoadDate' => string '2021-10-22' (length=10)
          public 'LoadDateType' => int 2
          public 'LoadNotes' => string '' (length=0)
          public 'LoadRef' => string '' (length=0)
          public 'TailLiftPU' => int 1
          public 'ConsigneeID' => null
          public 'ConsigneeName' => string 'Imkerei Ullrich' (length=15)
          public 'ConsigneeAddress' => string 'Carl-Benz-Str. 56' (length=17)
          public 'ConsigneeCity' => string 'Neulußheim' (length=11)
          public 'ConsigneeIndex' => null
          public 'ConsigneePostCode' => string '68809' (length=5)
          public 'ConsigneeCountry' => string 'DE' (length=2)
          public 'ConsigneeContact' => string '-' (length=1)
          public 'ConsigneePhone' => string '+49606847891-0' (length=14)
          public 'ConsigneeEmail' => string '-' (length=1)
          public 'DeliveryID' => null
          public 'DeliveryName' => string '' (length=0)
          public 'DeliveryAddress' => string '' (length=0)
          public 'DeliveryCity' => string '' (length=0)
          public 'DeliveryIndex' => string '' (length=0)
          public 'DeliveryPostCode' => null
          public 'DeliveryCountry' => string '' (length=0)
          public 'DeliveryContact' => string '' (length=0)
          public 'DeliveryPhone' => string '' (length=0)
          public 'DeliveryEmail' => string '' (length=0)
          public 'UnLoadTimeFrom' => string '' (length=0)
          public 'UnLoadTimeTo' => string '' (length=0)
          public 'UnLoadDate' => string '2021-10-22' (length=10)
          public 'UnloadDateType' => int 2
          public 'UnloadNotes' => string '' (length=0)
          public 'UnLoadRef' => string '' (length=0)
          public 'TailLiftDE' => int 1
          public 'OrderNr' => null
          public 'Insurance' => null
          public 'InsuranceValue' => int 0
          public 'InsuranceCurrency' => string '' (length=0)
          public 'ShipmentDate' => null
          public 'GoodsValue' => null
          public 'GoodsValueCurrency' => null
          public 'Notes' => null
          public 'dfuserid' => int 151
          public 'Office' => string 'LT' (length=2)
          public 'CreatedOn' => string '2021-10-22 15:53:11' (length=19)
          public 'Test' => int 1
          public 'Status' => int 0
          public 'Imported' => int 0
          public 'TrackLink' => string 'http://track.acelogisticsgroup.eu/trackandtrace/track.php?cc=LT&guid=024ee05c333711ecb7df000c29e523b2' (length=101)
          public 'LoadAdvice' => int 0
          public 'UnLoadAdvice' => int 0
          public 'ADR' => int 0
          public 'OrderType' => string 'R' (length=1)
          public 'LabelLink1' => string 'https://www.ace.LT/eservices/weborders/TE/sscclabel.php?Report=SSCCLabel1A4&JobGUID=024ee05c333711ecb7df000c29e523b2' (length=116)
          public 'LabelLink2' => string 'https://www.ace.LT/eservices/weborders/TE/sscclabel.php?Report=SSCCLabel2A4&JobGUID=024ee05c333711ecb7df000c29e523b2' (length=116)
          public 'LabelLink3' => null
          public 'WBLink' => string 'https://www.ace.LT/eservices/weborders/waybill.php?JobGUID=024ee05c333711ecb7df000c29e523b2' (length=91)


*/

            $this->RESPONSE_ERROR = "2";//uzregistravo, bet dar netikrinom lipduku, todel dabar 2... jeigu bus ir lipdukai, tai pasikeis i 1

            $this->XML_RESPONSE_COMMENT  = ''; // ACE neturi komento

            $this->RESPONSE_REQUEST_ID = $respA->ACERef;
            $this->RESPONSE_BOOKING_ID = $respA->ACERef;

            $this->RESPONSE_PDF_X = $respA->LabelLink1; //lipdukas A6 formatu
            $this->RESPONSE_PDF_Vazt = $respA->WBLink; //lipdukas WayBill
            if($this->RESPONSE_PDF_X AND $this->RESPONSE_PDF_Vazt){
                $this->RESPONSE_ERROR = 1;//0-nezinom, 1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
            }

            $this->RESPONSE_ShipmentIdentificationNumber = $respA->ACERef;
            $this->RESPONSE_OrderGUID = $respA->OrderGUID;
            $this->RESPONSE_CustomerID = $respA->CustomerID;
            $this->RESPONSE_CustomerGUID = $respA->CustomerGUID;
/*
            //suskaidom i lipduku array (nes kai lipdukas vienas tai jis pateikiamas kaip objektas, o jeigu ju keli tai kaip objektu array!!!!)
            //susivedam, kad visada butu objektu array
            $this->RESPONSE_PDF_ARRAY = array();
            if(is_object($this->RESPONSE_PDF_X)){
                $this->RESPONSE_PDF_ARRAY[]=$this->RESPONSE_PDF_X;
            }elseif(is_array($this->RESPONSE_PDF_X)){//end if
                foreach ($this->RESPONSE_PDF_X as $okeylip => $olip) {
                    $this->RESPONSE_PDF_ARRAY[]=$olip;
                }//end foreach
            }

            var_dump ($this->RESPONSE_PDF_ARRAY);

            if(is_array($this->RESPONSE_PDF_ARRAY)){
                //parsinam grazintus lipdukus is UPS RESPONSE
                foreach ($this->RESPONSE_PDF_ARRAY as $keylip => $lip) {

                    $root_path = dirname($script_path);

                    $LipdPath = $root_path . '../../../uploads/tvsLabel/';
                    //var_dump("+++++++++++++++++++++++".$LipdPath."+++++++++++++<br>");
                    $pref = "UPS_";
                    $PackNumber = $lip->TrackingNumber;

                    //pasiimam skaitmenini GIF
                    $lip64GIF = base64_decode($lip->ShippingLabel->GraphicImage);
                    $output_file_gif = $pref.$PackNumber.".gif";
                    $output_file_gif_with_path = $LipdPath.$output_file_gif;
                    //saugom GIF i diska
                    $BarcodeFile_gif = $this->savePdf($lip64GIF, $output_file_gif_with_path);

                    //tikrinam ar lipdukas susikure ir yra ten kur reikia
                    if(is_file($output_file_gif_with_path)){
                        $this->packs['PacksArray'][$keylip]['lipdukaiSusikure'] = "Y";
                    }else{
                        $this->packs['PacksArray'][$keylip]['lipdukaiSusikure'] = "N";
                    }
                    $this->packs['PacksArray'][$keylip]['TrackingNr'] = $lip->TrackingNumber;
                    $this->packs['PacksArray'][$keylip]['LipdukasPdf'] = $output_file_gif;
                    $this->packs['PacksArray'][$keylip]['VezejasReal'] = "UPS";
                    $this->packs['PacksArray'][$keylip]['Created'] = date("Y-m-d H:i:s");

                    $gifToPdf[] = $LipdPath.$output_file_gif; //zemiau testuojam PDF kurima is Gif ... darbui nereikia sito, tik testui

                    //saugom HTML faila kuris rodo suformatuota GIF lipduka
                    $lip64HTML = base64_decode($lip->ShippingLabel->HTMLImage);
                    $output_file_html = $pref.$PackNumber.".html";
                    $output_file_html_with_path = $LipdPath.$output_file_html;
                    $BarcodeFile_html =  $this->savePdf($lip64HTML, $output_file_html_with_path);
                    

                    echo "
                        <a href = '".$BarcodeFile_html."' target='_blank'>file $BarcodeFile HTML</a><br>
                    ";


                }//end foreach

                //tikrinam ar visi lipdukai susikure
                $visiLipdukaiSusikure = 'Y';//pradine reiksme
                if(is_array($this->packs['PacksArray'])){
                    foreach ($this->packs['PacksArray'] as $lkey => $lvalue) {
                        if($lvalue['lipdukaiSusikure']!='Y'){
                            $visiLipdukaiSusikure = 'N';//nesusikure bent vienas lipdukas
                        }else{

                        }
                    }
                }//end if
                if($visiLipdukaiSusikure == 'Y'){
                    //visi lipdukai susikure
                    $this->RESPONSE_ERROR = "1";//uzregistravo, ir yra visi lipdukai
                }
            }//end if

            // ***20210831*** disablinam cia bendrojo lipduko generavima ir perkeliam i atskira ajax funkcija
            //$this->CreatePdf ($SiuntaData['uid'], $gifToPdf);

            //var_dump($this->packs);
*/

        }//end if


        return true;
    }//end function



    public function RotateGif ($gifLocation){
        $filename = $gifLocation;
        list($file, $ext) = explode('.gif', $gifLocation);
        $ext = "gif";
        $rezFile = $file.'_R.'.$ext;
        $degrees = 270;

        // Content type
        //header('Content-type: image/gif');

        $source = imagecreatefromgif($filename);

        // Rotate
        $white = imagecolorallocate($source, 255, 255, 255);
        $rotate = imagerotate($source, $degrees, 0);

        // Set black color as transparent
        //$transp = imagecolorallocate($rotate, 0, 0, 0);
        //imagecolortransparent($rotate, $transp);

        // Output
        imagegif($rotate, $rezFile);


        //var_dump($rotate);

        // Free memory
        imagedestroy($source);
        imagedestroy($rotate);     

        return    $rezFile;
    }




    private function CreatePdf ($siuntaID, $GifArray){

        require_once '../../../helpers/dompdf/autoload.inc.php'; 
        // Instantiate and use the dompdf class 
        $dompdf = new \Dompdf\Dompdf();
        // Load content from html file 
        $html = '
        <HTML>
            <HEAD>
            <title></title> 
            <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
            <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
            <meta name="keywords" content="Aurika VVS" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <META name="title" content=":: Labels ::">
            <META name="copyright" content="Aurika UAB">
            
            <style>
                html { margin: 2px}
                .Rotate1 {
                    width:;
                    -webkit-transform: rotate(90deg);
                    -moz-transform: rotate(90deg);
                    -o-transform: rotate(90deg);
                    -ms-transform: rotate(90deg);
                    transform: rotate(90deg);
                    position: relative;
                    top:0px;
                    left:0px;
                }        
            </HEAD>
            <BODY style="padding:0px; margin:0px; background-color:FFFFFF;  ">';

        if(is_array($GifArray)){
            foreach ($GifArray as $key => $gif) {
                $rezFile = $this->RotateGif ($gif);
                //$html .= '<img  src="'.$rezFile.'"  class="Rotate"  height="649" width="392">';
                $html .= '<img  src="'.$rezFile.'"  class="Rotate"  height="645" width="392">';

            }//foreach
        }//if
        $html .= '
            </BODY>
        </HTML>';

        //echo "---<br>";
        var_dump($html);    
        


        $sugeneruotasPdf = $this->CreatePdfGif ($siuntaID, $html);     
        //['file']
        //['fileWidthPath']


        //jeigu failas yra tai ji irasom i DB
        if(file_exists($sugeneruotasPdf['fileWidthPath']) AND is_numeric($siuntaID) AND $siuntaID>0) {

                $wsql = 'UPDATE TOP (1) _TMS_Siuntos SET 
                            LabelLink=\''.$sugeneruotasPdf['file'].'\'
                        WHERE  uid=\''.$siuntaID.'\' ;'
                ;    

                var_dump($wsql);

                $wmssql = DBMSSqlCERM::getInstance();
                $retSQL = $wmssql->execSql($wsql, 1);            
        }

        /*
        $dompdf->loadHtml($html); 

        // (Optional) Setup the paper size and orientation 
        // $dompdf->setPaper('A4', 'portrait'); 

        //set size
        $customPaper = array(0,0,100,150);
        $dompdf->set_paper($customPaper);        
        // Render the HTML as PDF 
        $dompdf->render();
        $output = $dompdf->output();

        // Kai bus perkelta į NGMod, atkomentuoti šitą, ištrinti eilutę žemiau.

        //if (!file_exists('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/')) {
        //    mkdir('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/', 0777, true);
        //}
        //file_put_contents('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/' . $MatavDuom["MatUID"] . '.pdf', $output);

        file_put_contents('../../../uploads/tvsLabel/aaazzzzz.pdf', $output);
        */

        return $sugeneruotasPdf;

    }//end function



    private function CreateUPSManifestPdf ($DataArray){

        require_once '../../../helpers/dompdf/autoload.inc.php'; 
        // Instantiate and use the dompdf class 
        $dompdf = new \Dompdf\Dompdf();
        // Load content from html file 
        $html = '
        <HTML>
            <HEAD>
            <title></title> 
            <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
            <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
            <meta name="keywords" content="Aurika VVS" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <META name="title" content=":: Labels ::">
            <META name="copyright" content="Aurika UAB">
            
            <style>
                html { margin: 10px}

                .tvs_UPSM {
                    font: normal 11px verdana, arial, tahoma;
                    color: #000;
                    margin: 0px;
                    padding: 0px;
                    width: 100%;
                    border: 1px solid #000;
                    border-collapse: collapse;
                }

                .tvs_UPSM th {
                    margin: 0px;
                    padding: 3px;
                    border: 1px solid #EEE;
                    background-color: #EEE; 
                    text-align: left;
                    vertical-align: middle;
                    color: #000;
                    font: bold 12px verdana, arial, tahoma;
                    white-space: nowrap;
                }
                .tvs_UPSM td {
                    margin: 0px;
                    padding: 3px;
                    border: 1px solid #EEE;
                    background-color:#FFF;
                    text-align: left;
                    vertical-align: middle;
                    color: #000; 
                    font: normal 11px verdana, arial, tahoma;
                }                
            </HEAD>
            <BODY style="padding:0px; margin:0px; background-color:FFFFFF;  ">

            <br><br>
            <div style="width:100%; border: 1px solid #AAA; background-color: #EEE; font: normal 12px verdana, arial, tahoma;">'.$DataArray['SHIPPER'].'</div><br><br>
            <table class="tvs_UPSM">
                <tr>
                    <th>Tracking number</th>
                    <th>Packages</th>
                    <th>Ship to</th>
                    <th>Service</th>
                </tr>
        ';

        if(is_array($DataArray['SIUNTOS'])){
            foreach ($DataArray['SIUNTOS'] as $key => $pak) {
                //$rezFile = $this->RotateGif ($gif);
                //$html .= '<img  src="'.$rezFile.'"  class="Rotate"  height="649" width="392">';
                $html .= '
                    <tr>
                        <td>'.$pak['TrackingNr'].'</td>
                        <td>'.$pak['Packages'].'</td>
                        <td>'.$pak['ShipTo'].'</td>
                        <td>'.$pak['Service'].'</td>
                    </tr>
                ';

            }//foreach
        }//if
        $html .= '
            </table>
            <br><br>
            <table class="tvs_UPSM">
                <tr>
                    <td style="width:150px;">Shipment Total</td>
                    <td>'.$DataArray['ShipmentTotal'].'</td>
                </tr>
                <tr>
                    <td style="width:150px;">Package Total</td>
                    <td>'.$DataArray['PackageTotal'].'</td>
                </tr>
            </table>            

            </BODY>
        </HTML>';

        //echo "---<br>";
        var_dump($html);    
        

        
        $sugeneruotasPdf = $this->CreatePdfManifest ($html, $DataArray['siandienPDF']);     
        //['file']
        //['fileWidthPath']


        //jeigu failas yra tai ji irasom i DB
        /*
        if(file_exists($sugeneruotasPdf['fileWidthPath']) AND is_numeric($siuntaID) AND $siuntaID>0) {

                $wsql = 'UPDATE TOP (1) _TMS_Siuntos SET 
                            LabelLink=\''.$sugeneruotasPdf['file'].'\'
                        WHERE  uid=\''.$siuntaID.'\' ;'
                ;    

                var_dump($wsql);

                $wmssql = DBMSSqlCERM::getInstance();
                $retSQL = $wmssql->execSql($wsql, 1);            
        }
        */
        /*
        $dompdf->loadHtml($html); 

        // (Optional) Setup the paper size and orientation 
        // $dompdf->setPaper('A4', 'portrait'); 

        //set size
        $customPaper = array(0,0,100,150);
        $dompdf->set_paper($customPaper);        
        // Render the HTML as PDF 
        $dompdf->render();
        $output = $dompdf->output();

        // Kai bus perkelta į NGMod, atkomentuoti šitą, ištrinti eilutę žemiau.

        //if (!file_exists('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/')) {
        //    mkdir('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/', 0777, true);
        //}
        //file_put_contents('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/' . $MatavDuom["MatUID"] . '.pdf', $output);

        file_put_contents('../../../uploads/tvsLabel/aaazzzzz.pdf', $output);
        */

        return $sugeneruotasPdf;

    }//end function



    public function CreatePdfGif ($siuntaID, $HTMLtoPDF){

        //echo $html;
        //require_once 'helpers/dompdf/autoload.inc.php'; 


                // Reference the Dompdf namespace
                //use Dompdf\Dompdf; 
                // Instantiate and use the dompdf class 
                $dompdf = new \Dompdf\Dompdf();
                // Load content from html file 
                $dompdf->loadHtml($HTMLtoPDF); 

                // (Optional) Setup the paper size and orientation 
                //https://stackoverflow.com/questions/25376901/how-to-set-custom-width-and-height-of-pdf-using-dompdf
                $dompdf->setPaper('A6', 'portrait'); 
                // Render the HTML as PDF 
                $dompdf->render();
                $output = $dompdf->output();

                // Kai bus perkelta į NGMod, atkomentuoti šitą, ištrinti eilutę žemiau.

                //if (!file_exists('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/')) {
                //    mkdir('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/', 0777, true);
                //}
                //file_put_contents('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/' . $MatavDuom["MatUID"] . '.pdf', $output);

                $rezFilePath = '../../../uploads/tvsLabel/';

                file_put_contents($rezFilePath.'UPS_'.$siuntaID.'.pdf', $output);

                $rez['file'] = 'UPS_'.$siuntaID.'.pdf';
                $rez['fileWidthPath'] = $rezFilePath.'UPS_'.$siuntaID.'.pdf';
                return $rez;
    }//end function


    public function CreatePdfManifest ($HTMLtoPDF, $date){

        //echo $html;
        //require_once 'helpers/dompdf/autoload.inc.php'; 


                // Reference the Dompdf namespace
                //use Dompdf\Dompdf; 
                // Instantiate and use the dompdf class 
                $dompdf = new \Dompdf\Dompdf();
                // Load content from html file 
                $dompdf->loadHtml($HTMLtoPDF); 

                // (Optional) Setup the paper size and orientation 
                //https://stackoverflow.com/questions/25376901/how-to-set-custom-width-and-height-of-pdf-using-dompdf
                $dompdf->setPaper('A4', 'LANDSCAPE'); 
                // Render the HTML as PDF 
                $dompdf->render();
                $output = $dompdf->output();

                // Kai bus perkelta į NGMod, atkomentuoti šitą, ištrinti eilutę žemiau.

                //if (!file_exists('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/')) {
                //    mkdir('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/', 0777, true);
                //}
                //file_put_contents('../../../MNT/Aurika_CERM/' . $MatavDuom["UzsakovoID"] . '/Jobs/' . $MatavDuom["Batch_ID"] . '/sertifikatai/' . $MatavDuom["MatUID"] . '.pdf', $output);

                $rezFilePath = '../../../uploads/tvsLabel/';
                $fileName = 'UPSM_'.$date.'.pdf';

                file_put_contents($rezFilePath.$fileName, $output);

                $rez['file'] = $fileName;
                $rez['fileWidthPath'] = $rezFilePath.$fileName;
                return $rez;
    }//end function



    public function ajaxCreateLabelPDF ($SiuntaUID){
        
        //is DB kokie yra lipdukai

        $qryPak = "

            SELECT  *
            FROM _TMS_Pak AS A
            WHERE A.deleted<>1 AND SiuntaUID = '".$SiuntaUID."' 
            ORDER BY A.uid DESC
        ";
        
        $mssql = DBMSSqlCERM::getInstance();
        $SiuntosDuomPack = $mssql->querySql($qryPak, 1);    

        var_dump($SiuntosDuomPack); 

        //patikrinam ar yra lipduku failai, padarom failu masyva
        $lipdFileArray = array();
        $LipdPath = $root_path . '../../../uploads/tvsLabel/';
        if($SiuntosDuomPack){
            foreach ($SiuntosDuomPack as $key => $pakis) {
                if($pakis['LipdukasPdf']){
                    $lipdFileArray[] = $LipdPath.$pakis['LipdukasPdf'];    
                }//end if
            }//end foreach
        }//end if

        var_dump($lipdFileArray); 

        //generuojam lipdukus
        if($lipdFileArray){
            $LipdRez = $this->CreatePdf ($SiuntaUID, $lipdFileArray);
        }else{
            $LipdRez = "";
            $this->addError("Neaptikta lipdukų failų sugeneruotų iš UPS (GIF'ų)");
        }

        var_dump($LipdRez);

        //grazinam rez
        return $LipdRez;
    }//end function




        function CallAPI($method, $url, $header_data, $data = false)
        {
            $curl = curl_init();

            switch ($method)
            {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);

                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }

            // Optional Authentication:
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header_data);
            /*
            if($header_data){
                foreach ($header_data as $key => $value) {
                    echo "<br>".$key ."=>". $value. "<br>";
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $value);
                }
            }
            */
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);



            $result = curl_exec($curl);

            curl_close($curl);

            return $result;
        }










        function savePdf($pdf_string, $output_file) {
            // open the output file for writing
            $ifp = fopen( $output_file, 'wb' ); 

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



        function showPdf ($BarcodeFile){

        header('Content-Description: File Transfer'); 
            header('Content-Type: application/octet-stream'); 
            header('Content-Disposition: attachment; filename='.$BarcodeFile.''); 
            header('Content-Transfer-Encoding: binary'); 
            header('Expires: 0'); 
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
            header('Pragma: public'); 
            header('Content-Length: ' . filesize($BarcodeFile)); 
            ob_clean(); 
            flush(); 
            readfile($BarcodeFile);      
            exit();
        /*
            $filename = $name;

            $fileinfo = pathinfo($filename);
            $sendname = $fileinfo['filename'] . '.' . strtoupper($fileinfo['extension']);

            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=".$sendname." ");
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            /*
            //$name = 'file.pdf';
            //file_get_contents is standard function
            $content = file_get_contents($name);
        */

        /*  
            header('Content-Type: application/pdf');
            header('Content-Length: '.strlen('aaa' ));
            //header('Content-disposition: inline; filename="' . $name . '"');
            //header('Cache-Control: public, must-revalidate, max-age=0');
            //header('Pragma: public');
            //header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            //header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            
            echo $content;  
        */  
        }





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









 
    public function PrintManifest($Sandelys = 'KEG'){


        // nuskaitom UPS siuntas sios dienos
        $UPSSiuntosSiandien = $this->tvsMod->getUPSSiuntosSiandienManifest ($Sandelys);


        if($UPSSiuntosSiandien){
            $filePDF = $this->CreateUPSManifestPdf ($UPSSiuntosSiandien);

        }else{
            $this->addError("Nėra duomenų manifestui.");
            $filePDF = array();
        }



        /* disablinta 20211007 ir perdaryta is CSV i PDF
        // formuojam csv
        if($UPSSiuntosSiandien){
            // Open a file in write mode ('w')
            $path = "../../../uploads/tvsLabel/";
            $file = "UPSV_".$Sandelys.'_'.date("Ymd").".csv";
            $pathFile = $path.$file;
            $fp = fopen($pathFile, 'w');
              
            // Loop through file pointer and a line
            foreach ($UPSSiuntosSiandien as $fields) {
                fputcsv($fp, $fields);
            }
              
            fclose($fp);            
        }else{
            $this->addError("Nėra duomenų manifestui.");
        }
        */

        return $filePDF;

    }//end function



    public function tvsVarDump (){

        echo "<HR>";
        var_dump($this->addres);
        echo "<HR>";
        var_dump($this->tvs);
        echo "<HR>";

    }//end function

    
   
}//end class
