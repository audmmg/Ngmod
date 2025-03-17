<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class auditas_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

    }//end function 
    
    public function getDocumentNr($onlyMax=0){
        $mysql = DBMySql::getInstance();
        $q="SELECT MAX(uid) as maks FROM jr_auditas";
        $rezult = $mysql->querySqlOneRow($q, 1);
        if($onlyMax==1)
            return $rezult['maks']+1;
        else
            return "AUD-".str_pad($rezult['maks']+1, 6, '0', STR_PAD_LEFT);
    }
    
    public function getPadaliniai(){
        $mysql = DBMySql::getInstance();
        $q="SELECT `PadalinysID`, `Padalinys` FROM `PadaliniaiPT5022`";
        $rezult = $mysql->querySql($q, 1);
        return $rezult;
    }
    
    public function saveAuditas(){
        
            $this->saveAuditPersons($_POST['uid'],$_POST['p']['darbuotojas']);
            try {
                //Insert multiple rows:
                $table_name="jr_auditas";

                $insert_arr = array();
                $insert_arr[$table_name]['date']=$_POST['date'];
                $insert_arr[$table_name]['dokumento_id']=$_POST{'uid'};
                $insert_arr[$table_name]['created_by']=$_POST{'created_by'};
                $insert_arr[$table_name]['audito_tipas']=$_POST{'audito_tipas'};
                $insert_arr[$table_name]['nz_tipas']=$_POST{'nz_tipas'};
                $insert_arr[$table_name]['pavadinimas']=$_POST{'pavadinimas'};
                $insert_arr[$table_name]['kliento_id']=$_POST{'kliento_id'};
                $insert_arr[$table_name]['busena']=$_POST{'busena'};
                $insert_arr[$table_name]['informacija']=$_POST{'informacija'};
                $insert_arr[$table_name]['atlikti_veiksmai']=$_POST{'atlikti_veiksmai'};
                $insert_arr[$table_name]['auditas_atliekamas']=$_POST{'auditas_atliekamas'};
                $insert_arr[$table_name]['client_name']=$_POST{'client_name'};
                $insert_arr[$table_name]['client_contact']=$_POST{'client_contact'};
                $insert_arr[$table_name]['statistika']=$_POST{'statistika'};
                if(!empty($_POST{'audito_padaliniai'})){
                    $insert_arr[$table_name]['audito_padaliniai']=";".implode(";",$_POST{'audito_padaliniai'}).";";
                }
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
    }
    
    public function getAuditasInfo($id=0){
        $mysql = DBMySql::getInstance();
        if($id==0)$id=$_GET['uid'];
        $q="SELECT * FROM jr_auditas WHERE `uid`='{$id}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        $rezult['files']=$this->getUploadedFiles($rezult['dokumento_id']);
        $selected="";
        $rezult['audito_padaliniai'] = substr($rezult['audito_padaliniai'], 1, -1);
        $selected=explode(';',$rezult['audito_padaliniai']);
        
        $padaliniai=$this->getPadaliniai();
        if(!empty($padaliniai)){
            foreach($padaliniai as $k=>$padalinys){
                if(in_array($padalinys['PadalinysID'],$selected)){
                    $padaliniai[$k]['selected']='selected';    
                }   
            }
        }
        $rezult['selected_array']=$selected;
        $rezult['padaliniai']=$padaliniai;
        $qr="SELECT * FROM `jr_auditas_asmenys` WHERE `neatitiktis_id`='{$rezult['uid']}'";
        $rezult['item_persons']=$mysql->querySql($qr, 1); 
        return $rezult;    
    }
    
    public function getUploadedFiles($nr){
        $ds="/";
        $storeFolder = 'uploads';
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "auditas" . $nr))return;
        $files = scandir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "auditas" . $nr);
        return $files;
    }

    public function editAuditas(){
            $this->saveAuditPersons($_GET['uid'],$_POST['p']['darbuotojas']);
            try {
                //Insert multiple rows:
                $table_name="jr_auditas";

                $insert_arr = array();
                $insert_arr[$table_name]['uid']=$_GET['uid'];
                $insert_arr[$table_name]['date']=$_POST['date'];
                $insert_arr[$table_name]['audito_tipas']=$_POST{'audito_tipas'};
                $insert_arr[$table_name]['nz_tipas']=$_POST{'nz_tipas'};
                $insert_arr[$table_name]['pavadinimas']=$_POST{'pavadinimas'};
                $insert_arr[$table_name]['kliento_id']=$_POST{'kliento_id'};
                $insert_arr[$table_name]['busena']=$_POST{'busena'};
                $insert_arr[$table_name]['informacija']=$_POST{'informacija'};
                $insert_arr[$table_name]['atlikti_veiksmai']=$_POST{'atlikti_veiksmai'};
               $insert_arr[$table_name]['auditas_atliekamas']=$_POST{'auditas_atliekamas'};
                $insert_arr[$table_name]['client_name']=$_POST{'client_name'};
                $insert_arr[$table_name]['client_contact']=$_POST{'client_contact'};
                $insert_arr[$table_name]['statistika']=$_POST{'statistika'};
                if(!empty($_POST{'audito_padaliniai'})){
                    $insert_arr[$table_name]['audito_padaliniai']=";".implode(";",$_POST{'audito_padaliniai'}).";";
                }
                
                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                    if($_POST['remove']){
                        $ds="/";
                        $storeFolder = 'uploads';
                        foreach($_POST['remove'] as $rem){
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "auditas" . $_POST['dokumento_id'] . $ds . $rem);
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
    

    public function getAllAuditai($filter){
        $mysql = DBMySql::getInstance();
        $table='jr_auditas';
        $search="`{$table}`.uid!=''";
    
        $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `uid` DESC";
        if($_GET['unset']==1)unset($_SESSION['auditas_filter']);
        if(isset($_SESSION['auditas_filter']) && empty($filter))$filter=$_SESSION['auditas_filter'];        
        if(!empty($filter)){
            $rezult['filter']=$filter;
            if($filter['dokumento_nr']!=""){$search.=" AND `{$table}`.dokumento_id='{$filter['dokumento_nr']}'";}
            if($filter['busena']!=""){$search.=" AND `{$table}`.busena='{$filter['busena']}'";} 
            if($filter['data_nuo']!=""){$search.=" AND `{$table}`.date>='{$filter['data_nuo']}'";} 
            if($filter['data_iki']!=""){$search.=" AND `{$table}`.data<='{$filter['data_iki']}'";}
            if($filter['darbuotojas']!=""){$search.=" AND  `jr_asmenys`.user_id='{$filter['darbuotojas']}'";}
            if($filter['created_by']!=""){$search.=" AND `{$table}`.created_by='{$filter['created_by']}'";} 
            if($filter['pavadinimas']!=""){$search.=" AND `{$table}`.pavadinimas LIKE '%{$filter['pavadinimas']}%'";} 
            if($filter['statistika']!=""){$search.=" AND `{$table}`.statistika='{$filter['statistika']}'";} 
            if($filter['not_statistika']!=""){$search.=" AND `{$table}`.statistika IS NULL";} 
            
            if($filter['susije_darbuotojas']!=""){$search.=" AND  `jr_auditas_asmenys`.asmuo LIKE '{$filter['susije_darbuotojas']} %'";} 
            
            if($filter['pavadinimas']!=""){$search.=" AND `{$table}`.pavadinimas LIKE '%{$filter['pavadinimas']}%'";} 
            if($filter['kliento_id']!=""){$search.=" AND `{$table}`.kliento_id LIKE '%{$filter['kliento_id']}%'";}
            if($filter['client_name']!=""){$search.=" AND `{$table}`.client_name LIKE '%{$filter['client_name']}%'";}
            
            /*atfiltravimas pagal grupe padaliniu arba pagal viena to padalinio grupe*/
if($filter['padalinys']!="" && $filter['padalinys']!="all_vadyba" && $filter['padalinys']!="all_dizainas"
            && $filter['padalinys']!="all_keg" && $filter['padalinys']!="all_kpg" && $filter['padalinys']!="all_kokybe" && $filter['padalinys']!="all_transport")
                {$search.=" AND `{$table}`.audito_padaliniai LIKE '%;{$filter['padalinys']};%'";}
            else{
                if($filter['padalinys']=="all_vadyba"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;Aptarnavimas;%' OR `{$table}`.audito_padaliniai LIKE '%;Pardavimai;%' OR `{$table}`.audito_padaliniai LIKE '%;Sąskaitininkės;%')";
                }  
                if($filter['padalinys']=="all_dizainas"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;Paruošimas;%' OR `{$table}`.audito_padaliniai LIKE '%;Repro;%')";
                }   
                if($filter['padalinys']=="all_keg"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;Dažų paruošėjas;%' OR `{$table}`.audito_padaliniai LIKE '%;Paruošėjas spaudai;%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Spaudėjas;%' OR `{$table}`.audito_padaliniai LIKE '%;Skaitmena (spauda);%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Skaitmena (kirtimas);%' OR `{$table}`.audito_padaliniai LIKE '%;Operatorius (pjovimas);%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Operatorius (folijavimas);%' OR `{$table}`.audito_padaliniai LIKE '%;Pakavimas;%' )";
                } 
                if($filter['padalinys']=="all_kpg"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;Dažų paruošėjas;%' OR `{$table}`.audito_padaliniai LIKE '%;Paruošėjas spaudai;%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Spaudėjas;%' OR `{$table}`.audito_padaliniai LIKE '%;Operatorius (pjovimas);%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Operatorius (laminavimas);%' OR `{$table}`.audito_padaliniai LIKE '%;Operatorius (aliuminis);%' OR
                    `{$table}`.audito_padaliniai LIKE '%;Operatorius (shober);%')";
                } 
                if($filter['padalinys']=="all_kokybe"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;QC;%' OR `{$table}`.audito_padaliniai LIKE '%;Laboratorija;%')";
                }  
                
                if($filter['padalinys']=="all_transport"){
                    $search.=" AND(`{$table}`.audito_padaliniai LIKE '%;TNT;%' OR `{$table}`.audito_padaliniai LIKE '%;Omniva;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Itella;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Venipak;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;ACE;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;UPS;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;DPD;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Raben;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Schenker;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;DSV;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;NTG;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;DHL Express;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;DHL Freight;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Transporto vadybininkas;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Delamode;%'
                    OR `{$table}`.audito_padaliniai LIKE '%;Kiti;%'
                    )";
                }  
            }                        
            
        
            $q="SELECT `{$table}`.* FROM `{$table}`
            LEFT JOIN `jr_asmenys` ON
            `{$table}`.uid=`jr_asmenys`.auditas_id
            
            LEFT JOIN `jr_auditas_asmenys` ON
            `{$table}`.uid=`jr_auditas_asmenys`.neatitiktis_id
            
             WHERE {$search} GROUP BY `{$table}`.uid ORDER BY `{$table}`.uid DESC";//var_dump($q);die;
            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            $limit=$filter['per_page'];
            if($filter['page']!="")$start=($filter['page']-1)*$limit;else $start=0; 
            $q.=" LIMIT {$start},{$limit}";
            $_SESSION['auditas_filter']=$filter;
        }else{
            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            $limit=25; 
            $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `uid` DESC LIMIT 0,{$limit}";   
        }
        $rezult['list'] = $mysql->querySql($q, 1);
        if(count($rezult['list'])>0){
            foreach($rezult['list'] as $k=>$itm){
                $rezult['list'][$k]['files']=$this->getUploadedFiles($itm['dokumento_id']);
                $rezult['list'][$k]['darbuotojai']=$this->susijeAsmenys($itm['uid']);
            }
        }
        //$this->var_dump($rezult['list']);die;
        
        $rezult['page']=array_chunk($allList,$limit);
        $rezult['filternames']=$this->getNamesForFilter();
        $rezult['counted']=count($allList);
        return $rezult;  
    }
    
   public function susijeAsmenys($uid=0){
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_asmenys`.*, `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde FROM jr_asmenys
        LEFT JOIN `DarbuotojaiInfoPT5021` ON
        `jr_asmenys`.user_id=`DarbuotojaiInfoPT5021`.DarbuotojasID
         WHERE `auditas_id`='{$uid}' and `type`='0' ";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;       
   }

    public function getNamesForFilter(){
        $mysql = DBMySql::getInstance(); 
        $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde, `DarbuotojaiInfoPT5021`.uid 
        FROM `DarbuotojaiInfoPT5021` ORDER BY `vardas` ASC";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;   
   }
   
    function saveAuditPersons($neatitiktisID,$asmenys){
        $neatitiktisID=str_replace("AUD-","",$neatitiktisID);
        
        if(count($asmenys>0)&& !empty($asmenys)){
            foreach($asmenys as $asmuo){
                $table_name="jr_auditas_asmenys"; 
                $insert_arr = array();
                $insert_arr[$table_name]['neatitiktis_id']=$neatitiktisID; 
                //$insert_arr[$table_name]['produkto_id']=$produktoID;  
                $insert_arr[$table_name]['asmuo']=$asmuo; 
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);
            }
        }
    }

   public function deletePerson($uid){
            $query="DELETE FROM `jr_auditas_asmenys` WHERE `uid`='{$uid}'";
            DBMySql::deleteSimple($query);
            return "trina";  
   }
   
   /*issaugom rootcase ataskaitos suvestus duomenis*/
   public function saveRootCause($post){
        $mysql = DBMySql::getInstance();
            try {
                //Insert multiple rows:
                $table_name="jr_root_cause";

                $insert_arr = array();
                $insert_arr[$table_name]['why1']=$post['why1'];
                $insert_arr[$table_name]['why2']=$post['why2'];
                $insert_arr[$table_name]['why3']=$post['why3'];
                $insert_arr[$table_name]['why4']=$post['why4'];
                $insert_arr[$table_name]['why5']=$post['why5'];
                $insert_arr[$table_name]['because1']=$post['because1'];
                $insert_arr[$table_name]['because2']=$post['because2'];
                $insert_arr[$table_name]['because3']=$post['because3'];
                $insert_arr[$table_name]['because4']=$post['because4'];
                $insert_arr[$table_name]['because5']=$post['because5'];
                $insert_arr[$table_name]['similar']=$post['similar'];
                $insert_arr[$table_name]['rootcause']=$post['rootcause'];
                $insert_arr[$table_name]['date']=$post['date'];
                $insert_arr[$table_name]['id']=$post['uid'];
                $insert_arr[$table_name]['type']='1';
                if(!empty($post['uid']))
                $insert_arr[$table_name]['uid']='9'.$post['uid'];
                
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
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
            
           
           $this->saveRootPersons($post['susijes'], $post['uid'], '1');    
              
   }
   
   public function saveRootPersons($persons, $id, $type) {
        $mysql = DBMySql::getInstance();
        $table_name = 'jr_root_persons';
            try {
                $whereyra = " root_id ='".$id."' AND type='{$type}'";
                $Del_table_name='jr_root_persons';
                $delete_where_arr = array(
                    $Del_table_name => $whereyra
                );
                $options = array(
                    'log_data' => array(
                        'document_id' => ''
                    )
                );                
                $mysql->deleteData($delete_where_arr, $options);
            }catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            } 
            if(!empty($persons))
            foreach ($persons as $person) {
                try {
                    $insert_arr = array();
                    $insert_arr[$table_name]['root_id'] = $id;
                    $insert_arr[$table_name]['type'] = $type;
                    $insert_arr[$table_name]['person'] = $person;           
                    $options = array (
                        'log_data' => array( // 'log' => true,
                            'document_id' =>  $tempSamataData['CERM_OEEdienos']
                        )
                    );
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
            }
   }
   
   public function getsavedRootCausePersons($id){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_root_persons` WHERE `root_id`='{$id}' and `type` = '1'"; 
        $rezult = $mysql->querySql($q, 1); 
        return $rezult;            
   }
   
   public function getsavedRootCause($id){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_root_cause` WHERE `id`='{$id}' and `type` = '1'"; 
        $rezult = $mysql->querySqlOneRow($q, 1); 
        return $rezult;            
   }
    //------------------------------------
}//end class1
?>