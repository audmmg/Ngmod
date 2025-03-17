<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class vizitai_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();
        require_once ( $root_path. "modules/nz/nzCerm.mod.php");
        $this->nzCerm = new nzCerm_mod();
    }//end function 
    
    /*suformuojam dokumento numeri
    1-grazina tik skaiciuka naujaji
    0-grazina pilna numeri su raidemis
    */ 
    public function getDocumentNr($onlyMax=0,$pretenzijaType=0){
        if($pretenzijaType==0)$table='jr_vizitai';
        //if($pretenzijaType==1)$table='jr_neatitiktis_vid';
        $mysql = DBMySql::getInstance();
        $q="SELECT MAX(uid) as maks FROM `{$table}`";
        $rezult = $mysql->querySqlOneRow($q, 1);
        if($onlyMax==1)
            return $rezult['maks']+1;
        else{
            return str_pad($rezult['maks']+1, 6, '0', STR_PAD_LEFT);
            //if($pretenzijaType==0)return "NZ-".str_pad($rezult['maks']+1, 6, '0', STR_PAD_LEFT);
            //if($pretenzijaType==1)return "VN-".str_pad($rezult['maks']+1, 6, '0', STR_PAD_LEFT);
        }
            
    }   
    
    public function get_neatitiktys(){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM jr_neatitiktis ORDER BY `uid` DESC";  
        $rezult = $mysql->querySql($q, 1);  
        return $rezult;
    }
    
    //issaugom arba atnaujiname duomenis
    public function save($post){
            $this->saveVizitPersons($post['dokumento_nr'],$post['darbuotojas']);
            try {
                //Insert multiple rows:
                $table_name="jr_vizitai";
                
                $insert_arr = array();
                if(!empty($_GET['uid'])){
                    $insert_arr[$table_name]['uid']=$_GET['uid'];                  
                }

                if(empty($_GET['uid'])){
                    $insert_arr[$table_name]['dokumento_nr']=$post['dokumento_nr'];
                    $insert_arr[$table_name]['created_by']=$post['created_by'];
                    $insert_arr[$table_name]['created']=$post['created'];      
                }
                $insert_arr[$table_name]['date']=$post['date'];
                $insert_arr[$table_name]['client']=$post['client']; 
                $insert_arr[$table_name]['neatitiktis']=$post['neatitiktis'];
                $insert_arr[$table_name]['tikslas']=$post['tikslas']; 
                $insert_arr[$table_name]['rezultatas']=$post['rezultatas']; 
                $insert_arr[$table_name]['busena']=$post['busena']; 
                $insert_arr[$table_name]['statistika']=$post['statistika']; 
                // optional DATA:
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
                    if($_POST['remove']){
                        $ds="/";
                        $storeFolder = 'uploads';
                        foreach($_POST['remove'] as $rem){
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "vizitas" . $post['dokumento_nr'] . $ds . $rem);
                        }
                    }
                }else{
                    $this->AddError('Duomenu bazes klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }        
    }
    
    function saveVizitPersons($neatitiktisID,$asmenys){
        if(count($asmenys>0)&& !empty($asmenys)){
            foreach($asmenys as $asmuo){
                $table_name="jr_vizitai_asmenys"; 
                $insert_arr = array();
                $insert_arr[$table_name]['vizitas_id']=$neatitiktisID; 
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
    
    function getAllVisits($filter=""){
        $table='jr_vizitai';
        $search="`{$table}`.uid!=''";
        $mysql = DBMySql::getInstance();
        $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `busena`, `date` ASC";
        if($_GET['unset']==1)unset($_SESSION['vizitai_filter']);
        if(isset($_SESSION['vizitai_filter']) && empty($filter))$filter=$_SESSION['vizitai_filter'];        
        if(!empty($filter)){
            $rezult['filter']=$filter;
            if($filter['dokumento_nr']!=""){$search.=" AND `{$table}`.dokumento_nr='{$filter['dokumento_nr']}'";}
            if($filter['date']!=""){$search.=" AND `{$table}`.date='{$filter['date']}'";}
            if($filter['client']!=""){$search.=" AND `{$table}`.client='{$filter['client']}'";}
            if($filter['statistika']!=""){$search.=" AND `{$table}`.statistika='{$filter['statistika']}'";}
            if($filter['not_statistika']!=""){$search.=" AND `{$table}`.statistika IS NULL";}
            if($filter['busena']!=""){$search.=" AND `{$table}`.busena='{$filter['busena']}'";}
            if($filter['darbuotojas']!=""){$search.=" AND  `jr_vizitai_asmenys`.asmuo LIKE '{$filter['darbuotojas']}%'";}
            
           
            
            $q="SELECT `{$table}`.* FROM `{$table}`
            LEFT JOIN `jr_vizitai_asmenys` ON
            `{$table}`.dokumento_nr=`jr_vizitai_asmenys`.vizitas_id
            
             WHERE {$search} GROUP BY `{$table}`.uid ORDER BY `{$table}`.busena ASC";//var_dump($q);die;
            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            $limit=$filter['per_page'];
            if($filter['page']!="")$start=($filter['page']-1)*$limit;else $start=0; 
            $q.=" LIMIT {$start},{$limit}";
            $_SESSION['vizitai_filter']=$filter;
        }else{
            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            $limit=25; 
            $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `busena` ASC LIMIT 0,{$limit}";   
        }
        $rezult['list'] = $mysql->querySql($q, 1);
        if(count($rezult['list'])>0){
            foreach($rezult['list'] as $k=>$itm){
                $rezult['list'][$k]['files']=$this->getUploadedFiles($itm['dokumento_nr']);  
                $rezult['list'][$k]['neatitiktis']=str_pad($itm['neatitiktis'], 6, '0', STR_PAD_LEFT);
                $rezult['list'][$k]['darbuotojai']=$this->susijeAsmenys($itm['uid']);
                
            }
        }
        //$this->var_dump($rezult['list']);die;
        
        $rezult['page']=array_chunk($allList,$limit);
        $rezult['filternames']=$this->getNamesForFilter();
        $rezult['counted']=count($allList);//var_dump($rezult);
        return $rezult;        
    } 
   public function susijeAsmenys($uid=0){
        $uid=str_pad($uid, 6, '0', STR_PAD_LEFT);
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_vizitai_asmenys`.*FROM jr_vizitai_asmenys
         WHERE `vizitas_id`='{$uid}' ";
        $rezult = $mysql->querySql($q, 1);
        if (!empty($rezult)){
            foreach($rezult as $key=>$itm){
                if (strpos($itm['asmuo'], 'Įtraukta į statistiką') !== false) {
                    $rezult[$key]['statistic'] = true;
                    $rezult[$key]['asmuo'] = str_replace("Įtraukta į statistiką", "", $itm['asmuo']);
                } else {
                    $rezult[$key]['statistic'] = false;    
                }
            }
        }
    return $rezult;       
   }    
    public function getUploadedFiles($nr){
        $ds="/";
        $storeFolder = 'uploads';
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "vizitas" . $nr))return;
        $files = scandir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "vizitas" . $nr);
        return $files;
    }
    public function getNamesForFilter(){
        $mysql = DBMySql::getInstance(); 
        $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde, `DarbuotojaiInfoPT5021`.uid 
        FROM `DarbuotojaiInfoPT5021` ORDER BY `vardas` ASC";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;   
   }
    
   public function deleteVisit($uid){
            $mysql = DBMySql::getInstance();
            try {
                $whereyra = " uid ='".$uid."'";
                $Del_table_name='jr_vizitai';
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
            return "trina";  
   }
   
   //gaunam konkretaus uzsakymo info
   public function getInfo($uid=0){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM jr_vizitai WHERE `uid`='{$uid}'";  
        $rezult = $mysql->querySqlOneRow($q, 1);
        $rezult['files']=$this->getUploadedFiles($rezult['dokumento_nr']); 
        $rezult['item_persons']=$this->getVisitPersons($rezult['dokumento_nr']);
        return $rezult;    
   }
   
   public function getVisitPersons($nr){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM jr_vizitai_asmenys WHERE `vizitas_id`='{$nr}'" ;
        $rezult = $mysql->querySql($q, 1);
        return $rezult;
   }
   
   public function deletePerson($uid){
            $query="DELETE FROM `jr_vizitai_asmenys` WHERE `uid`='{$uid}'";
            DBMySql::deleteSimple($query);
            return "trina";      
   }

    //------------------------------------
}//end class1
?>