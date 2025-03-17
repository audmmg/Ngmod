<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class nz_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

    }//end function 


    public function getNzArray ($nzUID=0){
        $rezDuom = array();
        if($Date AND $this->is_ID($masina)){

            $mysql = DBMySql::getInstance();
            $sql = "
                        select *
                        FROM CERM_OEEdienos
                        WHERE Date = '".$Date."' AND MASINAID = '".$masina."'
                ";

            $rezDuom = $mysql->querySqlOneRow($sql, 1);
            //$rezDuom = $mysql->querySql($sql, 1);

        }else{
            $this->AddError('Neteisingi užklausos duomenys.');
        }

        //!!!!!! DEBUG
        //$this->var_dump($dienosDuom, "rezDuom <hr>$sql");//-----------------DEBUG

        return $rezDuom;
    }//end function



    public function writeDienosOEE($masina, $Date){

        $pavyko = false;


        $DienosOEEDuom = $this->getSiandienMasinosOEE($masina, $Date);
     
        if(is_array($DienosOEEDuom) AND count($DienosOEEDuom)>0){

            //irasom dienos OEE duomenis (pagal masina/diena)
            try {
                //Insert multiple rows:
                $table_name="CERM_OEEdienos1";

                $insert_arr = array();
                $insert_arr[$table_name]['OEE']=$DienosOEEDuom['OEE'];
                $insert_arr[$table_name]['Date']=$DienosOEEDuom['Date'];
                $insert_arr[$table_name]['MASINAID']=$DienosOEEDuom['MASINAID'];
                $insert_arr[$table_name]['MASINA']=$DienosOEEDuom['MASINA'];
                $insert_arr[$table_name]['DIRBOID']=$DienosOEEDuom['DIRBOID'];
                $insert_arr[$table_name]['DIRBO']=$DienosOEEDuom['DIRBO'];
                $insert_arr[$table_name]['Pamaina']='';
                $insert_arr[$table_name]['IrasytaDateTime']=date("Y-m-d H:i:s");

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
                }else{
                    $this->AddError('Duomenų bazės klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }


        }//end if

        return $pavyko;

    }//end function


    public function getNaujausi(){
        $mysql = DBMySql::getInstance();
        
        $q="SELECT * FROM `jr_neatitiktis` WHERE `busena`='14' AND `nz_tipas`='0' || `busena`='99'";
        $rezult[0] = $mysql->querySql($q, 1); //neatitiktis uzsakovo    
        $q="SELECT * FROM `jr_neatitiktis` WHERE `busena`='14' AND `nz_tipas`='1' || `busena`='99'";
        $rezult[1] = $mysql->querySql($q, 1); //neatitiktis vidine    
        $q="SELECT * FROM `jr_tiekejui` WHERE `busena`='0' || `busena`='99'";
        $rezult[2] = $mysql->querySql($q, 1); //pretenzija           
        $q="SELECT * FROM `jr_auditas` WHERE `busena`='1' || `busena`='99'";
        $rezult[3] = $mysql->querySql($q, 1);  //auditas
        $q="SELECT * FROM `jr_neatitiktis` WHERE `busena`='14' AND `nz_tipas`='2' || `busena`='99'";
        $rezult[4] = $mysql->querySql($q, 1); //incidentas    

        return $rezult; 
    }
    
    public function getMyJobs($userId){
        $today=date("Y-m-d");
        $todayPlus2=date('Y-m-d', strtotime("+2 days"));
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_asmenys`.*, `jr_auditas`.pavadinimas, `jr_auditas`.dokumento_id FROM `jr_asmenys` 
        LEFT JOIN `jr_auditas` ON
        `jr_asmenys`.auditas_id=`jr_auditas`.uid
        WHERE `jr_asmenys`.busena!='4' and `jr_asmenys`.busena!='6' AND `jr_asmenys`.user_id='{$userId}' AND `jr_asmenys`.type='0'
        ORDER BY `data_iki` ASC";
        $rezult['auditai'] = $mysql->querySql($q, 1);
        if(count($rezult['auditai'])>0){
            foreach($rezult['auditai'] as $k=>$aud){
                if($aud['data_iki']<=$today)$rezult['auditai'][$k]['color']='red'; 
                if($aud['data_iki']>$today && $aud['data_iki']<=$todayPlus2)$rezult['auditai'][$k]['color']='yellow';    
            }
        } 
        
        $q="SELECT `jr_asmenys`.*, `jr_neatitiktis`.kliento_pavadinimas, `jr_neatitiktis`.dokumento_nr FROM `jr_asmenys` 
        LEFT JOIN `jr_neatitiktis` ON
        `jr_asmenys`.auditas_id=`jr_neatitiktis`.uid
        WHERE `jr_asmenys`.busena!='4' and `jr_asmenys`.busena!='6' AND `jr_asmenys`.user_id='{$userId}' AND `jr_asmenys`.type!='0'
        ORDER BY `data_iki` ASC";
        $rezult['neatitiktys'] = $mysql->querySql($q, 1);
        if(count($rezult['neatitiktys'])>0){
            foreach($rezult['neatitiktys'] as $k=>$aud){
                if($aud['data_iki']<=$today)$rezult['neatitiktys'][$k]['color']='red'; 
                if($aud['data_iki']>$today && $aud['data_iki']<=$todayPlus2)$rezult['neatitiktys'][$k]['color']='yellow';    
            }
        }
        
        
        return $rezult;   
    }
    
    public function getMyVisits($name){
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_vizitai`.* FROM `jr_vizitai` LEFT JOIN `jr_vizitai_asmenys` ON 
        `jr_vizitai`.dokumento_nr=`jr_vizitai_asmenys`.vizitas_id
        WHERE `asmuo` LIKE '{$name}%' and `busena`='0'  GROUP BY `jr_vizitai`.uid ORDER BY `date` ASC"; 
        $rezult = $mysql->querySql($q, 1); 
        $today=date("Y-m-d");
        $todayPlus2=date('Y-m-d', strtotime("+2 days"));
        if(count($rezult)>0){
            foreach($rezult as $k=>$aud){
                if($aud['date']<=$today)$rezult[$k]['color']='red'; 
                if($aud['date']>$today && $aud['date']<=$todayPlus2)$rezult[$k]['color']='yellow'; 
                $rezult[$k]['neatitiktis']=str_pad($aud['neatitiktis'], 6, '0', STR_PAD_LEFT);   
            }            
        }
        return $rezult;
    }
    
    public function migrate_products(){
        require_once ( $root_path. "modules/nz/nzCerm.mod.php");
        $nzCerm = new nzCerm_mod();
        
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_neatitiktis_produktai` WHERE `prgr` IS NULL || `Pavadinimas` IS NULL LIMIT 100";
        $rezult = $mysql->querySql($q, 1); 
        if(count($rezult)>0){
            foreach($rezult as $itm){
                $data=$nzCerm->getProductDataByID($itm['prodid']);
                $table_name="jr_neatitiktis_produktai";
                $insert_arr = array();
                $insert_arr[$table_name]['uid']=$itm['uid'];
                $insert_arr[$table_name]['prgr']=$data['PG_PavadinimasLT'];
                $insert_arr[$table_name]['Pavadinimas']=$data['Pavadinimas'];
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);
            }
        }
        echo "changed:".count($rezult)."<br /><br />";
    }
    
    public function migrate_loss(){
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_neatitiktis` WHERE `gu_id` IS NOT NULL AND `gu_id`!=''";
        $rez = $mysql->querySql($q, 1); 
        foreach($rez as $k=>$r){       
            $q="SELECT `dokumento_nr`, 
            SUM(`nuostolis`) AS vin,
            SUM(`papildomas_nuostolis`) AS pn,
            SUM(`viso_nuostolio`) AS bn
            FROM `jr_neatitiktis_produktai` WHERE `dokumento_nr`='{$r['uid']}'
            GROUP BY `dokumento_nr`";
            $rezult = $mysql->querySql($q, 1);  
            
            
            $table_name="jr_neatitiktis";
            $insert_arr[$table_name]['uid']=$r['uid'];    
            $insert_arr[$table_name]['pn']=$rezult[0]['pn'];
            $insert_arr[$table_name]['bn']=$rezult[0]['bn'];
            $insert_arr[$table_name]['vn']=$rezult[0]['vin'];
            $insert_arr[$table_name]['iin']=$rezult[0]['vin'];
            if($r['nz_tipas']==1){
                $insert_arr[$table_name]['iin']=0;    
            }
            else{
                $insert_arr[$table_name]['vn']=0;     
            }
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
            $ret = $mysql->updateData($insert_arr, $options);
        } 
        echo $k;   
    }
    
 public function migrate_loss2(){
        $from=$_GET['pg']*200;
        require_once ( $root_path. "modules/nz/nzCerm.mod.php");
        $nzCerm = new nzCerm_mod();
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_neatitiktis_produktai` ORDER BY `uid` ASC LIMIT {$from},200";
        $rez = $mysql->querySql($q, 1); 
        echo count($rez)."<br />";
        foreach($rez as $k=>$r){ 
            $data=$nzCerm->getProductDataByID($r['prodid']);
            if($data['MatVNTID']=='/1000' || $data['MatVNTID']=='7')$dalikllis=1000;else $dalikllis=1;
            $nuostolis=($r['broko_kiekis']*$r['pardavimo_kaina'])/$dalikllis;
            $nuostolis=number_format($nuostolis,2);
            $viso_nuostolio=number_format($nuostolis+$r['papildomas_nuostolis'],2);
            $table_name="jr_neatitiktis_produktai";
            $insert_arr[$table_name]['uid']=$r['uid'];   
            $insert_arr[$table_name]['nuostolis']=$nuostolis;   
            $insert_arr[$table_name]['viso_nuostolio']=$viso_nuostolio;   
            $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
            $ret = $mysql->updateData($insert_arr, $options);
        }
    }

    public function getVeluojantys($nr){
        $mysql = DBMySql::getInstance();
        
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='0' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `data` > NOW() - INTERVAL 6 DAY AND `busena`!='0' )";
        $rezult[0] = $mysql->querySql($q, 1); //neatitiktis uzsakovo    
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='1' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `data` > NOW() - INTERVAL 6 DAY AND `busena`!='0' )";
        $rezult[1] = $mysql->querySql($q, 1); //neatitiktis vidine    
        $q="SELECT * FROM `jr_tiekejui` WHERE `date` < NOW() - INTERVAL {$nr} DAY AND `date` > NOW() - INTERVAL 6 DAY AND `busena`!='6' ";
        $rezult[2] = $mysql->querySql($q, 1); //pretenzija           
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='2' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `data` > NOW() - INTERVAL 6 DAY AND `busena`!='0' )";
        $rezult[3] = $mysql->querySql($q, 1); //incidentas    

        return $rezult; 
    }
    
    public function getVeluojantys5($nr){
        $mysql = DBMySql::getInstance();
        
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='0' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `busena`!='0' )";
        $rezult[0] = $mysql->querySql($q, 1); //neatitiktis uzsakovo    
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='1' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `busena`!='0' )";
        $rezult[1] = $mysql->querySql($q, 1); //neatitiktis vidine    
        $q="SELECT * FROM `jr_tiekejui` WHERE `date` < NOW() - INTERVAL {$nr} DAY AND `busena`!='6' ";
        $rezult[2] = $mysql->querySql($q, 1); //pretenzija           
        $q="SELECT * FROM `jr_neatitiktis` WHERE `nz_tipas`='2' AND( `data` < NOW() - INTERVAL {$nr} DAY AND `busena`!='0' )";
        $rezult[3] = $mysql->querySql($q, 1); //incidentas    

        return $rezult; 
    }

    //------------------------------------
}//end class1
?>