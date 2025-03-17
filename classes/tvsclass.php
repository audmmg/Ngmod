<?php
ob_start();

$script_path = dirname(__FILE__);
$root_path = dirname($script_path);

require_once($script_path . '/data.php');
require_once($root_path . '/config.php');
require_once($root_path . '/classes/xml2array.php');


if (@$_SESSION['debug']>0 || @Config::$DEBUG_MODE > 0 || true) {
    require_once($root_path . '/libs/FirePHPCore/FirePHP.class.php');
}

/**
 * Class TVS SHENKER
 *
 * @author  Arnoldas Ramonas
 *          20191030
 */
class tvsclass {

    public $mode = 'test'; //test/live

    public $address = array();

    //public $tvs = array();

    public $packs= array();
    public $PacksIsSet=false;

    public $errorArray = array();

    public $searchChar  = array('"', '\'', '<', '>', '&','&amp;amp;');
    public $replaceChar = array('&quot;', '&apos;', '&lt;', '&gt;', '&amp;', '&amp;');


    function __construct() {
        
        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

        //address
        $this->address['SHIPPER']['contactPerson']="";
        $this->address['SHIPPER']['contactPersonEmail']="";
        $this->address['SHIPPER']['contactPersonTel']="";
        $this->address['SHIPPER']['name1']="";
        $this->address['SHIPPER']['name2']="";
        $this->address['SHIPPER']['company_code']="";
        $this->address['SHIPPER']['customerAddressIdentifier']="";
        $this->address['SHIPPER']['email']="";
        $this->address['SHIPPER']['fax']="";
        $this->address['SHIPPER']['industry']="AUTOMOTIVE";
        $this->address['SHIPPER']['locationType']="PHYSICAL";
        $this->address['SHIPPER']['personType']="COMPANY"; // COMPANY, PERSON
        $this->address['SHIPPER']['mobilePhone']="";
        $this->address['SHIPPER']['phone']="";
        $this->address['SHIPPER']['poBox']="POBOX";
        $this->address['SHIPPER']['postalCode']="";
        $this->address['SHIPPER']['stateCode']="";
        $this->address['SHIPPER']['stateName']="";
        $this->address['SHIPPER']['preferredLanguage']="";
        $this->address['SHIPPER']['AddressId']="";
        $this->address['SHIPPER']['street1']="";
        $this->address['SHIPPER']['street2']="";
        $this->address['SHIPPER']['city']="";
        $this->address['SHIPPER']['countryCode']="LT";
        $this->address['SHIPPER']['type']="SHIPPER";
        $this->address['SHIPPER']['addressSet']=false;


        $this->address['CONSIGNEE']['contactPerson']="";
        $this->address['CONSIGNEE']['contactPersonEmail']="";
        $this->address['CONSIGNEE']['contactPersonTel']="";
        
        $this->address['CONSIGNEE']['name1']="";
        $this->address['CONSIGNEE']['name2']="";
        $this->address['CONSIGNEE']['company_code']="";
        $this->address['CONSIGNEE']['customerAddressIdentifier']="";
        $this->address['CONSIGNEE']['email']="";
        $this->address['CONSIGNEE']['fax']="";
        $this->address['CONSIGNEE']['industry']="AUTOMOTIVE";
        $this->address['CONSIGNEE']['locationType']="PHYSICAL";
        $this->address['CONSIGNEE']['mobilePhone']="";
        $this->address['CONSIGNEE']['personType']="COMPANY"; // COMPANY, PERSON
        $this->address['CONSIGNEE']['phone']="";
        $this->address['CONSIGNEE']['poBox']="POBOX";
        $this->address['CONSIGNEE']['postalCode']="";
        $this->address['CONSIGNEE']['stateCode']="";
        $this->address['CONSIGNEE']['stateName']="";
        $this->address['CONSIGNEE']['preferredLanguage']="";
        $this->address['CONSIGNEE']['AddressId']="";
        $this->address['CONSIGNEE']['street1']="";
        $this->address['CONSIGNEE']['street2']="";
        $this->address['CONSIGNEE']['city']="";
        $this->address['CONSIGNEE']['countryCode']="LT";
        $this->address['CONSIGNEE']['type']="CONSIGNEE";
        $this->address['CONSIGNEE']['addressSet']=false;
        



        //$this->tvs['shippingInformation']['packArray'] = array();
        //$this->tvs['shippingInformation']['grossWeight']='';
        //$this->tvs['shippingInformation']['volume']='';


        /* pakuotes struktura -> eis i pakuociu masyva $packs= array();*/
        /*
        $this->pack['packNr']='';
        $this->pack['length']='';
        $this->pack['width']='';
        $this->pack['height']='';
        $this->pack['volume']='';
        $this->pack['marksAndNumbers']='';
        $this->pack['packageType']='EP';
        $this->pack['pieces']='';
        $this->pack['stackable']='';
        */


    }//end function 




