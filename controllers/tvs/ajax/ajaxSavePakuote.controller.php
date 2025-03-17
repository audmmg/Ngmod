<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");
require_once( '../../../classes/TVSconfig.php');



class ajaxSavePakuoteController extends controller {

/////////////////////////////////KINTAMIEJI/////////////////////////////////

///////////////////////////////KONSTRUKTORIUS///////////////////////////////
    function __construct($smId=0) {
        parent::__construct();
        parent::clearError(); 

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "modules/tvs/tvs.mod.php");
        $this->tvsMod = new tvs_mod();

    }//end function


    /////////////////////////////////// MOTININE FUNKCIJA ///////////////////////////////
    // motinine paleidziamoji funkcija
    public function run($param = array()) {

        $ErrorStatus = "OK";
        

        //Pasiimam duomenys is Ajax
        $svorisBRUTTO = trim($this->getVar('svorisBRUTTO'));
        
        $Det_PackingSlip = trim($this->getVar('Det_PackingSlip'));
        $PackingSlipArray = explode(",", $Det_PackingSlip);
        
        $Det_ShipmentDate = trim($this->getVar('Det_ShipmentDate'));
        $Det_Vezejas = trim($this->getVar('Det_Vezejas'));
        $Det_VezejasID = trim($this->getVar('Det_VezejasID'));
        $Det_KlientasID = trim($this->getVar('Det_KlientasID'));

        /* neutralumas */
        $Det_NeutralumasKod = trim($this->getVar('Det_NeutralumasKod'));

        /*Express */
        $Det_Express = trim($this->getVar('Det_Express'));

        /*gavejas */
        $Det_Gavejas = trim($this->getVar('Det_Gavejas'));
        $Det_GavejasID = trim($this->getVar('Det_GavejasID'));
        $Det_AdresasID = trim($this->getVar('Det_AdresasID'));
        /* $Det_Adresas = trim($this->getVar('Det_Adresas'));*/
        $Det_PristatAdrFull = trim($this->getVar('Det_PristatAdrFull'));
        $Det_Street = trim($this->getVar('Det_Street'));
        $Det_Miestas = trim($this->getVar('Det_Miestas'));
        $Det_SaliesKodas = trim($this->getVar('Det_SaliesKodas'));
        $Det_PostKodas = trim($this->getVar('Det_PostKodas'));
        $Det_Delivery_email = trim($this->getVar('Det_Delivery_email'));
        $Det_Delivery_phone = trim($this->getVar('Det_Delivery_phone'));
        $Det_Delivery_contact = trim($this->getVar('Det_Delivery_contact'));
        $Det_Delivery_contact_email = trim($this->getVar('Det_Delivery_contact_email'));
        $Det_Delivery_contact_phone = trim($this->getVar('Det_Delivery_contact_phone'));

        /*siuntejas */
        $Det_SiuntejoID = trim($this->getVar('Det_SiuntejoID'));
        $Det_SiuntejoName = trim($this->getVar('Det_SiuntejoName'));
        $Det_SiuntejoAdresasID = trim($this->getVar('Det_SiuntejoAdresasID'));
        $Det_SiuntejoAdrF = trim($this->getVar('Det_SiuntejoAdrF'));
        $Det_SiuntejoStreet = trim($this->getVar('Det_SiuntejoStreet'));
        $Det_SiuntejoMiestas = trim($this->getVar('Det_SiuntejoMiestas'));
        $Det_SiuntejoSaliesKodas = trim($this->getVar('Det_SiuntejoSaliesKodas'));
        $Det_SiuntejoPostKodas = trim($this->getVar('Det_SiuntejoPostKodas'));
        $Det_Siuntejo_email = trim($this->getVar('Det_Siuntejo_email'));
        $Det_Siuntejo_phone = trim($this->getVar('Det_Siuntejo_phone'));
        $Det_Siuntejo_contact = trim($this->getVar('Det_Siuntejo_contact'));
        $Det_Siuntejo_contact_email = trim($this->getVar('Det_Siuntejo_contact_email'));
        $Det_Siuntejo_contact_phone = trim($this->getVar('Det_Siuntejo_contact_phone'));

        /*dileris */
        $Det_DilerioID = trim($this->getVar('Det_DilerioID'));
        $Det_DilerioName = trim($this->getVar('Det_DilerioName'));
        $Det_DilerioAdresasID = trim($this->getVar('Det_DilerioAdresasID'));
        $Det_DilerioAdrF = trim($this->getVar('Det_DilerioAdrF'));
        $Det_DilerioStreet = trim($this->getVar('Det_DilerioStreet'));
        $Det_DilerioMiestas = trim($this->getVar('Det_DilerioMiestas'));
        $Det_DilerioSaliesKodas = trim($this->getVar('Det_DilerioSaliesKodas'));
        $Det_DilerioPostKodas = trim($this->getVar('Det_DilerioPostKodas'));
        $Det_Dilerio_email = trim($this->getVar('Det_Dilerio_email'));
        $Det_Dilerio_phone = trim($this->getVar('Det_Dilerio_phone'));
        $Det_Dilerio_contact = trim($this->getVar('Det_Dilerio_contact'));
        $Det_Dilerio_contact_email = trim($this->getVar('Det_Dilerio_contact_email'));
        $Det_Dilerio_contact_phone = trim($this->getVar('Det_Dilerio_contact_phone'));

        
        $pakuotes = trim($this->getVar('pakuotes'));
        $Det_Gamyba = trim($this->getVar('Det_Gamyba'));
        
        $Det_Packing_slip_comment = trim($this->getVar('Det_Packing_slip_comment'));
        $Det_DeliveryComment = trim($this->getVar('Det_DeliveryComment'));

        $Det_VezejasSelected = trim($this->getVar('Det_VezejasSelected')); // STABVENIPAK; STABSHENKER; SNESIUSTI, NaN
        $sVP_ManifestKEGKPG = trim($this->getVar('sVP_ManifestKEGKPG')); //detVP_ManifestKPG, detVP_ManifestKEG
        $sVP_Express = trim($this->getVar('sVP_Express'));
        
        $sVP_4Rankos = trim($this->getVar('sVP_4Rankos'));
        $sVPIsvData = trim($this->getVar('sVPIsvData'));
        $sVPIsvLaikasNuo = trim($this->getVar('sVPIsvLaikasNuo'));
        $sVPIsvLaikasIki = trim($this->getVar('sVPIsvLaikasIki'));
        $sVP_PristatytiIki = trim($this->getVar('sVP_PristatytiIki'));

        $sSCH_SandelysKEGKPG = trim($this->getVar('sSCH_SandelysKEGKPG'));
        $sSCH_PristatytiIki = trim($this->getVar('sSCH_PristatytiIki'));
        $sSCH_Premium = trim($this->getVar('sSCH_Premium'));
        $sSCH_Lift = trim($this->getVar('sSCH_Lift'));
        //$sSCH_4Rankos = trim($this->getVar('sSCH_4Rankos'));
        $sSCH_liftas = trim($this->getVar('sSCH_liftas'));
        $sSCH_IsvData = trim($this->getVar('sSCH_IsvData'));
        $sSCH_IsvTimeFrom = trim($this->getVar('sSCH_IsvTimeFrom'));
        $sSCH_IsvTimeTo = trim($this->getVar('sSCH_IsvTimeTo'));

        $s_PristatytiIki = trim($this->getVar('Det_PristatytiIki'));

        $sUPS_SandelysKEGKPG = trim($this->getVar('sUPS_SandelysKEGKPG'));
        $sUPS_ServiceID = trim($this->getVar('sUPS_ServiceID'));
        $sUPS_IsvData = trim($this->getVar('sUPS_IsvData'));

        $sACE_SandelysKEGKPG = trim($this->getVar('sACE_SandelysKEGKPG'));
        $sACE_ServiceID = trim($this->getVar('sACE_ServiceID'));
        $sACE_Lifts = trim($this->getVar('sACE_Lifts'));
        $sACE_IsvDatas = trim($this->getVar('sACE_IsvData'));

        $sCAT_SandelysKEGKPG = trim($this->getVar('sCAT_SandelysKEGKPG'));
        $sCAT_IsvDatas = trim($this->getVar('sCAT_IsvData'));

        $Det_ArIPastomata = trim($this->getVar('Det_ArIPastomata'));
        $Det_ClientCompanyCode = trim($this->getVar('Det_ClientCompanyCode'));



        $PakArray = array();
        if($PackingSlipArray){
            //foreach ($PackingSlipArray as $key => $PSlip) {
                //if($PSlip){

                    $PakArray['PSlipStr'] = $Det_PackingSlip;
                    $PakArray['PSlipArray'] = $PackingSlipArray;
                    $PakArray['svorisBRUTTO'] = $svorisBRUTTO;
                    $PakArray['Det_ShipmentDate'] = $Det_ShipmentDate;
                    $PakArray['Det_Vezejas'] = $Det_Vezejas;
                    $PakArray['Det_VezejasID'] = $Det_VezejasID;
                    $PakArray['Det_KlientasID'] = $Det_KlientasID;

                    /* NEUTRALUMAS */
                    
                    $PakArray['Det_NeutralumasKod'] = $Det_NeutralumasKod;

                    /*Express*/
                    $PakArray['Det_Express'] = $Det_Express;

                    if($Det_VezejasSelected=='STABSHENKER'){
                        $PakArray['PristatytiIki'] = $sSCH_PristatytiIki;
                    }elseif($Det_VezejasSelected=='STABVENIPAK'){
                        if($sVP_PristatytiIki=='10:00'){
                            $PakArray['PristatytiIki'] = 10;
                        }elseif($sVP_PristatytiIki=='12:00'){
                            $PakArray['PristatytiIki'] = 12;
                        }else{
                            $PakArray['PristatytiIki'] = 0;
                        }
                    }elseif($Det_VezejasSelected=='STABUPS'){
                        $PakArray['PristatytiIki'] = $s_PristatytiIki;
                    }elseif($Det_VezejasSelected=='STABACE'){
                        if($sACE_ServiceID=='aceSpeed10'){
                            $PakArray['PristatytiIki'] = 10;
                        }elseif($sACE_ServiceID=='aceSpeed12'){
                            $PakArray['PristatytiIki'] = 12;
                        }else{
                            $PakArray['PristatytiIki'] = 0;
                        }
                    }else{
                        $PakArray['PristatytiIki'] = $s_PristatytiIki;
                    }
                    if($PakArray['PristatytiIki']!=10 OR $PakArray['PristatytiIki']!=12){
                        $PakArray['PristatytiIki'] = 0;
                    }

                    /* gavejas */
                    $PakArray['Det_Gavejas'] = $Det_Gavejas;
                    $PakArray['Det_GavejasID'] = $Det_GavejasID;
                    $PakArray['Det_AdresasID'] = $Det_AdresasID;
                    /*$PakArray['Det_Adresas'] = $Det_Adresas;*/
                    $PakArray['Det_PristatAdrFull'] = $Det_PristatAdrFull;
                    $PakArray['Det_Street'] = $Det_Street;
                    $PakArray['Det_Miestas'] = $Det_Miestas;
                    $PakArray['Det_PostKodas'] = $Det_PostKodas;
                    $PakArray['Det_SaliesKodas'] = $Det_SaliesKodas;
                    $PakArray['Det_Delivery_email'] = $Det_Delivery_email;
                    $PakArray['Det_Delivery_phone'] = $Det_Delivery_phone;
                    $PakArray['Det_Delivery_contact'] = $Det_Delivery_contact;
                    $PakArray['Det_Delivery_contact_email'] = $Det_Delivery_contact_email;
                    $PakArray['Det_Delivery_contact_phone'] = $Det_Delivery_contact_phone;

                    /* siuntejas */
                    $PakArray['Det_SiuntejoID'] = $Det_SiuntejoID;
                    $PakArray['Det_SiuntejoName'] = $Det_SiuntejoName;
                    $PakArray['Det_SiuntejoAdresasID'] = $Det_SiuntejoAdresasID;
                    $PakArray['Det_SiuntejoAdrF'] = $Det_SiuntejoAdrF;
                    $PakArray['Det_SiuntejoStreet'] = $Det_SiuntejoStreet;
                    $PakArray['Det_SiuntejoMiestas'] = $Det_SiuntejoMiestas;
                    $PakArray['Det_SiuntejoSaliesKodas'] = $Det_SiuntejoSaliesKodas;
                    $PakArray['Det_SiuntejoPostKodas'] = $Det_SiuntejoPostKodas;
                    $PakArray['Det_Siuntejo_email'] = $Det_Siuntejo_email;
                    $PakArray['Det_Siuntejo_phone'] = $Det_Siuntejo_phone;
                    $PakArray['Det_Siuntejo_contact'] = $Det_Siuntejo_contact;
                    $PakArray['Det_Siuntejo_contact_email'] = $Det_Siuntejo_contact_email;
                    $PakArray['Det_Siuntejo_contact_phone'] = $Det_Siuntejo_contact_phone;

                    /* dileris */
                    $PakArray['Det_DilerioID'] = $Det_DilerioID;
                    $PakArray['Det_DilerioName'] = $Det_DilerioName;
                    $PakArray['Det_DilerioAdresasID'] = $Det_DilerioAdresasID;
                    $PakArray['Det_DilerioAdrF'] = $Det_DilerioAdrF;
                    $PakArray['Det_DilerioStreet'] = $Det_DilerioStreet;
                    $PakArray['Det_DilerioMiestas'] = $Det_DilerioMiestas;
                    $PakArray['Det_DilerioSaliesKodas'] = $Det_DilerioSaliesKodas;
                    $PakArray['Det_DilerioPostKodas'] = $Det_DilerioPostKodas;
                    $PakArray['Det_Dilerio_email'] = $Det_Dilerio_email;
                    $PakArray['Det_Dilerio_phone'] = $Det_Dilerio_phone;
                    $PakArray['Det_Dilerio_contact'] = $Det_Dilerio_contact;
                    $PakArray['Det_Dilerio_contact_email'] = $Det_Dilerio_contact_email;
                    $PakArray['Det_Dilerio_contact_phone'] = $Det_Dilerio_contact_phone;


                    $PakArray['pakuotes'] = $pakuotes;
                    $PakArray['Det_Gamyba'] = $Det_Gamyba;

                    $PakArray['Det_Packing_slip_comment'] = $Det_Packing_slip_comment;
                    $PakArray['Det_DeliveryComment'] = $Det_DeliveryComment;

                    /* ar i pastomata */
                    $PakArray['Det_ArIPastomata'] = $Det_ArIPastomata;
                    $PakArray['Det_ClientCompanyCode'] = $Det_ClientCompanyCode;

                //}
            //}//foreach
        }//end if

        //!!!!!! DEBUG
        $this->var_dump($PakArray, "PakArray<hr>$SiuntaUID");//-----------------DEBUG


        //Tikrinti ar gerai ivestos pakuotes, ar nera klaidu
        $paksIsOK = $this->tvsMod->checkPacks($PakArray['pakuotes']);

        //!!!!!! DEBUG
        $this->var_dump($paksIsOK, "paksIsOK<hr>");//-----------------DEBUG

        //!!!!!! DEBUG
        $this->var_dump($PakArray['svorisBRUTTO'], "PakArray['svorisBRUTTO']<hr>");//-----------------DEBUG

        //tikrinam procentini skirtuma tarp sum pakuociu svoriu ir bendro ivesto svorio (ar teisingai suvesti svoriai prie pakuociu)
        if($paksIsOK['SUMSVORIS'] > 0){
            $svorisProc = round(abs(1-($paksIsOK['SUMSVORIS']/$PakArray['svorisBRUTTO']*1))*100);
        }

        //!!!!!! DEBUG
        $this->var_dump($svorisProc, "svorisProc<hr>");//-----------------DEBUG


        if($paksIsOK['SUMSVORIS']<=0 OR $svorisProc>10){//jeigu nera sum svorio per pakuotes arba skirtumas daugiau kaip 10 proc, tai klaida
            $paksIsOK['YRA'] = 'S'; //klaida svoriuose ... skirtumas daugiau kaip 10%
        }

    if ($paksIsOK['YRA'] == 'N'){


/* ************************** SAUGOM DUOMENYS I DB ******************************************* */
        $rezTmp=$this->tvsMod->savePakuote($PakArray);
        $rez = $rezTmp['OK'];
        $SiuntaUID = $rezTmp['SiuntaID'];

        //!!!!!! DEBUG
        $this->var_dump($rezTmp, "*****rezTmp<hr>");//-----------------DEBUG


        /* 2022104 - Papildomas tikrinimas po saugojimo (kad pagauti kas chitina su Beno Strumskio telefono numeriu irasydamas ji kaip gavejo tel Nr, beda ta, kad paskui benui skambina vezejas kaip siuntos gavejui, bet jo numeris ten neturi buti. Siunta uzregistruojam, bet siusti vezejui informacijos neleidziam) */
        //echo " $$$$$$$$$$$$$4$$$$$$$ ".$PakArray['Det_Delivery_contact_phone']."@@@@@@@@@@@@@@@".$PakArray['Det_Delivery_contact_phone']."@@@@@@@@<br>";
        if((strpos($PakArray['Det_Delivery_contact_phone'], '72445')) OR (strpos($PakArray['Det_Delivery_contact_phone'], '72445')) ){//jeigu yra irasyta Beno Strumskio tel nr dalis tai stabdom siuntima vezejui
            $rez = 'NOTOK';
            $this->tvsMod->addError("Nurodytas neteisingas gavėjo telefono numeris. Beno tel nr.");
            //echo "<br>**********BENO NUMERIS ***********<Br>";
        }else{//end if
            //echo "<br>**********Geras kontakto tel nr***********<Br>";
        }



        //$rez=='TESTAVIMUI'; //20250130 TIK TESTAVIMUI, disablinti visa sia eilute po testavimo
/* ******************************* REGISTRUOJAM SIUNTA PAS VEZEJA PO SAUGOJIMO ******************************** */
        //$neregistruojamiKlientaiArray = array ('101736', '105371', '104717', '100741','106488','105665', '101736', '105950', '104925', '101536', '101747', '103348', '100899', '100888', '104717', '100941', '104104');
        //20250312 - pajungiant CAT vezeja is auksciau esamo saraso pasalinam IntersnackPL(101747), kad galima butu ji automatiskai registruoti per CAT
        $neregistruojamiKlientaiArray = array ('101736', '105371', '104717', '100741','106488','105665', '101736', '105950', '104925', '101536', '103348', '100899', '100888', '104717', '100941', '104104');
        if($rez=='OK' AND $SiuntaUID AND !in_array($PakArray['Det_KlientasID'], $neregistruojamiKlientaiArray)){

            //Bandom uzregistruoti siunta
            //**********************************************************************************************************************


                    //nusiskaitom issaugotos siuntos duomenys is TVS lenteles
                    //$SiuntaDataTmp = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);
                    if($Det_Gamyba == 'ETK'){
                        $SiuntaDataTmp = $this->tvsMod->getSiuntaDuomToTranspETK($SiuntaUID);
                    }else{
                        $SiuntaDataTmp = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);
                    }


                    $SiuntaData = $SiuntaDataTmp['Duom'];

                    //!!!!!! DEBUG
                    $this->var_dump($SiuntaData, "SiuntaData55555<hr>$SiuntaUID");//-----------------DEBUG

                    $ErrorStatus = "OK";
                    $TranspID = $Det_Vezejas;
                    //$SiuntaUID = $this->getVar('SiuntaUID');

                    $sParam['sSvorisVisoBrutto']= $svorisBRUTTO;

                    //VENIPAK
                    $sParam['sVP_ManifestKEGKPG']= $sVP_ManifestKEGKPG;
                    $sParam['sVP_Express']= $sVP_Express;
                    $sParam['sVP_PristatytiIki']= $sVP_PristatytiIki; //$this->getVar('sVP_PristatytiIki'); /* 10:00 12:00 ,...*/
                    $sParam['sVP_4Rankos']= $sVP_4Rankos;
                    $sParam['sVPIsvData'] = $this->getVar('sVPIsvData');
                    $sParam['sVPIsvLaikasNuo'] = $this->getVar('sVPIsvLaikasNuo');
                    $sParam['sVPIsvLaikasIki'] = $this->getVar('sVPIsvLaikasIki');

                    //SCHENKER
                    $sParam['sSCH_SandelysKEGKPG']= $sSCH_SandelysKEGKPG;
                    $sParam['sSCH_PristatytiIki']= $sSCH_PristatytiIki;
                    $sParam['sSCH_Premium']= $sSCH_Premium;
                    $sParam['sSCH_Lift']= $sSCH_Lift;
                    $sParam['sSCH_liftas']= $sSCH_liftas;
                    $sParam['sSCH_IsvData']= $sSCH_IsvData;
                    $sParam['sSCH_IsvTimeFrom']= $sSCH_IsvTimeFrom;
                    $sParam['sSCH_IsvTimeTo']= $sSCH_IsvTimeTo;

                    //UPS
                    $sParam['sUPS_SandelysKEGKPG']= $sUPS_SandelysKEGKPG;
                    $sParam['sUPS_ServiceID']= $sUPS_ServiceID;
                    $sParam['sUPS_IsvData']= $sUPS_IsvData;

                    //ACE
                    $sParam['sACE_SandelysKEGKPG']= $sACE_SandelysKEGKPG;
                    $sParam['sACE_ServiceID']= $sACE_ServiceID;
                    $sParam['sACE_Lifts']= $sACE_Lifts;
                    $sParam['sACE_IsvData']= $sACE_IsvData;

                    //CAT
                    $sParam['sCAT_SandelysKEGKPG']= $sCAT_SandelysKEGKPG;
                    $sParam['sCAT_IsvData']= $sCAT_IsvData;


                    $sParam['det_CargoDescription'] = $this->getVar('Det_VezejoPastaba');

                    /* NEUTRALUMAS */
                    $sParam['NeutralumasKod'] = $SiuntaData['NeutralumasKod'];

                    /* i pastomata */
                    $sParam['Det_ArIPastomata'] = $SiuntaData['ArIPastomata'];
                    $sParam['Det_ClientCompanyCode'] = $SiuntaData['ClientImKodas'];
                    

                    /* GAVEJAS */
                    $sParam['det_Gavejas'] = $SiuntaData['Gavejas'];
                    $sParam['det_AdresasID'] = $SiuntaData['AdresasID'];
                    $sParam['det_Street1'] = $SiuntaData['Delivery_street'];
                    //$sParam['det_Street2'] = $this->getVar('det_Street2');
                    //$sParam['det_Region'] = $SiuntaData['Delivery_street'];
                    $sParam['det_City'] = $SiuntaData['Miestas'];;
                    $sParam['det_CountryCode'] = $SiuntaData['SaliesKodas'];
                    $sParam['det_PostCode'] = $SiuntaData['PostKodas'];
                    $sParam['det_FullAddress'] = $SiuntaData['Adresas'];
                    $sParam['det_ContactPerson'] = $SiuntaData['Delivery_contact'];
                    if($SiuntaData['Delivery_contact_email']){
                        $sParam['det_ContactPersonMail'] = $SiuntaData['Delivery_contact_email'];
                    }else{
                        $sParam['det_ContactPersonMail'] = $SiuntaData['Delivery_email'];
                    }
                    if($SiuntaData['Delivery_contact_phone']){
                        $sParam['det_ContactPersonTel'] = $SiuntaData['Delivery_contact_phone'];
                    }else{
                        $sParam['det_ContactPersonTel'] = $SiuntaData['Delivery_phone'];
                    }

                    /* SIUNTEJAS */
                    $sParam['SiuntejoName'] = $SiuntaData['SiuntejoName'];
                    $sParam['SiuntejoID'] = $SiuntaData['SiuntejoID'];
                    $sParam['SiuntejoStreet'] = $SiuntaData['SiuntejoStreet'];
                    $sParam['SiuntejoMiestas'] = $SiuntaData['SiuntejoMiestas'];;
                    $sParam['SiuntejoSaliesKodas'] = $SiuntaData['SiuntejoSaliesKodas'];
                    $sParam['SiuntejoPostKodas'] = $SiuntaData['SiuntejoPostKodas'];
                    $sParam['SiuntejoAdrF'] = $SiuntaData['SiuntejoAdrF'];
                    $sParam['Siuntejo_contact'] = $SiuntaData['Siuntejo_contact'];
                    if($SiuntaData['Siuntejo_contact_email']){
                        $sParam['Siuntejo_contact_email'] = $SiuntaData['Siuntejo_contact_email'];
                    }else{
                        $sParam['Siuntejo_contact_email'] = $SiuntaData['Siuntejo_email'];
                    }
                    if($SiuntaData['Siuntejo_contact_phone']){
                        $sParam['Siuntejo_contact_phone'] = $SiuntaData['Siuntejo_contact_phone'];
                    }else{
                        $sParam['Siuntejo_contact_phone'] = $SiuntaData['Siuntejo_phone'];
                    }

                    /* DILERIS */
                    $sParam['DilerioName'] = $SiuntaData['DilerioName'];
                    $sParam['DilerioAdresasID'] = $SiuntaData['DilerioAdresasID'];
                    $sParam['DilerioStreet'] = $SiuntaData['DilerioStreet'];
                    $sParam['DilerioMiestas'] = $SiuntaData['DilerioMiestas'];;
                    $sParam['DilerioSaliesKodas'] = $SiuntaData['DilerioSaliesKodas'];
                    $sParam['DilerioPostKodas'] = $SiuntaData['DilerioPostKodas'];
                    $sParam['DilerioAdrF'] = $SiuntaData['DilerioAdrF'];
                    $sParam['Dilerio_contact'] = $SiuntaData['Dilerio_contact'];
                    if($SiuntaData['Dilerio_contact_email']){
                        $sParam['Dilerio_contact_email'] = $SiuntaData['Dilerio_contact_email'];
                    }else{
                        $sParam['Dilerio_contact_email'] = $SiuntaData['Dilerio_email'];
                    }
                    if($SiuntaData['Dilerio_contact_phone']){
                        $sParam['Dilerio_contact_phone'] = $SiuntaData['Dilerio_contact_phone'];
                    }else{
                        $sParam['Dilerio_contact_phone'] = $SiuntaData['Dilerio_phone'];
                    }



                    $sParam['det_IsvAtsakingas'] = $SiuntaData['OPER']['user_name']; //$this->getVar('det_IsvAtsakingas');
                    $sParam['det_IsvAtsakingasTel'] = $SiuntaData['OPER']['user_Mob'];//$this->getVar('det_IsvAtsakingasTel');
                    $sParam['det_IsvAtsakingasEmail'] = $SiuntaData['OPER']['user_Email'];//$this->getVar('det_IsvAtsakingasEmail');
                    


        //!!!!!! DEBUG
        $this->var_dump($sParam, "sParam<hr>$SiuntaUID");//-----------------DEBUG


                    if($SiuntaData['SiuntosBusena']<1){// tikrinam ar dar neregistruota pas vezeja

                            //nusiskaitom duomenys apie siunta
                            if($SiuntaUID){
                                //$PackData = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);
                            

                            
                                //pagal vezeja, formuojam XML ir siunciam
                                //STABVENIPAK; STABSHENKER; SNESIUSTI, NaN

                                //!!!!!! DEBUG
                                //$this->var_dump($Det_VezejasSelected, "Det_VezejasSelected<hr>$SiuntaUID");//-----------------DEBUG

                                switch ($Det_VezejasSelected) {
                                    case 'SNESIUSTI':

                                            //registruojam tik siunta, pakuociu neregistruojam... jos bus uzregistruotos is transporto sistemos (Edita)
                                    
                                            $rezData['SIUNTAOK']='NOTOK';
                                            $rezData['SIUNTAMSG']=' Siunta NEUŽREGISTRUOTA jokio vežėjo sistemoje.';
                                            $rezData['Duom']=array();
                                        
                                        break;
                                    case 'STABSHENKER':
/* ************************************************************** SCHENKER ************************************************************************ */                                    

                                        $root_path = COMMON::getRootFolder();
                                        require_once ($root_path . "classes/tvsschenker.php");
                                        $this->tvsschenker = new tvsschenker();
                                        //tikrinam ar neivyko klaidu sukuriant objekta
                                        if($this->tvsschenker->haveErrors()>0){
                                            $createOK['OK'] = false;
                                            //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                                        }else{//end if
                                            $createOK['OK'] = true;
                                            //$createOK['ERROR'] = '';


                                            //$SiuntaUID='59';
                                            $this->tvsschenker->setSiuntaData($SiuntaUID, $sParam);

                                            //var_dump($this->tvsschenker);

                                            if($this->tvsschenker->DataIsSet===true){
                                                /*jeigu DataIsSet===true, tai tada true ir visi kiti:
                                                    $this->SHIPPERDataSet===true
                                                    $this->CONSIGNEEDataSet===true
                                                    $this->PacksIsSet === true
                                                */

                                                $this->tvsschenker->generateSCHXML();

                                                //echo "<br><br><hr>";
                                                //var_dump($rezz);
                                                //var_dump($this->tvsschenker->SCHXML_created);
                                                //var_dump($this->tvsschenker->SCHXML);
                                                //var_dump($this->tvsschenker->errorArray);

                                                if($this->tvsschenker->SCHXML_created === true){
                                                    //jeigu sukurem XML tai siunciam ji i SCHENKERI
                                                    $this->tvsschenker->sendXML();
                                                    //jeigu pavyko tai $this->RESPONSE_ERROR == 1, jeigu dalinai(be pdf) tai =2 ir nepavuko tai =3
                                                    // tai tikrinsim zemiau
                                                }else{
                                                    //echo "--- NO CREATE XML ---";
                                                }

                                            }else{//end if
                                                //nedarom nieko
                                                //echo "--- NO SET DATA ---";
                                            }


                                                if($this->tvsschenker->haveErrors()>0){
                                                   //echo $this->tvsschenker->getErrorsAsHtml ('ALL');
                                                }//end if

                                                //ziurim ar visas procesas pavyko ir, jeigu reikia, isvedam klaidas
                                                // 1- jeigu viskas OK, 2-jeigu duomenys nusiunte, bet PDF nera, 3-jeigu nenusiunte ir pdf nera
                                                if($this->tvsschenker->RESPONSE_ERROR == 1 OR $this->tvsschenker->RESPONSE_ERROR == 2){


                                                    //echo "<br>SIUNTA UZREGISTRUOTA<br>";

                                                    // RUOSIAM DUOMENYS DB UPDATE apie siuntos registracija pas vezeja
                                                    //$rezData['SIUNTAOK']='OK';
                                                    $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                                    $rezData['Duom']['SiuntuNr'] = $this->tvsschenker->SiuntaData['PAKS']['PacksArray'];
                                                    $rezData['Duom']['SiuntuNrSCH'] = $this->tvsschenker->RESPONSE_BOOKING_ID;
                                                    $rezData['Duom']['HisAction'] = 'IDSCHENKER';
                                                    $rezData['Duom']['VezejasReal'] = 'SCHENKER';
                                                    $rezData['Duom']['ManifestID'] = '';
                                                    $rezData['Duom']['SiuntaPDF'] = $this->tvsschenker->RESPONSE_PDF;

                                                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                                                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                                                    

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    

                                                    $toDB['ManifestID'] = '';
                                                    $toDB['SendetXML'] = $this->tvsschenker->SCHXML;
                                                    $toDB['ResponseXML'] = $this->tvsschenker->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsschenker->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsschenker->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsschenker->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsschenker->XML_result_send_user;
                                                    $toDB['SiuntaPDF'] = $this->tvsschenker->RESPONSE_PDF;

                                                    $error = $this->tvsschenker->getErrorsAsHtml ('ALL');
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDSCHENKER';
                                                    $toDB['SiuntuNr'] = $this->tvsschenker->SiuntaData['PAKS']['PacksArray'];
                                                    $toDB['SiuntuNrSCH'] = $this->tvsschenker->RESPONSE_BOOKING_ID;
                                                    $toDB['SiuntuDocNr'] = $this->tvsschenker->RESPONSE_REQUEST_ID;
                                                    $toDB['Comment'] = '';
                                                    
                                                    $toDB['KrovinysReg']='Y'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsschenker->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "SCHENKER";
                                                    $toDB['VezejoSiuntosNr'] = $this->tvsschenker->RESPONSE_BOOKING_ID; // vezejo numeris 
                                                    //$toDB['VezejoSiuntosNr'] = $this->tvsschenker->tvs['reference']['number'];//musu numeris pas vazeja

                                                    $toDB['Manifest'] = '';
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;

                                                    $toDB['PacksArrayFull'] = $this->tvsschenker->packs['PacksArray'];

                                                    /* *****SURASOM REZ I DB ******* */
                                                    //echo "<hr>";
                                                    //var_dump($toDB);

                                                    if($this->tvsschenker->RESPONSE_ERROR == 1){
                                                        $rezData['SIUNTAOK']='OK';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje.';
                                                        //$rezData['Duom']=$toDB;
                                                    }elseif($this->tvsschenker->RESPONSE_ERROR == 2){
                                                        $rezData['SIUNTAOK']='OK1';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje. Tačiau LIPDUKAS NESUSIKŪRĖ';
                                                        //$rezData['Duom']='';//$toDB;
                                                    }


                                                }else{

                                                    $RegErrorStr = $this->tvsschenker->getErrorsAsHtml();

                                                    //$rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['Duom']['SiuntuNr'] = "";
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    $error = $this->tvsschenker->getErrorsAsHtml ('ALL');
                                                    //var_dump($error);

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    $toDB['SendetXML'] = $this->tvsschenker->SCHXML;
                                                    $toDB['ResponseXML'] = $this->tvsschenker->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsschenker->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsschenker->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsschenker->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsschenker->XML_result_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDSCHENKER';
                                                    $toDB['SiuntuNr'] = $this->tvsschenker->
                                                    $toDB['Comment'] = '';

                                                    $toDB['KrovinysReg']='N'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsschenker->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='1';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "SCHENKER";
                                                    $toDB['VezejoSiuntosNr'] = "";
                                                    
                                                    $toDB['Manifest'] ='';
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;

                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['SIUNTAMSG']=$RegErrorStr;
                                                    //$rezData['Duom']=array();

                                                }



                                                /* *****SURASOM REZ I DB ******* 
                                                    Papildom TMS_Siuntos
                                                    surasom pakuotes i TMS_Pak
                                                    irasom i TMS_His
                                                */
                                                $dbRez = $this->tvsMod->saveTvsTranspReg($toDB); 

                                                //echo "<hr>".$dbRez;

                                                if($dbRez!='OK'){
                                                    $SaveErrorsStr = $this->tvsMod->getErrorArrayAsStr();
                                                    // ????????? AR REIKIA SAKYTI, KAD NEPAVYKO UZREGISTRUOTI JEIGU NEPAVYKO UPDATINTI DUOMENU DB BET REALIAI UZSIREGISTRAVO PAS VEZEJA???
                                                    // ??? $rezData['SIUNTAOK']='NOTOK';
                                                    // ??? $rezData['SIUNTAMSG']=$RegErrorStr;
                                                    // ??? //$rezData['DUOM']=array();

                                                }



                                        }//end else (nera erroru)

/* ************************************************************** /SCHENKER ************************************************************************ */                                    
                                        break;
                                    case 'STABVENIPAK':
/* ************************************************************** VENIPAK ************************************************************************ */                                    
                                        //echo "<br>*****V0****";

                                        $root_path = COMMON::getRootFolder();
                                        require_once ($root_path . "classes/tvsvenipak.php");
                                        $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);

                                        //echo "<br>*****V1****";
                                        //tikrinam ar neivyko klaidu sukuriant objekta
                                        if($this->tvsvenipak->haveErrors()>0){
                                            $createOK['OK'] = false;
                                            $createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                                            echo "<br>*****V2****<br>";
                                            echo  $this->tvsvenipak->getErrorsAsHtml ();
                                        }else{//end if
                                            $createOK['OK'] = true;
                                            //$createOK['ERROR'] = '';
                                            //echo "<br>*****V3****";
                                        }

                                        if ($createOK['OK']===true){
                                                //echo "<br>*****V4****";
                                                /* KROVINIO UZSAKYMAS */
                                                $action = 'KR'; //KR-krovinio registracija, KI-kurjerio iskvietimas, LS-lipduko spausdinimas
                                                $param=array();
                                                $param['sVP_PristatytiIki']=$sParam['sVP_PristatytiIki'];
                                                $param['sVP_Express']=$sParam['sVP_Express'];
                                                $param['sVP_4Rankos']= $sParam['sVP_4Rankos'];
                                                $param['det_CargoDescription']=$sParam['det_CargoDescription'];
                                                $param['Det_ArIPastomata']=$sParam['Det_ArIPastomata'];
                                              
                                                var_dump($param);

                                                $KR_rez = $this->tvsvenipak->run($action, $param);


                                                if($KR_rez===true){

                                                    //echo "<br>*****V5****";
                                                    $rezData['SIUNTAOK']='OK';
                                                    $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                                    $rezData['Duom']['SiuntuNr'] = $this->tvsvenipak->rezKR_SiuntuNr;
                                                    $rezData['Duom']['HisAction'] = 'IDVENIPAK';
                                                    $rezData['Duom']['VezejasReal'] = 'VENIPAK';
                                                    $rezData['Duom']['ManifestID'] = $this->tvsvenipak->manifest;
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    //var_dump($rezData);
                                                    /*
                                                    if($sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                                                        $rezData['Duom']['ManifestID'] = $this->tvsvenipak->manifestKEG;
                                                    }elseif($sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                                                        $rezData['Duom']['ManifestID'] = $this->tvsvenipak->manifestKPG;
                                                    }
                                                    */
                                                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                                                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                                                    $error = $this->tvsvenipak->getErrorsAsHtml ('ALL');

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    ;

                                                    $toDB['ManifestID'] = $this->tvsvenipak->manifest;
                                                    /*
                                                    if($sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                                                        $toDB['ManifestID'] = $this->tvsvenipak->manifestKEG;
                                                    }elseif($sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                                                        $toDB['ManifestID'] = $this->tvsvenipak->manifestKPG;
                                                    }
                                                    */

                                                    $toDB['SendetXML'] = $this->tvsvenipak->XML_KR;
                                                    $toDB['ResponseXML'] = $this->tvsvenipak->XML_KR_result;
                                                    $toDB['SendetXMLTime'] = $this->tvsvenipak->XML_KR_send_time;
                                                    $toDB['SendetXMLOK'] = 1;
                                                    $toDB['SendetXMLUserUID'] = $this->tvsvenipak->XML_KR_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsvenipak->XML_KR_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDVENIPAK';
                                                    $toDB['SiuntuNr'] = $this->tvsvenipak->rezKR_SiuntuNr;
                                                    $toDB['Comment'] = '';
                                                    
                                                    $toDB['KrovinysReg']='Y'; 
                                                    //$toDB['KurjerisReg']='Y'; // (Y/N);
                                                    $toDB['SiuntaUzregistruota']=$this->tvsvenipak->XML_KR_send_time;
                                                    //$toDB['VezejasUzsakyta']=$this->tvsvenipak->XML_KI_send_time;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    //$toDB['DocLabel']="/img/0001.pdf"; // siuntos label'iai
                                                    $toDB['VezejasReal'] = "VENIPAK";
                                                    $toDB['VezejoSiuntosNr'] = $this->tvsvenipak->shipment_code;
                                                    

                                                    $toDB['Manifest'] = $this->tvsvenipak->manifest;
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;
                                                    /*
                                                    if($sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                                                        $toDB['Manifest'] = $this->tvsvenipak->manifestKEG;
                                                    }elseif($sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                                                        $toDB['Manifest'] = $this->tvsvenipak->manifestKPG;
                                                    }
                                                    */


                                                    $toDB['PacksArrayFull'] = $this->tvsvenipak->packs['PacksArrayFull'];

                                                    /* *****SURASOM REZ I DB ******* */
                                                    //echo "<hr>";
                                                    //var_dump($toDB);




/* --------------------------------
array (size=20)
  'actTip' => string 'SIUNTA' (length=6)
  'SiuntaUID' => string '30' (length=2)
  'ManifestID' => string '57943200226061' (length=14)
  'SendetXML' => string '
        <?xml version="1.0" encoding="UTF-8"?>
        <description type="1">
            <manifest title="57943200226061">
                <shipment>
                    <consignee>
                        <name>Polipaks SIA </name>
                        <company_code></company_code>
                        <country>LV</country>
                        <city>Riga</city>
                        <address>Malkalni, Vetras </address>
                        <post_code>2167</post_code>
                        <contact_person>Evita Shakele</contact_person>
                        <contact_tel>+ (371) 67 785 745</contact_tel>
                        <contact_email>evita.shakele@polipaks.com</contact_email>
                    </consignee>
                    <sender>
                        <name>Aurika, UAB</name>
                        <company_code>132878726</company_code>
                        <country>LT</country>
                        <city>Kaunas</city>
                        <address>Taikos pr. 129A</address>
                        <post_code>51127</post_code>
                        <contact_person>Edita Kupčiūnienė</contact_person>
                        <contact_tel>+37068802736</contact_tel>
                        <contact_email>transportas@aurika.lt</contact_email>
                    </sender>
                    <attribute>
                        <shipment_code>S0030</shipment_code>
        <delivery_type>nwd10</delivery_type>
                    <delivery_mode>1</delivery_mode>
                        
                        <return_doc>0</return_doc>
                        <return_doc_consignee>
                            <name>Aurika, UAB</name>
                            <company_code>132878726</company_code>
                            <country>LT</country>
                            <city>Kaunas</city>
                            <address>Taikos pr. 129A</address>
                            <post_code>51127</post_code>
                            <contact_person>Edita Kupčiūnienė</contact_person>
                            <contact_tel>+37068802736</contact_tel>
                        </return_doc_consignee>
                        <cod>0</cod>
                        <cod_type>EUR</cod_type>
                        <doc_no>PS346129</doc_no>
                        <comment_door_code> </comment_door_code>
                        <comment_office_no> </comment_office_no>
                        <comment_warehous_no> </comment_warehous_no>
                        <comment_call>1</comment_call>
                    </attribute>
        
                        <pack>
                            <pack_no>V57943E0001282</pack_no>
                            <doc_no>PS346129_01</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001283</pack_no>
                            <doc_no>PS346129_02</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001284</pack_no>
                            <doc_no>PS346129_03</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001285</pack_no>
                            <doc_no>PS346129_04</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001286</pack_no>
                            <doc_no>PS346129_05</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001287</pack_no>
                            <doc_no>PS346129_06</doc_no>
                            <weight>10</weight>
                            <volume>0.108</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001288</pack_no>
                            <doc_no>PS346129_07</doc_no>
                            <weight>10</weight>
                            <volume>0.027</volume>
                        </pack>
                    
                        <pack>
                            <pack_no>V57943E0001289</pack_no>
                            <doc_no>PS346129_08</doc_no>
                            <weight>10</weight>
                            <volume>0.015625</volume>
                        </pack>
                    
                </shipment>
            </manifest>
        </description>
        ' (length=5233)
  'ResponseXML' => string '<?xml version="1.0" encoding="UTF-8"?>
  <answer type="ok">
    <text>V57943E0001282</text>
<text>V57943E0001283</text>
<text>V57943E0001284</text>
<text>V57943E0001285</text>
<text>V57943E0001286</text>
<text>V57943E0001287</text>
<text>V57943E0001288</text>
<text>V57943E0001289</text>
</answer>' (length=297)
  'SendetXMLTime' => string '2020-02-26 14:15:09' (length=19)
  'SendetXMLOK' => int 1
  'SendetXMLUserUID' => string '501' (length=3)
  'SendetXMLUser' => string 'Arnoldas Ramonas' (length=16)
  'ActionErrors' => string '' (length=0)
  'HisGroup' => string 'SIUNTA_REG' (length=10)
  'HisAction' => string 'IDVENIPAK' (length=9)
  'SiuntuNr' => 
    array (size=8)
      0 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001282' (length=14)
      1 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001283' (length=14)
      2 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001284' (length=14)
      3 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001285' (length=14)
      4 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001286' (length=14)
      5 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001287' (length=14)
      6 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001288' (length=14)
      7 => 
        array (size=1)
          'KR_SiuntaNr' => string 'V57943E0001289' (length=14)
  'Comment' => string '' (length=0)
  'KrovinysReg' => string 'Y' (length=1)
  'SiuntaUzregistruota' => string '2020-02-26 14:15:09' (length=19)
  'SiuntosBusena' => string '2' (length=1)
  'VezejasReal' => string 'VENIPAK' (length=7)
  'Manifest' => string '57943200226061' (length=14)
  'PacksArrayFull' => 
    array (size=8)
      'V57943E0001282' => 
        array (size=8)
          'pack_no' => string 'V57943E0001282' (length=14)
          'pack_doc_no' => string 'PS346129_01' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001283' => 
        array (size=8)
          'pack_no' => string 'V57943E0001283' (length=14)
          'pack_doc_no' => string 'PS346129_02' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001284' => 
        array (size=8)
          'pack_no' => string 'V57943E0001284' (length=14)
          'pack_doc_no' => string 'PS346129_03' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001285' => 
        array (size=8)
          'pack_no' => string 'V57943E0001285' (length=14)
          'pack_doc_no' => string 'PS346129_04' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001286' => 
        array (size=8)
          'pack_no' => string 'V57943E0001286' (length=14)
          'pack_doc_no' => string 'PS346129_05' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001287' => 
        array (size=8)
          'pack_no' => string 'V57943E0001287' (length=14)
          'pack_doc_no' => string 'PS346129_06' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.108
          'Plotis' => string '20' (length=2)
          'Ilgis' => string '20' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001288' => 
        array (size=8)
          'pack_no' => string 'V57943E0001288' (length=14)
          'pack_doc_no' => string 'PS346129_07' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.027
          'Plotis' => string '30' (length=2)
          'Ilgis' => string '30' (length=2)
          'Aukstis' => string '30' (length=2)
          'Tipas' => string 'PK' (length=2)
      'V57943E0001289' => 
        array (size=8)
          'pack_no' => string 'V57943E0001289' (length=14)
          'pack_doc_no' => string 'PS346129_08' (length=11)
          'Svoris' => string '10' (length=2)
          'Turis' => float 0.015625
          'Plotis' => string '25' (length=2)
          'Ilgis' => string '25' (length=2)
          'Aukstis' => string '25' (length=2)
          'Tipas' => string 'PK' (length=2)


          ----------------------------------- */


                                                    $rezData['SIUNTAOK']='OK';
                                                    $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje.';
                                                    //$rezData['Duom']=''; //$toDB;


                                                }else{

                                                    echo "<br>*****V6****";
                                                    $RegErrorArray = $this->tvsvenipak->getErrorsAsHtml();

                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['Duom']['SiuntuNr'] = "";
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    $error = $this->tvsvenipak->getErrorsAsHtml ('ALL');
                                                    //var_dump($error);

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    $toDB['SendetXML'] = $this->tvsvenipak->XML_KR;
                                                    $toDB['ResponseXML'] = $this->tvsvenipak->XML_KR_result;
                                                    $toDB['SendetXMLTime'] = $this->tvsvenipak->XML_KR_send_time;
                                                    $toDB['SendetXMLOK'] = 0;
                                                    $toDB['SendetXMLUserUID'] = $this->tvsvenipak->XML_KR_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsvenipak->XML_KR_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDVENIPAK';
                                                    $toDB['SiuntuNr'] = '';
                                                    $toDB['Comment'] = '';

                                                    $toDB['KrovinysReg']=''; 
                                                    //$toDB['KurjerisReg']='';//nekeiciam
                                                    $toDB['SiuntaUzregistruota']=$this->tvsvenipak->XML_KI_send_time;
                                                    //$toDB['VezejasUzsakyta']=$this->tvsvenipak->XML_KI_send_time;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    //$toDB['DocLabel']="/img/0001.pdf"; // siuntos label'iai
                                                    $toDB['VezejasReal'] = "VENIPAK";
                                                    $toDB['VezejoSiuntosNr'] = "";
                                                    
                                                    $toDB['Manifest'] = $this->tvsvenipak->manifest;
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;
                                                    /*
                                                    if($sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                                                        $toDB['Manifest'] = $this->tvsvenipak->manifestKEG;
                                                    }elseif($sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                                                        $toDB['Manifest'] = $this->tvsvenipak->manifestKPG;
                                                    }
                                                    */




                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['SIUNTAMSG']=$RegErrorArray;
                                                    //$rezData['Duom']=array();

                                                }



                                                /* *****SURASOM REZ I TVS DB ******* */
                                                $dbRez = $this->tvsMod->saveTvsTranspReg($toDB);

                                                /* jei siunta is NAV, tai koreguojam Navisiono DB, kad siunta issiusta */
                                                // if($Det_Gamyba == 'ETK' AND $toDB['LiveDemo']=='live'){
                                                if($Det_Gamyba == 'ETK'){
                                                    // $toDB['LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;
                                                    $dbRez = $this->tvsMod->saveTvsTranspRegNavision($toDB, $PakArray['PSlipArray']);
                                                }else{
                                                    echo "****///****///*** $Det_Gamyba **** ".$toDB['LiveDemo']."****<br><br>";
                                                }

                                                //echo "<hr>".$dbRez;

                                                if($dbRez!='OK'){
                                                    $SaveErrorsStr = $this->tvsMod->getErrorArrayAsStr();
                                                }
                                        }else{
                                            echo "<br>*****V7****";
                                            $rezData['SIUNTAOK']='NOTOK';
                                            $rezData['SIUNTAMSG']=' Siunta NEUŽREGISTRUOTA vežėjo sistemoje. ' . $createOK['ERROR'];
                                            $rezData['Duom']=array();
                                        }

/* ************************************************************** /VENIPAK ************************************************************************ */                                    
                                        break;

                                    case 'STABUPS':
/* ************************************************************** UPS ************************************************************************ */                                    
                                        //echo "<br>*****V0****";

                                        $root_path = COMMON::getRootFolder();
                                        require_once ($root_path . "classes/tvsups.php");
                                        $this->tvsups = new tvsups('test', $SiuntaUID, $sParam);

                                        //tikrinam ar neivyko klaidu sukuriant objekta
                                        if($this->tvsups->haveErrors()>0){
                                            $createOK['OK'] = false;
                                            //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                                        }else{//end if
                                            $createOK['OK'] = true;
                                            //$createOK['ERROR'] = '';


                                            //$SiuntaUID='59';
                                            $this->tvsups->setSiuntaData($SiuntaUID, $sParam);

                                            //var_dump($this->tvsups);

                                            if($this->tvsups->DataIsSet===true){
                                                /*jeigu DataIsSet===true, tai tada true ir visi kiti:
                                                    $this->SHIPPERDataSet===true
                                                    $this->CONSIGNEEDataSet===true
                                                    $this->PacksIsSet === true
                                                */

                                                $this->tvsups->generateUPSXML();

                                                //echo "<br><br><hr>";
                                                //var_dump($rezz);
                                                //var_dump($this->tvsups->SCHXML_created);
                                                //var_dump($this->tvsups->SCHXML);
                                                //var_dump($this->tvsups->errorArray);

                                                if($this->tvsups->UPSXML_created === true){
                                                    //jeigu sukurem XML tai siunciam ji i SCHENKERI
                                                    $this->tvsups->sendXML($SiuntaData);
                                                    //jeigu pavyko tai $this->RESPONSE_ERROR == 1, jeigu dalinai(be pdf) tai =2 ir nepavuko tai =3
                                                    // tai tikrinsim zemiau
                                                }else{
                                                    //echo "--- NO CREATE XML ---";
                                                }

                                            }else{//end if
                                                //nedarom nieko
                                                //echo "--- NO SET DATA ---";
                                            }




                                            /* ******************************** UPS RESPONSE APDOROJIMAS ********************************** */




                                                if($this->tvsups->haveErrors()>0){
                                                   //echo $this->tvsups->getErrorsAsHtml ('ALL');
                                                }//end if

                                                //ziurim ar visas procesas pavyko ir, jeigu reikia, isvedam klaidas
                                                // 1- jeigu viskas OK, 2-jeigu duomenys nuiunte, bet PDF nera, 3-jeigu nenusiunte ir pdf nera
                                                if($this->tvsups->RESPONSE_ERROR == 1 OR $this->tvsups->RESPONSE_ERROR == 2){


                                                    echo "<br>SIUNTA UZREGISTRUOTA-".$this->tvsups->RESPONSE_ERROR."----<br>";

                                                    // RUOSIAM DUOMENYS DB UPDATE apie siuntos registracija pas vezeja
                                                    //$rezData['SIUNTAOK']='OK';
                                                    $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                                    $rezData['Duom']['SiuntuNr'] = $this->tvsups->packs['PacksArray'];//$this->tvsups->SiuntaData['PAKS']['PacksArray'];
                                                    $rezData['Duom']['SiuntuNrUPS'] = $this->tvsups->RESPONSE_BOOKING_ID;
                                                    $rezData['Duom']['HisAction'] = 'IDUPS';
                                                    $rezData['Duom']['VezejasReal'] = 'UPS';
                                                    $rezData['Duom']['ManifestID'] = '';
                                                    //TODO nera bendro visu lipduku pdf, reikia sukurti pdf faila is GIF
                                                    $rezData['Duom']['SiuntaPDF'] = '';//$this->tvsups->RESPONSE_PDF;

                                                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                                                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                                                    

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    

                                                    $toDB['ManifestID'] = '';
                                                    $toDB['SendetXML'] = $this->tvsups->UPSXML;
                                                    $toDB['ResponseXML'] = $this->tvsups->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsups->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsups->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsups->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsups->XML_result_send_user;
                                                    //TODO nera bendro visu lipduku pdf, reikia sukurti pdf faila is GIF
                                                    $toDB['SiuntaPDF'] = '';//$this->tvsups->RESPONSE_PDF;

                                                    $error = $this->tvsups->getErrorsAsHtml ('ALL');
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDUPS';
                                                    $toDB['SiuntuNr'] = $this->tvsups->packs['PacksArray'];
                                                    $toDB['SiuntuNrUPS'] = $this->tvsups->RESPONSE_BOOKING_ID;
                                                    $toDB['SiuntuDocNr'] = $this->tvsups->RESPONSE_REQUEST_ID;
                                                    $toDB['Comment'] = '';
                                                    
                                                    $toDB['KrovinysReg']='Y'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsups->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "UPS";
                                                    $toDB['VezejoSiuntosNr'] = $this->tvsups->RESPONSE_BOOKING_ID; // vezejo numeris 
                                                    //$toDB['VezejoSiuntosNr'] = $this->tvsups->tvs['reference']['number'];//musu numeris pas vazeja

                                                    $toDB['Manifest'] = '';
                                                    $toDB['TrackingNr'] = $this->tvsups->RESPONSE_ShipmentIdentificationNumber; 
                                                    $toDB['Service'] = $sParam['sUPS_ServiceID'];
                                                    $toDB['LiveDemo'] = TVS_CONFIG::UPS_MODE;
//210921_______________________
                                                    $toDB['PacksArrayFull'] = $this->tvsups->packs['PacksArray'];

                                                    // *****SURASOM REZ I DB ******* 
                                                    //echo "<hr>";
                                                    //var_dump($toDB);

                                                    if($this->tvsups->RESPONSE_ERROR == 1){
                                                        $rezData['SIUNTAOK']='OK';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje.';
                                                        //$rezData['Duom']=$toDB;
                                                    }elseif($this->tvsups->RESPONSE_ERROR == 2){
                                                        $rezData['SIUNTAOK']='OK1';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje. Tačiau LIPDUKAS NESUSIKŪRĖ';
                                                        //$rezData['Duom']='';//$toDB;
                                                    }


                                                }else{

                                                    $error = $this->tvsups->getErrorsAsHtml('ALL');

                                                    //$rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['Duom']['SiuntuNr'] = "";
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    //$error = $this->tvsups->getErrorsAsHtml ('ALL');
                                                    //var_dump($error);

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    $toDB['SendetXML'] = $this->tvsups->SCHXML;
                                                    $toDB['ResponseXML'] = $this->tvsups->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsups->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsups->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsups->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsups->XML_result_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDUPS';
                                                    $toDB['SiuntuNr'] = '';//$this->tvsups->
                                                    $toDB['Comment'] = '';

                                                    $toDB['KrovinysReg']='N'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsups->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='1';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "UPS";
                                                    $toDB['VezejoSiuntosNr'] = "";
                                                    
                                                    $toDB['Manifest'] ='';
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::UPS_MODE;

                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['SIUNTAMSG']=$error;
                                                    //$rezData['Duom']=array();

                                                }



                                                /* *****SURASOM REZ I DB ******* 
                                                    Papildom TMS_Siuntos
                                                    surasom pakuotes i TMS_Pak
                                                    irasom i TMS_His
                                                */

                                                $dbRez = $this->tvsMod->saveTvsTranspReg($toDB); 

                                                //echo "<hr>".$dbRez;

                                                if($dbRez!='OK'){
                                                    $SaveErrorsStr = $this->tvsMod->getErrorArrayAsStr();
                                                    // ????????? AR REIKIA SAKYTI, KAD NEPAVYKO UZREGISTRUOTI JEIGU NEPAVYKO UPDATINTI DUOMENU DB BET REALIAI UZSIREGISTRAVO PAS VEZEJA???
                                                    // ??? $rezData['SIUNTAOK']='NOTOK';
                                                    // ??? $rezData['SIUNTAMSG']=$RegErrorStr;
                                                    // ??? //$rezData['DUOM']=array();

                                                }



                                        }//end else (nera erroru)
/* ************************************************************** /UPS ************************************************************************ */                                    
                                        break;


                                    case 'STABACE':
/* ************************************************************** ACE ************************************************************************ */                                    
                                        //echo "<br>*****V0****";

                                        $root_path = COMMON::getRootFolder();
                                        require_once ($root_path . "classes/tvsace.php");
                                        $this->tvsace = new tvsace('test', $SiuntaUID, $sParam);

                                        //tikrinam ar neivyko klaidu sukuriant objekta
                                        if($this->tvsace->haveErrors()>0){
                                            $createOK['OK'] = false;
                                            //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                                        }else{//end if
                                            $createOK['OK'] = true;
                                            //$createOK['ERROR'] = '';

                                            //$SiuntaUID='59';
                                            var_dump($sParam);
                                            
                                            $this->tvsace->setSiuntaData($SiuntaUID, $sParam);

                                            //var_dump($this->tvsups);

                                            if($this->tvsace->DataIsSet===true){
                                                /*jeigu DataIsSet===true, tai tada true ir visi kiti:
                                                    $this->SHIPPERDataSet===true
                                                    $this->CONSIGNEEDataSet===true
                                                    $this->PacksIsSet === true
                                                */

                                                $this->tvsace->generateACEXML();

                                                //echo "<br><br><hr>";
                                                //var_dump($rezz);
                                                var_dump($this->tvsace->ACEXML_created);
                                                //var_dump($this->tvsace->ACEXML);
                                                var_dump($this->tvsace->errorArray);

                                                if($this->tvsace->ACEXML_created === true){
                                                    //jeigu sukurem XML tai siunciam ji i ACE
                                                    $this->tvsace->sendXML($SiuntaData); 
                                                }else{
                                                    echo "--- NO CREATE XML ---";
                                                }

                                            }else{//end if
                                                //nedarom nieko
                                                //echo "--- NO SET DATA ---";
                                            }

//die("STOP");
// 20211020 ****** ___________________

                                            /* ******************************** ACE RESPONSE APDOROJIMAS ********************************** */




                                                if($this->tvsace->haveErrors()>0){
                                                   //echo $this->tvsace->getErrorsAsHtml ('ALL');
                                                }//end if

                                                //ziurim ar visas procesas pavyko ir, jeigu reikia, isvedam klaidas
                                                // 1- jeigu viskas OK, 2-jeigu duomenys nuiunte, bet PDF nera, 3-jeigu nenusiunte ir pdf nera
                                                if($this->tvsace->RESPONSE_ERROR == 1 OR $this->tvsace->RESPONSE_ERROR == 2){


                                                    echo "<br>SIUNTA UZREGISTRUOTA-".$this->tvsace->RESPONSE_ERROR."----<br>";

                                                    // RUOSIAM DUOMENYS DB UPDATE apie siuntos registracija pas vezeja
                                                    //$rezData['SIUNTAOK']='OK';
                                                    $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                                    $rezData['Duom']['SiuntuNr'] = $this->tvsace->packs['PacksArray'];//$this->tvsace->SiuntaData['PAKS']['PacksArray'];
                                                    $rezData['Duom']['SiuntuNrACE'] = $this->tvsace->RESPONSE_BOOKING_ID;
                                                    $rezData['Duom']['HisAction'] = 'IDACE';
                                                    $rezData['Duom']['VezejasReal'] = 'ACE';
                                                    $rezData['Duom']['ManifestID'] = '';

                                                    $rezData['Duom']['SiuntaPDF'] = $this->tvsace->RESPONSE_PDF_X;
                                                    $rezData['Duom']['VaztarPDF'] = $this->tvsace->RESPONSE_PDF_Vazt;

                                                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                                                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                                                    

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    

                                                    $toDB['ManifestID'] = '';
                                                    $toDB['SendetXML'] = $this->tvsace->ACEXML;
                                                    $toDB['ResponseXML'] = $this->tvsace->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsace->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsace->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsace->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsace->XML_result_send_user;
                                                    //TODO nera bendro visu lipduku pdf, reikia sukurti pdf faila is GIF
                                                    $toDB['SiuntaPDF'] = $this->tvsace->RESPONSE_PDF_X;
                                                    $toDB['VaztarPDF'] = $this->tvsace->RESPONSE_PDF_Vazt;

                                                    $error = $this->tvsace->getErrorsAsHtml ('ALL');
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDACE';
                                                    $toDB['SiuntuNr'] = $this->tvsace->packs['PacksArray'];
                                                    $toDB['SiuntuNrACE'] = $this->tvsace->RESPONSE_BOOKING_ID;
                                                    $toDB['SiuntuDocNr'] = $this->tvsace->RESPONSE_REQUEST_ID;
                                                    $toDB['Comment'] = '';
                                                    
                                                    $toDB['KrovinysReg']='Y'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsace->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "ACE";
                                                    $toDB['VezejoSiuntosNr'] = $this->tvsace->RESPONSE_BOOKING_ID; // vezejo numeris 
                                                    //$toDB['VezejoSiuntosNr'] = $this->tvsace->tvs['reference']['number'];//musu numeris pas vazeja

                                                    $toDB['Manifest'] = '';
                                                    $toDB['TrackingNr'] = $this->tvsace->RESPONSE_ShipmentIdentificationNumber; 
                                                    $toDB['Service'] = $sParam['sACE_ServiceID'];
                                                    $toDB['LiveDemo'] = TVS_CONFIG::ACE_MODE;
//210921_______________________
                                                    $toDB['PacksArrayFull'] = $this->tvsace->packs['PacksArray'];

                                                    // *****SURASOM REZ I DB ******* 
                                                    //echo "<hr>";
                                                    //var_dump($toDB);

                                                    if($this->tvsace->RESPONSE_ERROR == 1){
                                                        $rezData['SIUNTAOK']='OK';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje.';
                                                        //$rezData['Duom']=$toDB;
                                                    }elseif($this->tvsace->RESPONSE_ERROR == 2){
                                                        $rezData['SIUNTAOK']='OK1';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje. Tačiau LIPDUKAS NESUSIKŪRĖ';
                                                        //$rezData['Duom']='';//$toDB;
                                                    }


                                                }else{

                                                    $error = $this->tvsace->getErrorsAsHtml('ALL');

                                                    //$rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['Duom']['SiuntuNr'] = "";
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    //$error = $this->tvsace->getErrorsAsHtml ('ALL');
                                                    //var_dump($error);

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    $toDB['SendetXML'] = $this->tvsace->SCHXML;
                                                    $toDB['ResponseXML'] = $this->tvsace->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvsace->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvsace->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvsace->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvsace->XML_result_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['VaztarPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDACE';
                                                    $toDB['SiuntuNr'] = '';//$this->tvsace->
                                                    $toDB['Comment'] = '';

                                                    $toDB['KrovinysReg']='N'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvsace->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='1';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "ACE";
                                                    $toDB['VezejoSiuntosNr'] = "";
                                                    
                                                    $toDB['Manifest'] ='';
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::ACE_MODE;

                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['SIUNTAMSG']=$error;
                                                    //$rezData['Duom']=array();

                                                }



                                                /* *****SURASOM REZ I DB ******* 
                                                    Papildom TMS_Siuntos
                                                    surasom pakuotes i TMS_Pak
                                                    irasom i TMS_His
                                                */

                                                $dbRez = $this->tvsMod->saveTvsTranspReg($toDB); 

                                                //echo "<hr>".$dbRez;

                                                if($dbRez!='OK'){
                                                    $SaveErrorsStr = $this->tvsMod->getErrorArrayAsStr();
                                                    // ????????? AR REIKIA SAKYTI, KAD NEPAVYKO UZREGISTRUOTI JEIGU NEPAVYKO UPDATINTI DUOMENU DB BET REALIAI UZSIREGISTRAVO PAS VEZEJA???
                                                    // ??? $rezData['SIUNTAOK']='NOTOK';
                                                    // ??? $rezData['SIUNTAMSG']=$RegErrorStr;
                                                    // ??? //$rezData['DUOM']=array();

                                                }



                                        }//end else (nera erroru)
/* ************************************************************** /UPS ************************************************************************ */                                    
                                        break;










                                    case 'STABCAT':
/* ************************************************************** CAT ************************************************************************ */                                    
                                        //echo "<br>*****V0****";

                                        $root_path = COMMON::getRootFolder();
                                        require_once ($root_path . "classes/tvscat.php");
                                        $this->tvscat = new tvscat('test', $SiuntaUID, $sParam);

                                        //tikrinam ar neivyko klaidu sukuriant objekta
                                        if($this->tvscat->haveErrors()>0){
                                            $createOK['OK'] = false;
                                            //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                                        }else{//end if
                                            $createOK['OK'] = true;
                                            //$createOK['ERROR'] = '';

                                            //$SiuntaUID='59';
                                            var_dump($sParam);
                                            
                                            $this->tvscat->setSiuntaData($SiuntaUID, $sParam);

                                            var_dump($this->tvscat);

                                            if($this->tvscat->DataIsSet===true){
                                                /*jeigu DataIsSet===true, tai tada true ir visi kiti:
                                                    $this->SHIPPERDataSet===true
                                                    $this->CONSIGNEEDataSet===true
                                                    $this->PacksIsSet === true
                                                */

                                                $this->tvscat->generateCATXML();

                                                //echo "<br><br><hr>";
                                                //var_dump($rezz);
                                                var_dump($this->tvscat->CATXML_created);
                                                //var_dump($this->tvsace->ACEXML);
                                                var_dump($this->tvscat->errorArray);

                                                if($this->tvscat->CATXML_created === true){
                                                    //jeigu sukurem XML tai siunciam ji i ACE
                                                    $this->tvscat->sendXML($SiuntaData); 
                                                }else{
                                                    echo "--- NO CREATE XML ---";
                                                }

                                            }else{//end if
                                                //nedarom nieko
                                                //echo "--- NO SET DATA ---";
                                            }


                                            /* ******************************** CAT RESPONSE APDOROJIMAS ********************************** */




                                                if($this->tvscat->haveErrors()>0){
                                                   //echo $this->tvsace->getErrorsAsHtml ('ALL');
                                                }//end if

                                                //ziurim ar visas procesas pavyko ir, jeigu reikia, isvedam klaidas
                                                // 1- jeigu viskas OK, 2-jeigu duomenys nuiunte, bet PDF nera, 3-jeigu nenusiunte ir pdf nera
                                                if($this->tvscat->RESPONSE_ERROR == 1 OR $this->tvscat->RESPONSE_ERROR == 2){


                                                    echo "<br>SIUNTA UZREGISTRUOTA-".$this->tvscat->RESPONSE_ERROR."----<br>";

                                                    // RUOSIAM DUOMENYS DB UPDATE apie siuntos registracija pas vezeja
                                                    //$rezData['SIUNTAOK']='OK';
                                                    $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                                    $rezData['Duom']['SiuntuNr'] = $this->tvscat->packs['PacksArray'];//$this->tvsace->SiuntaData['PAKS']['PacksArray'];
                                                    $rezData['Duom']['SiuntuNrCAT'] = $this->tvscat->RESPONSE_BOOKING_ID;
                                                    $rezData['Duom']['HisAction'] = 'IDCAT';
                                                    $rezData['Duom']['VezejasReal'] = 'CAT';
                                                    $rezData['Duom']['ManifestID'] = '';

                                                    $rezData['Duom']['SiuntaPDF'] = ''; //$this->tvscat->RESPONSE_PDF_X;
                                                    $rezData['Duom']['VaztarPDF'] = ''; //$this->tvscat->RESPONSE_PDF_Vazt;

                                                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                                                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                                                    

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    

                                                    $toDB['ManifestID'] = '';
                                                    $toDB['SendetXML'] = $this->tvscat->CATXML;
                                                    $toDB['ResponseXML'] = $this->tvscat->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvscat->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvscat->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvscat->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvscat->XML_result_send_user;
                                                    //TODO nera bendro visu lipduku pdf, reikia sukurti pdf faila is GIF
                                                    $toDB['SiuntaPDF'] = ''; //$this->tvscat->RESPONSE_PDF_X;
                                                    $toDB['VaztarPDF'] = ''; //$this->tvscat->RESPONSE_PDF_Vazt;

                                                    $error = $this->tvscat->getErrorsAsHtml ('ALL');
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDCAT';
                                                    $toDB['SiuntuNr'] = $this->tvscat->packs['PacksArray'];
                                                    $toDB['SiuntuNrACE'] = $this->tvscat->RESPONSE_BOOKING_ID;
                                                    $toDB['SiuntuDocNr'] = $this->tvscat->RESPONSE_REQUEST_ID;
                                                    $toDB['Comment'] = '';
                                                    
                                                    $toDB['KrovinysReg']='Y'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvscat->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='2';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "CAT";
                                                    $toDB['VezejoSiuntosNr'] = $this->tvscat->RESPONSE_BOOKING_ID; // vezejo numeris 
                                                    //$toDB['VezejoSiuntosNr'] = $this->tvsace->tvs['reference']['number'];//musu numeris pas vazeja

                                                    $toDB['Manifest'] = '';
                                                    $toDB['TrackingNr'] = $this->tvscat->RESPONSE_BOOKING_ID; //$this->tvscat->RESPONSE_ShipmentIdentificationNumber; 
                                                    $toDB['Service'] = $sParam['sCAT_ServiceID'];
                                                    $toDB['LiveDemo'] = TVS_CONFIG::CAT_MODE;
//210921_______________________
                                                    $toDB['PacksArrayFull'] = $this->tvscat->packs['PacksArray'];

                                                    // *****SURASOM REZ I DB ******* 
                                                    //echo "<hr>";
                                                    //var_dump($toDB);

                                                    if($this->tvscat->RESPONSE_ERROR == 1){
                                                        $rezData['SIUNTAOK']='OK';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje.';
                                                        //$rezData['Duom']=$toDB;
                                                    }elseif($this->tvscat->RESPONSE_ERROR == 2){
                                                        $rezData['SIUNTAOK']='OK1';
                                                        $rezData['SIUNTAMSG']=' Siunta užregistruota vežėjo sistemoje. Tačiau LIPDUKAS NESUSIKŪRĖ';
                                                        //$rezData['Duom']='';//$toDB;
                                                    }


                                                }else{

                                                    $error = $this->tvscat->getErrorsAsHtml('ALL');

                                                    //$rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['Duom']['SiuntuNr'] = "";
                                                    $rezData['Duom']['SiuntaPDF'] = "";
                                                    //$error = $this->tvsace->getErrorsAsHtml ('ALL');
                                                    //var_dump($error);

                                                    $toDB['actTip'] = 'SIUNTA'; // SIUNTA, KURJERIS
                                                    $toDB['SiuntaUID'] = $SiuntaUID;
                                                    $toDB['SendetXML'] = $this->tvscat->SCHXML;
                                                    $toDB['ResponseXML'] = $this->tvscat->XML_RESPONSE_XML;
                                                    $toDB['SendetXMLTime'] = $this->tvscat->XML_RESPONSE_TIME;
                                                    $toDB['SendetXMLOK'] = $this->tvscat->RESPONSE_ERROR;//1-OK, 2-reg OK, bet PDF NEOK, 3-Reg NEOK
                                                    $toDB['SendetXMLUserUID'] = $this->tvscat->XML_result_send_userUID;
                                                    $toDB['SendetXMLUser'] = $this->tvscat->XML_result_send_user;
                                                    $toDB['SiuntaPDF'] = "";
                                                    $toDB['VaztarPDF'] = "";
                                                    $toDB['ActionErrors'] = $error;

                                                    $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                                    $toDB['HisAction'] = 'IDCAT';
                                                    $toDB['SiuntuNr'] = '';//$this->tvsace->
                                                    $toDB['Comment'] = '';

                                                    $toDB['KrovinysReg']='N'; 
                                                    $toDB['SiuntaUzregistruota']=$this->tvscat->XML_RESPONSE_TIME;
                                                    $toDB['SiuntosBusena']='1';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                                    $toDB['VezejasReal'] = "CAT";
                                                    $toDB['VezejoSiuntosNr'] = "";
                                                    
                                                    $toDB['Manifest'] ='';
                                                    $toDB['TrackingNr'] = '';
                                                    $toDB['Service'] = '';
                                                    $toDB['LiveDemo'] = TVS_CONFIG::CAT_MODE;

                                                    $rezData['SIUNTAOK']='NOTOK';
                                                    $rezData['SIUNTAMSG']=$error;
                                                    //$rezData['Duom']=array();

                                                }



                                                /* *****SURASOM REZ I DB ******* 
                                                    Papildom TMS_Siuntos
                                                    surasom pakuotes i TMS_Pak
                                                    irasom i TMS_His
                                                */

                                                $dbRez = $this->tvsMod->saveTvsTranspReg($toDB); 

                                                //echo "<hr>".$dbRez;

                                                if($dbRez!='OK'){
                                                    $SaveErrorsStr = $this->tvsMod->getErrorArrayAsStr();
                                                    // ????????? AR REIKIA SAKYTI, KAD NEPAVYKO UZREGISTRUOTI JEIGU NEPAVYKO UPDATINTI DUOMENU DB BET REALIAI UZSIREGISTRAVO PAS VEZEJA???
                                                    // ??? $rezData['SIUNTAOK']='NOTOK';
                                                    // ??? $rezData['SIUNTAMSG']=$RegErrorStr;
                                                    // ??? //$rezData['DUOM']=array();

                                                }



                                        }//end else (nera erroru)
/* ************************************************************** /CAT ************************************************************************ */                                    
                                        break;



                                    default:
                                            echo "<br>*****V8****";
                                            $rezData['SIUNTAOK']='NOTOK';
                                            $rezData['SIUNTAMSG']=' Siunta NEUŽREGISTRUOTA vežėjo sistemoje...';
                                            $rezData['Duom']=array();

                                        break;

                                }//end swith
                            }//end if

                    }else{//
                        echo "<br>*****V9****"; 
                        //siunta jau uzregistruota pas vezeja... apsauga nuo kvadro clicku
                        //pranesimu nemetam
                    }

                    //!!!!!! DEBUG
                    $this->var_dump($toDB, "toDB++++<hr>$SiuntaUID");//-----------------DEBUG

                    //!!!!!! DEBUG
                    $this->var_dump($dbRez, "dbRez<hr>");//-----------------DEBUG


            //**********************************************************************************************************************
            //formuojam atsakyma
            $RezArray['error']='OK';
            $RezArray['actionMessage']='Duomenys išsaugoti.';
            $RezArray['SiuntaID']=$rezTmp['SiuntaID'];
            $RezArray['PakSlipArray']=explode(',', str_replace(" ", "",$Det_PackingSlip));
            $RezArray['SiuntaData']=$rezData;
        }else{//end jeigu buvo klaida saugant duomenys i nGmod arba buvo nurodytas Beno Tel nr kaip gavejo Nr
            $RezArray['error']='NOTOK';
            $RezArray['actionMessage']=$this->tvsMod->getErrorArrayAsStr();
            $RezArray['SiuntaID']=$rezTmp['SiuntaID'];
            $RezArray['PakSlipArray']=explode(',', str_replace(" ", "",$Det_PackingSlip));

                $rezData['SIUNTAOK']='NOTOK';
                $rezData['SIUNTAMSG']="\n Siunta NEUŽREGISTRUOTA vežėjo sistemoje. Nepakanka duomenų, arba negalimas automatinis siuntimas šiam klientui.";
                $rezData['Duom']=array();

            $RezArray['SiuntaData']=$rezData;
        }
    }else{//yra klaidu pakuociu kode

        if($paksIsOK['YRA'] == 'S'){
            $errorStr = 'Per didelis skirtumas tarp įvesto bendro svorio ir atskirų pakuočių suminio svorio!\n Galimai neteisingai įvestas pakuočių svoris.';
        }else{
            $errorStr = $paksIsOK['Kiekis'] .' '.$paksIsOK['Svoris'] .' '.$paksIsOK['Plotis'] .' '.$paksIsOK['Ilgis'] .' '.$paksIsOK['Aukstis'] .' '.$paksIsOK['Tipas'].' '.$paksIsOK['Kita'];            
        }
        
        //$errorStr = preg_replace('/\s+/', '', $errorStr);
        $RezArray['error']='NOTOK';
        $RezArray['actionMessage']=$errorStr;
        $RezArray['SiuntaID']=array();
        $RezArray['PakSlipArray']=explode(',', str_replace(" ", "",$Det_PackingSlip));
        $RezArray['SiuntaData']=array();

    }//end else klaida pakuotese

