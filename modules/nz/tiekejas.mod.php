<?php
/////////////TESTAS, TESTAS, TESTAS, TESTAS///////////
$testas = "yra";





error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class tiekejas_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

    }//end function 
    
    public function getDocumentNr($onlyMax=0){
        $mysql = DBMySql::getInstance();
        $q="SELECT MAX(uid) as maks FROM jr_tiekejui";
        $rezult = $mysql->querySqlOneRow($q, 1);
        if($onlyMax==1)
            return $rezult['maks']+1;
        else
            return str_pad($rezult['maks']+1, 6, '0', STR_PAD_LEFT);
    }
    
    public function save($post,$ritiniai){
        
            $mysql = DBMySql::getInstance();
            try {
                //Insert multiple rows:
                $table_name="jr_tiekejui";
                //if($post['pretenzija_type']==1)$table_name="jr_neatitiktis_vid";

                $insert_arr = array();
                if(!empty($_GET['uid']))
                $insert_arr[$table_name]['uid']=$_GET['uid'];
                $insert_arr[$table_name]['created_by']=$post['created_by'];
                $insert_arr[$table_name]['date']=$post['date'];
                if(empty($_GET['uid']))
                $insert_arr[$table_name]['dokumento_id']=$post['dokumento_id'];
                $insert_arr[$table_name]['created_by']=$post['created_by'];
                $insert_arr[$table_name]['zaliava_id']=$post['zaliava_id'];
                $insert_arr[$table_name]['zaliavos_pavadinimas']=$post['zaliavos_pavadinimas'];
                $insert_arr[$table_name]['tiekejo_artikelis']=$post['tiekejo_artikelis'];
                $insert_arr[$table_name]['pretenzijos_tipas']=$post['pretenzijos_tipas'];
                $insert_arr[$table_name]['busena']=$post['busena'];
                $insert_arr[$table_name]['zaliavos_tiekejas']=$post['zaliavos_tiekejas'];
                $insert_arr[$table_name]['kontaktinis_asmuo']=$post['kontaktinis_asmuo'];
                $insert_arr[$table_name]['papildoma_informacija']=$post['papildoma_informacija'];
                $insert_arr[$table_name]['tyrimo_eiga']=$post['tyrimo_eiga'];
                $insert_arr[$table_name]['isvada']=$post['isvada'];
                
                $this->set_state($post['dokumento_id'],$post['busena']);
                
                if(count($post['padariniai_ch']))
                $insert_arr[$table_name]['padariniai_ch']=implode(";",$post['padariniai_ch']);
                else
                $insert_arr[$table_name]['padariniai_ch'] = null;
                
                $insert_arr[$table_name]['padariniai_other']=$post['padariniai_other'];
                
                if(count($post['defektas']))
                $insert_arr[$table_name]['defektas']=implode(";",$post['defektas']);
                else
                $insert_arr[$table_name]['defektas'] = null;
                
                if(count($post['adding']))
                $insert_arr[$table_name]['adding']=implode(";",$post['adding']);
                else
                $insert_arr[$table_name]['adding'] = null;
                
                $insert_arr[$table_name]['priezastis']=$post['priezastis'];
                $insert_arr[$table_name]['kiekis']=$post['kiekis'];
                $insert_arr[$table_name]['matas']=$post['matas'];
                $insert_arr[$table_name]['reikalavimai']=$post['reikalavimai'];
                $insert_arr[$table_name]['suma']=$post['suma'];
               
                
                $this->saveRitiniai($ritiniai,$post['dokumento_id']);
                
                // optional DATA:
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );

                
                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                
                $ret = $mysql->updateData($insert_arr, $options);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                    if($_POST['remove']){
                        $ds="/";
                        $storeFolder = 'uploads';
                        foreach($_POST['remove'] as $rem){
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "tiekejas" . $_POST['p']['dokumento_id'] . $ds . $rem);
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
    
    public function saveRitiniai ($post,$dokumento_id){
       
            $mysql = DBMySql::getInstance();
            try {
                $whereyra = " dokumento_nr ='".$dokumento_id."'";
                $Del_table_name='jr_tiekejui_rulonai';
                $delete_where_arr = array(
                    $Del_table_name => $whereyra
                );
                $options = array(
                    'log_data' => array()
                );
                $mysql->deleteData($delete_where_arr, $options);
                //$mysql->querySql("DELETE FROM `jr_tiekejui_rulonai` WHERE `dokumento_nr`='{$dokumento_id}'");
            }catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            } 
            
            
            if(!empty($post))
            foreach($post as $k=>$p)
            try {
                //Insert multiple rows:
                $table_name="jr_tiekejui_rulonai";

                $insert_arr = array();
                $insert_arr[$table_name]['id']=$p['id'];
                $insert_arr[$table_name]['sandelis']=$p['sandelis'];
                $insert_arr[$table_name]['dokumento_nr']=$dokumento_id;
                
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );

                
                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                
                $ret = $mysql->updateData($insert_arr, $options);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;           
                }else{
                    $this->AddError('Duomenu bazes klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            } 
    }
    
    
    function getAll($type=0,$filter=""){
        if($type==0)$table='jr_tiekejui';
        
        $search="`{$table}`.uid!=''";
        $mysql = DBMySql::getInstance();
        $q="SELECT `{$table}`.* FROM `{$table}` ORDER BY `uid` DESC";
        if($_GET['unset']==1)unset($_SESSION['tiekejas_filter']);
        if(isset($_SESSION['tiekejas_filter']) && empty($filter))$filter=$_SESSION['tiekejas_filter'];        
        if(!empty($filter)){
            $rezult['filter']=$filter;
            if($filter['dokumento_nr']!=""){$search.=" AND `{$table}`.dokumento_id='{$filter['dokumento_nr']}'";}
            if($filter['data_nuo']!=""){$search.=" AND `{$table}`.date>='{$filter['data_nuo']}'";} 
            if($filter['data_iki']!=""){$search.=" AND `{$table}`.date<='{$filter['data_iki']}'";}
            if($filter['zaliava_id']!=""){$search.=" AND `{$table}`.zaliava_id='{$filter['zaliava_id']}'";} 
            if($filter['zaliavos_pavadinimas']!=""){$search.=" AND `{$table}`.zaliavos_pavadinimas LIKE '%{$filter['zaliavos_pavadinimas']}%'";}
            if($filter['zaliavos_tiekejas']!=""){$search.=" AND `{$table}`.zaliavos_tiekejas LIKE '%{$filter['zaliavos_tiekejas']}%'";}
            if($filter['pretenzijos_tipas']!=""){$search.=" AND `{$table}`.pretenzijos_tipas='{$filter['pretenzijos_tipas']}'";}
            if($filter['busena']!=""){$search.=" AND `{$table}`.busena='{$filter['busena']}'";}
            if($filter['defektas']!=""){$search.=" AND `{$table}`.defektas LIKE '%{$filter['defektas']}%'";} 
            if($filter['padariniai_ch']!=""){$search.=" AND `{$table}`.padariniai_ch LIKE '%{$filter['padariniai_ch']}%'";}
            if($filter['created_by']!=""){$search.=" AND `{$table}`.created_by='{$filter['created_by']}'";} 
            
            
            $q="SELECT `{$table}`.* FROM `{$table}`
             WHERE {$search} GROUP BY `{$table}`.uid ORDER BY `{$table}`.uid DESC";//var_dump($q);die;
            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            $limit=$filter['per_page'];
            if($filter['page']!="")$start=($filter['page']-1)*$limit;else $start=0; 
            $q.=" LIMIT {$start},{$limit}";
            $_SESSION['tiekejas_filter']=$filter;
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
            }
        }

        
        $rezult['page']=array_chunk($allList,$limit);
        $rezult['counted']=count($allList);
         $rezult['filternames']=$this->getNamesForFilter();
        
        return $rezult;        
    } 

    public function getNamesForFilter(){
        $mysql = DBMySql::getInstance(); 
        $q="SELECT `DarbuotojaiInfoPT5021`.vardas, `DarbuotojaiInfoPT5021`.pavarde, `DarbuotojaiInfoPT5021`.uid 
        FROM `DarbuotojaiInfoPT5021` ORDER BY `vardas` ASC";
        $rezult = $mysql->querySql($q, 1);
    return $rezult;   
   } 

    public function getUploadedFiles($nr){
        $ds="/";
        $storeFolder = 'uploads';
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "tiekejas" . $nr))return;
        $files = scandir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "tiekejas" . $nr);

        return $files;
    }   
    
    public function getEditInfo($id=0){
        $table='jr_tiekejui';  
        $mysql = DBMySql::getInstance();     
        $q="SELECT * FROM `{$table}` WHERE `uid`='{$id}'";
        $rezult = $mysql->querySqlOneRow($q, 1);   
        $rezult['files']=$this->getUploadedFiles($rezult['dokumento_id']);
        $rezult['rulonai']=$this->getRulonai($rezult['dokumento_id']);
        $rezult['padariniai_ch_array']=[];
        $rezult['defektas_array']=[];
        $rezult['adding_array']=[];
        if($rezult['padariniai_ch']!=null)
            $rezult['padariniai_ch_array']=explode(";",$rezult['padariniai_ch']);
        
        if($rezult['defektas']!=null)
            $rezult['defektas_array']=explode(";",$rezult['defektas']);
        
        if($rezult['adding']!=null)
            $rezult['adding_array']=explode(";",$rezult['adding']);
        
        return $rezult;
    }
    
    public function getRulonai($dokumento_id){
        $mysql = DBMySql::getInstance(); 
        $q="SELECT * FROM `jr_tiekejui_rulonai` WHERE `dokumento_nr`='{$dokumento_id}'"; 
        $rezult = $mysql->querySql($q, 1); 
        return $rezult;       
    }  
    
    public function tiekejasPDF($uid){
        $info=$this->getEditInfo($uid); 
        if($info['padariniai_ch']!=""){
            $exploded=explode(";",$info['padariniai_ch']);
            foreach($exploded as $exp){
                if($exp!="")$info['consequence'.$exp]=1;
            }
        }  
        if($info['defektas']!=""){
            $exploded=explode(";",$info['defektas']);
            foreach($exploded as $exp){
                if($exp!="")$info['def'.$exp]=1;
            }
        }  
        if($info['adding']!=""){
            $exploded=explode(";",$info['adding']);
            foreach($exploded as $exp){
                if($exp!="")$info['ad'.$exp]=1;
            }
        } 
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `DarbuotojaiInfoPT5021` WHERE `uid`='".$info['created_by']."'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        $info['initiatedby']=$rezult['Vardas']." ".$rezult['Pavarde']; 
        //var_dump($info);die;
        return $info; 
    } 
 
    public function set_state($id,$state){
        $mysql = DBMySql::getInstance();
            try {
                //Insert multiple rows:
                $table_name="jr_busena";

                $insert_arr = array();
                $insert_arr[$table_name]['newstate']=$state;
                $insert_arr[$table_name]['document']=$id;
                $insert_arr[$table_name]['user']=SESSION::getUserID();
                $insert_arr[$table_name]['time']=date("Y-m-d h:i:s");
                $insert_arr[$table_name]['type']="1";
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
                    $this->AddError('Duomenu bazes klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }             
    }
    
}//end class1
?>