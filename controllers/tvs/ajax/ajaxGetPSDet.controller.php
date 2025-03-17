<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");

class ajaxGetPSDetController extends controller {

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
        $PackingSlipArrayTmp = $this->getVar('PSA');

        //issiparsinam grynus packing slip ID

        $PackingSlipArray = array();
        if($PackingSlipArrayTmp){
            //$PackingSlipNetvarkytiArray = explode(",", $PackingSlipArrayStr);
            foreach ($PackingSlipArrayTmp as $key => $PSID) {
                $PackingSlipArray[]= substr($PSID, 3);
            }

        }//end if

        var_dump($PackingSlipArray);

        
        //begam per visus PSilpus ir susirenkam info
        $rezArray = array();
        if($PackingSlipArray){
            $PackData = $this->tvsMod->getPackData($PackingSlipArray);
        }else{//end if
            $PackData = array();
        }
        
        //!!!!!! DEBUG
        $this->var_dump($PackData, "*****PackData <hr>$SQL_TVS<hr> ");//-----------------DEBUG
        

        //Pagal Gintares nurodyta logika, parenkam vezeja (email 20200402 15:52)
        

        $PakuociuSk = 0;
        $PaleciuSk = 0;
        $PakTipas = 'N';//N-nezinomas
        if($PackData['PAKUOTESARRAY']){
            //var_dump($PackData['PAKUOTESARRAY']);
            foreach ($PackData['PAKUOTESARRAY'] as $keyPak => $pak) {
                if($pak['tipas']=="PK" OR $pak['tipas']=="DD" OR $pak['tipas']=="RD"){
                    $PakuociuSk ++;
                    if($PakTipas == 'N'){// tik jeigu dar nebuvo nustatytas joks tipas, o jeigu buvo dezutes arba paletes tai nebekeiciam
                        $PakTipas = 'D';//D-Dezute, jeigu siuntoje nebuvo nei vienos paletes
                    }
                }else if ($pak['tipas']=="EP" OR $pak['tipas']=="MP"){
                    $PaleciuSk++;
                    $PakTipas = 'P';//P-palete (jeigu yra nors viena palate, tai paletes tipas - t.y. permusam jeigu buvo dezutes tipas)
                }
            }//foreach
        }//end if

        // Surenkam duomenys vezejo nustatymui
        $Param['ClientID']=$PackData['SHORT']['ClientID'];
        $Param['VezejasStr']=$PackData['SHORT']['VezejasStr'];
        $Param['Det_SaliesKodas']=$PackData['SHORT']['Det_SaliesKodas'];
        $Param['GalutinisBrutto']=$PackData['SVORIAI']['GalutinisBrutto'];
        $Param['ShipmentFromStr']=$PackData['SHORT']['ShipmentFromStr']; 
        $Param['PakuociuSk']=$PakuociuSk;
        $Param['PaleciuSk']=$PaleciuSk;
        $Param['PakTipas']=$PakTipas;
        $Param['TranzitoLaikas']=$PackData['SHORT']['PristaLaikasDarboDienomis'];
        $Param['Express']=$PackData['SHORT']['express'];
        
        


        $NewVezejas = $this->tvsMod->ParinktiVezeja($Param);
        /* 
        // 2021-10-01 perkelta ir patobulinta, dabar yra $this->tvsMod->ParinktiVezeja($PackData);
        $NewVezejas = "MANUAL"; //defaultinis atvejis
        if($PackData){
            //Estrella 100889 (kitos estrella nereikalingos)
            //Marood 100888
            //Packarna 100941
            //Voss  103355
            $vaziuojaTikSuDSVArray = array('100889', '100888', '100941', '103355');
            $vaziuojaSavoTransportuArray = array('AURIKA', 'KLIENTO TR');
            if(in_array($PackData['SHORT']['ClientID'], $vaziuojaTikSuDSVArray)){
                //$NewVezejas = "DSV";
                $NewVezejas = "MANUAL";
            }elseif(in_array($PackData['SHORT']['VezejasStr'], $vaziuojaSavoTransportuArray)){
                $NewVezejas = "KITA";//Kai vezama su Aurika arba KLIENTO transportas
            }else{
                switch ($PackData['SHORT']['Det_SaliesKodas']) {
                    case 'LT': //LT, LV, EE 
                    case 'LV': //LT, LV, EE 
                    case 'EE': //LT, LV, EE 
                        $NewVezejas = "VENIPAK";
                        break;
                    case 'DE': //DE, AT
                    case 'AT': //DE, AT 
                    case 'NO': //DE, AT 
                    case 'SE': //DE, AT 
                    case 'BE': //DE, AT 
                    case 'DK': //DE, AT 
                    case 'FR': //DE, AT 
                    case 'NL': //DE, AT 
                    case 'PL': //DE, AT 
                    case 'FI': //DE, AT 
                        if($PackData['SVORIAI']['GalutinisBrutto']*1<100){//jeigu bendras svoris maziau uz 100 kg tada vezam su ACE
                            $NewVezejas = "MANUAL";//ACE
                        }else{
                            $NewVezejas = "SCHENKER";
                        }
                        //Yra papildomu salygu kurios apdorosis veliau su pakuociu ir svoriu ivedimu
                        //Schenker: jeigu yra 1 ir daugiau paletes (arba palete +plius dezutes prie tos paletes) arba bendras svoris > 100kg nesvarbu kokia pakuote, bet ne daugiau kaip 10 paleciu, arba ne daugiau kaip ??? kg
                        //ACE: jeigu dezutes/ritineliai/pakuotes iki 100kg. 
                        //Kuomet vežama daugiau kaip 10 paleciu paliekam rankiniam apdorojimui -> RANKINIS IVEDIMAS
                        break;
                    
                    default:
                        $NewVezejas = "MANUAL";
                        break;
                }//end switch
            }//end else
        }//end if
        */



        $PackData['SHORT']['NustatytasVezejasStr']=$NewVezejas;
        /*

        if($this->is_ID($KortelesUID) ){ 

            //ziurim ar asmuo turi teise atlikti toki veiksma pagal korteles busena (ar gali keisti i sekancia spalva)
            $ArPasikeiteBusena = $this->KanbanMod->KeistiBusenaJeiguGaliu($KortelesUID);

            if($ArPasikeiteBusena['OK']===true){
                $actionMessage = "Būsena pakeista";
                $ErrorStatus = "OK";
            }else{
                $this->addErrorArray($this->KanbanMod->getErrorArray());
                $actionMessage = $this->getErrorArrayAsStr();
                $ErrorStatus = "NOTOK";
            }

        }else{

                $ErrorStatus = "NOTOK";
                $actionMessage='Duomenų perdavimo klaida! ';
        }
        */

        /*
        $rezultArray['error']=$ErrorStatus;
        $rezultArray['actionMessage']=$actionMessage;
        $rezultArray['dat']['BgCod']=$ArPasikeiteBusena['BgCod'];
        $rezultArray['dat']['TxtCod']=$ArPasikeiteBusena['TxtCod'];
        */

        //!!!!!! DEBUG
        $this->var_dump($PackData, "PackData1111");//-----------------DEBUG
        //$PackData['ERROR']['Error'] = ' ';

        echo "**--**";
        echo json_encode($PackData);

    }//END FUNCTION


}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxGetPSDetController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