    public function setAddress ($SiuntaData, $addressType){


var_dump($SiuntaData);
//var_dump($this->sParam);

        $SiuntaData['type'] = $addressType;


        //jeigu is formos atejo kiti adreso duomenys nei DB, tai imam formos adresa (didesnis prioritetas)
        $SiuntaData['GavName'] = $SiuntaData['Gavejas'];



        $SiuntaData['PostKodas'] = $this->sParam['det_PostCode'];
        $SiuntaData['Delivery_street'] = $this->sParam['det_Street1'];
        $SiuntaData['street2'] = $this->sParam['det_Street2'];
        $SiuntaData['Miestas'] = $this->sParam['det_City'];
        $SiuntaData['SaliesKodas'] = $this->sParam['det_CountryCode'];

        $SiuntaData['Delivery_contact'] = $this->sParam['det_ContactPerson'];
        $SiuntaData['Delivery_contact_phone'] = $this->sParam['det_ContactPersonTel'];
        $SiuntaData['Delivery_contact_email'] = $this->sParam['det_ContactPersonMail'];

        if($this->sParam['Det_ArIPastomata']=='PICKUP'){//jeigu siunta i pastomata, tai keiciam koda
            $SiuntaData['ClientImKodas']=$this->sParam['Det_ClientCompanyCode'];
        }

//var_dump($SiuntaData);

        //tikrinam duomenis
        if ($SiuntaData){
            if (!$SiuntaData['Gavejas']){
                $Error['message'] = "Nenustatytas įmonės pavadinimas";
                $Error['code'] = "TAD-0001"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['PostKodas']){
                $Error['message'] = "Nenustatytas pašto kodas";
                $Error['code'] = "TAD-0002"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Delivery_street']){
                $Error['message'] = "Nenustatytas adresas";
                $Error['code'] = "TAD-0003"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['Miestas']){
                $Error['message'] = "Nenustatytas miestas";
                $Error['code'] = "TAD-0004"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['SaliesKodas']){
                $Error['message'] = "Nenustatytas šalies kodas";
                $Error['code'] = "TAD-0005"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }
            if (!$SiuntaData['type'] OR ($SiuntaData['type']!='SHIPPER' AND $SiuntaData['type']!='CONSIGNEE' AND $SiuntaData['type']!='DELIVERY' AND $SiuntaData['type']!='PICKUP')){
                $Error['message'] = "Nenustatytas siuntėjo ar gavėjo tipas (SHIPPER, CONSIGNEE, DELIVERY, PICKUP)";
                $Error['code'] = "TAD-0006"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }

            if (!$SiuntaData['Delivery_contact_phone'] AND !$SiuntaData['Delivery_phone'] ){ // BUTINAS VENIPAKUI, nebutinai SCHENKERIUI
                $Error['message'] = "Nenustatytas gavėjo telefono numeris.";
                $Error['code'] = "TAD-0007"; //t-transport AD-address data
                $Error['group'] = "AD"; //AD - address data error
                $this->addError ($Error);
            }



            $klaiduSk = $this->haveErrors ('AD');
            if($klaiduSk==0){
                //$SiuntaData['Gavejas'] = str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Gavejas']);
                $SiuntaData['Gavejas'] = (htmlspecialchars_decode($SiuntaData['Gavejas'],ENT_QUOTES) == $SiuntaData['Gavejas']) ? htmlspecialchars($SiuntaData['Gavejas'],ENT_QUOTES) : $SiuntaData['Gavejas'];

                //$SiuntaData['ClientName']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['ClientName']);
                $SiuntaData['ClientName'] = (htmlspecialchars_decode($SiuntaData['ClientName'],ENT_QUOTES) == $SiuntaData['ClientName']) ? htmlspecialchars($SiuntaData['ClientName'],ENT_QUOTES) : $SiuntaData['ClientName'];

                //$SiuntaData['Miestas']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Miestas']);
                $SiuntaData['Miestas'] = (htmlspecialchars_decode($SiuntaData['Miestas'],ENT_QUOTES) == $SiuntaData['Miestas']) ? htmlspecialchars($SiuntaData['Miestas'],ENT_QUOTES) : $SiuntaData['Miestas'];

                //$SiuntaData['Delivery_street']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_street']);
                $SiuntaData['Delivery_street'] = (htmlspecialchars_decode($SiuntaData['Delivery_street'],ENT_QUOTES) == $SiuntaData['Delivery_street']) ? htmlspecialchars($SiuntaData['Delivery_street'],ENT_QUOTES) : $SiuntaData['Delivery_street'];

                //$SiuntaData['street2']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['street2']);
                $SiuntaData['street2'] = (htmlspecialchars_decode($SiuntaData['street2'],ENT_QUOTES) == $SiuntaData['street2']) ? htmlspecialchars($SiuntaData['street2'],ENT_QUOTES) : $SiuntaData['street2'];

                //SiuntaData['Delivery_contact']=str_replace($this->searchChar, $this->replaceChar, $SiuntaData['Delivery_contact']);
                $SiuntaData['Delivery_contact'] = (htmlspecialchars_decode($SiuntaData['Delivery_contact'],ENT_QUOTES) == $SiuntaData['Delivery_contact']) ? htmlspecialchars($SiuntaData['Delivery_contact'],ENT_QUOTES) : $SiuntaData['Delivery_contact'];

                $this->address[$SiuntaData['type']]['name1']=$SiuntaData['Gavejas'];
                $this->address[$SiuntaData['type']]['name2']=$SiuntaData['ClientName'];
                $this->address[$SiuntaData['type']]['company_code']=$SiuntaData['ClientImKodas'];
                $this->address[$SiuntaData['type']]['countryCode']=$SiuntaData['SaliesKodas'];
                $this->address[$SiuntaData['type']]['city']=$SiuntaData['Miestas'];
                $this->address[$SiuntaData['type']]['street1']=$SiuntaData['Delivery_street'];
                $this->address[$SiuntaData['type']]['street2']=$SiuntaData['street2'];//!!!!!!!!!!!!nera gatves2
                $this->address[$SiuntaData['type']]['stateCode']=$SiuntaData['stateCode'];//!!!!!!!!!!  nera StateCode
                $this->address[$SiuntaData['type']]['stateName']=$SiuntaData['stateName'];//!!!!!!!!!!  nera StateName
                $this->address[$SiuntaData['type']]['postalCode']=$SiuntaData['PostKodas'];    

                //pilnas adresas
                if($SiuntaData['street2']){
                    $this->address[$SiuntaData['type']]['fullAddress']=$SiuntaData['Delivery_street'].' '.$SiuntaData['street2'].', '.$SiuntaData['Miestas'].', '.$SiuntaData['SaliesKodas'].', '.$SiuntaData['PostKodas'];    
                }else{
                    $this->address[$SiuntaData['type']]['fullAddress']=$SiuntaData['Delivery_street'].', '.$SiuntaData['Miestas'].', '.$SiuntaData['SaliesKodas'].', '.$SiuntaData['PostKodas'];    
                }


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
                $this->address[$SiuntaData['type']]['addressSet']=true;

               

                /*
                $this->address[$addressData['type']]['contactPerson']=$addressData['contactPerson'];
                $this->address[$addressData['type']]['contactPersonEmail']=$addressData['contactPersonEmail'];
                $this->address[$addressData['type']]['name1']=$addressData['name1'];
                $this->address[$addressData['type']]['name2']=$addressData['name2'];
                $this->address[$addressData['type']]['customerAddressIdentifier']=$addressData['customerAddressIdentifier'];
                $this->address[$addressData['type']]['email']=$addressData['email'];
                $this->address[$addressData['type']]['fax']=$addressData['fax'];
                $this->address[$addressData['type']]['industry']=$addressData['industry'];
                $this->address[$addressData['type']]['locationType']=$addressData['locationType'];
                $this->address[$addressData['type']]['mobilePhone']=$addressData['mobilePhone'];
                $this->address[$addressData['type']]['personType']=$addressData['personType']; // COMPANY, PERSON
                $this->address[$addressData['type']]['phone']=$addressData['phone'];
                $this->address[$addressData['type']]['poBox']=$addressData['poBox'];
                $this->address[$addressData['type']]['postalCode']=$addressData['postalCode'];
                $this->address[$addressData['type']]['stateCode']=$addressData['stateCode'];
                $this->address[$addressData['type']]['stateName']=$addressData['stateName'];
                $this->address[$addressData['type']]['preferredLanguage']=$addressData['preferredLanguage'];
                $this->address[$addressData['type']]['AddressId']=$addressData['AddressId'];
                $this->address[$addressData['type']]['street1']=$addressData['street'];
                $this->address[$addressData['type']]['street2']=$addressData['street2'];
                $this->address[$addressData['type']]['city']=$addressData['city'];
                $this->address[$addressData['type']]['countryCode']=$addressData['countryCode'];
                $this->address[$addressData['type']]['type']=$addressData['type'];
                $this->address[$addressData['type']]['addressSet']="Y";
                */

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





     public function setPackBasic ($SiuntaData){


        //tikrinam duomenis
        if ($SiuntaData){
            //echo "<br>CIA999";
            if (!$SiuntaData['SvorisSumR']){
                $this->PacksIsSet=false;
                $Error['message'] = "Nenurodytas suminis svoris.";
                $Error['code'] = "TPD-0007"; //t-transport AD-address data
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
                            $PaksArray['PACK'][$i]['Plotis'] = $pPlotis;
                            $PaksArray['PACK'][$i]['Ilgis'] = $pIlgis;
                            $PaksArray['PACK'][$i]['Aukstis'] = $pAukstis;
                            //$PaksArray['PACK'][$i]['Turis'] = $pPlotis/100*$pIlgis/100*$pAukstis/100*$pKiekis; //202301016  !!!! prie vienos paltes duomenu suskaiciuoja visu paleciu turi ir jis virsija limita kai daug paleciu - problema
                            $PaksArray['PACK'][$i]['Turis'] = $pPlotis/100*$pIlgis/100*$pAukstis/100;// cia nereikia dauginti is kiekio, nes kiekviena pakuote yra atskirai (PROBLEMA: kai siunciamos 6 ir daugiau paleciu, tai turis tada virsija limita, o turi imtis atskirai kiekvienos paletes)
                            $PaksArray['PACK'][$i]['Svoris'] = $pPakioSvoris;
                            $PaksArray['PACK'][$i]['Tipas'] = $pTipas;

                            $PaksArray['SUMKiekis'] += $pKiekis;//pakuociu/paleciu kiekis
                            $PaksArray['SUMPlotis'] += $pPlotis;
                            $PaksArray['SUMIlgis'] += $pIlgis;
                            $PaksArray['SUMAukstis'] += $pAukstis;
                            $PaksArray['SUMTuris'] += $PaksArray['PACK'][$i]['Turis'];
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
                            $Error['code'] = "TPD-0101"; //t-transport AD-address data
                            $Error['group'] = "PD"; //AD - address data error
                            $this->addError ($Error);

                        }//end else
                    }//end if
                }//end foreach

                if(!$PaksArray['SUMPalets']){
                    $PaksArray['SUMPalets']=0;
                }

                //rasom komenta apie pakuotes (VENIPAKUI)
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
                $Error['code'] = "TPD-0008"; //t-transport AD-address data
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
                $this->packs['SvorisSum']=$SiuntaData['SvorisSumR'];

                $this->packs['PacksArray']=$PaksArray['PACK'];
                $this->packs['SUMKiekis']=$PaksArray['SUMKiekis'];
                $this->packs['SUMPlotis']=$PaksArray['SUMPlotis'];
                $this->packs['SUMIlgis']=$PaksArray['SUMIlgis'];
                $this->packs['SUMAukstis']=$PaksArray['SUMAukstis'];
                $this->packs['SUMTuris']=$PaksArray['SUMTuris'];
                $this->packs['SUM_Spp']=$PaksArray['SUM_Spp'];

                $this->packs['SUMPalets']=$PaksArray['SUMPalets'];
                $this->packs['SUM_EUPalets']=$PaksArray['SUM_EUPalets'];
                $this->packs['SUM_NonEUPalets']=$PaksArray['SUM_NonEUPalets'];
                $this->packs['SUM_Boxes']=$PaksArray['SUM_Boxes'];
                $this->packs['SUM_Paks']=$PaksArray['SUM_Paks'];
                $this->packs['SUM_Rolls']=$PaksArray['SUM_Rolls'];



                $this->PacksIsSet=true;

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
                $Error['code'] = "TAD-1001"; //t-transport AD-address data
                $Error['group'] = "PD"; 
                //echo "<br>CIA888";
                $returnRez = false;
                
        }

        //priskiriam adresa




        return $returnRez; //boolen
    }//end function




    public function getInfoBySiuntaNr($SiuntaUID){

        $SiuntaUID = 46;
        $rez = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);

        
    }//end function




