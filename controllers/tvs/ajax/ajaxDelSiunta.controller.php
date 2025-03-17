<?php
///////////////////////////////////////////////////
// VEIKSMAS KAI Planeryje darbo busena keiciama i DERINAMA ir panasiai
//////////////////////////////////////////////////
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
ob_start();
//$root_path = COMMON::getRootFolder();
require_once ("../../controller.php");
require_once ("../../../mail/class/SMTPMailer.php");


class ajaxDelSiuntaController extends controller {

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
        $SiuntaID = $this->getVar('SID');

       
        if($SiuntaID){
            $DelRez = $this->tvsMod->delSiuntaData($SiuntaID);
            //$DelRez['sendMail']=$sendMail;    
            //$DelRez['sendMailMail']=$sendMailMail;

            //20250130 - TIK TESTAVIMUI GESINAM
            $DelRez['sendMail']='N';

            if($DelRez['sendMail']=='Y'){
                //$MailDuom['mail'] = $DelRez['sendMailMail'];
                $MailDuom['mail'] = 'booking.vilnius@dbschenker.com';
                //$MailDuom['mail'] = 'arcodelt@gmail.com';
                $MailDuom['delSiuntaNrPasVezeja'] = $DelRez['delSiuntaNrPasVezeja'];
                $MailDuom['delSiuntaVezejasReal'] = $DelRez['delSiuntaVezejasReal'];;
                $mail_rez = $this->SendMessMail_SiuntaDel($MailDuom);
            }
            


        }else{//end if
            $DelRez['OK'] = 'NOTOK';
            $DelRez['Comment'] = 'Nežinomas siuntos numeris.';
        }
        

        //!!!!!! DEBUG
        //$this->var_dump($DelRez, "DelRez");//-----------------DEBUG

