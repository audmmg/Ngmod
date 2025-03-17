<?php
///////////////////////////////////////////////////
// 
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
//$root_path = "../../../";
require_once ("../../controller.php");
require_once( '../../../classes/TVSconfig.php');

class ajaxUzsakytiTrController extends controller {

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
        $TranspID = $this->getVar('TranspID');
        $SiuntaUID = $this->getVar('SiuntaUID');

        $sParam['sSvorisVisoBrutto']= $this->getVar('sSvorisVisoBrutto');

        /*
        $sParam['sVP_ManifestKEGKPG']= $this->getVar('sVP_ManifestKEGKPG');
        $sParam['sVP_Express']= $this->getVar('sVP_Express');
        $sParam['sVP_PristatytiIki']= $this->getVar('sVP_PristatytiIki');
        $sParam['sVPIsvData'] = $this->getVar('sVPIsvData');
        $sParam['sVPIsvLaikasNuo'] = $this->getVar('sVPIsvLaikasNuo');
        $sParam['sVPIsvLaikasIki'] = $this->getVar('sVPIsvLaikasIki');
        */

        $sParam['sVP_ManifestKEGKPG']= $this->getVar('sVP_ManifestKEGKPG');
        $sParam['sVP_Express']= $this->getVar('sVP_Express');
        $sParam['sVP_PristatytiIki']= $this->getVar('sVP_PristatytiIki');
        $sParam['sVPIsvData'] = $this->getVar('sVPIsvData');
        $sParam['sVPIsvLaikasNuo'] = $this->getVar('sVPIsvLaikasNuo');
        $sParam['sVPIsvLaikasIki'] = $this->getVar('sVPIsvLaikasIki');
        $sParam['sVP_LiveDemo'] = TVS_CONFIG::VENIPAK_MODE;


        $sParam['sMAN_VEZEJAS'] = $this->getVar('sMAN_VEZEJAS');
        $sParam['sMAN_SiuntaOrder'] = $this->getVar('sMAN_SiuntaOrder');
        $sParam['sMAN_TrackingNr'] = $this->getVar('sMAN_TrackingNr');
        $sParam['sMAN_Service'] = $this->getVar('sMAN_Service');
        
        $sParam['sMAN_PristatytiIki'] = $this->getVar('sMAN_PristatytiIki');
        $sParam['sMANIsvData'] = $this->getVar('sMANIsvData');
        $sParam['sMANIsvLaikasNuo'] = $this->getVar('sMANIsvLaikasNuo');
        $sParam['sMANIsvLaikasIki'] = $this->getVar('sMANIsvLaikasIki');
        $sParam['sMAN_LiveDemo'] = TVS_CONFIG::MANUAL_MODE;


        $sParam['sSCHENKER_LiveDemo'] = TVS_CONFIG::SCHENKER_MODE;


        $sParam['det_CargoDescription'] = $this->getVar('det_CargoDescription');

        //$sParam['det_Adresas'] = $this->getVar('det_AdresasFull');
        $sParam['det_Street1'] = $this->getVar('det_Street1');
        $sParam['det_Street2'] = $this->getVar('det_Street2');
        $sParam['det_Region'] = $this->getVar('det_Region');
        $sParam['det_City'] = $this->getVar('det_City');
        $sParam['det_CountryCode'] = $this->getVar('det_CountryCode');
        $sParam['det_PostCode'] = $this->getVar('det_PostCode');
        $sParam['det_ContactPerson'] = $this->getVar('det_ContactPerson');
        $sParam['det_ContactPersonMail'] = $this->getVar('det_ContactPersonMail');
        $sParam['det_ContactPersonTel'] = $this->getVar('det_ContactPersonTel');

        $sParam['det_IsvAtsakingas'] = $this->getVar('det_IsvAtsakingas');
        $sParam['det_IsvAtsakingasTel'] = $this->getVar('det_IsvAtsakingasTel');
        $sParam['det_IsvAtsakingasEmail'] = $this->getVar('det_IsvAtsakingasEmail');
        


       //var_dump($sParam);