    /* ****************** ERROR manage function ****************** */

    //prideti klaida
    public function addError ($ErrorData){

        /*
            $ErrorData['message'];
            $ErrorData['code'];
            $ErrorData['group'];
        */
        $this->errorArray[$ErrorData['code']] = $ErrorData;
    }


    //ar turim klaidu
    public function haveErrors ($group = 'ALL'){
        $count = 0;
        if($this->errorArray){
            foreach ($this->errorArray as $key => $error) {
                if ($group == 'ALL' OR $group == $error['group']){
                    $count++;
                }//end if
            }//end foreach
        }//end if

        return $count;
    }//end function


    //isvalyti klaidas
    public function clearErrors (){
        $this->errorArray=array();
    }

    //grazinti klaidu masyva
    public function getErrorsArray (){
        return $this->errorArray;
    }

    //grazinti klaidas kaip HTML
    public function getErrorsAsHtml ($group = 'ALL'){
        $errorsHtml = "";
        if($this->errorArray){
            foreach ($this->errorArray as $key => $error) {
                if ($group == 'ALL' OR $group == $error['group']){
                    $errorsHtml .= "[".$error['code']."] ".$error['message']."\n";
                }//end if
            }//end foreach
        }

        echo "<hr>".var_dump($errorsHtml)."<hr>";
        return $errorsHtml;
    }




    public function tvsVarDump (){

        echo "<HR>";
        var_dump($this->addres);
        echo "<HR>";
        var_dump($this->tvs);
        echo "<HR>";

    }//end function

    
   
}//end class
