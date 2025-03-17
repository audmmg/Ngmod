<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class asmenys_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

    }//end function

   public function asmenysList($type=0){
        $uid=$_GET['uid'];
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_asmenys`.*, `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde FROM jr_asmenys
        LEFT JOIN `DarbuotojaiInfoPT5021` ON
        `jr_asmenys`.user_id=`DarbuotojaiInfoPT5021`.uid
         WHERE `auditas_id`='{$uid}' AND `type`='{$type}' && `auditas_id`!=''";
        $rezult = $mysql->querySql($q, 1);
        if(count($rezult)>0){
            foreach($rezult as $k=>$r){
                $files=$this->getUploadedFiles(str_pad($r['auditas_id'], 6, '0', STR_PAD_LEFT),$r['user_id'],$type);
                //var_dump($r);
                $rezult[$k]['files']=count($files);
            }
        }


    return $rezult;
   }

   public function getNames(){
        $mysql = DBMySql::getInstance();
        $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde, `DarbuotojaiInfoPT5021`.DarbuotojasID , `DarbuotojaiInfoPT5021`.uid
        FROM `DarbuotojaiInfoPT5021` WHERE `DarbuotojaiInfoPT5021`.Dirba = 'Y' ORDER BY `vardas` ASC";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;
   }

   public function addPerson($author){
            try {
                //Insert multiple rows:
                $table_name="jr_asmenys";

                $insert_arr = array();
                $insert_arr[$table_name]['user_id']=$_POST['vardas'];
                $insert_arr[$table_name]['auditas_id']=$_POST['uid'];
                $insert_arr[$table_name]['data_iki']=$_POST['data_iki'];
                $insert_arr[$table_name]['uzduotis']=$_POST['uzduotis'];
                $insert_arr[$table_name]['busena']=$_POST['busena'];
                $insert_arr[$table_name]['type']=$_POST['type'];
                $insert_arr[$table_name]['padalinys']=$_POST['pad'];
                $insert_arr[$table_name]['veiksmas']=$_POST['veiksm'];
                $insert_arr[$table_name]['sukurta']=date("Y-m-d");
                $insert_arr[$table_name]['author']=$author;
                // optional DATA:
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );


                //!!!!!! DEBUG
                $this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);

                $worker=$this->getWorkerInfo($_POST['vardas']);
                //!!!!!! DEBUG
                $this->var_dump($worker, "--- worker ".$_POST['vardas']." ");//-----------------DEBUG

                //mail($worker['Email'],"Jums paskirta nauja uzduotis", 'Jums paskirta nauja uzduotis '.str_pad($_POST['uid'], 6, '0', STR_PAD_LEFT)." ".$_POST['uzduotis']." atlikti iki ".$_POST['data_iki']." ".$_POST['linkas']);





                /************email****************/
    /*        $headers = "MIME-Version: 1.0\r\n";
            $headers .= "From: ngmod@aurika.lt\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  */

$mailTXT="<HTML>
<HEAD>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <style>
        html,body {
            margin:0;
            padding:0;
            width:100%;
            height:100% !important;
            min-height:100%;
            background:#FFF;
            font-family: Verdana, Arial, Tahoma, sans-serif;
            color:#555;
            font-size: 12px;
            text-align: center;
        }
    </style>
