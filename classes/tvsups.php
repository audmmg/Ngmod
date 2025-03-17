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
class tvsups  extends tvsclass{
    //private static $useDebug = true;

    public $mode = 'notSet'; //test/live/notSet, veliau ateis is TVSconfig.php TVS_CONFIG::SCHENKER_MODE;


    //private $AccessKeyDemo = '550e7680-c908-40ec-878f-8fd9771a73e6';
    //private $AccessKeyLive = '3339e71f-b4b8-4bed-930f-485bb95248cd';
    //private $AccessKey = '';

    /* DEMO acountas */
    /* prisijungimas prie demo sistemos stebejimui
        https://eschenker-fat.dbschenker.com/nges-portal/secured/#!/booking/my-bookings

    */

    private $UPSKrovinioUzsakLinkLive = "https://onlinetools.ups.com/ship/v1/shipments?additionaladdressvalidation=city";
    private $UPSKrovinioUzsakLinkDemo = "https://wwwcie.ups.com/ship/v1/shipments";
    private $UPSKrovinioUzsakLink = "";


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
    private $SenderAddressIDETK = "AURIKAUAB";
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

        $this->mode = TVS_CONFIG::UPS_MODE;

        //echo "---SCHENKER---<Br>";

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();


        if($this->mode=='live'){
            
            $this->UPSKrovinioUzsakLink = $this->UPSKrovinioUzsakLinkLive;
            //$this->UPSLipdukoSpausdLink = $this->UPSLipdukoSpausdLinkLive;
            
            $this->UPSKurjerioIskvietimas = $this->UPSKurjerioIskvietimasLive;
            //echo "********* $mode=live ************<br>";

        }elseif($this->mode=='test'){

            $this->UPSKrovinioUzsakLink = $this->UPSKrovinioUzsakLinkDemo;
            //$this->UPSLipdukoSpausdLink = $this->UPSLipdukoSpausdLinkDemo;

            $this->UPSKurjerioIskvietimas = $this->UPSKurjerioIskvietimasDemo;

            //echo "********* $mode=test ************<br>";

        }else{
            $this->mode = 'notSet';

            $this->UPSKrovinioUzsakLink = '';

            //echo "********* $mode=NOTSET ************<br>";
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
        if($this->sParam['sUPS_SandelysKEGKPG']=='KEG'){
            $this->sandelys = 'KEG';
        }else if($this->sParam['sUPS_SandelysKEGKPG']=='KPG'){
            $this->sandelys = 'KPG';
        }else if($this->sParam['sUPS_SandelysKEGKPG']=='ETK' OR $this->sParam['sUPS_SandelysKEGKPG']=='ETK1'){
            $this->sandelys = 'ETK';
        }else{
            $Error['message'] = 'Nenustatyta iš kurio sandėlio bus siunta';
            $Error['code'] = "TAD-U2011"; //t-transport AD-address data
            $Error['group'] = "AD"; //AD - address data error
            $this->addError ($Error);
        }



        //pasiimam duomenys is TVS_Siuntos, TVS_Pack, TVS_keys lenteliu
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
                        if($this->SiuntaData['Sandelys']=='KEG' OR $this->SiuntaData['Sandelys']=='KPG' OR $this->SiuntaData['Sandelys']=='ETK'  OR $this->SiuntaData['Sandelys']=='ETK1'){
                            //$this->setSenderAurikaAdresas ($this->sParam['sUPS_SandelysKEGKPG']);
                            $this->setSenderAurikaAdresas ($this->SiuntaData['Sandelys']);
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

                    /* CIAS negali buti neutralumo */
                    $this->tvs['neutralShipping']=0;
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

                    $this->tvs['ServiceCode']=$sParam['sUPS_ServiceID'];

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

                    $this->tvs['ServiceCode']=$sParam['sUPS_ServiceID'];

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








    public function generateUPSXML ($param=array()){

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
        $this->addres['CONSIGNEE']['phone'] = '-';

//var_dump($this->addres);


        $JSON_ups = '

{
 "ShipmentRequest":{
    "Shipment":{
        "Description":"Packaging materials",
        "Shipper":{
            "Name":"'.$this->addres['SHIPPER']['name1'].'",
            "AttentionName":"'.$this->addres['SHIPPER']['contactPerson']['name'].'",
            "Phone":{
                "Number":"'.$this->addres['SHIPPER']['phone'].'"
            },
            "ShipperNumber":"976V1F",
            "Address":{
                "AddressLine":"'.$this->addres['SHIPPER']['street'].'",
                "City":"'.$this->addres['SHIPPER']['city'].'",
                "PostalCode":"'.$this->addres['SHIPPER']['postalCode'].'",
                "CountryCode":"'.$this->addres['SHIPPER']['countryCode'].'"
            }
        },
        "ShipTo":{
            "Name":"'.$this->addres['CONSIGNEE']['name1'].'",
            "AttentionName":"'.$this->addres['CONSIGNEE']['contactPerson']['name'].'",
            "Phone":{
                "Number":"'.$this->addres['CONSIGNEE']['phone'].'"
            },
            "Address":{
                "AddressLine":"'.$this->addres['CONSIGNEE']['street'].'",
                "City":"'.$this->addres['CONSIGNEE']['city'].'",
                "PostalCode":"'.$this->addres['CONSIGNEE']['postalCode'].'",
                "CountryCode":"'.$this->addres['CONSIGNEE']['countryCode'].'"
            }
        },
        "ShipFrom":{
            "Name":"'.$this->addres['PICKUP']['name1'].'",
            "AttentionName":"'.$this->addres['PICKUP']['contactPerson']['name'].'",
            "PhoneNumber":"'.$this->addres['PICKUP']['contactPerson']['phone'].'",
            "Address":{
                "AddressLine":"'.$this->addres['PICKUP']['street'].'",
                "City":"'.$this->addres['PICKUP']['city'].'",
                "PostalCode":"'.$this->addres['PICKUP']['postalCode'].'",
                "CountryCode":"'.$this->addres['PICKUP']['countryCode'].'"
            }
        },
        "SoldTo":{
            "Name":"'.$this->addres['CONSIGNEE']['name1'].'",
            "AttentionName":"'.$this->addres['CONSIGNEE']['contactPerson']['name'].'",
            "PhoneNumber":"'.$this->addres['CONSIGNEE']['phone'].'",
            "Address":{
                "AddressLine":"'.$this->addres['CONSIGNEE']['street'].'",
                "City":"'.$this->addres['CONSIGNEE']['city'].'",
                "PostalCode":"'.$this->addres['CONSIGNEE']['postalCode'].'",
                "CountryCode":"'.$this->addres['CONSIGNEE']['countryCode'].'"
            }
        },
        "PaymentInformation":{
            "ShipmentCharge":{
                "Type":"01",
                "BillShipper":{
                    "AccountNumber":"976V1F"
                }
            }
        },
        
        "ReferenceNumber":{
            "Code":"PO",
            "Value":"123456"
        },

        "Service":{
            "Code":"'.$this->tvs['ServiceCode'].'",
            "Description":"'.$this->tvs['ServiceDescription'].'"
        },
';


if($this->packs['PacksArray']){
    $JSON_ups .= '
        "Package":[
    ';

    $pakNo = 1;

    var_dump($this->packs['PacksArray']);

    foreach ($this->packs['PacksArray'] as $key => $pakis) {
        
        if ($pakNo > 1){
            $JSON_ups .= ', ';
        }
        //$tmpTuris = $pakis['Turis'] * $pakis['Kiekis'];
        //$tmpSvoris = $pakis['GrossSvoris'] * $pakis['Kiekis'];

        if($pakis['Kiekis']>0){
            //for ($i=1; $i <= $pakis['Kiekis']; $i++) {  NEREIKIA, nes auksciau surasem kiekviena dezute/palete kaip atskira o ne grupe vienodu

                $JSON_ups .= '
                        {
                            "Description":"International Goods",
                            "Packaging":{
                                "Code":"02"
                            },
                            "Dimension":{
                                "UnitOfMeasurement":{
                                    "Code":"CM"
                                },
                                "Length":"'.$pakis['Ilgis'].'",
                                "Width":"'.$pakis['Plotis'].'",
                                "Height":"'.$pakis['Aukstis'].'"
                            },
                            "PackageWeight":{
                                "UnitOfMeasurement":{
                                    "Code":"KGS"
                                },
                                "Weight":"'.$pakis['Svoris'].'"
                            },
                            "PackageServiceOptions":""
                        }     
                ';   

                //if($i<$pakis['Kiekis']){
                //    $JSON_ups .= ',';
                //}
        
                $pakNo++;// reikalingas del kablelio padejimo reikalingoje vietoje
            //}//end for
        }//end if

    }//end foreach
    $JSON_ups .= '
            ]
            ,
    ';

}//end if

 $JSON_ups .= '
    "ItemizedChargesRequestedIndicator":"",
    "RatingMethodRequestedIndicator":"",
    "TaxInformationIndicator":"",
    "ShipmentRatingOptions":{
        "NegotiatedRatesIndicator":""
    }
 },
    "LabelSpecification":{
        "LabelImageFormat":{
            "Code":"GIF"
        }
    }
 }
}


';




/* TIK TESTAVIMUI */
/*
$JSON_ups='

{
 "ShipmentRequest":{
    "Shipment":{
        "Description":"Packaging materials",
        "Shipper":{
            "Name":"LSP Rhein-Label GmbH & Co. KG.",
            "AttentionName":"-",
            "Phone":{
                "Number":"+492255 952341"
            },
            "ShipperNumber":"976V1F",
            "Address":{
                "AddressLine":"Wilkensstr. 51",
                "City":"Swisttal-Odendorf",
                "PostalCode":"53913",
                "CountryCode":"LT"
            }
        },
        "ShipTo":{
            "Name":"FeSa Obst- & Gemüsehandels GmbH &",
            "AttentionName":"-",
            "Phone":{
                "Number":"-"
            },
            "Address":{
                "AddressLine":"Im Grund 1",
                "City":"Mutterstadt",
                "PostalCode":"67112",
                "CountryCode":"DE"
            }
        },
        "ShipFrom":{
            "Name":"LSP Rhein-Label GmbH & Co. KG.",
            "AttentionName":"-",
            "PhoneNumber":"",
            "Address":{
                "AddressLine":"Wilkensstr. 51",
                "City":"Swisttal-Odendorf",
                "PostalCode":"51127",
                "CountryCode":"LT"
            }
        },
        "SoldTo":{
            "Name":"FeSa Obst- & Gemüsehandels GmbH &",
            "AttentionName":"-",
            "PhoneNumber":"-",
            "Address":{
                "AddressLine":"Im Grund 1",
                "City":"Mutterstadt",
                "PostalCode":"67112",
                "CountryCode":"DE"
            }
        },
        "PaymentInformation":{
            "ShipmentCharge":{
                "Type":"01",
                "BillShipper":{
                    "AccountNumber":"976V1F"
                }
            }
        },
        
        "ReferenceNumber":{
            "Code":"PO",
            "Value":"123456"
        },

        "Service":{
            "Code":"11",
            "Description":""
        },

        "Package":[
    
                        {
                            "Description":"International Goods",
                            "Packaging":{
                                "Code":"02"
                            },
                            "Dimension":{
                                "UnitOfMeasurement":{
                                    "Code":"CM"
                                },
                                "Length":"120",
                                "Width":"80",
                                "Height":"170"
                            },
                            "PackageWeight":{
                                "UnitOfMeasurement":{
                                    "Code":"KGS"
                                },
                                "Weight":"20"
                            },
                            "PackageServiceOptions":""
                        }     
                
            ]
            ,
    
    "ItemizedChargesRequestedIndicator":"",
    "RatingMethodRequestedIndicator":"",
    "TaxInformationIndicator":"",
    "ShipmentRatingOptions":{
        "NegotiatedRatesIndicator":""
    }
 },
    "LabelSpecification":{
        "LabelImageFormat":{
            "Code":"GIF"
        }
    }
 }
}

';
*/

        $this->UPSXML = $JSON_ups;

        if($pavyko===true){
            $this->UPSXML_created = true;
        }else{
            $this->UPSXML_created = false;
        }

        var_dump($this->UPSXML);


        return $pavyko;
    }//end function









    private function setSenderAurikaAdresas ($gamyba){

        switch ($gamyba) {
            case 'KEG':
                    //address
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['contactPerson']['phone']=$this->SenderContactTelKEG;
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
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['contactPerson']['phone']=$this->SenderContactTelKEG;
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
                    $this->addres['SHIPPER']['contactPerson']['name']=$this->SenderContactPersonKEG;
                    $this->addres['SHIPPER']['contactPerson']['email']=$this->SenderContactMailKEG;
                    $this->addres['SHIPPER']['contactPerson']['phone']=$this->SenderContactTelKEG;
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
                    $this->addres['SHIPPER']['street']=$this->SenderAddressKEG; // "Jovarų g. 2A";
                    $this->addres['SHIPPER']['street2']="";
                    $this->addres['SHIPPER']['city']="Kaunas";
                    $this->addres['SHIPPER']['countryCode']="LT";
                    $this->addres['SHIPPER']['type']="SHIPPER";                    
                    $this->SHIPPERDataSet = true;

                 break;
            
            default:
                    $this->addres['SHIPPER']['contactPerson']['name']="";
                    $this->addres['SHIPPER']['contactPerson']['email']="";
                    $this->addres['SHIPPER']['contactPerson']['phone']="";
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
                    $this->addres['PICKUP']['contactPerson']['phone']=$this->SenderContactTelKEG;
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
                    $this->addres['PICKUP']['contactPerson']['phone']=$this->SenderContactTelKPG;
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
                    $this->addres['PICKUP']['contactPerson']['phone']=$this->SenderContactTelETK;
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
                    $this->addres['PICKUP']['contactPerson']['phone']='';
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
                                //$this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                if($SiuntaData['Sandelys']=='KEG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKEG;
                                }elseif($SiuntaData['Sandelys']=='KPG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKPG;
                                }elseif($SiuntaData['Sandelys']=='ETK' OR $SiuntaData['Sandelys']=='ETK1'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeETK;
                                }else{
                                    $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                }
                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                //$this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                $this->addres['SHIPPER']['countryCode']='LT';// visada LT nes turi sutapti su "ShipperNumber" kodo priskirto saliai (kitaip ismeta klaida: 120120 )
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                //$this->setPICKUPAdresa ($SiuntaData['Sandelys']);
                                /*
                                PICKUP logika LS ir 2LS atveju deliojama is Siuntejo ir Aurikos duomenu, 
                                pagal Gintares  (transportas@aurika.lt) laiska 2020-10-02 10:49
                                */
                                switch ($SiuntaData['Sandelys']) {
                                        case 'KEG':
                                                //address
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKEG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                                $this->addres['PICKUP']['countryCode']="LT";
                                                $this->addres['PICKUP']['type']="PICKUP";                    
                                                $this->PICKUPDataSet = true;
                                            break;

                                        case 'KPG':
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKPG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                                $this->addres['PICKUP']['countryCode']="LT";
                                                $this->addres['PICKUP']['type']="PICKUP";                    
                                                $this->PICKUPDataSet = true;
                                             break;
                                        case 'ETK':
                                        case 'ETK1':
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKPG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
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
                                //$this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                if($SiuntaData['Sandelys']=='KEG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKEG;
                                }elseif($SiuntaData['Sandelys']=='KPG'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKPG;
                                }elseif($SiuntaData['Sandelys']=='ETK' OR $SiuntaData['Sandelys']=='ETK1'){
                                    $this->addres['SHIPPER']['postalCode']=$this->SenderPostCodeKPG;
                                }else{
                                    $this->addres['SHIPPER']['postalCode']=trim ($SiuntaData['SiuntejoPostKodas']);
                                }

                                $this->addres['SHIPPER']['stateCode']="";
                                $this->addres['SHIPPER']['stateName']="";
                                $this->addres['SHIPPER']['preferredLanguage']='';
                                $this->addres['SHIPPER']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                $this->addres['SHIPPER']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                $this->addres['SHIPPER']['street2']="";
                                $this->addres['SHIPPER']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                //$this->addres['SHIPPER']['countryCode']=trim ($SiuntaData['SiuntejoSaliesKodas']);
                                $this->addres['SHIPPER']['countryCode']='LT';// visada LT nes turi sutapti su "ShipperNumber" kodo priskirto saliai (kitaip ismeta klaida: 120120 )
                                $this->addres['SHIPPER']['type']="SHIPPER";                    
                                $this->SHIPPERDataSet = true;


                                //address
                                //$this->setPICKUPAdresa ($SiuntaData['Sandelys']);

                                /*
                                PICKUP logika LS ir 2LS atveju deliojama is Siuntejo ir Aurikos duomenu, 
                                pagal Gintares  (transportas@aurika.lt) laiska 2020-10-02 10:49
                                */
                                switch ($SiuntaData['Sandelys']) {
                                        case 'KEG':
                                                //address
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKEG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                                $this->addres['PICKUP']['countryCode']="LT";
                                                $this->addres['PICKUP']['type']="PICKUP";                    
                                                $this->PICKUPDataSet = true;
                                            break;

                                        case 'KPG':
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKPG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
                                                $this->addres['PICKUP']['countryCode']="LT";
                                                $this->addres['PICKUP']['type']="PICKUP";                    
                                                $this->PICKUPDataSet = true;
                                             break;
                                        case 'ETK':
                                        case 'ETK1':
                                                $this->addres['PICKUP']['contactPerson']['name']=trim ($Siuntejo_contact_name);
                                                $this->addres['PICKUP']['contactPerson']['email']=trim ($SiuntaData['Siuntejo_contact_email']);
                                                $this->addres['PICKUP']['name1']=trim (substr($SiuntaData['SiuntejoName'], 0, 35));//trim ($SiuntaData['SiuntejoName']);
                                                $this->addres['PICKUP']['name2']="";
                                                $this->addres['PICKUP']['customerAddressIdentifier']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['email']=trim ($SiuntaData['Siuntejo_email']);
                                                $this->addres['PICKUP']['fax']="";
                                                $this->addres['PICKUP']['industry']="AUTOMOTIVE";
                                                $this->addres['PICKUP']['locationType']="PHYSICAL";
                                                $this->addres['PICKUP']['mobilePhone']=trim ($SiuntaData['Siuntejo_contact_phone']);
                                                $this->addres['PICKUP']['personType']="COMPANY"; // COMPANY, PERSON
                                                $this->addres['PICKUP']['phone']=trim ($SiuntaData['Siuntejo_phone']);
                                                $this->addres['PICKUP']['poBox']="POBOX";
                                                $this->addres['PICKUP']['postalCode']=$this->SenderPostCodeKPG;
                                                $this->addres['PICKUP']['stateCode']="";
                                                $this->addres['PICKUP']['stateName']="";
                                                $this->addres['PICKUP']['preferredLanguage']="LT";
                                                $this->addres['PICKUP']['schenkerAddressId']=trim ($SiuntaData['SiuntejoAdresasID']);
                                                $this->addres['PICKUP']['street']=trim ($SiuntaData['SiuntejoStreet']);
                                                $this->addres['PICKUP']['street2']="";
                                                $this->addres['PICKUP']['city']=trim ($SiuntaData['SiuntejoMiestas']);
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
                                $this->setSenderAurikaAdresas ($SiuntaData['Sandelys']);

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





    /* pasiruosimas XML siuntimui, siuntimas ir rezultatu apdorojimas */
    public function sendXML ($SiuntaData=array()){

var_dump($SiuntaData);

        $method='POST';
        //$url =  "https://onlinetools.ups.com/ship/v1/shipments?additionaladdressvalidation=city";
        //$url =  "https://wwwcie.ups.com/ship/{version}/shipments";
        $url = $this->UPSKrovinioUzsakLink;


        $header_data[]="Content-Type:application/json";
        $header_data[]="Accept:application/json";
        $header_data[]="Username:Aurika";
        //$header_data[]="Password:Siuntos0323";
        $header_data[]="Password:SSiuntos0413+"; //20220413 pasikeite
        $header_data[]="transId:1234567";
        $header_data[]="Accept:application/json";
        $header_data[]="AccessLicenseNumber:DD83A0C55C662D3D";


        echo"<br><br><br>UPSXML-JSON<hr>";
        var_dump($this->UPSXML);
        echo"<br><br><br><hr>";


        $resp = $this->CallAPI($method, $url, $header_data, $this->UPSXML);
        echo"<br><br><br><hr>";
        var_dump($resp);
        echo"<br><br><br><hr>";
        //$response = file_get_contents('http://example.com/path/to/api/call?param1=5');
        $respArray = json_decode($resp);
        //$response = new SimpleXMLElement($response);
        var_dump($respArray);
        echo"<br><br><br><hr>";

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



        if(is_array($respArray->response->errors)){
            $errorArray = $respArray->response->errors;
            $this->RESPONSE_ERROR = "3";//yra klaidu
            $this->RESPONSE_ERROR_ARRAY = $errorArray;
            $this->RESPONSE_ShipmentIdentificationNumber = '';
            foreach ($errorArray as $klaidaKey => $klaida) {

                $this->RESPONSE_ERROR_TEXT = "[".$klaida->code."] ".$klaida->message;
                $Error['message'] = "[".$klaida->code."] ".$klaida->message;
                $Error['code'] = "TRG-U1099"; //t-transport RG-registracijos error
                $Error['group'] = "RG"; //RG siuntos registracijos error
                $this->addError ($Error);

            }
            var_dump($resperr);
        } else if($respArray->ShipmentResponse->Response->ResponseStatus->Code==1){//success

/* RESPONSE PVZ:

object(stdClass)[33]
  public 'ShipmentResponse' => 
    object(stdClass)[18]
      public 'Response' => 
        object(stdClass)[15]
          public 'ResponseStatus' => 
            object(stdClass)[13]
              public 'Code' => string '1' (length=1)
              public 'Description' => string 'Success' (length=7)
          public 'Alert' => 
            object(stdClass)[16]
              public 'Code' => string '120900' (length=6)
              public 'Description' => string 'User Id and Shipper Number combination is not qualified to receive negotiated rates' (length=83)
          public 'TransactionReference' => 
            object(stdClass)[17]
              public 'TransactionIdentifier' => string 'ciewgss118t4cPSltlkH1K' (length=22)
      public 'ShipmentResults' => 
        object(stdClass)[20]
          public 'Disclaimer' => 
            object(stdClass)[19]
              public 'Code' => string '01' (length=2)
              public 'Description' => string 'Taxes are included in the shipping cost and apply to the transportation charges but additional duties/taxes may apply and are not reflected in the total amount due.' (length=164)
          public 'ShipmentCharges' => 
            object(stdClass)[22]
              public 'TransportationCharges' => 
                object(stdClass)[21]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '109.13' (length=6)
              public 'ServiceOptionsCharges' => 
                object(stdClass)[23]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '0.00' (length=4)
              public 'TaxCharges' => 
                object(stdClass)[24]
                  public 'Type' => string 'PVM' (length=3)
                  public 'MonetaryValue' => string '22.92' (length=5)
              public 'TotalCharges' => 
                object(stdClass)[25]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '109.13' (length=6)
              public 'TotalChargesWithTaxes' => 
                object(stdClass)[26]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '132.05' (length=6)
          public 'RatingMethod' => string '01' (length=2)
          public 'BillableWeightCalculationMethod' => string '02' (length=2)
          public 'BillingWeight' => 
            object(stdClass)[28]
              public 'UnitOfMeasurement' => 
                object(stdClass)[27]
                  public 'Code' => string 'KGS' (length=3)
                  public 'Description' => string 'Kilograms' (length=9)
              public 'Weight' => string '3.5' (length=3)
          public 'ShipmentIdentificationNumber' => string '1Z976V1F6892020529' (length=18)
          public 'PackageResults' => 
            object(stdClass)[29]
              public 'TrackingNumber' => string '1Z976V1F6892020529' (length=18)
              public 'ServiceOptionsCharges' => 
                object(stdClass)[30]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '0.00' (length=4)
              public 'ShippingLabel' => 
                object(stdClass)[32]
                  public 'ImageFormat' => 
                    object(stdClass)[31]
                      public 'Code' => string 'GIF' (length=3)
                      public 'Description' => string 'GIF' (length=3)
                  public 'GraphicImage' => string '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'... (length=42108)
                  public 'HTMLImage' => string 'PCFET0NUWVBFIEhUTUwgUFVCTElDICItLy9JRVRGLy9EVEQgSFRNTCAzLjIvL0VOIj4KPGh0bWw+PGhlYWQ+PHRpdGxlPgpWaWV3L1ByaW50IExhYmVsPC90aXRsZT48bWV0YSBjaGFyc2V0PSJVVEYtOCI+PC9oZWFkPjxzdHlsZT4KICAgIC5zbWFsbF90ZXh0IHtmb250LXNpemU6IDgwJTt9CiAgICAubGFyZ2VfdGV4dCB7Zm9udC1zaXplOiAxMTUlO30KPC9zdHlsZT4KPGJvZHkgYmdjb2xvcj0iI0ZGRkZGRiI+CjxkaXYgY2xhc3M9Imluc3RydWN0aW9ucy1kaXYiPgo8dGFibGUgY2xhc3M9Imluc3RydWN0aW9ucy10YWJsZSIgbmFtZWJvcmRlcj0iMCIgY2VsbHBhZGRpbmc9IjAiIGNlbGxzcGFjaW5nPSIwIiB3aWR0aD0iNjAwIj48dHI+Cjx0ZCBoZWlnaHQ9IjQxMCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KPEIgY2xhc3M9ImxhcmdlX3RleHQiPlZpZXcvUHJpbnQgTGFiZWw8L0I+CiZuYnNwOzxicj4KJm5ic3A7PGJyPgo8b2wgY2xhc3M9InNtYWxsX3RleHQiPiA8bGk+PGI+UHJpbnQgdGhlIGxhYmVsOjwvYj4gJm5ic3A7ClNlbGVjdCBQcmludCBmcm9tIHRoZSBGaWxlIG1lbnUgaW4gdGhpcyBicm93c2VyIHdpbmRvdyB0byBwcmludCB0aGUgbGFiZWwgYmVsb3cuPGJyPjxicj48bGk+PGI+CkZvbGQgdGhlIHByaW50ZWQgbGFiZWwgYXQgdGhlIGRvdHRlZCBsaW5lLjwvYj4gJm5ic3A7ClBsYWNlIHRoZSBsYWJlbCBpbiBhIFVQUyBTaGlwcGluZyBQb3VjaC4gSWYgeW91IGRvIG5vdCBoYXZlIGEgcG91Y2gsIGFmZml4IHRoZSBmb2xkZWQgbGFiZWwgdXNpbmcgY2xlYXIgcGxhc3RpYyBzaGlwcGluZyB0YXBlIG92ZXIgdGhlIGVudGlyZSBsYWJlbC48YnI+PGJyPjxsaT48Yj5QaWNrdXAgYW5kIERyb3Atb2ZmPC9iPjx1bD48bGk+RGFpbHkgUGlja3VwIGN1c3RvbWVyczogSGF2ZSB5b3VyIHNoaXBtZW50KHMpIHJlYWR5IGZvciB0aGUgZHJpdmVyIGFzIHVzdWFsLiAgIDxsaT5UbyBTY2hlZHVsZSBhIFBpY2t1cCBvciB0byBmaW5kIGEgZHJvcC1vZmYgbG9jYXRpb24sIHNlbGVjdCB0aGUgUGlja3VwIG9yIERyb3Atb2ZmIGljb24gZnJvbSB0aGUgdG9vbCBiYXIuIDwvdWw+PC9vbD48L3RkPjwvdHI+PC90YWJsZT48dGFibGUgYm9yZGVyPSIwIiBjZWxscGFkZGluZz0iMCIgY2VsbHNwYWNpbmc9IjAiIHdpZHRoPSI2MDAiPgo8dHI+Cjx0ZCBjbGFzcz0ic21hbGxfdGV4dCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KJm5ic3A7Jm5ic3A7Jm5ic3A7CjxhIG5hbWU9ImZvbGRIZXJlIj5GT0xEIEhFUkU8L2E+PC90ZD4KPC90cj4KPHRyPgo8dGQgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj48aHI+CjwvdGQ+CjwvdHI+CjwvdGFibGU+Cgo8dGFibGU+Cjx0cj4KPHRkIGhlaWdodD0iMTAiPiZuYnNwOwo8L3RkPgo8L3RyPgo8L3RhYmxlPgoKPC9kaXY+Cjx0YWJsZSBib3JkZXI9IjAiIGNlbGxwYWRkaW5nPSIwIiBjZWxsc3BhY2luZz0iMCIgd2lkdGg9IjY1MCIgPjx0cj4KPHRkIGFsaWduPSJsZWZ0IiB2YWxpZ249InRvcCI+CjxJTUcgU1JDPSIuL2xhYmVsMVo5NzZWMUY2ODkyMDIwNTI5LmdpZiIgaGVpZ2h0PSIzOTIiIHdpZHRoPSI2NTEiPgo8L3RkPgo8L3RyPjwvdGFibGU+CjwvYm9keT4KPC9odG1sPgo=' (length=2140)






                  Kai lipdukai du tai juos deda i array!!!!!!!:

                  object(stdClass)[37]
  public 'ShipmentResponse' => 
    object(stdClass)[18]
      public 'Response' => 
        object(stdClass)[15]
          public 'ResponseStatus' => 
            object(stdClass)[13]
              public 'Code' => string '1' (length=1)
              public 'Description' => string 'Success' (length=7)
          public 'Alert' => 
            object(stdClass)[16]
              public 'Code' => string '120900' (length=6)
              public 'Description' => string 'User Id and Shipper Number combination is not qualified to receive negotiated rates' (length=83)
          public 'TransactionReference' => 
            object(stdClass)[17]
              public 'TransactionIdentifier' => string 'ciewgst118t4rwVhmF0drs' (length=22)
      public 'ShipmentResults' => 
        object(stdClass)[20]
          public 'Disclaimer' => 
            object(stdClass)[19]
              public 'Code' => string '01' (length=2)
              public 'Description' => string 'Taxes are included in the shipping cost and apply to the transportation charges but additional duties/taxes may apply and are not reflected in the total amount due.' (length=164)
          public 'ShipmentCharges' => 
            object(stdClass)[22]
              public 'TransportationCharges' => 
                object(stdClass)[21]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '162.48' (length=6)
              public 'ServiceOptionsCharges' => 
                object(stdClass)[23]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '0.00' (length=4)
              public 'TaxCharges' => 
                object(stdClass)[24]
                  public 'Type' => string 'PVM' (length=3)
                  public 'MonetaryValue' => string '34.12' (length=5)
              public 'TotalCharges' => 
                object(stdClass)[25]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '162.48' (length=6)
              public 'TotalChargesWithTaxes' => 
                object(stdClass)[26]
                  public 'CurrencyCode' => string 'EUR' (length=3)
                  public 'MonetaryValue' => string '196.60' (length=6)
          public 'RatingMethod' => string '01' (length=2)
          public 'BillableWeightCalculationMethod' => string '02' (length=2)
          public 'BillingWeight' => 
            object(stdClass)[28]
              public 'UnitOfMeasurement' => 
                object(stdClass)[27]
                  public 'Code' => string 'KGS' (length=3)
                  public 'Description' => string 'Kilograms' (length=9)
              public 'Weight' => string '8.5' (length=3)
          public 'ShipmentIdentificationNumber' => string '1Z976V1F6896977936' (length=18)
          public 'PackageResults' => 
            array (size=2)
              0 => 
                object(stdClass)[29]
                  public 'TrackingNumber' => string '1Z976V1F6896977936' (length=18)
                  public 'ServiceOptionsCharges' => 
                    object(stdClass)[30]
                      public 'CurrencyCode' => string 'EUR' (length=3)
                      public 'MonetaryValue' => string '0.00' (length=4)
                  public 'ShippingLabel' => 
                    object(stdClass)[32]
                      public 'ImageFormat' => 
                        object(stdClass)[31]
                          public 'Code' => string 'GIF' (length=3)
                          public 'Description' => string 'GIF' (length=3)
                      public 'GraphicImage' => string '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'... (length=42292)
                      public 'HTMLImage' => string 'PCFET0NUWVBFIEhUTUwgUFVCTElDICItLy9JRVRGLy9EVEQgSFRNTCAzLjIvL0VOIj4KPGh0bWw+PGhlYWQ+PHRpdGxlPgpWaWV3L1ByaW50IExhYmVsPC90aXRsZT48bWV0YSBjaGFyc2V0PSJVVEYtOCI+PC9oZWFkPjxzdHlsZT4KICAgIC5zbWFsbF90ZXh0IHtmb250LXNpemU6IDgwJTt9CiAgICAubGFyZ2VfdGV4dCB7Zm9udC1zaXplOiAxMTUlO30KPC9zdHlsZT4KPGJvZHkgYmdjb2xvcj0iI0ZGRkZGRiI+CjxkaXYgY2xhc3M9Imluc3RydWN0aW9ucy1kaXYiPgo8dGFibGUgY2xhc3M9Imluc3RydWN0aW9ucy10YWJsZSIgbmFtZWJvcmRlcj0iMCIgY2VsbHBhZGRpbmc9IjAiIGNlbGxzcGFjaW5nPSIwIiB3aWR0aD0iNjAwIj48dHI+Cjx0ZCBoZWlnaHQ9IjQxMCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KPEIgY2xhc3M9ImxhcmdlX3RleHQiPlZpZXcvUHJpbnQgTGFiZWw8L0I+CiZuYnNwOzxicj4KJm5ic3A7PGJyPgo8b2wgY2xhc3M9InNtYWxsX3RleHQiPiA8bGk+PGI+UHJpbnQgdGhlIGxhYmVsOjwvYj4gJm5ic3A7ClNlbGVjdCBQcmludCBmcm9tIHRoZSBGaWxlIG1lbnUgaW4gdGhpcyBicm93c2VyIHdpbmRvdyB0byBwcmludCB0aGUgbGFiZWwgYmVsb3cuPGJyPjxicj48bGk+PGI+CkZvbGQgdGhlIHByaW50ZWQgbGFiZWwgYXQgdGhlIGRvdHRlZCBsaW5lLjwvYj4gJm5ic3A7ClBsYWNlIHRoZSBsYWJlbCBpbiBhIFVQUyBTaGlwcGluZyBQb3VjaC4gSWYgeW91IGRvIG5vdCBoYXZlIGEgcG91Y2gsIGFmZml4IHRoZSBmb2xkZWQgbGFiZWwgdXNpbmcgY2xlYXIgcGxhc3RpYyBzaGlwcGluZyB0YXBlIG92ZXIgdGhlIGVudGlyZSBsYWJlbC48YnI+PGJyPjxsaT48Yj5QaWNrdXAgYW5kIERyb3Atb2ZmPC9iPjx1bD48bGk+RGFpbHkgUGlja3VwIGN1c3RvbWVyczogSGF2ZSB5b3VyIHNoaXBtZW50KHMpIHJlYWR5IGZvciB0aGUgZHJpdmVyIGFzIHVzdWFsLiAgIDxsaT5UbyBTY2hlZHVsZSBhIFBpY2t1cCBvciB0byBmaW5kIGEgZHJvcC1vZmYgbG9jYXRpb24sIHNlbGVjdCB0aGUgUGlja3VwIG9yIERyb3Atb2ZmIGljb24gZnJvbSB0aGUgdG9vbCBiYXIuIDwvdWw+PC9vbD48L3RkPjwvdHI+PC90YWJsZT48dGFibGUgYm9yZGVyPSIwIiBjZWxscGFkZGluZz0iMCIgY2VsbHNwYWNpbmc9IjAiIHdpZHRoPSI2MDAiPgo8dHI+Cjx0ZCBjbGFzcz0ic21hbGxfdGV4dCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KJm5ic3A7Jm5ic3A7Jm5ic3A7CjxhIG5hbWU9ImZvbGRIZXJlIj5GT0xEIEhFUkU8L2E+PC90ZD4KPC90cj4KPHRyPgo8dGQgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj48aHI+CjwvdGQ+CjwvdHI+CjwvdGFibGU+Cgo8dGFibGU+Cjx0cj4KPHRkIGhlaWdodD0iMTAiPiZuYnNwOwo8L3RkPgo8L3RyPgo8L3RhYmxlPgoKPC9kaXY+Cjx0YWJsZSBib3JkZXI9IjAiIGNlbGxwYWRkaW5nPSIwIiBjZWxsc3BhY2luZz0iMCIgd2lkdGg9IjY1MCIgPjx0cj4KPHRkIGFsaWduPSJsZWZ0IiB2YWxpZ249InRvcCI+CjxJTUcgU1JDPSIuL2xhYmVsMVo5NzZWMUY2ODk2OTc3OTM2LmdpZiIgaGVpZ2h0PSIzOTIiIHdpZHRoPSI2NTEiPgo8L3RkPgo8L3RyPjwvdGFibGU+CjwvYm9keT4KPC9odG1sPgo=' (length=2140)
              1 => 
                object(stdClass)[33]
                  public 'TrackingNumber' => string '1Z976V1F6896095746' (length=18)
                  public 'ServiceOptionsCharges' => 
                    object(stdClass)[34]
                      public 'CurrencyCode' => string 'EUR' (length=3)
                      public 'MonetaryValue' => string '0.00' (length=4)
                  public 'ShippingLabel' => 
                    object(stdClass)[36]
                      public 'ImageFormat' => 
                        object(stdClass)[35]
                          public 'Code' => string 'GIF' (length=3)
                          public 'Description' => string 'GIF' (length=3)
                      public 'GraphicImage' => string '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'... (length=42336)
                      public 'HTMLImage' => string 'PCFET0NUWVBFIEhUTUwgUFVCTElDICItLy9JRVRGLy9EVEQgSFRNTCAzLjIvL0VOIj4KPGh0bWw+PGhlYWQ+PHRpdGxlPgpWaWV3L1ByaW50IExhYmVsPC90aXRsZT48bWV0YSBjaGFyc2V0PSJVVEYtOCI+PC9oZWFkPjxzdHlsZT4KICAgIC5zbWFsbF90ZXh0IHtmb250LXNpemU6IDgwJTt9CiAgICAubGFyZ2VfdGV4dCB7Zm9udC1zaXplOiAxMTUlO30KPC9zdHlsZT4KPGJvZHkgYmdjb2xvcj0iI0ZGRkZGRiI+CjxkaXYgY2xhc3M9Imluc3RydWN0aW9ucy1kaXYiPgo8dGFibGUgY2xhc3M9Imluc3RydWN0aW9ucy10YWJsZSIgbmFtZWJvcmRlcj0iMCIgY2VsbHBhZGRpbmc9IjAiIGNlbGxzcGFjaW5nPSIwIiB3aWR0aD0iNjAwIj48dHI+Cjx0ZCBoZWlnaHQ9IjQxMCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KPEIgY2xhc3M9ImxhcmdlX3RleHQiPlZpZXcvUHJpbnQgTGFiZWw8L0I+CiZuYnNwOzxicj4KJm5ic3A7PGJyPgo8b2wgY2xhc3M9InNtYWxsX3RleHQiPiA8bGk+PGI+UHJpbnQgdGhlIGxhYmVsOjwvYj4gJm5ic3A7ClNlbGVjdCBQcmludCBmcm9tIHRoZSBGaWxlIG1lbnUgaW4gdGhpcyBicm93c2VyIHdpbmRvdyB0byBwcmludCB0aGUgbGFiZWwgYmVsb3cuPGJyPjxicj48bGk+PGI+CkZvbGQgdGhlIHByaW50ZWQgbGFiZWwgYXQgdGhlIGRvdHRlZCBsaW5lLjwvYj4gJm5ic3A7ClBsYWNlIHRoZSBsYWJlbCBpbiBhIFVQUyBTaGlwcGluZyBQb3VjaC4gSWYgeW91IGRvIG5vdCBoYXZlIGEgcG91Y2gsIGFmZml4IHRoZSBmb2xkZWQgbGFiZWwgdXNpbmcgY2xlYXIgcGxhc3RpYyBzaGlwcGluZyB0YXBlIG92ZXIgdGhlIGVudGlyZSBsYWJlbC48YnI+PGJyPjxsaT48Yj5QaWNrdXAgYW5kIERyb3Atb2ZmPC9iPjx1bD48bGk+RGFpbHkgUGlja3VwIGN1c3RvbWVyczogSGF2ZSB5b3VyIHNoaXBtZW50KHMpIHJlYWR5IGZvciB0aGUgZHJpdmVyIGFzIHVzdWFsLiAgIDxsaT5UbyBTY2hlZHVsZSBhIFBpY2t1cCBvciB0byBmaW5kIGEgZHJvcC1vZmYgbG9jYXRpb24sIHNlbGVjdCB0aGUgUGlja3VwIG9yIERyb3Atb2ZmIGljb24gZnJvbSB0aGUgdG9vbCBiYXIuIDwvdWw+PC9vbD48L3RkPjwvdHI+PC90YWJsZT48dGFibGUgYm9yZGVyPSIwIiBjZWxscGFkZGluZz0iMCIgY2VsbHNwYWNpbmc9IjAiIHdpZHRoPSI2MDAiPgo8dHI+Cjx0ZCBjbGFzcz0ic21hbGxfdGV4dCIgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj4KJm5ic3A7Jm5ic3A7Jm5ic3A7CjxhIG5hbWU9ImZvbGRIZXJlIj5GT0xEIEhFUkU8L2E+PC90ZD4KPC90cj4KPHRyPgo8dGQgYWxpZ249ImxlZnQiIHZhbGlnbj0idG9wIj48aHI+CjwvdGQ+CjwvdHI+CjwvdGFibGU+Cgo8dGFibGU+Cjx0cj4KPHRkIGhlaWdodD0iMTAiPiZuYnNwOwo8L3RkPgo8L3RyPgo8L3RhYmxlPgoKPC9kaXY+Cjx0YWJsZSBib3JkZXI9IjAiIGNlbGxwYWRkaW5nPSIwIiBjZWxsc3BhY2luZz0iMCIgd2lkdGg9IjY1MCIgPjx0cj4KPHRkIGFsaWduPSJsZWZ0IiB2YWxpZ249InRvcCI+CjxJTUcgU1JDPSIuL2xhYmVsMVo5NzZWMUY2ODk2MDk1NzQ2LmdpZiIgaGVpZ2h0PSIzOTIiIHdpZHRoPSI2NTEiPgo8L3RkPgo8L3RyPjwvdGFibGU+CjwvYm9keT4KPC9odG1sPgo=' (length=2140)




*/
            $this->RESPONSE_ERROR = "2";//uzregistravo, bet dar netikrinom lipduku, todel dabar 2... jeigu bus ir lipdukai, tai pasikeis i 1

            $this->XML_RESPONSE_COMMENT  = $respArray->ShipmentResponse->Response->Alert->Description;

            $this->RESPONSE_REQUEST_ID = $respArray->ShipmentResponse->Response->TransactionReference->TransactionIdentifier;

            $this->RESPONSE_PDF_X = $respArray->ShipmentResponse->ShipmentResults->PackageResults;

            $this->RESPONSE_ShipmentIdentificationNumber = $respArray->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber;

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
                    
                    /*
                    echo "
                        <a href = '".$BarcodeFile_html."' target='_blank'>file $BarcodeFile HTML</a><br>
                    ";
                    */

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





    //Kurjerio iskvietimas
    public function PickupCreationRequest($UPSSiuntosSiandienArray, $siandien, $Sandelys){
    
        $PickupDate = date ("Ymd");
        $PickupDateFull = date ("Y-m-d H:i:s");


    /*
    private $SenderName = "Aurika, UAB";
    private $SenderCompanyCode = "132878726";
    private $SenderCountryCode = "LT";
    private $SenderCity = "Kaunas";


    private $SenderAddressIDKEG = "AURIKAUAB";
    private $SenderAddressIDKPG = "AURIKAUAB1";
    private $SenderAddressID = '';

    private $SenderAddressKEG = "Taikos pr. 129A";
    private $SenderAddressKPG = "Chemijos g. 29F";
    private $SenderAddress = '';

    private $SenderPostCodeKEG = "51127";
    private $SenderPostCodeKPG = "51333";
    private $SenderPostCode = '';

    private $SenderContactPersonKEG = "AURIKA shipping department";//"Edita Kupčiūnienė";
    private $SenderContactPersonKPG = "AURIKA shipping department";//"Edita Kupčiūnienė";
    private $SenderContactPerson = '';

    private $SenderContactTelKEG = "+37068802736";
    private $SenderContactTelKPG = "+37068802736";
    private $SenderContactTel = '';

    private $SenderContactMailKEG = "transportas@aurika.lt";
    private $SenderContactMailKPG = "transportas@aurika.lt";
    private $SenderContactMail = '';

    private $SHIPPERDataSet = false;
    private $PICKUPDataSet = false;
    private $CONSIGNEEDataSet = false;
    private $DELIVERYDataSet = false;
    */

    if($Sandelys == 'KEG'){
        $SenderAddress = $this->SenderAddressKEG;
        $SenderPostCode = $this->SenderPostCodeKEG;
    }else if($Sandelys == 'KPG'){
        $SenderAddress = $this->SenderAddressKPG;
        $SenderPostCode = $this->SenderPostCodeKPG;
        $SenderContactTel = $this->SenderContactTelKPG;
    }else if($Sandelys == 'ETK' OR $Sandelys == 'ETK1'){
        $SenderAddress = $this->SenderAddressETK;
        $SenderPostCode = $this->SenderPostCodeETK;
        $SenderContactTel = $this->SenderContactTelETK;
    }

        $SenderContactTel = '68802736';
        $SenderContactTelExt = '+370';


        // KEG "ReadyTime": "1600",
        // KPG "ReadyTime": "1500",
        //"CloseTime": "2000",

        if($Sandelys=='KPG'){
            $ReadyTime = "1500";
        }else{
            $ReadyTime = "1600";
        }

        $JSON_ups = '
{
    "PickupCreationRequest": {
        "RatePickupIndicator": "N",
        "Shipper": {
            "Account": {
                "AccountNumber": "976V1F",
                "AccountCountryCode": "LT"
            }
        },
        "PickupDateInfo": {
            "CloseTime": "2000",
            "ReadyTime": "'.$ReadyTime.'",
            "PickupDate": "'.$PickupDate.'"
        },
        "PickupAddress": {
            "CompanyName": "'.$this->SenderName.'",
            "ContactName": "Shipping department",
            "AddressLine": "'.$SenderAddress.'",
            "Room": "",
            "Floor": "1",
            "City": "Kaunas",
            "StateProvince": "",
            "Urbanization": "",
            "PostalCode": "'.$SenderPostCode.'",
            "CountryCode": "LT",
            "ResidentialIndicator": "N",
            "PickupPoint": "Warehouse",
            "Phone": {
                "Number": "'.$SenderContactTel.'",
                "Extension": "'.$SenderContactTelExt.'"
            }
        },
        "AlternateAddressIndicator": "Y",
        "PickupPiece": [
    ';
        $pirmas = 1;
        foreach ($UPSSiuntosSiandienArray['Siuntos'] as $key => $value) {
            if($pirmas>1){
                $JSON_ups .= ', ';
            }



            /*
                ContainerCode 
                Valid values:
                + 01 = PACKAGE
                02 = UPS LETTER
                03 = PALLET
                Note: 03 is used for only WWEF services


                Service Codes
                001 UPS Next Day Air 
                002 UPS 2nd Day Air 
                003 UPS Ground 
                004 UPS Ground, UPS Standard 
                007 UPS Worldwide Express 
                008 UPS Worldwide Expedited 
                + 011 UPS Standard 
                012 UPS 3 Day Select 
                013 UPS Next Day Air Saver 
                014 UPS Next Day Air Early
                021 UPS Economy 
                031 UPS Basic 
                054 UPS Worldwide Express Plus 
                059 UPS 2nd Day Air A.M. 
                064 UPS Express NA1 
                +065 UPS Saver 
                071 UPS Worldwide Express Freight Midday
                074 UPS Express 12:00
                082 UPS Standard Today 
                083 UPS Today Dedicated Courier 
                084 UPS Intercity Today 
                085 UPS Today Express 
                086 UPS Today Express Saver 
                096 UPS Worldwide Express Freight

            */

            /* UPS sako, kad reikia naudoti 065 vietoje 011 kai siuntos Lirtuvoje */
            $JSON_ups .= '
                {
                    "ServiceCode": "011",
                    "Quantity": "'.$value['pKiekis'].'",
                    "DestinationCountryCode": "'.$value['SaliesKodas'].'",
                    "ContainerCode": "01"
                }
            ';
            $pirmas++;
        }//end foreach
    $JSON_ups .= '
        ],
        "TotalWeight": {
            "Weight": "'.$UPSSiuntosSiandienArray['SiuntuSvoriuSum'].'",
            "UnitOfMeasurement": "KGS"
        },
        "OverweightIndicator": "N",
        "PaymentMethod": "01"
    ';
    /*
    $JSON_ups .= '
        "SpecialInstruction": "Jias Test -----",
        "ReferenceNumber": "CreatePickupRefJia",
        "Notification": {
            "ConfirmationEmailAddress": "vholloway@ups.com",
            "UndeliverableEmailAddress": "vholloway@ups.com"
        },
        "CSR": {
            "ProfileId": "1 Q83 122",
            "ProfileCountryCode": "US"
        }
    ';
    */

    $JSON_ups .= '
    }
}
        ';    


var_dump($JSON_ups);


        $method='POST';
        //$url =  "https://onlinetools.ups.com/ship/v1707/pickups"; //(LIVE)
        //$url =  "https://wwwcie.ups.com//ship/v1707/pickups"; //(TEST)
        if($this->UPSKurjerioIskvietimas){
            $url = $this->UPSKurjerioIskvietimas;
        }else{
            $url = "";// bandant kreiptis i  API ismes errora
        }


        $header_data[]="Content-Type:application/json";
        $header_data[]="Accept:application/json";
        $header_data[]="Username:Aurika";
        //$header_data[]="Password:Siuntos0323";
        $header_data[]="Password:SSiuntos0413+"; //20220413 pasikeite
        $header_data[]="transId:1234567";
        $header_data[]="Accept:application/json";
        $header_data[]="AccessLicenseNumber:DD83A0C55C662D3D";        

        $resp = $this->CallAPI($method, $url, $header_data, $JSON_ups);


        echo"<br><br><br><hr>";
        var_dump($resp);
        echo"<br><br><br><hr>";
        //$response = file_get_contents('http://example.com/path/to/api/call?param1=5');
        $respArray = json_decode($resp);
        //$response = new SimpleXMLElement($response);
        var_dump($respArray);
        echo"<br><br><br><hr>";


        if($respArray->PickupCreationResponse->Response->ResponseStatus->Code == 1){
            $rezArray['error']='OK';
            $rezArray['actionMessage']='UPS kurjeris iškviestas sėkmingai.';

            $PickupCode = $respArray->PickupCreationResponse->PRN;

            //irasom i lentele, kad siandien kurjeris iskviestas tam tikram sandelyje jeigu tai ne demo request
            //if($this->mode=='live'){

                if($Sandelys == 'KEG'){
                    $wsql = 'UPDATE TOP (1) _TMS_numbers SET 
                                UPS_KurjerisKEG=\'Y\',
                                UPS_KurjerisKEGDate=\''.$PickupDateFull.'\',
                                UPS_KurjerisKEGCode=\''.$PickupCode.'\'
                            WHERE  row=\'1\' ;'
                    ;    
                }else if($Sandelys == 'KPG'){
                    $wsql = 'UPDATE TOP (1) _TMS_numbers SET 
                                UPS_KurjerisKPG=\'Y\',
                                UPS_KurjerisKPGDate=\''.$PickupDateFull.'\',
                                UPS_KurjerisKEGCode=\''.$PickupCode.'\'
                            WHERE  row=\'1\' ;'
                    ;    
                }else if($Sandelys == 'ETK' OR $Sandelys == 'ETK1'){
                    $wsql = 'UPDATE TOP (1) _TMS_numbers SET 
                                UPS_KurjerisETK=\'Y\',
                                UPS_KurjerisETKDate=\''.$PickupDateFull.'\',
                                UPS_KurjerisETKCode=\''.$PickupCode.'\'
                            WHERE  row=\'1\' ;'
                    ;    
                }//end elseif
                var_dump($wsql);

                $wmssql = DBMSSqlCERM::getInstance();
                $retSQL = $wmssql->execSql($wsql, 1);                    

           // }//end if


        }else{
            $rezArray['error']='NOTOK';
            $rezArray['actionMessage']="Klaida iškviečiant UPS kurjerį!\n" . $respArray->PickupCreationResponse->Response->Description;

        }

        //ar geras responsas
        /*
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
        */


        return $rezArray;

    }//end function






    /* siunciam XML UPSiui */
    private function connectSend__________(){

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
                $Error['message'] = "Gauta klaida iš SCHENKER: 500 Internal Server Error";
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
                        $Error['message'] = "Klaida registruojant siuntą: ".$errorComment;
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
    public function sendXML_________ ($param=array()){

        //FORMUOJAM XML

        //SIUNCIAM XML
        //$pavyko = false;//pradine reiksme
        if ($this->UPSXML_created){
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