        //nusiskaitom duomenys apie siunta
        if($SiuntaUID){
            //$PackData = $this->tvsMod->getSiuntaDuomToTransp($SiuntaUID);
        

        
            //pagal vezeja, formuojam XML ir siunciam
            switch ($TranspID) {

                case 'IDMANUAL':
                case 'IDMANUALDONE':

                    //nieko nesiunciam, tik updatinam DB 

                    $man_rez = $this->tvsMod->siustiMANUAL ($SiuntaUID, $sParam);
                    $rezData['OK']=$man_rez['OK'];


                    $rezData['Duom']['SiuntaUID'] = $man_rez['Data']['uid'];
                    $rezData['Duom']['SiuntuNr'] = $man_rez['Data']['PAKS'];
                    $rezData['Duom']['HisAction'] = 'IDMANUAL';
                    $rezData['Duom']['VezejasReal'] = $man_rez['Data']['VezejasReal'];
                    //$rezData['Duom']['ManifestID'] = $this->tvsvenipak->manifest;
                    $rezData['Duom']['KrRegData'] = date("Y-m-d");
                    $rezData['Duom']['KrRegDataTime'] = date("Y-m-d H:i:s");

                    //var_dump($man_rez);



                break;

                case 'IDSHENKER': // is TVS sandelio modulio nenaudojama
                    

                    
                break;

                case 'IDVENIPAK':  // is TVS sandelio modulio nenaudojama

                    $root_path = COMMON::getRootFolder();
                    require_once ($root_path . "classes/tvsvenipak.php");
                    $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);
                    //tikrinam ar neivyko klaidu sukuriant objekta
                    if($this->tvsvenipak->haveErrors()>0){
                        $createOK['OK'] = false;
                        //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                    }else{//end if
                        $createOK['OK'] = true;
                        //$createOK['ERROR'] = '';
                    }

                    if ($createOK['OK']===true){
                            /* KROVINIO UZSAKYMAS */
                            $action = 'KR'; //KR-krovinio registracija, KI-kurjerio iskvietimas, LS-lipduko spausdinimas
                            $param=array();
                            $param['sVP_PristatytiIki']=$sParam['sVP_PristatytiIki'];
                            $param['sVP_Express']=$sParam['sVP_Express'];
                            $param['det_CargoDescription']=$sParam['det_CargoDescription'];

                          
                            $KR_rez = $this->tvsvenipak->run($action, $param);

                            if($KR_rez===true){

                                $rezData['OK']='OK';
                                $rezData['Duom']['SiuntaUID'] = $SiuntaUID;
                                $rezData['Duom']['SiuntuNr'] = $this->tvsvenipak->rezKR_SiuntuNr;
                                $rezData['Duom']['HisAction'] = 'IDVENIPAK';
                                $rezData['Duom']['VezejasReal'] = 'VENIPAK';
                                $rezData['Duom']['ManifestID'] = $this->tvsvenipak->manifest;
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
                                ;

                                $toDB['Manifest'] = $this->tvsvenipak->manifest;
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

                            }else{

                                $rezData['OK']='NOTOK';
                                $rezData['Duom']['SiuntuNr'] = "";
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
                                
                                $toDB['Manifest'] = $this->tvsvenipak->manifest;
                                /*
                                if($sParam['sVP_ManifestKEGKPG']=='KEGMAN'){
                                    $toDB['Manifest'] = $this->tvsvenipak->manifestKEG;
                                }elseif($sParam['sVP_ManifestKEGKPG']=='KPGMAN'){
                                    $toDB['Manifest'] = $this->tvsvenipak->manifestKPG;
                                }
                                */


                                /* *****SURASOM REZ I DB ******* */
                            }

                            $dbRez = $this->tvsMod->saveTvsTranspReg($toDB);

                            //echo "<hr>".$dbRez;
                            if($dbRez!='OK'){
                                $rezData['OK']='NOTOK';
                            }
                    }else{
                        $rezData['OK']='NOTOK';
                    }

                    break;
                case 'IDVENIPAKKI':

                    $root_path = COMMON::getRootFolder();
                    require_once ($root_path . "classes/tvsvenipak.php");
                    $this->tvsvenipak = new tvsvenipak('test', $SiuntaUID, $sParam);
                    //tikrinam ar neivyko klaidu sukuriant objekta
                    if($this->tvsvenipak->haveErrors()>0){
                        $createOK['OK'] = false;
                        //$createOK['ERROR'] = $this->tvsvenipak->getErrorsAsHtml ();
                    }else{//end if
                        $createOK['OK'] = true;
                        //$createOK['ERROR'] = '';
                    }

                    if ($createOK['OK']===true){


                            /* KURJERIO ISKVIETIMAS */
                            $action = 'KI'; //KR-krovinio registracija, KI-kurjerio iskvietimas, LS-lipduko spausdinimas
                            $param=array();
                            $KI_rez = $this->tvsvenipak->run($action, $param);

                            if($KI_rez===true){

                                $rezData['OK']='OK';
                                $rezData['Duom']['OrderNr'] = $this->tvsvenipak->rezKI_OrderNr;
                                $rezData['Duom']['HisAction'] = 'IDVENIPAKKI';
                                $error = $this->tvsvenipak->getErrorsAsHtml ('ALL');

                                $toDB['actTip'] = 'KURJERIS'; // SIUNTA, KURJERIS
                                $toDB['SiuntaUID'] = $SiuntaUID;
                                $toDB['SendetXML'] = $this->tvsvenipak->XML_KI;
                                $toDB['ResponseXML'] = $this->tvsvenipak->XML_KI_result;
                                $toDB['SendetXMLTime'] = $this->tvsvenipak->XML_KI_send_time;
                                $toDB['SendetXMLOK'] = 1;
                                $toDB['SendetXMLUserUID'] = $this->tvsvenipak->XML_KI_send_userUID;
                                $toDB['SendetXMLUser'] = $this->tvsvenipak->XML_KI_send_user;
                                $toDB['ActionErrors'] = $error;

                                $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                $toDB['HisAction'] = 'IDVENIPAKKI';
                                $toDB['OrderNr'] = $this->tvsvenipak->rezKI_OrderNr;
                                $toDB['Comment'] = '';
                                
                                //$toDB['KrovinysReg']='Y'; 
                                $toDB['KurjerisReg']='Y'; // (Y/N);
                                //$toDB['SiuntaUzregistruota']=$this->tvsvenipak->XML_KI_send_time;
                                $toDB['VezejasUzsakyta']=$this->tvsvenipak->XML_KI_send_time;
                                $toDB['SiuntosBusena']='3';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                //$toDB['DocLabel']="/img/0001.pdf"; // siuntos label'iai
                                $toDB['VezejasReal'] = "VENIPAK";


                                /* *****SURASOM REZ I DB ******* */
                                //echo "<hr>";
                                //var_dump($toDB);

                            }else{

                                $rezData['OK']='NOTOK';
                                $rezData['Duom']['OrderNr'] = "";
                                $error = $this->tvsvenipak->getErrorsAsHtml ('ALL');
                                //var_dump($error);

                                $toDB['actTip'] = 'KURJERIS'; // SIUNTA, KURJERIS
                                $toDB['SiuntaUID'] = $SiuntaUID;
                                $toDB['SendetXML'] = $this->tvsvenipak->XML_KI;
                                $toDB['ResponseXML'] = $this->tvsvenipak->XML_KI_result;
                                $toDB['SendetXMLTime'] = $this->tvsvenipak->XML_KI_send_time;
                                $toDB['SendetXMLOK'] = 0;
                                $toDB['SendetXMLUserUID'] = $this->tvsvenipak->XML_KI_send_userUID;
                                $toDB['SendetXMLUser'] = $this->tvsvenipak->XML_KI_send_user;
                                $toDB['ActionErrors'] = $error;

                                $toDB['HisGroup'] = 'SIUNTA_REG';//siuntu registravimo grupe, kartu su kurjerio iskvietimais
                                $toDB['HisAction'] = 'IDVENIPAKKI';
                                $toDB['OrderNr'] = '';
                                $toDB['Comment'] = '';

                                //$toDB['KrovinysReg']=''; 
                                $toDB['KurjerisReg']='';//nekeiciam
                                //$toDB['SiuntaUzregistruota']=$this->tvsvenipak->XML_KI_send_time;
                                $toDB['VezejasUzsakyta']=$this->tvsvenipak->XML_KI_send_time;
                                $toDB['SiuntosBusena']='3';//0-nera siuntos, 1-yra siunta, 2-uzregistruota, 3-uzsakytas kurjeris 4-paimta 5-pristatyta 6-atsaukta
                                //$toDB['DocLabel']="/img/0001.pdf"; // siuntos label'iai
                                $toDB['VezejasReal'] = "VENIPAK";

                                /* *****SURASOM REZ I DB ******* */
                            }

                            $dbRez = $this->tvsMod->saveTvsTranspReg($toDB);

                            //echo "<hr>".$dbRez;
                            if($dbRez!='OK'){
                                $rezData['OK']='NOTOK';
                            }

                    }else{
                        $rezData['OK']='NOTOK';
                    }

                    break;
                
                default:
                    $error = "Duomenų perdavimo klaida.Nežinomas veiksmas";
                    break;
            }//end switch

        }//end if



        if($rezData['OK']!='OK'){ 
                //parsisiunciam klaidas
                if($this->tvsMod->countError()>0){
                    $this->AddErrorArray ($this->tvsMod->getErrorArray());
                }
                $actionMessage = $this->getErrorArrayAsStr();
                if($TranspID=='IDVENIPAKKI'){
                    $actionMessage .= $this->tvsvenipak->getErrorsAsHtml ('ALL');
                }
                $ErrorStatus = "NOTOK";
        }else{
                $actionMessage = ' ';
                $ErrorStatus = "OK";
        }


        
        $rezultArray['error']=$ErrorStatus;
        $rezultArray['actionMessage']=$actionMessage;
        $rezultArray['data']=$rezData['Duom'];
        

        //!!!!!! DEBUG
        //$this->var_dump($rezultArray, "rezultArray");//-----------------DEBUG

        echo "**--**";
        echo json_encode($rezultArray);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxUzsakytiTrController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