</HEAD>
<BODY>";
$mysql = DBMySql::getInstance();
$q="SELECT * FROM `DarbuotojaiInfoPT5021` WHERE `uid`='".SESSION::getUserID()."'";
$rezult = $mysql->querySqlOneRow($q, 1);
$paskyre=$rezult['Vardas']." ".$rezult['Pavarde'];
$mailTXT.='<p>Jums paskirta nauja uzduotis '.str_pad($_POST['uid'], 6, '0', STR_PAD_LEFT)." ".$_POST['uzduotis']." atlikti iki ".$_POST['data_iki']." <a href='".$_POST['linkas']."' target='_blank'>".$_POST['linkas']."</a> Užduotį skyrė:".$paskyre."</p>";
$mailTXT.="</BODY></HTML>";

          //  mail($worker['Email'],"Jums paskirta nauja uzduotis",$mailTXT,$headers);

                /******************email******/


                                ///SMTP MAILAS

                              $root_path = COMMON::getRootFolder();
                              require_once ($root_path . "mail/class/SMTPMailer.php");

                              $mail = new SMTPMailer();

                            //pagal JIRA IT-8197
                            //20240123 - is saraso isimam sandra.s pagal uzduoti IT-16355
                            $emailGroup = array ('simona.urbonas@aurika.lt', 'agne.rutkauskiene@aurika.lt');
                            if(in_array($worker['Email'], $emailGroup)){
                                $workerEmail='aurika-intersnack@aurika.lt';
                            }else{
                                $workerEmail=$worker['Email'];
                            }


                              //$mail->addTo($worker['Email']);
                                $mail->addTo($workerEmail);

                                  $mail->Subject('Jums paskirta nauja uzduotis');
                                          $mail->Body(
                                            $mailTXT
                                            );


                                          if ($mail->Send()){
                                              //echo 'Pavyko issiusti. '.$worker['Email'];
                                                echo 'Pavyko issiusti. '.$workerEmail;
                                          }
                                          else
                                          {
                                            //echo 'Nepavyko issiusti. '.$worker['Email'];
                                            echo 'Nepavyko issiusti. '.$workerEmail;
                                          }




                                ///SMTP MAILAS


                die;
                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                }else{
                    $this->AddError('Duomenų bazės klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }

   }

   public function getWorkerInfo($uid){
    $mysql = DBMySql::getInstance();
    $q="SELECT * FROM `DarbuotojaiInfoPT5021` WHERE `uid`='{$uid}'";
    $rezult = $mysql->querySqlOneRow($q, 1);

    //!!!!!! DEBUG
    $this->var_dump($rezult, "emailo paieska<hr>$q");//-----------------DEBUG

    return $rezult;
   }

   public function getPersonTask($id,$type){
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_asmenys`.*, `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde FROM `jr_asmenys`
        LEFT JOIN `DarbuotojaiInfoPT5021` ON
        `jr_asmenys`.user_id=`DarbuotojaiInfoPT5021`.uid
         WHERE `jr_asmenys`.uid='{$id}' AND `jr_asmenys`.type='{$type}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        $rezult['dokumento_id']=str_pad($rezult['auditas_id'], 6, '0', STR_PAD_LEFT);
        $rezult['files']=$this->getUploadedFiles($rezult['dokumento_id'],$rezult['user_id'],$type);
        return $rezult;
   }

    public function getUploadedFiles($nr,$userid,$type){
        if($type==0)$folder="auditasAUD-";
        if($type==1)$folder="pretenzija";
        $ds="/";

        //$storeFolder = 'uploads';echo COMMON::getRootFolder() . $ds. $storeFolder . $ds . $folder . $nr . $ds . $userid;die;
        if (!file_exists(COMMON::getRootFolder() . $ds. "uploads/". $storeFolder . $ds . $folder . $nr . $ds . $userid))return;
        $files = scandir(COMMON::getRootFolder() . $ds. "uploads/". $storeFolder . $ds . $folder . $nr . $ds . $userid);
        return $files;
    }

    public function getUploadedImages($nr,$userid,$type){
        $ImagesArray = [];
        if($type==0)$folder="auditas";
        if($type==1)$folder="pretenzija";
        if($type==3)$folder="auditasAUD-";
        $ds="/";
        $storeFolder = 'uploads';
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $folder . $nr . $ds . $userid))return;
        $files = scandir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $folder . $nr . $ds . $userid);
        $file_display = [ 'jpg', 'jpeg', 'png', 'gif' ];
        foreach($files as $file){
            $file_type = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($file_type, $file_display) == true) {
                $ImagesArray[] = $file;
            }
        }
        return $ImagesArray;
    }

    public function updateTask($type=0){
            if($type==0)$folder="auditas";
            if($type==1)$folder="pretenzija";
            try {
                //Insert multiple rows:
                $table_name="jr_asmenys";

                $insert_arr = array();
                $insert_arr[$table_name]['uid']=$_POST['uid'];
                $insert_arr[$table_name]['data_iki']=$_POST['data_iki'];
                $insert_arr[$table_name]['uzduotis']=$_POST['uzduotis'];
                $insert_arr[$table_name]['uzduotislang']=$_POST['uzduotislang'];
                $insert_arr[$table_name]['busena']=$_POST['busena'];
                $insert_arr[$table_name]['veiksmas']=$_POST['veiks'];
                if($_POST['busena']==4)
                    $insert_arr[$table_name]['baigta']=date("Y-m-d H:i:s");



                // optional DATA:
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );


                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                    if(!empty($_POST['removef'])){
                        $ds="/";
                        $storeFolder = 'uploads';
                        $removes=explode('8mx8',$_POST['removef']);
                        foreach($removes as $rem){echo COMMON::getRootFolder() . $ds. $storeFolder . $ds . $folder . $_POST['fo1'] . $ds . $_POST['fo2'] .$ds .$rem;
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $folder . $_POST['fo1'] . $ds . $_POST['fo2'] .$ds .$rem);
                        }
                    }
                }else{
                    $this->AddError('Duomenų bazės klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }
    }

    public function getAllUzduotys($filter){
        $table="jr_asmenys";
        $search="`{$table}`.uid!=''";
        $mysql = DBMySql::getInstance();
        $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `data_iki` DESC";
        if($_GET['unset']==1)unset($_SESSION['uzduotys_filter']);
        if(isset($_SESSION['uzduotys_filter']) && empty($filter))$filter=$_SESSION['uzduotys_filter'];
        if(!empty($filter)){
            $rezult['filter']=$filter;

            if($filter['data_nuo']!=""){$search.=" AND `{$table}`.data_iki>='{$filter['data_nuo']}'";}
            if($filter['data_iki']!=""){$search.=" AND `{$table}`.data_iki<='{$filter['data_iki']}'";}

            if($filter['sukurta']!=""){$search.=" AND `{$table}`.sukurta='{$filter['sukurta']}'";}
            if($filter['baigta']!=""){$search.=" AND `{$table}`.baigta LIKE '{$filter['baigta']}%'";}
            if($filter['author']!=""){$search.=" AND  `{$table}`.author='{$filter['author']}'";}
            if($filter['darbuotojas']!=""){$search.=" AND  `{$table}`.user_id='{$filter['darbuotojas']}'";}
            if($filter['created_by']!=""){$search.=" AND `{$table}`.created_by='{$filter['created_by']}'";}
            if($filter['type']!=""){$search.=" AND `{$table}`.type='{$filter['type']}'";}
            if($filter['busena']!=""){$search.=" AND `{$table}`.busena='{$filter['busena']}'";}
            if($filter['neatitiktis']!=""){$search.=" AND `{$table}`.auditas_id LIKE '%{$filter['neatitiktis']}%'";}
            if($filter['veiksmas']!=""){$search.=" AND `{$table}`.veiksmas='{$filter['veiksmas']}'";}

            /*atfiltravimas pagal grupe padaliniu arba pagal viena to padalinio grupe*/
            if($filter['padalinys']!="" && $filter['padalinys']!="all_vadyba" && $filter['padalinys']!="all_dizainas"
            && $filter['padalinys']!="all_keg" && $filter['padalinys']!="all_kpg" && $filter['padalinys']!="all_kokybe" && $filter['padalinys']="all_transport")
                {$search.=" AND padalinys LIKE '%{$filter['padalinys']}%'";}
            else{
                if($filter['padalinys']=="all_vadyba"){
                    $search.=" AND(padalinys LIKE '%Aptarnavimas%' OR padalinys LIKE '%Pardavimai%' OR padalinys LIKE '%Sąskaitininkės%')";
                }
                if($filter['padalinys']=="all_dizainas"){
                    $search.=" AND(padalinys LIKE '%Paruošimas%' OR padalinys LIKE '%Repro%')";
                }
                if($filter['padalinys']=="all_keg"){
                    $search.=" AND(padalinys LIKE '%Dažų paruošėjas%' OR padalinys LIKE '%Paruošėjas spaudai%' OR
                    padalinys LIKE '%Spaudėjas%' OR padalinys LIKE '%Skaitmena (spauda)%' OR
                    padalinys LIKE '%Skaitmena (kirtimas)%' OR padalinys LIKE '%Operatorius (pjovimas)%' OR
                    padalinys LIKE '%Operatorius (folijavimas)%' OR padalinys LIKE '%Pakavimas%' )";
                }
                if($filter['padalinys']=="all_kpg"){
                    $search.=" AND(padalinys LIKE '%Dažų paruošėjas%' OR padalinys LIKE '%Paruošėjas spaudai%' OR
                    padalinys LIKE '%Spaudėjas%' OR padalinys LIKE '%Operatorius (pjovimas)%' OR
                    padalinys LIKE '%Operatorius (laminavimas)%' OR padalinys LIKE '%Operatorius (aliuminis)%' OR
                    padalinys LIKE '%Operatorius (shober)%')";
                }
                if($filter['padalinys']=="all_kokybe"){
                    $search.=" AND(padalinys LIKE '%QC%' OR padalinys LIKE '%Laboratorija%')";
                }

                if($filter['padalinys']=="all_transport"){
                    $search.=" AND(padalinys LIKE '% TNT %' OR padalinys LIKE '% Omniva %'
                    OR padalinys LIKE '% Itella %'
                    OR padalinys LIKE '% Venipak %'
                    OR padalinys LIKE '% ACE %'
                    OR padalinys LIKE '% UPS %'
                    OR padalinys LIKE '% DPD %'
                    OR padalinys LIKE '% Raben %'
                    OR padalinys LIKE '% Schenker %'
                    OR padalinys LIKE '% DSV %'
                    OR padalinys LIKE '% NTG %'
                    OR padalinys LIKE '% DHL Express %'
                    OR padalinys LIKE '% DHL Freight %'
                    OR padalinys LIKE '% Transporto vadybininkas %'
                    OR padalinys LIKE '% Delamode %'
                    OR padalinys LIKE '% Kiti %'
                    )";
                }
            }

            $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde,`{$table}`.* FROM `{$table}`
            LEFT JOIN  `DarbuotojaiInfoPT5021` ON
            `{$table}`.user_id=`DarbuotojaiInfoPT5021`.uid
             WHERE {$search} GROUP BY `{$table}`.uid ORDER BY `{$table}`.busena ASC, `{$table}`.data_iki DESC";//var_dump($q);die;

            $list=$mysql->querySql($q, 1);
            if(count($list)>0){
                foreach($list as $k=>$ls){
                    $q="SELECT * FROM `DarbuotojaiInfoPT5021` WHERE `uid`='".$ls['author']."'";
                    $rezult = $mysql->querySqlOneRow($q, 1);
                    $list[$k]['paskyre']=$rezult['Vardas']." ".$rezult['Pavarde'];
                }
                $rezult['list']=$list;
            }
            $_SESSION['uzduotys_filter']=$filter;
        }else{
            $rezult['list']="";
        }
        $rezult['filter']=$filter;
        //$rezult['list'] = $mysql->querySql($q, 1);
        $rezult['filternames']=$this->getNamesForFilter();
        //var_dump($rezult['list']);die;
        return $rezult;
    }


    public function getNamesForFilter(){
        $mysql = DBMySql::getInstance();
        $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde, `DarbuotojaiInfoPT5021`.uid
        FROM `DarbuotojaiInfoPT5021` WHERE `DarbuotojaiInfoPT5021`.Dirba = 'Y' ORDER BY `vardas` ASC";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;
   }

   public function deletePerson($uid){
            $query="DELETE FROM `jr_asmenys` WHERE `uid`='{$uid}'";
            DBMySql::deleteSimple($query);
            return "trina";
   }
    //------------------------------------
}//end class1
?>