var_dump($RezArray);

/*
C:\WWW\AURIKA_NEW\controllers\tvs\ajax\ajaxSavePakuote.controller.php:1589:
array (size=5)
  'error' => string 'OK' (length=2)
  'actionMessage' => string 'Duomenys išsaugoti.' (length=20)
  'SiuntaID' => string '39989' (length=5)
  'PakSlipArray' => 
    array (size=1)
      0 => string '386750' (length=6)
  'SiuntaData' => 
    array (size=3)
      'Duom' => 
        array (size=10)
          'SiuntaUID' => string '39989' (length=5)
          'SiuntuNr' => 
            array (size=2)
              0 => 
                array (size=8)
                  'Kiekis' => int 1
                  'Plotis' => float 38
                  'Ilgis' => float 29
                  'Aukstis' => float 25
                  'Turis' => float 0.03
                  'Svoris' => float 10
                  'GrossSvoris' => float 10
                  'Tipas' => string 'BX' (length=2)
              1 => 
                array (size=8)
                  'Kiekis' => int 1
                  'Plotis' => float 38
                  'Ilgis' => float 29
                  'Aukstis' => float 25
                  'Turis' => float 0.03
                  'Svoris' => float 5
                  'GrossSvoris' => float 5
                  'Tipas' => string 'BX' (length=2)
          'SiuntuNrACE' => string 'test' (length=4)
          'HisAction' => string 'IDACE' (length=5)
          'VezejasReal' => string 'ACE' (length=3)
          'ManifestID' => string '' (length=0)
          'SiuntaPDF' => string 'https://www.ace.LT/eservices/weborders/TE/sscclabel.php?Report=SSCCLabel1A4&JobGUID=7b98681337db11ecb7df000c29e523b2' (length=116)
          'VaztarPDF' => string 'https://www.ace.LT/eservices/weborders/waybill.php?JobGUID=7b98681337db11ecb7df000c29e523b2' (length=91)
          'KrRegData' => string '2021-10-28' (length=10)
          'KrRegDataTime' => string '2021-10-28 12:40:37' (length=19)
      'SIUNTAOK' => string 'OK' (length=2)
      'SIUNTAMSG' => string ' Siunta užregistruota vežėjo sistemoje.' (length=42)
*/      

        echo $RezArray."**--**";
        echo json_encode($RezArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxSavePakuoteController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