        echo "**--**";
        echo json_encode($DelRez);

    }//END FUNCTION


    private function SendMessMail_SiuntaDel___($MailDuom) {

        var_dump($MailDuom);

        if(!$ReplayAddress){
            $ReplayAddress = 'transportas@aurika.lt';
        }

                

                $userEmail = $MailDuom['mail'];

                $mailSubject = "!!! SIUNTA ".$MailDuom['delSiuntaNrPasVezeja']." ANULIUOJAMA";
                $mailTXT= "
                    <br>
                    Sveiki,
                    <br><br>
                    informuojame, kad siunta ".$MailDuom['delSiuntaNrPasVezeja']." nevažiuoja, prašome ją anuliuoti.
                    <br><br>
                    
                    Ačiū,
                    <br>
                    UAB Aurika,
                    <br>
                    Edita Kupčiūnienė | Transporto vadybininkė<br>
                    Chemijos g. 29F, LT-51333, Kaunas<br>
                    Mob.: +370 688 02736<br>
                    E-mail: transportas@aurika.lt<br>
                    <br><br>
                ";
                

                $root_path = COMMON::getRootFolder();
                require_once ($root_path . "classes/PHPMailer/sendMail.php");
                $this->Mailer = new sendMail();

                
                if(filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
                    $mailTXT=$MailDuom['text'];


                    //siuntimu array
                    $sendMailDataArray = array(
                        array(
                            'Email' => $MailDuom['mail'], 
                            'Name' => '', 
                            'FromName' => 'UAB Aurika Transportas', 
                            'Subject' => $mailSubject, 
                            'Body' => $mailTXT, 
                        )
                    );


                    $mailRez = $this->Mailer->sendPHPMail($sendMailDataArray);

                    /*
                    if ($mailObj->Send()){
                        $mailTXT .= "\r\n <br>pranešimas vežėjui: ". $userEmail ." išsiųstas sėkmingai\r\n";
                        $mailRez = "eMAIL SEND OK";
                        $mailSubject = "OK-". $mailSubject;// kad sumestu man i atskira folderi
                    }
                    else
                    {
                        $mailTXT .= "\r\n <br>pranešimas vežėjui: ". $userEmail ." NEišsiųstas !!!! \r\n";
                        $mailRez = "eMAIL SEND ERROR";
                        $mailSubject = "!!! NOTOK-". $mailSubject;// kad sumestu man i atskira folderi
                    }
                    */
                    
                    echo "************1**************";

                }else{
                    $this->addError("Laiškas vežėjui ".$MailDuom['delSiuntaVezejasReal']." apie siuntos ".$MailDuom['delSiuntaNrPasVezeja']." atšaukimą neišsiųstas. Neteisingas el. pašto adresas.");


                    echo "************2**************";

                }





        return $mailRez;
    }//end function




    private function SendMessMail_SiuntaDel($MailDuom) {

        var_dump($MailDuom);



        if(!$ReplayAddress){
            $ReplayAddress = 'transportas@aurika.lt';
        }

                

                $userEmail = $MailDuom['mail'];

                //20250130 TIK TESTAVIMUI
                $userEmail = 'arnas@aurika.lt';

                $mailSubject = "!!! SIUNTA ".$MailDuom['delSiuntaNrPasVezeja']." ANULIUOJAMA";
                $mailTXT= "Sveiki,
                    <br><br>
                    informuojame, kad siunta ".$MailDuom['delSiuntaNrPasVezeja']." nevažiuoja, prašome ją anuliuoti.
                    <br><br>
                    
                    Ačiū,
                    <br>
                    UAB Aurika,
                    <br>
                    Edita Kupčiūnienė | Transporto vadybininkė<br>
                    Chemijos g. 29F, LT-51333, Kaunas<br>
                    Mob.: +370 688 02736<br>
                    E-mail: transportas@aurika.lt<br>
                    <br><br>
                ";
                

                
                if(filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
                    

                    ///SMTP MAILAS
                    //$root_path = COMMON::getRootFolder();
                    //require_once ($root_path . "mail/class/SMTPMailer.php");
                    $mailObj = new SMTPMailer();
                    $mailObj->addTo($userEmail);
                    $mailObj->Subject($mailSubject);
                    $mailObj->Body($mailTXT);
                    $mailObj->addReplyTo($ReplayAddress);

                    
                    if ($mailObj->Send()){
                        $mailTXT .= "\r\n <br>pranešimas vežėjui: ". $userEmail ." išsiųstas sėkmingai\r\n";
                        $mailRez = "eMAIL SEND OK";
                        $mailSubject = "OK-". $mailSubject;// kad sumestu man i atskira folderi
                    }
                    else
                    {
                        $mailTXT .= "\r\n <br>pranešimas vežėjui: ". $userEmail ." NEišsiųstas !!!! \r\n";
                        $mailRez = "eMAIL SEND ERROR";
                        $mailSubject = "!!! NOTOK-". $mailSubject;// kad sumestu man i atskira folderi
                    }
                    
                    

                    $mailObj1 = new SMTPMailer();
                    $mailObj1->addTo('checkcermerror@aurika.lt');
                    $mailObj1->Subject($mailSubject);
                    $mailObj1->Body($mailTXT);
                    $mailObj1->addReplyTo($ReplayAddress);
                    $mailObj1->Send();

                    $mailObj3 = new SMTPMailer();
                    $mailObj3->addTo('transportas@aurika.lt');
                    $mailObj3->Subject($mailSubject);
                    $mailObj3->Body($mailTXT);
                    $mailObj3->addReplyTo($ReplayAddress);
                    //20250130 TIK TESTAVIMUI --- $mailObj3->Send();

                    //echo "************1**************";

                }else{
                    $this->addError("Laiškas vežėjui ".$MailDuom['delSiuntaVezejasReal']." apie siuntos ".$MailDuom['delSiuntaNrPasVezeja']." atšaukimą neišsiųstas. Neteisingas el. pašto adresas.");

                    $mailObj2 = new SMTPMailer();
                    $mailObj2->addTo('transportas@aurika.lt');
                    $mailObj2->Subject($mailSubject);
                    $mailObj2->Body($mailTXT);
                    $mailObj2->addReplyTo($ReplayAddress);
                    //20250130 TIK TESTAVIMUI --- $mailObj2->Send();

                    $mailObj4 = new SMTPMailer();
                    $mailObj4->addTo('arnas@aurika.lt');
                    $mailObj4->Subject($mailSubject);
                    $mailObj4->Body($mailTXT);
                    $mailObj4->addReplyTo($ReplayAddress);
                    $mailObj4->Send();

                    //echo "************2**************";

                }





        return $mailRez;
    }//end function



}//end class


////////////////////////PALEIDZIAM AJAX ///////////////

if (!SESSION::issetUserID()) {
    die("Access denied. User must login first!");
}
if(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $controller = new ajaxDelSiuntaController();
    $controller->run();
} else {
    if (!(strpos($_SERVER["SCRIPT_FILENAME"], 'index.php'))) {
        die('direct access is forbidden');
    }
}

?>
