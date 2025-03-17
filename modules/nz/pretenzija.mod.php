<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class pretenzija_mod extends module {

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
        if($pretenzijaType==0)$table='jr_neatitiktis';
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

    public function reserve_id(){
            $mysql = DBMySql::getInstance();
            $table_name='jr_neatitiktis';
            $insert_arr = array();
            $insert_arr[$table_name]['dokumento_nr']="0";
            $insert_arr[$table_name]['data']=date("Y-m-d H:i:s");
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );
            $ret = $mysql->updateData($insert_arr, $options);
    }

    /*suformuojam padalinius ir priezasciu sarasa is db*/
    public function priezastysList($priezastis=0){
        $mysql = DBMySql::getInstance();
        $priezastys[0]['label']="Repro";
        $priezastys[1]['label']="SF išrašymo klaidos";
        $priezastys[2]['label']="Klaidos aptiktos po laboratorijos patikros";
        $priezastys[3]['label']="Vadyba";
        $priezastys[4]['label']="Sąmatininkai";
        $priezastys[5]['label']="Sandėlis / Logistika";
        $priezastys[6]['label']="KEG ir KPG gamyba";
        $priezastys[7]['label']="Kita";
        $priezastys[8]['label']="KEG įrengimai";
        $priezastys[9]['label']="KPG įrengimai";
        
        if($priezastis!="0"){
            $priezastis = substr($priezastis, 1, -1);
            $select=explode(";",$priezastis);
        }
        else{
            $select[]=0;
        }

        foreach($priezastys as $key=>$val){
            unset($rezult);
            $q="SELECT * FROM jr_priezastys WHERE `padalinys`='{$key}' ORDER BY `priezastis` ASC";
            $rezult = $mysql->querySql($q, 1);;
            foreach($rezult as $k=>$rez){
                if(in_array($rez['uid'],$select))
                    $rezult[$k]['ch']="checked";
                else
                    $rezult[$k]['ch']="";
            }
            $size=ceil(count($rezult)/3);
            $rezult2=array_chunk($rezult,$size);
            $priezastys[$key]['list']=$rezult2;
        }

        return $priezastys;
    }

    public function count_total_loss($post){
        $ret['nuostolis']=0;
        $ret['papildomas_nuostolis']=0;
        $ret['viso_nuostolio']=0;
        if(count($post['gaminys'])>0){
            foreach($post['gaminys'] as $k=>$itm){
                $ret['nuostolis']+=$itm['nuostolis'];
                $ret['papildomas_nuostolis']+=$itm['papildomas_nuostolis'];
                $ret['viso_nuostolio']+=$itm['viso_nuostolio'];
            }
        }
        $return['pn']=$ret['papildomas_nuostolis'];
        $return['bn']=$ret['viso_nuostolio'];
        $return['vn']=$ret['nuostolis'];
        $return['in']=$ret['nuostolis'];
        if($post['nz_tipas']==1){
            $return['in']=0;
        }
        else{
            $return['vn']=0;
        }
        return $return;
    }

    /* issaugome naujos pretenzijos duomenis */
    public function savePretenzija($post){

        $this->savePretenzijaProducts($post);
        $this->savePretenzijaPersonProject($post['dokumento_nr'],"",$post['darbuotojas']);

        $total_loss=$this->count_total_loss($post);


            try {
                //Insert multiple rows:
                $table_name="jr_neatitiktis";
                //if($post['pretenzija_type']==1)$table_name="jr_neatitiktis_vid";

                $insert_arr = array();
                //if(!empty($_GET['uid'])){
                    $insert_arr[$table_name]['uid']=$post['uid'];
                //}

                if(empty($_GET['uid'])){
                    $insert_arr[$table_name]['dokumento_nr']=$post['dokumento_nr'];
                    $insert_arr[$table_name]['created_by']=$post['created_by'];
                    $insert_arr[$table_name]['data']=$post['data'];
                }

                $insert_arr[$table_name]['gu_id']=$post['gu_id'];
                $insert_arr[$table_name]['priimta_atmesta']=$post['priimta_atmesta'];
                $insert_arr[$table_name]['gavimo_data']=$post['gavimo_data'];
                $insert_arr[$table_name]['brokas']=$post['brokas'];
                $insert_arr[$table_name]['busena']=$post['busena'];
                $insert_arr[$table_name]['nz_tipas']=$post['nz_tipas'];
                $insert_arr[$table_name]['kliento_id']=$post['kliento_id'];
                $insert_arr[$table_name]['kliento_pavadinimas']=$post['kliento_pavadinimas'];
                $insert_arr[$table_name]['kontaktinis_asmuo']=$post['kontaktinis_asmuo'];
                $insert_arr[$table_name]['vadybininkas']=$post['vadybininkas'];
                $insert_arr[$table_name]['bendri_nuostoliai']=$post['bendri_nuostoliai'];
                $insert_arr[$table_name]['papildoma_informacija']=$post['papildoma_informacija'];
                $insert_arr[$table_name]['tyrimo_eiga']=$post['tyrimo_eiga'];
                $insert_arr[$table_name]['comment']=$post['comment'];
                $insert_arr[$table_name]['isvada']=$post['isvada'];
                $insert_arr[$table_name]['viso_m']=$post['viso_m'];
                $insert_arr[$table_name]['sf_id']=$post['sf_id'];

                $insert_arr[$table_name]['papildoma_informacija_lang']=$post['papildoma_informacija_lang'];
                $insert_arr[$table_name]['tyrimo_eiga_lang']=$post['tyrimo_eiga_lang'];
                $insert_arr[$table_name]['isvada_lang']=$post['isvada_lang'];
                $insert_arr[$table_name]['comment_lang']=$post['comment_lang'];

                $insert_arr[$table_name]['pn']=$total_loss['pn'];
                $insert_arr[$table_name]['bn']=$total_loss['bn'];
                $insert_arr[$table_name]['vn']=$total_loss['vn'];
                $insert_arr[$table_name]['iin']=$total_loss['in'];

                if(count($post['priezastis'])>0)
                $insert_arr[$table_name]['priezastis']=";".implode(";",$post['priezastis']).";";
                if(count($post['extra_info'])>0)
                $insert_arr[$table_name]['extra_info']=implode(";",$post['extra_info']);
                if($post['busena']==0 && empty($post['end_time'])){
                    $insert_arr[$table_name]['baigta']=date("Y-m-d H:i:s");
                }
                $insert_arr[$table_name]['client_email']=$post['kontaktinis_email'];
                $insert_arr[$table_name]['client_lang']=$post['kontaktinis_lang'];
                $insert_arr[$table_name]['svarbi']=$post['svarbi'];
                $insert_arr[$table_name]['statistika']=$post['statistika'];
                //var_dump($_FILES);die;
                if(!empty($_FILES['ksf_id'])){
                    $insert_arr[$table_name]['ksf_id']=$_FILES['ksf_id']['name'];
                }

                if(empty($_GET['uid'])){
                    $this->informClient($post['kontaktinis_email'],$post['kontaktinis_lang'],$post['dokumento_nr']);
                }

                // optional DATA:
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );

                $this->set_state($post['dokumento_nr'],$post['busena']);
                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                    if($_POST['remove']){
                        $ds="/";
                        $storeFolder = 'uploads';
                        foreach($_POST['remove'] as $rem){
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "pretenzija" . $post['dokumento_nr'] . $ds . $rem);
                        }
                    }
                }else{
                    $this->AddError('Duomenų bazės klaida!');
                }


            } catch (Exception $ex) {
                $this->AddError((string)$ex);
                $ret = null;
            }
            if($post['busena']==30){
                //$this->generateInformPDF($_GET['uid']);
                //echo __DIR__;
                //$a=file("http://localhost/aurika/PHP/PHP/index.php?rc=infoPDF&rd=nz&uid=68");var_dump($a);die;

                //file_get_contents(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)."?rc=infoPDF&rd=nz&uid=".$_GET['uid']);
                $this->inform_client($_GET['uid']);
            }

            if(!empty($_FILES['ksf_id'])){
                $this->upload_invoice_file($post['dokumento_nr']);
            }

            /****jei keicia tipa***/

            if($post['change_type']==1){
                header('Location: '.parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)."?rc=pretenzijaEdit&rd=nz&uid=".$_GET['uid']."&nz_tipas=".$post['nz_tipas']);
            }
            /**jei keicia tipa pabaiga***/

            $sql = "DELETE FROM jr_neatitiktis WHERE dokumento_nr='0' AND data<DATE(NOW() - INTERVAL 1 DAY)";
            DBMySql::deleteSimple($sql);
    }

    public function setPdfState($id){
        $this->set_state(str_pad($id, 6, '0', STR_PAD_LEFT),"30");

        $mysql = DBMySql::getInstance();
            try {
                //Insert multiple rows:
                $table_name="jr_neatitiktis";

                $insert_arr = array();
                $insert_arr[$table_name]['uid']=$id;
                $insert_arr[$table_name]['busena']="30";
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

    //issaugom prisegta kreditine saskaita faktura
    public function upload_invoice_file($doc_nr){
        $ds          = "/";
        $storeFolder = 'uploads';
        $nr="pretenzija".$doc_nr;
        $tempFile = $_FILES['ksf_id']['tmp_name'];
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr)) {
            mkdir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr, 0777, true);
        }
        $targetPath = COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr .$ds;
        $targetFile =  $targetPath. $_FILES['ksf_id']['name'];
        move_uploaded_file($tempFile,$targetFile);
    }

    public function inform_client($id){
    //$file = $path . "/" . $filename;
    //$filename = 'myfile';
    $nr=str_pad($id, 6, '0', STR_PAD_LEFT);
    $ds="/";
    $storeFolder = 'uploads';
    $file=COMMON::getRootFolder() . $ds. $storeFolder . $ds . "pretenzija" . $nr . $ds ."answer".$nr."pdf";

    $mailto = 'mykolaskazlauskas@gmail.com';
    $subject = 'Aurika attachment';
    $message = 'My message';

    $content = file_get_contents($file);
    $content = chunk_split(base64_encode($content));

    // a random hash will be necessary to send mixed content
    $separator = md5(time());

    // carriage return type (RFC)
    $eol = "\r\n";

    // main header (multipart mandatory)
    $headers = "From: name <test@test.com>" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
    $headers .= "This is a MIME encoded message." . $eol;

    // message
    $body = "--" . $separator . $eol;
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
    $body .= $message . $eol;

    // attachment
    $body .= "--" . $separator . $eol;
    $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
    $body .= "Content-Transfer-Encoding: base64" . $eol;
    $body .= "Content-Disposition: attachment" . $eol;
    $body .= $content . $eol;
    $body .= "--" . $separator . "--";

    //SEND Mail
    if (mail($mailto, $subject, $body, $headers)) {
        //echo "mail send ... OK"; // or use booleans here
    } else {
        echo "mail send ... ERROR!";
        print_r( error_get_last() );
    }
    }

    public function savePretenzijaProducts($post){



            if(count($post['gaminys'])>0){
            $sql = "DELETE FROM jr_neatitiktis_produktai WHERE dokumento_nr='{$post['dokumento_nr']}'";
            DBMySql::deleteSimple($sql);
            foreach($post['gaminys'] as $k=>$p)
            {
            if(!empty($p['prodid'])){
            $this->savePretenzijaPerson($post['dokumento_nr'],$p['prodid'],$p['darbuotojas']);
            try {
                //Insert multiple rows:
                $table_name="jr_neatitiktis_produktai";

                $insert_arr = array();
                /*if(!empty($p['uid'])){
                    $insert_arr[$table_name]['uid']=$p['uid'];
                }
                else*/
                $insert_arr[$table_name]['dokumento_nr']=$post['dokumento_nr'];
                $insert_arr[$table_name]['gu_id']=$post['gu_id'];
                $insert_arr[$table_name]['prodid']=$p['prodid'];
                $insert_arr[$table_name]['technine']=$p['technine'];
                $insert_arr[$table_name]['item_viso_m']=$p['item_viso_m'];
                $insert_arr[$table_name]['broko_kiekis']=$p['broko_kiekis'];
                $insert_arr[$table_name]['matas']=$p['matas'];
                $insert_arr[$table_name]['nuostolis']=$p['nuostolis'];
                $insert_arr[$table_name]['papildomas_nuostolis']=$p['papildomas_nuostolis'];
                $insert_arr[$table_name]['viso_nuostolio']=$p['viso_nuostolio'];
                $insert_arr[$table_name]['pardavimo_kaina']=$p['pardavimo_kaina'];
                $insert_arr[$table_name]['prgr']=$p['prgr'];
                $insert_arr[$table_name]['Pavadinimas']=$p['pavadinimas'];

                // optional DATA:$insert_arr[$table_name]['broko_kiekis']=$p['broko_kiekis'];
                $options = array (
                    'log_data' => array( // 'log' => true,
                        'document_id' =>  $tempSamataData['CERM_OEEdienos']
                    )
                );


                //!!!!!! DEBUG
                //$this->var_dump($insert_arr, "insert_arr duom-$masina-$Date-<hr>$sql");//-----------------DEBUG

                //$mysql = DBMySql::getInstance();
                $mysql = DBMySql::getInstance();
                $ret = $mysql->updateData($insert_arr, $options);

                //tikrinam ar pasiseke
                if($this->isSqlOK ($ret)){
                    $pavyko = true;
                    if($_POST['remove']){
                        $ds="/";
                        $storeFolder = 'uploads';
                        foreach($_POST['remove'] as $rem){
                            unlink(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "pretenzija" . $post['dokumento_nr'] . $ds . $rem);
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
            }
        }
    }

    function savePretenzijaPerson($neatitiktisID,$produktoID,$asmenys){

        if(count($asmenys>0)&& !empty($asmenys)){
            foreach($asmenys as $asmuo){
                $table_name="jr_neatitiktis_produktai_asmenys";
                $insert_arr = array();
                $insert_arr[$table_name]['neatitiktis_id']=$neatitiktisID;
                $insert_arr[$table_name]['produkto_id']=$produktoID;
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

    function savePretenzijaPersonProject($neatitiktisID,$produktoID,$asmenys){

        if(count($asmenys>0)&& !empty($asmenys)){
            foreach($asmenys as $asmuo){
                $table_name="jr_neatitiktis_produktai_asmenys";
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



    function getAllPretenzijos($type=0,$filter=""){
        ini_set('max_execution_time', 0);
        if($type==0)$table='jr_neatitiktis';
        if($type==1)$table='jr_neatitiktis_vid';
        $search="`{$table}`.uid!=''";
        $mysql = DBMySql::getInstance();
        $q="SELECT `{$table}`.* FROM `{$table}` WHERE `jr_neatitiktis`.dokumento_nr!='0' GROUP BY `{$table}`.uid ORDER BY `uid` DESC";
        $qt="SELECT `{$table}`.*,
            SUM(vn) AS tvn,
            SUM(iin) AS tin,
            SUM(bn) AS tbn,
            SUM(pn) AS tpn FROM `{$table}` ORDER BY `uid` DESC";
        if($_GET['unset']==1)unset($_SESSION['neatitiktys_filter']);
        if(isset($_SESSION['neatitiktys_filter']) && empty($filter))$filter=$_SESSION['neatitiktys_filter'];
        if(!empty($filter)){
            $rezult['filter']=$filter;
            if($filter['dokumento_nr']!=""){$search.=" AND `{$table}`.dokumento_nr='{$filter['dokumento_nr']}'";}
            if($filter['gu_id']!=""){$search.=" AND `{$table}`.gu_id='{$filter['gu_id']}'";}
            if($filter['priimta_atmesta']!=""){$search.=" AND `{$table}`.priimta_atmesta='{$filter['priimta_atmesta']}'";}
            if($filter['busena']!=""){$search.=" AND `{$table}`.busena='{$filter['busena']}'";}
            if($filter['nz_tipas']!=''){$search.=" AND `{$table}`.nz_tipas='{$filter['nz_tipas']}'";}
            if($filter['pavadinimas']!=""){$search.=" AND `{$table}`.pavadinimas LIKE '%{$filter['pavadinimas']}%'";}
            if($filter['kliento_pavadinimas']!=""){$search.=" AND `{$table}`.kliento_pavadinimas LIKE '%{$filter['kliento_pavadinimas']}%'";}
            if($filter['vadybininkas']!=""){$search.=" AND `{$table}`.vadybininkas LIKE '%{$filter['vadybininkas']}%'";}
            if($filter['data_nuo']!=""){$search.=" AND `{$table}`.data>='{$filter['data_nuo']}'";}
            if($filter['data_iki']!=""){$search.=" AND `{$table}`.data<='{$filter['data_iki']}'";}
            if($filter['darbuotojas']!=""){$search.=" AND  `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '{$filter['darbuotojas']} %'";}
            if($filter['created_by']!=""){$search.=" AND `{$table}`.created_by='{$filter['created_by']}'";}
            if($filter['pamaina']!=""){$search.=" AND (`jr_neatitiktis_produktai_asmenys`.asmuo REGEXP '[[:<:]]{$filter['pamaina']}[[:>:]]' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% {$filter['pamaina']} Įtraukta į statistiką')";}
            if($filter['bn']=="1"){$search.=" AND `{$table}`.busena='0'";}
            if($filter['bn']=="2"){$search.=" AND `{$table}`.busena!='0'";}
            if($filter['svarbi']!=""){$search.=" AND `{$table}`.svarbi='{$filter['svarbi']}'";}
            if($filter['statistika']!=""){$search.=" AND `{$table}`.statistika='{$filter['statistika']}'";}
            if($filter['not_statistika']!=""){$search.=" AND `{$table}`.statistika IS NULL";}
            if($filter['brokas']!=""){$search.=" AND `{$table}`.brokas='{$filter['brokas']}'";}
            if($filter['extra_info']!=""){$search.=" AND `{$table}`.extra_info LIKE '%{$filter['extra_info']}%'";}
            if($filter['prodid']!=""){$search.=" AND `jr_neatitiktis_produktai`.prodid='{$filter['prodid']}'";}
            if($filter['rootcause']!=""){$search.=" AND `jr_root_cause`.id!=''";}

            if($filter['reason']!=""){
                $searchReason = ' AND(';
                foreach($filter['reason'] as $k=>$item){
                    $reason_exploded=explode("_",$item);
                    $logicOperator = ' OR ';
                    if($k==0){
                        $logicOperator = '';
                    }
                    if(isset($reason_exploded[1])){
                        $searchReason.=$logicOperator.$this->format_reasons_query($reason_exploded[1]);

                    }
                    else{
                        $searchReason.=" {$logicOperator} `{$table}`.priezastis LIKE '%;{$item};%'";
                    }
                }
                $searchReason .= ')';
                $search.= $searchReason;
            }

            /*atfiltravimas pagal grupe padaliniu arba pagal viena to padalinio grupe*/
            if($filter['padalinys']!="" && $filter['padalinys']!="all_vadyba" && $filter['padalinys']!="all_dizainas"
            && $filter['padalinys']!="all_keg" && $filter['padalinys']!="all_kpg" && $filter['padalinys']!="all_kokybe" && $filter['padalinys']!="all_transport")
                {$search.=" AND `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% {$filter['padalinys']} %'";}
            else{
                if($filter['padalinys']=="all_vadyba"){
                    $search.=" AND(`jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Aptarnavimas %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Pardavimai %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Sąskaitininkės %')";
                }
                if($filter['padalinys']=="all_dizainas"){
                    $search.=" AND(`jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Paruošimas %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Repro %')";
                }
                if($filter['padalinys']=="all_keg"){
                    $search.=" AND( `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Paruošėjas spaudai %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Spaudėjas %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Skaitmena (spauda) %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Skaitmena (kirtimas) %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Operatorius (pjovimas) %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Operatorius (folijavimas) %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG Pakavimas %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KEG gaminių sandėlys %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '%KEG technologai%' )";
                }
                if($filter['padalinys']=="all_kpg"){
                    $search.=" AND( `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Paruošėjas spaudai %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Spaudėjas %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Operatorius (pjovimas) %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Operatorius (laminavimas) %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Operatorius (aliuminis) %' OR
                    `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG Operatorius (shober) %'
                     OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG gaminių sandėlys %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% KPG technologai %' )";
                }
                if($filter['padalinys']=="all_kokybe"){
                    $search.=" AND(`jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% QC %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Laboratorija %')";
                }

                if($filter['padalinys']=="all_transport"){
                    $search.=" AND(`jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% TNT %' OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Omniva %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Itella %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Venipak %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% ACE %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% UPS %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% DPD %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Raben %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Schenker %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% DSV %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% NTG %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% DHL Express %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% DHL Freight %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Transporto vadybininkas %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Delamode %'
                    OR `jr_neatitiktis_produktai_asmenys`.asmuo LIKE '% Kiti %'
                    )";
                }
            }

            $q="SELECT `{$table}`.*
            FROM `{$table}`
            LEFT JOIN `jr_asmenys` ON
            `{$table}`.dokumento_nr=`jr_asmenys`.auditas_id

            LEFT JOIN `jr_neatitiktis_produktai_asmenys` ON
            `{$table}`.dokumento_nr=`jr_neatitiktis_produktai_asmenys`.neatitiktis_id

            LEFT JOIN `jr_neatitiktis_produktai` ON
            `{$table}`.dokumento_nr=`jr_neatitiktis_produktai`.dokumento_nr

            LEFT JOIN `jr_root_cause` ON
            `{$table}`.dokumento_nr=`jr_root_cause`.id

             WHERE {$search} AND `jr_neatitiktis`.dokumento_nr!='0' GROUP BY `{$table}`.uid ORDER BY `{$table}`.uid DESC";//var_dump($q);die;

            $allList=$mysql->querySql($q, 1);
            $all=count($allList);
            if(!empty($allList)){
                $totals[0]['tpn']=0;
                $totals[0]['tbn']=0;
                $totals[0]['tvn']=0;
                $totals[0]['tin']=0;
                foreach($allList as $al){
                    $totals[0]['tpn']+=$al['pn'];
                    $totals[0]['tbn']+=$al['bn'];
                    $totals[0]['tvn']+=$al['vn'];
                    $totals[0]['tin']+=$al['iin'];
                }
            }



            $limit=$filter['per_page'];
            if($filter['page']!="")$start=($filter['page']-1)*$limit;else $start=0;
            $q.=" LIMIT {$start},{$limit}";

            $_SESSION['neatitiktys_filter']=$filter;
        }else{
            $allList=$mysql->querySql($q, 1);
            $totals=$mysql->querySql($qt, 1);
            $all=count($allList);
            $limit=25;
            $q="SELECT `{$table}`.* FROM `{$table}` WHERE `dokumento_nr`!='0' ORDER BY `uid` DESC LIMIT 0,{$limit}";
        }
        //var_dump($q);die;
        $rezult['list'] = $mysql->querySql($q, 1);
        if(count($rezult['list'])>0){

            foreach($rezult['list'] as $k=>$itm){
                $rezult['list'][$k]['files']=$this->getUploadedFiles($itm['dokumento_nr']);

                //$visi_gaminiai=$this->nzCerm->getJobDuomByJobID($itm['gu_id']);
                        $reikalingi_gaminiai=$this->getPretenzijaProducts($itm['dokumento_nr']);
                        if(count($reikalingi_gaminiai)>0){

                            foreach($reikalingi_gaminiai as $key=>$r){



                                        $qr="SELECT * FROM `jr_neatitiktis_produktai_asmenys` WHERE `produkto_id`='{$r['prodid']}'
                                        AND `neatitiktis_id`='{$itm['dokumento_nr']}'";
                                        $reikalingi_gaminiai[$key]['item_persons']=$mysql->querySql($qr, 1);



                            }
                        }

                        $rezult['list'][$k]['pr']=$reikalingi_gaminiai;//var_dump($rezult['list'][$k]['pr']);die;



                $rezult['list'][$k]['priezastys']=$this->priezastysListinList($itm['priezastis']);
                $rezult['list'][$k]['darbuotojai']=$this->susijeAsmenys($itm['uid']);

                $rezult['list'][$k]['first_reaction']=$this->get_first_reaction($itm['dokumento_nr']);


            }
        }
        //$this->var_dump($rezult['list']);die;



        $rezult['page']=array_chunk($allList,$limit);
        $rezult['filternames']=$this->getNamesForFilter();
        $rezult['filterpriezastys']=$this->priezastysList();
        $rezult['counted']=count($allList);
        $rezult['pn']=number_format($totals[0]['tpn'],2);
        $rezult['bn']=number_format($totals[0]['tpn']+$totals[0]['tvn']+$totals[0]['tin'],2);
        $rezult['vn']=number_format($totals[0]['tvn'],2);
        $rezult['in']=number_format($totals[0]['tin'],2);
        return $rezult;
    }

    function priezastysListinList($ids){
        if(empty($ids))return;
        $ids = substr($ids, 1, -1);
        $ids=str_replace(";",' or `uid`=',$ids);
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_priezastys` WHERE `uid`=$ids";
        $rezult = $mysql->querySql($q, 1);
        //var_dump($q);die;
        if(count($rezult)>0){
            foreach($rezult as $r)
            $return.="<br />".$r['priezastis'];
        }

        return $return;
    }

    function get_first_reaction($id){
        $q="SELECT MIN(time) FROM jr_busena where `document`='{$id}' AND (`newstate`='8' || `newstate`='9' || `newstate`='6')";
        $mysql = DBMySql::getInstance();
        $rezult = $mysql->querySqlOneRow($q, 1);
        return $rezult['MIN(time)'];
    }



    /* susirenkam visa informacija apie pretencija*/
    function getPretenzijaInfo($id,$type=0){
        if($type==0)$table='jr_neatitiktis';
        if($type==1)$table='jr_neatitiktis_vid';
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `{$table}` WHERE `uid`='{$id}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        //gaunam visus gaminius kurie yra uzsakyme
        $visi_gaminiai=$this->nzCerm->getJobDuomByJobID($rezult['gu_id']);
        $reikalingi_gaminiai=$this->getPretenzijaProducts($rezult['dokumento_nr']);
        if(count($reikalingi_gaminiai>0)){
            foreach($reikalingi_gaminiai as $key=>$r){
                foreach($visi_gaminiai['PRODUCT'] as $k=>$v){
                    if($r['prodid']==$v['ProdID']){
                        $reikalingi_gaminiai[$key]=array_merge($reikalingi_gaminiai[$key],$v);
                        $qr="SELECT * FROM `jr_neatitiktis_produktai_asmenys` WHERE `produkto_id`='{$r['prodid']}'
                        AND `neatitiktis_id`='{$rezult['dokumento_nr']}'";
                        $reikalingi_gaminiai[$key]['item_persons']=$mysql->querySql($qr, 1);
                    }
                }
            }
        }
        $rezult['sukure']=$this->getWorkerInfo($rezult['created_by']);
        $rezult['pr']=$reikalingi_gaminiai;
        $rezult['priezastis']=$this->priezastysList($rezult['priezastis']);
        $rezult['files']=$this->getUploadedFiles($rezult['dokumento_nr']);

        if(!empty($rezult['extra_info'])){
            $explode=explode(";",$rezult['extra_info'] );
            foreach($explode as $extra){
                if(!empty($extra))
                $rezult['extra_info_checked'][$extra]='checked';
            }
        }

        $qr="SELECT * FROM `jr_neatitiktis_produktai_asmenys` WHERE `produkto_id` IS NULL AND `neatitiktis_id`='{$rezult['dokumento_nr']}'";
        $rezult['item_persons']=$mysql->querySql($qr, 1);

        return $rezult;
    }

    function loadAllPretenzijaGaminiai($id){
        $table='jr_neatitiktis';

        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `{$table}` WHERE `uid`='{$id}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        //gaunam visus gaminius kurie yra uzsakyme
        $visi_gaminiai=$this->nzCerm->getJobDuomByJobID($rezult['gu_id']);
        $reikalingi_gaminiai=$this->getPretenzijaProducts($rezult['dokumento_nr']);
        if(count($reikalingi_gaminiai>0)){

            /*foreach($reikalingi_gaminiai as $key=>$r){
                foreach($visi_gaminiai['PRODUCT'] as $k=>$v){
                    if($r['prodid']==$v['ProdID']){
                        $v['checked']='checked';
                        $reikalingi_gaminiai[$key]=array_merge($reikalingi_gaminiai[$key],$v);
                        $qr="SELECT * FROM `jr_neatitiktis_produktai_asmenys` WHERE `produkto_id`='{$r['prodid']}'
                        AND `neatitiktis_id`='{$rezult['dokumento_nr']}'";
                        $reikalingi_gaminiai[$key]['item_persons']=$mysql->querySql($qr, 1);
                    }else{
                        $reikalingi_gaminiai[]=$v;
                    }
                }
            }*/
            $added[]=0;
            foreach($reikalingi_gaminiai as $key=>$r){
                foreach($visi_gaminiai['PRODUCT'] as $k=>$v){
                    if($r['prodid']==$v['ProdID']){
                        $v['checked']='checked';
                        $reikalingi_gaminiai[$key]=array_merge($reikalingi_gaminiai[$key],$v);
                        $qr="SELECT * FROM `jr_neatitiktis_produktai_asmenys` WHERE `produkto_id`='{$r['prodid']}'
                        AND `neatitiktis_id`='{$rezult['dokumento_nr']}'";
                        $reikalingi_gaminiai[$key]['item_persons']=$mysql->querySql($qr, 1);
                        $added[]=$v['ProdID'];
                    }
                }
            }

            foreach($visi_gaminiai['PRODUCT'] as $k=>$v){
                if(!in_array($v['ProdID'],$added))
                $reikalingi_gaminiai[]=$v;
            }


        }
        $rezult['pr']=$reikalingi_gaminiai;


        return $rezult;
    }

   public function susijeAsmenys($uid=0){
        $uid=str_pad($uid, 6, '0', STR_PAD_LEFT);
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_neatitiktis_produktai_asmenys`.*FROM jr_neatitiktis_produktai_asmenys
         WHERE `neatitiktis_id`='{$uid}' ";
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
        if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "pretenzija" . $nr))return;
        $files = scandir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . "pretenzija" . $nr);
        return $files;
    }

    /*suformuojam sarasa pretenzijos produktu kurie yra issaugoti*/
    function getPretenzijaProducts($id){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM jr_neatitiktis_produktai WHERE `dokumento_nr`='{$id}'";
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
    public function set_state($id,$state){
        $mysql = DBMySql::getInstance();
            try {
                //Insert multiple rows:
                $table_name="jr_busena";

                $insert_arr = array();
                $insert_arr[$table_name]['newstate']=$state;
                $insert_arr[$table_name]['document']=$id;
                $insert_arr[$table_name]['user']=SESSION::getUserID();
                $insert_arr[$table_name]['time']=date("Y-m-d H:i:s");
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

    public function state($uid,$type){
        $mysql = DBMySql::getInstance();
        $q="SELECT `jr_busena`.*, `DarbuotojaiInfoPT5021`.Vardas, `DarbuotojaiInfoPT5021`.Pavarde FROM jr_busena
        LEFT JOIN `DarbuotojaiInfoPT5021` ON
        `jr_busena`.user=`DarbuotojaiInfoPT5021`.uid
         WHERE `document`='{$uid}' AND `type`='{$type}' ORDER BY `uid` ASC";

        $rezult = $mysql->querySql($q, 1);

        return $rezult;
    }

    public function informClient($email,$lang,$nr){
        if(strtolower($lang)!='lt'){
            $message="en message ".$nr;
        }
        else{
            $message="lt zinute ".$nr;
        }


          ///SMTP MAILAS

        $root_path = COMMON::getRootFolder();
        require_once ($root_path . "mail/class/SMTPMailer.php");

        $mail = new SMTPMailer();

        $mail->addTo('mykolas@jaunareklama.lt');

        $mail->Subject('Informacija apie nauja');

        $mail->Body(
                $message
        );

        
        if ($mail->Send()){
            echo 'Pavyko issiusti.';
        }
        else
        {
          echo 'Nepavyko issiusti.';
        }
        

        ///SMTP MAILAS


        ///mail("mykolas@jaunareklama.lt","Informacija apie nauja",$message); //senas php maileris


    }

    public function getWorkerInfo($uid){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `DarbuotojaiInfoPT5021` WHERE `uid`='{$uid}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        return $rezult;
   }

   public function deletePretenzija($uid){
            $mysql = DBMySql::getInstance();
            try {
                $whereyra = " uid ='".$uid."'";
                $Del_table_name='jr_neatitiktis';
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

   public function deletePerson($uid){
            $query="DELETE FROM `jr_neatitiktis_produktai_asmenys` WHERE `uid`='{$uid}'";
            DBMySql::deleteSimple($query);
            return "trina";
   }

   public function getVisit($uid){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_vizitai` WHERE `neatitiktis`='{$uid}'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        return $rezult;
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
                $insert_arr[$table_name]['type']='0';
                if(!empty($post['uid']))
                $insert_arr[$table_name]['uid']=$post['uid'];

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


            $this->saveRootPersons($post['susijes'], $post['uid'], '0');

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

   public function getsavedRootCause($id){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_root_cause` WHERE `id`='{$id}' and `type` = '0'";
        $rezult = $mysql->querySqlOneRow($q, 1);
        return $rezult;
   }

   public function getsavedRootCausePersons($id){
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_root_persons` WHERE `root_id`='{$id}' and `type` = '0'";
        $rezult = $mysql->querySql($q, 1);
        return $rezult;
   }

   public function format_reasons_query($parent){
    //$search.=" AND `{$table}`.priezastis LIKE '%;{$filter['reason']};%'";
        $table='jr_neatitiktis';
        $search=" (";
        $mysql = DBMySql::getInstance();
        $q="SELECT * FROM `jr_priezastys` WHERE `padalinys`='{$parent}'";
        $rezult = $mysql->querySql($q, 1);
        foreach($rezult as $k=>$itm){
            if($k==0){
                $search.="`{$table}`.priezastis LIKE '%;{$itm['uid']};%'";
            }else{
                $search.=" OR `{$table}`.priezastis LIKE '%;{$itm['uid']};%'";
            }
        }
        $search.=") ";
        return $search;
   }

    //------------------------------------
}//end class1
?>
