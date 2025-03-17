<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class nzCerm_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

        //Visos spaudos masinos
        $this->masinosArray    = array('5010', '5020','5030','5040','5050','5060','5070','5080','5090','5100','5110','5120','5130','5131');
        $this->masinosKEGArray = array('5010', '5020','5030','5040','5050','5060','5070','5080','5090','5100','5110','5131');
        $this->masinosKPGArray = array('5120', '5130','5131');

    }//end function 





    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina zaliavu sarasa pagal dali pavadinimo 
    // naudojama dropListui
    // 
    //***************************************************************************************
    public function getZalList ($ZalPavadDalis, $kiekElementu=20){

        $GUArray = array();
        if ($ZalPavadDalis AND strlen ($ZalPavadDalis)>3){

            if(!$kiekElementu OR !is_numeric($kiekElementu)){
                $kiekElementu = 20;
            }
            $sql = '
                 SELECT TOP '.$kiekElementu.'
                    A.drg__ref AS ZalID,
                    A.drg__rpn AS ZalKodas,
                    A.drg__oms AS ZalPavad,
                    A.rowid    AS irasoID,
                    matoVnt =  
                          CASE A.pap__srt  
                             WHEN \'2\' THEN \'kg\'  
                             WHEN \'6\' THEN \'kg\'  
                             WHEN \'5\' THEN \'m2\'  
                             ELSE \'vnt\'  
                          END                    

                FROM "drgers__" AS A
                WHERE 
                    A.drg__oms LIKE \'%'.$ZalPavadDalis.'%\' 
            ';

            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySql($sql, 1);

        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function




    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina zaliavos (informacija ir plociu) pagal SUBSTRATO ID !!!! (Substrate ->> Material)
    // Struktura: Zaliava ->> zaliavu plociai (galimi plociai) ->> Zaliavos SKU (konkgretus ritinelis)
    // Zaliavos SKU grazina kita funkcija
    // 
    //***************************************************************************************
    public function getZalDuom ($SubstrID){

        $GUArray = array();
        if ($this->is_ID($SubstrID)){



            $sql = '
                 SELECT 
                    A.drg__ref AS ZalID,
                    A.drg__rpn AS ZalKodas,
                    A.drg__oms AS ZalPavad,

                    A.typfrrpn AS ZalTopTipas,
                    A.srtfrrpn AS ZalTopKodas,
                    A.typrgrpn AS ZalBackTipas,
                    A.srtrgrpn AS ZalBackKodas,
                    A.typlmrpn AS ZalKlijuTipas,
                    A.srtlmrpn AS ZalKlijuKodas,
                    A.lev__ref AS ZalTiekejasID,
                    A.lev__rpn AS ZalTiekejas,
                    A.zyn__ref AS ZalTiekejoZalKodas,
                    A.ean___13 AS ZalBarkodas,
                    A.pap__srt AS ZalPopieriausTipas,
                    A.diktemic AS ZalStoris,
                    A.grammage AS ZalGramatura,
                    A.prys__m2 AS Zalm2Kaina, /* kai perkama m2 */
                    A.prys__kg AS ZalkgKaina, /* kai perkama kg */

                    A.rlweti_l AS ZalRitinioPlotis,
                    A.prvm__kg AS Zalm2Indeksas, /* kai perkama kg */
                    A.prvm__m2 AS ZalkgIndeksas, /* kaip perkama m2 */
                    A.prvma_m2 AS ZalkgAIndeksas,
                    A.munt_ref AS ZalValiutosKodas,
                    A.calc_pry AS ZalSkaiciuotaIrPirkimoKainaSkirtinga, /* Y,N */
                    T.naam____ AS TKPavad,
                    T.lev__rpn AS TKKodas,
                    T.land_ref AS TKSalis,
                    T.postnaam AS TKMiestas,
                    T.post_ref AS TKPastoKodas,
                    T.straat__ AS TKAdresas,
                    T.telefoon AS TKTel,
                    T.telefax_ AS TKFaksas,
                    T.telex___ AS TKEmail,
                    T.website_ AS TKKodas,

                    matoVnt =  
                          CASE A.pap__srt  
                             WHEN \'2\' THEN \'kg\'  
                             WHEN \'6\' THEN \'kg\'  
                             WHEN \'5\' THEN \'m2\'  
                             ELSE \'vnt\'  
                          END,                   

                    A.rowid    AS irasoID
                FROM "drgers__" AS A
                
                LEFT JOIN "levbas__" AS T ON (T.lev__ref = A.lev__ref )
                WHERE 
                    A.drg__ref = \''.$SubstrID.'\'
            ';

            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySqlOneRow($sql, 1);


            //ISTRAUKIAM ZALIAVOS PLOCIUS
            $ritinelArray=array();
            if($this->is_ID($GetData['ZalID'])){
                $sqlP = '
                     SELECT 
                        A.drg__ref AS ZalID,
                        A.art__ref AS ZalPID,
                        A.art__srt AS ZalPTypas,
                        A.art__rpn AS ZalPKodas,
                        A.art_oms1 AS ZalPPavad,

                        A.grammage AS ZalPGramatura,
                        A.gramm___ AS ZalPGramatura1,
                        A.breedte_ AS ZalPPlotis,
                        A.diktemic AS ZalPStoris,
                        A.lev__ref AS ZalPTiekejoID,
                        A.zyn__ref AS ZalPTiekejoKodas,
                        A.dossier_ AS ZalP_KEG_KPG, /* 10 - KEG gamykla, 20-KPG gamykla */
                        A.lev__ref AS ZalTiekejasID,
                        matoVnt =  
                              CASE A.art__srt  
                                 WHEN \'2\' THEN \'kg\'  
                                 WHEN \'6\' THEN \'kg\'  
                                 WHEN \'5\' THEN \'m2\'  
                                 ELSE \'vnt\'  
                              END,                    

                        A.rowid    AS ZalPIrasoID
                    FROM "artiky__" AS A
                    WHERE 
                        A.drg__ref = \''.$GetData['ZalID'].'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetDataP = $mssql->querySql($sqlP, 1);

                if(is_array($GetDataP) AND count($GetDataP)>0){
                    $GetData['PLOCIAI'] = $GetDataP;
                }else{
                    $GetData['PLOCIAI'] = array();
                }
            }//end if



        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function






    //***************************************************************************************
    // DB: CERM
    // 2017.06.23
    // Arnas
    // Grazina zaliavos (informacija ir BUTENT TO plocio/material) pagal MATERIAL ID !!!! (Substrate ->> Material)
    // Struktura: Zaliava ->> zaliavu plociai (galimi plociai) ->> Zaliavos SKU (konkgretus ritinelis)
    // 
    // 
    //***************************************************************************************
    public function getZalDuomByMaterialID ($MaterID){

        $GUArray = array();
        if ($this->is_ID($MaterID)){

            //ISTRAUKIAM MATERIAL DUOMENIS (ZALIAVOS PLOCIUS)
            $ritinelArray=array();
            if($this->is_ID($MaterID)){
                $sqlP = '
                     SELECT 
                        A.drg__ref AS ZalID,
                        A.art__ref AS ZalPID,
                        A.art__srt AS ZalPTypas,
                        A.art__rpn AS ZalPKodas,
                        A.art_oms1 AS ZalPPavad,

                        A.grammage AS ZalPGramatura,
                        A.gramm___ AS ZalPGramatura1,
                        A.breedte_ AS ZalPPlotis,
                        A.diktemic AS ZalPStoris,
                        A.lev__ref AS ZalPTiekejoID,
                        A.zyn__ref AS ZalPTiekejoKodas,
                        A.dossier_ AS ZalP_KEG_KPG, /* 10 - KEG gamykla, 20-KPG gamykla */
                        A.lev__ref AS ZalTiekejasID,
                        matoVnt =  
                              CASE A.art__srt  
                                 WHEN \'2\' THEN \'kg\'  
                                 WHEN \'6\' THEN \'kg\'  
                                 WHEN \'5\' THEN \'m2\'  
                                 ELSE \'vnt\'  
                              END,                    

                        A.rowid    AS ZalPIrasoID
                    FROM "artiky__" AS A
                    WHERE 
                        A.art__ref = \''.$MaterID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetDataP = $mssql->querySql($sqlP, 1);


                if(is_array($GetDataP) AND count($GetDataP)>0){
                    $SubstrID = $GetDataP[0]['ZalID'];

                        $sql = '
                             SELECT 
                                A.drg__ref AS ZalID,
                                A.drg__rpn AS ZalKodas,
                                A.drg__oms AS ZalPavad,

                                A.typfrrpn AS ZalTopTipas,
                                A.srtfrrpn AS ZalTopKodas,
                                A.typrgrpn AS ZalBackTipas,
                                A.srtrgrpn AS ZalBackKodas,
                                A.typlmrpn AS ZalKlijuTipas,
                                A.srtlmrpn AS ZalKlijuKodas,
                                A.lev__ref AS ZalTiekejasID,
                                A.lev__rpn AS ZalTiekejas,
                                A.zyn__ref AS ZalTiekejoZalKodas,
                                A.ean___13 AS ZalBarkodas,
                                A.pap__srt AS ZalPopieriausTipas,
                                A.diktemic AS ZalStoris,
                                A.grammage AS ZalGramatura,
                                A.prys__m2 AS Zalm2Kaina, /* kai perkama m2 */
                                A.prys__kg AS ZalkgKaina, /* kai perkama kg */

                                A.rlweti_l AS ZalRitinioPlotis,
                                A.prvm__kg AS Zalm2Indeksas, /* kai perkama kg */
                                A.prvm__m2 AS ZalkgIndeksas, /* kaip perkama m2 */
                                A.prvma_m2 AS ZalkgAIndeksas,
                                A.munt_ref AS ZalValiutosKodas,
                                A.calc_pry AS ZalSkaiciuotaIrPirkimoKainaSkirtinga, /* Y,N */
                                T.naam____ AS TKPavad,
                                T.lev__rpn AS TKKodas,
                                T.land_ref AS TKSalis,
                                T.postnaam AS TKMiestas,
                                T.post_ref AS TKPastoKodas,
                                T.straat__ AS TKAdresas,
                                T.telefoon AS TKTel,
                                T.telefax_ AS TKFaksas,
                                T.telex___ AS TKEmail,
                                T.website_ AS TKKodas,

                                matoVnt =  
                                      CASE A.pap__srt  
                                         WHEN \'2\' THEN \'kg\'  
                                         WHEN \'6\' THEN \'kg\'  
                                         WHEN \'5\' THEN \'m2\'  
                                         ELSE \'vnt\'  
                                      END,                   

                                A.rowid    AS irasoID
                            FROM "drgers__" AS A
                            
                            LEFT JOIN "levbas__" AS T ON (T.lev__ref = A.lev__ref )
                            WHERE 
                                A.drg__ref = \''.$SubstrID.'\'
                        ';

                        $mssql = DBMSSqlCERM::getInstance();
                        $GetData = $mssql->querySqlOneRow($sql, 1);

                        //!!!!!! DEBUG
                        //$this->var_dump($GetData, "GetData SUBSTR <hr>$sql<hr> ");//-----------------DEBUG

                }//end if


                if(is_array($GetDataP) AND count($GetDataP)>0){
                    $GetData['PLOCIAI'] = $GetDataP;
                }else{
                    $GetData['PLOCIAI'] = array();
                }
            }//end if



        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "GetData <hr>$sqlP<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function




    //***************************************************************************************
    // DB: CERM
    // 2017.06.23
    // Arnas
    // Grazina TIK Substrate duomenis pagal SUBSTRATO ID !!!! (Substrate ->> Material)
    // Struktura: Zaliava ->> zaliavu plociai (galimi plociai) ->> Zaliavos SKU (konkgretus ritinelis)
    // Zaliavos SKU grazina kita funkcija
    // 
    //***************************************************************************************
    public function getSubstrateDuom ($SubstrID){

        if ($this->is_ID($SubstrID)){

            $sql = '
                 SELECT 
                    A.drg__ref AS ZalID,
                    A.drg__rpn AS ZalKodas,
                    A.drg__oms AS ZalPavad,

                    A.typfrrpn AS ZalTopTipas,
                    A.srtfrrpn AS ZalTopKodas,
                    A.typrgrpn AS ZalBackTipas,
                    A.srtrgrpn AS ZalBackKodas,
                    A.typlmrpn AS ZalKlijuTipas,
                    A.srtlmrpn AS ZalKlijuKodas,
                    A.lev__ref AS ZalTiekejasID,
                    A.lev__rpn AS ZalTiekejas,
                    A.zyn__ref AS ZalTiekejoZalKodas,
                    A.ean___13 AS ZalBarkodas,
                    A.pap__srt AS ZalPopieriausTipas,
                    A.diktemic AS ZalStoris,
                    A.grammage AS ZalGramatura,
                    A.prys__m2 AS Zalm2Kaina, /* kai perkama m2 */
                    A.prys__kg AS ZalkgKaina, /* kai perkama kg */

                    A.rlweti_l AS ZalRitinioPlotis,
                    A.prvm__kg AS Zalm2Indeksas, /* kai perkama kg */
                    A.prvm__m2 AS ZalkgIndeksas, /* kaip perkama m2 */
                    A.prvma_m2 AS ZalkgAIndeksas,
                    A.munt_ref AS ZalValiutosKodas,
                    A.calc_pry AS ZalSkaiciuotaIrPirkimoKainaSkirtinga, /* Y,N */
                    T.naam____ AS TKPavad,
                    T.lev__rpn AS TKKodas,
                    T.land_ref AS TKSalis,
                    T.postnaam AS TKMiestas,
                    T.post_ref AS TKPastoKodas,
                    T.straat__ AS TKAdresas,
                    T.telefoon AS TKTel,
                    T.telefax_ AS TKFaksas,
                    T.telex___ AS TKEmail,
                    T.website_ AS TKKodas,

                    matoVnt =  
                          CASE A.pap__srt  
                             WHEN \'2\' THEN \'kg\'  
                             WHEN \'6\' THEN \'kg\'  
                             WHEN \'5\' THEN \'m2\'  
                             ELSE \'vnt\'  
                          END,                   

                    A.rowid    AS irasoID
                FROM "drgers__" AS A
                
                LEFT JOIN "levbas__" AS T ON (T.lev__ref = A.lev__ref )
                WHERE 
                    A.drg__ref = \''.$SubstrID.'\'
            ';

            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySqlOneRow($sql, 1);

        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function






    //***************************************************************************************
    // DB: CERM
    // 2017.06.23
    // Arnas
    // Grazina TIK zaliavos plociu (materials) duomenis  pagal MATERIAL ID !!!! 
    // Struktura: Substrate ->> Material ->> Materials SKU arba Zaliava (pavadinimas, bendri duomenys) ->> zaliavu plociai (galimi plociai) ->> Zaliavos SKU (konkgretus ritinelis)
    // 
    // 
    //***************************************************************************************
    public function getMaterialDuom ($MaterID){

        $GUArray = array();
        if ($this->is_ID($MaterID)){

                $sqlP = '
                     SELECT 
                        A.drg__ref AS ZalID,
                        A.art__ref AS ZalPID,
                        A.art__srt AS ZalPTypas,
                        A.art__rpn AS ZalPKodas,
                        A.art_oms1 AS ZalPPavad,

                        A.grammage AS ZalPGramatura,
                        A.gramm___ AS ZalPGramatura1,
                        A.breedte_ AS ZalPPlotis,
                        A.diktemic AS ZalPStoris,
                        A.lev__ref AS ZalPTiekejoID,
                        A.zyn__ref AS ZalPTiekejoKodas,
                        A.dossier_ AS ZalP_KEG_KPG, /* 10 - KEG gamykla, 20-KPG gamykla */
                        A.lev__ref AS ZalTiekejasID,
                        matoVnt =  
                              CASE A.art__srt  
                                 WHEN \'2\' THEN \'kg\'  
                                 WHEN \'6\' THEN \'kg\'  
                                 WHEN \'5\' THEN \'m2\'  
                                 ELSE \'vnt\'  
                              END,                    

                        A.rowid    AS ZalPIrasoID
                    FROM "artiky__" AS A
                    WHERE 
                        A.art__ref = \''.$MaterID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetDataP = $mssql->querySqlOneRow($sqlP, 1);


        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetDataP, "GetDataP <hr>$sqlP<hr> ");//-----------------DEBUG

        return $GetDataP;
    }//end function




    //***************************************************************************************
    // DB: CERM
    // 2017.06.23
    // Arnas
    // Grazina VISUS zaliavos plocius (materials) priklausancius Substratui pagal Substrate ID !!!! 
    // Struktura: Substrate ->> Material ->> Materials SKU arba Zaliava (pavadinimas, bendri duomenys) ->> zaliavu plociai (galimi plociai) ->> Zaliavos SKU (konkgretus ritinelis)
    // 
    // 
    //***************************************************************************************
    public function getMaterialsUnderSubstrateList ($SubstrID){

        if ($this->is_ID($SubstrID)){
                $sql = '
                     SELECT 
                        A.drg__ref AS ZalID,
                        A.art__ref AS ZalPID,
                        A.art__srt AS ZalPTypas,
                        A.art__rpn AS ZalPKodas,
                        A.art_oms1 AS ZalPPavad,

                        A.grammage AS ZalPGramatura,
                        A.gramm___ AS ZalPGramatura1,
                        A.breedte_ AS ZalPPlotis,
                        A.diktemic AS ZalPStoris,
                        A.lev__ref AS ZalPTiekejoID,
                        A.zyn__ref AS ZalPTiekejoKodas,
                        A.dossier_ AS ZalP_KEG_KPG, /* 10 - KEG gamykla, 20-KPG gamykla */
                        A.lev__ref AS ZalTiekejasID,
                        matoVnt =  
                              CASE A.art__srt  
                                 WHEN \'2\' THEN \'kg\'  
                                 WHEN \'6\' THEN \'kg\'  
                                 WHEN \'5\' THEN \'m2\'  
                                 ELSE \'vnt\'  
                              END,                    

                        A.rowid    AS ZalPIrasoID
                    FROM "artiky__" AS A
                    WHERE 
                        A.drg__ref = \''.$SubstrID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetDataP = $mssql->querySql($sql, 1);


        }//End if

        //!!!!!! DEBUG
        //$this->var_dump($GetDataP, "GetDataP <hr>$sqlP<hr> ");//-----------------DEBUG

        return $GetDataP;
    }//end function






    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina zaliavos SKU (konkrecius zaliavos ritinelius), pagal zaliavos ID
    // 
    // 
    //***************************************************************************************
    public function getZalSKU ($ZalID, $ZalPlotis){

        $GUArray = array();
        if ($this->is_ID($ZalID) AND is_numeric($ZalPlotis)){

            $sql = '
                 SELECT 
                    A.drg__ref AS ZalID,
                    A.drg__rpn AS ZalKodas,
                    A.drg__oms AS ZalPavad,

                    A.typfrrpn AS ZalTopTipas,
                    A.srtfrrpn AS ZalTopKodas,
                    A.typrgrpn AS ZalBackTipas,
                    A.srtrgrpn AS ZalBackKodas,
                    A.typlmrpn AS ZalKlijuTipas,
                    A.srtlmrpn AS ZalKlijuKodas,
                    A.lev__ref AS ZalTiekejasID,
                    A.lev__rpn AS ZalTiekejas,
                    A.zyn__ref AS ZalTiekejoZalKodas,
                    A.pap__srt AS ZalPopieriausTipas,
                    A.rlweti_l AS ZalPlotis,
                    A.prvm__kg AS Zalm2Indeksas, /* kai perkama kg */
                    A.prvm__m2 AS ZalkgIndeksas, /* kaip perkama m2 */
                    A.prvma_m2 AS ZalkgAIndeksas,
                    T.naam____ AS TKPavad,
                    T.lev__rpn AS TKKodas,
                    T.land_ref AS TKSalis,
                    T.postnaam AS TKMiestas,
                    T.post_ref AS TKPastoKodas,
                    T.straat__ AS TKAdresas,
                    T.telefoon AS TKTel,
                    T.telefax_ AS TKFaksas,
                    T.telex___ AS TKEmail,
                    T.website_ AS TKKodas,

                    Z.breedte_ AS ZalRitPlotis,

                    S.artd_ref AS ZalSKU,
                    S.zyn__ref AS ZalSKU_TiekKod,
                    S.in_stock AS ZalSKU_Sandelyje,
                    S.vak__ref AS ZalSKU_VietaSandelyje,
                    S.levdat__ AS ZalSKU_AtvykstaData,
                    S.levr_vnr AS ZalSKU_AtvykstaUzsNr,
                    S.nakprijs AS ZalSKU_SKUKaina,
                    S.besteld_ AS ZalSKU_TikrasKiekis,

                    matoVnt =  
                          CASE A.pap__srt  
                             WHEN \'2\' THEN \'kg\'  
                             WHEN \'6\' THEN \'kg\'  
                             WHEN \'5\' THEN \'m2\'  
                             ELSE \'vnt\'  
                          END,
                    A.rowid    AS irasoID
                FROM "drgers__" AS A
                LEFT JOIN "levbas__" AS T ON (T.lev__ref = A.lev__ref )
                LEFT JOIN "artiky__" AS Z ON (Z.drg__ref = A.drg__ref )
                LEFT JOIN "artikd__" AS S ON (S.art__ref = Z.art__ref )
                WHERE 
                    A.drg__ref = \''.$ZalID.'\' AND
                    Z.breedte_ = \''.$ZalPlotis.'\'
            ';

            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySql($sql, 1);



        }else{//End if
            $this->AddError('Pateikta neteisinga informacija paieškai.');
        }

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function



    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina Tiekejo (imones) duomenis
    // ir tiekejo kontaktiniu asmenu duomenis
    // pagal tiekejo (imones) ID
    //***************************************************************************************
    public function getTiekejoDuom ($TiekejID){

            if ($this->is_ID($TiekejID)){

                $sql = '
                    SELECT 
                        A.lev__ref AS ClientImID,
                        A.lev__rpn AS CI_TrumapasPavad,
                        A.naam____ AS CI_Pavadinimas,

                        A.gln_____ AS CI_GlobalLocatNr,
                        A.land_ref AS CI_SaliesKodas,
                        A.straat__ AS CI_Gatve,
                        A.postnaam AS CI_Miestas,
                        A.county__ AS CI_PapildomAdr,

                        A.telefoon AS CI_Telefonas,
                        A.telefax_ AS CI_Faksas,
                        A.telex___ AS CI_ElPastas,
                        
                        
                        A.website_ AS CI_WWW,
                        A.munt_ref AS CI_ValiutosID,
                        A.betk_ref AS CI_TermOfPaymentID,
                        A.btw___nr AS CI_PVMKodas,
                        
                        

                        A.wij__usr AS CI_AurikaKontaktas,
                        A.trn__ref AS CI_TranzitoZonaID,
                        

                        A.trncom_1 AS CI_trncom_1,
                        A.trncom_2 AS CI_trncom_2,
                        
                        A.rowid    AS CI_irasoID
                    FROM "levbas__" AS A
                    
                    WHERE A.lev__ref = \''.$TiekejID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetData = $mssql->querySqlOneRow($sql, 1);


                //pasiimam kontaktiniu asmenu sarasa
                $GetData['ASMENYS'] = $this->getTiekejKontaktai ($TiekejID);

            }else{//End if
                $GetData = array();
            }

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "GetData <hr>$sql<hr> ");//-----------------DEBUG

            return $GetData;
    }//end function



    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina tiekejo (imones) kontaktiniu asmenu sarasa
    // pagal tiekejo (imones) ID
    //***************************************************************************************
    public function getTiekejKontaktai ($TiekejoID){

            if ($this->is_ID($TiekejoID)){

                $sql = '
                    SELECT 
                        A.lev__ref AS TiekejImID,
                        A.knp__ref AS TK_KontAsmNr,
                        A.knp__nam AS TK_Pavarde,
                        A.knp__vnm AS TK_Vardas,
                        A.knp__sex AS TK_lytis,
                        A.land_ref AS TK_SaliesKodas,
                        A.straat__ AS TK_Gatve,
                        A.postnaam AS TK_Miestas,
                        A.county__ AS TK_PapildomAdr,
                        A.post_ref AS TK_PastoKodas,
                        A.telefoon AS TK_Telefonas,
                        A.tel_auto AS TK_MobTel,
                        A.tel__pkp AS TK_PapildomTel,
                        A.email___ AS TK_ElPastas,
                        A.mailing_ AS TK_EmailPatvirtintas,
                        A.funktie_ AS TK_Pareigos,
                        A.comment_ AS TK_Komentaras,
                        A.rowid    AS TK_irasoID
                    FROM "konplv__" AS A
                    
                    WHERE A.lev__ref = \''.$TiekejoID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetData = $mssql->querySql($sql, 1);

            }else{//End if
                $GetData = array();
            }

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "TiekejoID <hr>$sql<hr> ");//-----------------DEBUG

            return $GetData;//End if
    }//end function



    //***************************************************************************************
    // DB: CERM
    // 2017.03.24
    // Arnas
    // Grazina tiekeju (imoniu) sarasa
    // gali grazinti sarasa pagal paduodama parametra: 
    //  $tkSearch. Tai gali buti dalis tiekejo pavadinimo
    //  $tkSParam {PRADZIA, BETKUR}(nebutinas parametras) - nurodo ar dalis pavadinimo turi prasideti nuo pradzios ar bet kurioje pavadinimo vietoje
    //***************************************************************************************
    public function getTiekejList ($tkSearch, $tkParam='BETKUR'){

        $GetData = array();

        $WHERE = "";    
        if($tkSearch){
            if(strtoupper($tkParam)=='PRADZIA'){
                $WHERE = "WHERE A.naam____ LIKE '".$tkSearch."%' ";
            }else{
                $WHERE = "WHERE A.naam____ LIKE '%".$tkSearch."%' ";
            }
        }

        $sql = '
            SELECT 
                        A.lev__ref AS ClientImID,
                        A.lev__rpn AS CI_TrumapasPavad,
                        A.naam____ AS CI_Pavadinimas,

                        A.gln_____ AS CI_GlobalLocatNr,
                        A.land_ref AS CI_SaliesKodas,
                        A.straat__ AS CI_Gatve,
                        A.postnaam AS CI_Miestas,
                        A.county__ AS CI_PapildomAdr,

                        A.telefoon AS CI_Telefonas,
                        A.telefax_ AS CI_Faksas,
                        A.telex___ AS CI_ElPastas,
                        
                        
                        A.website_ AS CI_WWW,
                        A.munt_ref AS CI_ValiutosID,
                        A.betk_ref AS CI_TermOfPaymentID,
                        A.btw___nr AS CI_PVMKodas,
                        
                        

                        A.wij__usr AS CI_AurikaKontaktas,
                        A.trn__ref AS CI_TranzitoZonaID,
                        

                        A.trncom_1 AS CI_trncom_1,
                        A.trncom_2 AS CI_trncom_2,
                        
                        A.rowid    AS CI_irasoID
            FROM "levbas__" AS A
            
            '.$WHERE.'
        ';

        $mssql = DBMSSqlCERM::getInstance();
        $GetData = $mssql->querySql($sql, 1);

        //!!!!!! DEBUG
        $this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function





    //***************************************************************************************
    // DB: CERM
    // 2017.03.01
    // Arnas
    // Grazina klientu (imoniu) sarasa
    // gali grazinti sarasa pagal paduodama parametra: 
    //  $klSearch. Tai gali buti dalis kliento pavadinimo
    //  $klSParam {PRADZIA, BETKUR}(nebutinas parametras) - nurodo ar dalis pavadinimo turi prasideti nuo pradzios ar bet kurioje pavadinimo vietoje
    //***************************************************************************************
    public function getClientList ($klSearch, $klSParam='BETKUR'){

        $ClientDuom = array();

        $WHERE = "";    
        if($klSearch){
            if(strtoupper($klSParam)=='PRADZIA'){
                $WHERE = "WHERE A.naam____ LIKE '".$klSearch."%' ";
            }else{
                $WHERE = "WHERE A.naam____ LIKE '%".$klSearch."%' ";
            }
        }

        $sql = '
            SELECT 
                A.kla__ref AS ClientImID,
                A.kla__rpn AS CI_TrumapasPavad,
                A.naam____ AS CI_Pavadinimas,
                A.gln_____ AS CI_GlobalLocatNr,
                A.land_ref AS CI_SaliesKodas,
                A.straat__ AS CI_Gatve,
                A.postnaam AS CI_Miestas,
                A.county__ AS CI_PapildomAdr,

                A.telefoon AS CI_Telefonas,
                A.telefax_ AS CI_Faksas,
                A.telex___ AS CI_ElPastas,
                A.eml__bat AS CI_EmailPatvirtintas,
                A.eml__vkp AS CI_SalesOrderCofirmation,
                A.website_ AS CI_WWW,
                A.munt_ref AS CI_ValiutosID,
                A.betk_ref AS CI_TermOfPaymentID,
                A.btw___nr AS CI_PVMKodas,
                A.btw_____ AS CI_VATyn,
                A.handelnr AS CI_ImKodas, /* ????? */
                A.vrt__ref AS CI_AtstovasAurikojeID,
                A.wij__usr AS CI_AurikaKontaktas,
                A.trn__ref AS CI_TranzitoZonaID,
                A.ord_begl AS CI_JobManager,
                A.int_cont AS CI_VidinisKontaktas,
                A.rowid    AS CI_irasoID
            FROM "klabas__" AS A
            
            '.$WHERE.'
        ';

        $mssql = DBMSSqlCERM::getInstance();
        $GetData = $mssql->querySql($sql, 1);

        //!!!!!! DEBUG
        //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

        return $GetData;
    }//end function




    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina kliento (imones) duomenis
    // ir kliento kontaktiniu asmenu duomenis
    // pagal kliento (imones) ID
    //***************************************************************************************
    public function getClientContacts ($ClientID){

        $ClientDuom = array();
            if ($this->is_ID($ClientID)){

                $sql = '
                    SELECT 
                        A.kla__ref AS ClientImID,
                        A.kla__rpn AS CI_TrumapasPavad,
                        A.naam____ AS CI_Pavadinimas,
                        A.gln_____ AS CI_GlobalLocatNr,
                        A.land_ref AS CI_SaliesKodas,
                        A.straat__ AS CI_Gatve,
                        A.postnaam AS CI_Miestas,
                        A.county__ AS CI_PapildomAdr,

                        A.telefoon AS CI_Telefonas,
                        A.telefax_ AS CI_Faksas,
                        A.telex___ AS CI_ElPastas,
                        A.eml__bat AS CI_EmailPatvirtintas,
                        A.eml__vkp AS CI_SalesOrderCofirmation,
                        A.website_ AS CI_WWW,
                        A.munt_ref AS CI_ValiutosID,
                        A.betk_ref AS CI_TermOfPaymentID,
                        A.btw___nr AS CI_PVMKodas,
                        A.btw_____ AS CI_VATyn,
                        A.handelnr AS CI_ImKodas, /* ????? */
                        A.vrt__ref AS CI_AtstovasAurikojeID,
                        A.wij__usr AS CI_AurikaKontaktas,
                        A.trn__ref AS CI_TranzitoZonaID,
                        A.ord_begl AS CI_JobManager,
                        A.int_cont AS CI_VidinisKontaktas,
                        A.rowid    AS CI_irasoID
                    FROM "klabas__" AS A
                    
                    WHERE A.kla__ref = \''.$ClientID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetData = $mssql->querySqlOneRow($sql, 1);


                //pasiimam kontaktiniu asmenu sarasa
                $GetData['ASMENYS'] = $this->getClientContakts ($ClientID);

            }else{//End if
                $GetData = array();
            }

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

            return $GetData;
    }//end function



    //***************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Grazina kliento (imones) kontaktiniu asmenu sarasa
    // pagal kliento (imones) ID
    //***************************************************************************************
    public function getClientContakts ($ClientID){

        $ClientDuom = array();
            if ($this->is_ID($ClientID)){

                $sql = '
                    SELECT 
                        A.kla__ref AS ClientImID,
                        A.knp__ref AS CA_KontAsmNr,
                        A.knp__nam AS CA_Pavarde,
                        A.knp__vnm AS CA_Vardas,
                        A.knp__sex AS CA_lytis,
                        A.land_ref AS CA_SaliesKodas,
                        A.straat__ AS CA_Gatve,
                        A.postnaam AS CA_Miestas,
                        A.county__ AS CA_PapildomAdr,
                        A.post_ref AS CA_PastoKodas,
                        A.telefoon AS CA_Telefonas,
                        A.tel_auto AS CA_MobTel,
                        A.tel__pkp AS CA_PapildomTel,
                        A.email___ AS CA_ElPastas,
                        A.mailing_ AS CA_EmailPatvirtintas,
                        A.funktie_ AS CA_Pareigos,
                        A.comment_ AS CA_Komentaras,
                        A.rowid    AS CI_irasoID
                    FROM "konpkl__" AS A
                    
                    WHERE A.kla__ref = \''.$ClientID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetData = $mssql->querySql($sql, 1);

            }else{//End if
                $GetData = array();
            }

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

            return $GetData;//End if
    }//end function


    //***************************************************************************************
    // DB: CERM
    // 2017.01.11
    // Arnas
    // Grazina GAM (Produkto) uzsakyta/pagaminta kiekiai
    // 
    //***************************************************************************************
    public function getProdUzsakytaPagaminta_____BANED ($ProdID){

        $ClientDuom = array();
            if ($this->is_ID($ClientID)){

                $sql = '
                    SELECT 
                        A.kla__ref AS ClientImID,
                        A.knp__ref AS CA_KontAsmNr,
                        A.knp__nam AS CA_Pavarde,
                        A.knp__vnm AS CA_Vardas,
                        A.knp__sex AS CA_lytis,
                        A.land_ref AS CA_SaliesKodas,
                        A.straat__ AS CA_Gatve,
                        A.postnaam AS CA_Miestas,
                        A.county__ AS CA_PapildomAdr,
                        A.post_ref AS CA_PastoKodas,
                        A.telefoon AS CA_Telefonas,
                        A.tel_auto AS CA_MobTel,
                        A.tel__pkp AS CA_PapildomTel,
                        A.email___ AS CA_ElPastas,
                        A.mailing_ AS CA_EmailPatvirtintas,
                        A.funktie_ AS CA_Pareigos,
                        A.comment_ AS CA_Komentaras,
                        A.rowid    AS CI_irasoID
                    FROM "konpkl__" AS A
                    
                    WHERE A.kla__ref = \''.$ClientID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $GetData = $mssql->querySql($sql, 1);

            }else{//End if
                $GetData = array();
            }

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG

            return $GetData;//End if
    }//end function




    //****************************************************************************************
    // DB: CERM
    // 2017.01.09
    // Arnas
    // Istraukia info apie Stock Job pagal JOB ID
    //****************************************************************************************
    public function getJobDuomByJobID ($JobID=0){

        $GUArray = array();
        if ($this->is_ID($JobID)){

            $sql = '
                 SELECT 
                    
                    B.type_ord AS JOB_Type,
                    B.ord__ref AS JobID,
                    B.omschr__ AS JobPavad,
                    B.ord__rpn AS Raktazodis,
                    B.annul___ AS Anuliuotas,
                    B.prd__ref AS ProduktTypeID,
                    
                    B.jobnr_vw AS ProcessID,
                    B.best_dat AS OrderDate,
                    B.klgr_ref AS ClientGrupeID,
                    B.kla__ref AS ClientID,
                    C.naam____ AS ClientName,
                    C.kla__rpn AS ClientNameShort,
                    C.telefoon AS ClientTel,
                    C.telex___ AS ClientEmail,
                    C.land_ref AS ClientKalba,
                    B.prd__ref AS ProductTypeID,
                    B.omsaant_ AS OrderMatVnt,
                    B.Leverdat AS PristatymoData,
                    B.bon__ref AS SamatosID,
                    B.off__ref AS CalculationID,
                    B.tstval01 AS GamybosPlanID,
                    B.tstval02 AS DazuParuosID,
                    B.tstval03 AS JDFstatusID, 
                    B.tstval04 AS FPKstatusID,
                    B.tstval05 AS DIZplanerID,
                    B.tstval06 AS PerkelimoBus,
                    B.tstval07 AS KlaiduBus,
                    B.prkl_ref AS KlientoOrderNr,

                    B.vrt__ref AS RepresentativeID,
                    D.naam____ AS RepresentativeName,
                    D.email___ AS RepresentativeEmail,

                    B.ord_begl AS Aptarnaujantis,
                    B.kalkulat AS SkaiciavoVardas,
                    B.int_cont AS VidinKontaktAsm,

                    B.dossier_ AS Ord_KEGKPG_ID,


                    B.rowid AS Ord_rowid                    
                FROM "order___" AS B
                LEFT JOIN "klabas__" AS C ON (B.kla__ref = C.kla__ref )
                LEFT JOIN "verte___" AS D ON (B.vrt__ref = D.vrt__ref )
                
                WHERE 
                    B.ord__ref=\''.$JobID.'\' AND B.type_ord = 2
            ';

            //B.type_ord - 1-production, 2-stokJob, 3 prepress job
            //B.annul___ - N-aktyvus, Y-anuliuotas
            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySqlOneRow($sql, 1);

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG


            if(is_array($GetData) AND count($GetData)>0){//jeigu radom JOBa


                    //Paimam sunaudotos zaliavos kiekius JOBui (geri metrai, visi metrai)
                    $sqlZalMetr = "
                        SELECT 
                            SUM(aantal__) AS visiMetrai, 
                            SUM (aantlmok) AS geriMetrai, 
                            ord__ref AS cJOBID, 
                            art__ref AS MaterialID
                        FROM plcrol__ 
                        WHERE 
                            ord__ref='".$GetData['JobID']."' AND 
                            art__ref IS NOT NULL AND 
                            art__ref != '' AND 
                            wp___ref IN ('".implode('\',\'',$this->masinosArray)."')
                            GROUP BY ord__ref, art__ref                        
                    ";

                    //TODO gal gali buti, kad grazins kelias zaliavas (reikia pasitikslinti del sito)
                    $ZalSunaudoj = $mssql->querySqlOneRow($sqlZalMetr, 1);

                    //!!!!!! DEBUG
                    //$this->var_dump($ZalSunaudoj, "ZalSunaudoj <hr>$sqlZalMetr<hr> ");//-----------------DEBUG

                    if($ZalSunaudoj['cJOBID']==$GetData['JobID']){
                        $GetData['ZalSunaudSuPrivedimuMetrai']=(float)$ZalSunaudoj['visiMetrai'];
                        $GetData['ZalSunaudGeriMetrai']=(float)$ZalSunaudoj['geriMetrai'];
                        $GetData['ZalSunaudPrivedimoMetrai']=$ZalSunaudoj['visiMetrai'] - $ZalSunaudoj['geriMetrai'];
                    }else{
                        $GetData['ZalSunaudSuPrivedimuMetrai']=0;
                        $GetData['ZalSunaudGeriMetrai']=0;
                        $GetData['ZalSunaudPrivedimoMetrai']=0;
                    }



                    //Gamybos planavimo busenos
                    switch ($GetData['GamybosPlanID']) {
                        case '1000':
                            $GetData['GamybosPlanID_txt']="Nežinoma";
                            break;
                        case '2000':
                            $GetData['GamybosPlanID_txt']="Galima planuoti";
                            break;
                        case '3000':
                            $GetData['GamybosPlanID_txt']="Planuojamas (autom.)";
                            break;
                        case '4000':
                            $GetData['GamybosPlanID_txt']="Planas patvirtintas";
                            break;
                        
                        default:
                            $GetData['GamybosPlanID_txt']="nenurodyta";
                            break;
                    }//end switch



                    //Dazu paruosimo busenos
                    switch ($GetData['DazuParuosID']) {
                        case '0100':
                            $GetData['DazuParuosID_txt']="Nežinoma";
                            break;
                        case '0200':
                            $GetData['DazuParuosID_txt']="Trūksta formulės";
                            break;
                        case '0300':
                            $GetData['DazuParuosID_txt']="Formulė žinoma";
                            break;
                        case '0400':
                            $GetData['DazuParuosID_txt']="Formulė paruošta";
                            break;
                        
                        default:
                            $GetData['DazuParuosID_txt']="nenurodyta";
                            break;
                    }//end switch

                    //JDF statusas
                    switch ($GetData['JDFstatusID']) {
                        case '0500':
                            $GetData['JDFstatusID_txt']="NEišsiųsta";
                            break;
                        case '1000':
                            $GetData['JDFstatusID_txt']="Kiekis pakeistas";
                            break;
                        case '2000':
                            $GetData['JDFstatusID_txt']="Darbas pakeistas";
                            break;
                        case '3000':
                            $GetData['JDFstatusID_txt']="JDF išsiųstas";
                            break;
                        
                        default:
                            $GetData['JDFstatusID_txt']="nenurodyta";
                            break;
                    }//end switch



                    //FPK statusas
                    switch ($GetData['FPKstatusID']) {
                        case '0500':
                            $GetData['FPKstatusID_txt']="Nežinoma";
                            break;
                        case '0600':
                            $GetData['FPKstatusID_txt']="Klišės išskaidytos";
                            break;
                        case '0700':
                            $GetData['FPKstatusID_txt']="Vokai paruošti";
                            break;
                        case '1000':
                            $GetData['FPKstatusID_txt']="Klišės yra";
                            break;
                        case '2000':
                            $GetData['FPKstatusID_txt']="Gaminti klišes";
                            break;
                        case '3000':
                            $GetData['FPKstatusID_txt']="Perdaryti klišes";
                            break;
                        case '5000':
                            $GetData['FPKstatusID_txt']="Klišės pagamintos";
                            break;
                        
                        default:
                            $GetData['FPKstatusID_txt']="nenurodyta";
                            break;
                    }//end switch



                    //Informacija apie produktus
                    $sqlpr = '
                        SELECT 
                            A.afg__ref AS ProdID,
                            A.afg__rpn AS GAMNr,
                            A.versiref AS Versija,

                            J.off__ref AS JobID,

                            A.off1_ref AS CalculationID1,
                            O.off__ref AS CalculationID,
                            O.bon__ref AS EstimateID,
                            A.zynrefkl AS KlArikukNr,


                            A.afg__rpn AS GmodNr,
                            I.omschr__ AS GrupPavad,


                            A.dossier_ AS PRD_Gamykl_KEG_KPG,
                    
                            A.user____ AS Aptarnaujantis,
                            A.dat_crea AS Sukurtas,
                            A.uitgeput AS Anuliuotas,
                            A.dat_uitg AS AnuliuotasData,
                            A.afg_oms1 AS Pavadinimas,
                            A.afg_oms2 AS StockM,
                            A.prkl_ref AS ProdGroupID,
                            A.srtdrkvl AS Type,
                            A.vrijdat1 AS DizPlanerData,

                            A.tstval01 AS GamBusID,
                            B.omschr__ AS GamBusName,
                            A.tstval02 AS NaujKart,
                            A.tstval03 AS FotoZyma,
                            A.tstval04 AS Embozingas,
                            A.tstval05 AS FPKbusena,
                            A.tstval07 AS KlaiduBusena,
                                                
                            A.wij__dat AS IvedimoData,
                            A.kla__ref AS KlientasID,
                            A.kla__rpn AS KlientasShort,
                            A.knp__ref AS KontaktoPersona,
                            A.zynrefkl AS KlientoKodas,
                            
                            A.ext__ref AS GrupavKodas,
                            
                            A.vrt__ref AS VadybininkoGID,
                            A.munt_ref AS ValiutosKodas,
                            A.kolom_10 AS PrekKodas,
                            A.layoutnr AS Layoutas,
                            A.accoord_ AS Sutartas,
                            A.off1_ref AS EsamoSkaiciavimoID,
                            A.ordrefpp AS PrepressJob,
                            
                            A.vpak_ref AS PakavimoProcID,
                            A.aant__e2 AS EtikRitinelyje,
                            A.aant__e3 AS RitinDezej,
                            A.aant__e4 AS DezPleteje,
                            A.aant__e5 AS Paleciu,
                            
                            A.art__ref AS MedziagaID,
                            A.art_ref2 AS Medziaga2ID,
                            A.art_ref3 AS Medziaga3ID,
                            A.art_ref4 AS Medziaga4ID,
                            A.art_ref5 AS Medziaga5ID,
                            
                            A.vpakcom2 AS Pastaba2,
                            A.vpakcom3 AS Pastaba3,
                            A.vpakcom4 AS Pastaba4,
                            A.vpakcom5 AS Pastaba5,
                            
                            A.layoutnr AS Layout,
                                                
                            A.layvpk_2 AS LayOut2,
                            A.etiket_2 AS LayOut2ONOFF,
                            A.aanteti2 AS KiekisPak2,
                            
                            A.layvpk_3 AS LayOut3,
                            A.etiket_3 AS LayOut3ONOFF,
                            A.aanteti3 AS KiekisPak3,
                            
                            A.layvpk_4 AS LayOut4,
                            A.etiket_4 AS LayOut4ONOFF,
                            A.aanteti4 AS KiekisPak4,
                            
                            A.layvpk_5 AS LayOut5,
                            A.etiket_5 AS LayOut5ONOFF,
                            A.aanteti5 AS KiekisPak5,
                            
                            A.laypalet AS PaletesLayOutas,
                            
                            A.aant_rol AS KiekisRitinelyje,
                            A.diamt_mx AS DiametrasRitinel,
                            
                            A.dikteafg AS produktoStorisMcM,
                            A.kern____ AS IvoresDiam,
                            A.diktekrn AS IvoresStoris,
                            
                            A.afg_orig AS TevasProdukto,
                            
                            A.eti_vorm AS PeilioFormaID,
                            A.radius__ AS PeilioR,
                            A.etiket_b AS PeilPlotis,
                            A.etiket_h AS PeilIlgis,
                            A.krit___1 AS PlotisC,
                            A.krit___2 AS IlgisC,

                            A.rol____b AS RitinelPlotis,
                            A.lblgp___ AS TarpasTarpEtik,
                            A.lblgp_mn AS TarpasTarpEtikMin,
                            A.lblgp_mx AS TarpasTarpEtikMax,
                            A.m2__1000 AS m2__1000,
                            A.klcod_fr AS SpalvuKodPriekio,
                            A.klcod_lm AS SpalvuKodKliju,
                            A.klcod_rg AS SpalvuKodNugareles,
                            A.wikk____ AS SukimoID,
                            A.vrm__ref AS PeilioID,
                            F.stns_ref AS SamatPeilioID,
                            A.pap__srt AS PopieriausTipasID,
                            A.drg__ref AS SubstrateID,
                            M.drg__oms AS SubstrateOMS,

                            A.art__ref AS MaterialID,
                            
                            A.commkern AS SubstrateKomentaras,
                            A.drg__las AS ArGalimMedzSujungimInRoll,
                            
                            A.kpnafw_1 AS NumeravimasPerforavimas,
                            A.kpnafw_2 AS Lakavimas,
                            A.kpnafw_3 AS Laminavimas,
                            A.kpnafw_4 AS Lankst,
                            A.kpnafw_6 AS Ikirtimas,
                            A.kpnafw_7 AS Isemimas,
                            A.kpnafw_8 AS Foil,
                            A.kpnafw_9 AS MaterialID,
                            
                            A.wij__dat AS PaskKeitimoData,
                            A.wij__usr AS PskutinisKeite,
                            
                            A.rowid AS ID,
                            
                            A.comm_chk AS KomentarasKiekiui,
                            
                            A.diamtmax AS MaxDiam,
                            A.diamtmin AS MinDiam,
                            
                            A.src_file AS SourceFile,
                            
                            A.prys_srt AS MatVNTID,

                            A.vrijveld AS KiekApmokestKlisiu,
                            
                            /* Klientu info is klientu DB lenteles*/
                            C.naam____ AS KLpavad,
                            C.straat__ AS KLadresas,
                            C.land_ref AS KLsaliesKodas,
                            C.county__ AS KLapygarda,
                            C.post_ref AS KLpastoKodas,
                            C.postnaam AS KLmiestas,
                            C.kla__rpn AS KLtrumpas,
                            C.telefoon AS KLtel,
                            C.telefax_ AS KLfaksas,
                            C.telex___ AS KLemail,
                            C.taal_ref AS KLkalbaID,
                            C.kla__com AS KLkomentaras,
                            C.website_ AS KLweb,
                            C.munt_ref AS KLvaliutosKodas,
                            C.btw___nr AS KLPVMkodas,
                            C.btw_____ AS KLpvmY_N,
                            C.handelnr AS KLimonesKod,
                            C.klgr_ref AS KLklientoGrupesID,
                            C.vrt__ref AS KLatstovasID,
                            C.amo__akn AS KLapyvartaGal,
                            C.levtrref AS KLpageidaujamVezejasID,
                            C.geblokk_ AS KLblokavimasDarbui,
                            C.gblk_off AS KLblokavimasUzsakymui,
                            C.gblk_lev AS KLblokavimasPristatymui,
                            C.gblk_fak AS KLblokavimasInvoices,
                            C.gblk_bst AS KLblokavimasPardUzsak,
                            C.int_cont AS KLvidinisKontaktAsm,
                            C.wij__dat AS KLivedimoData,
                            C.gblk_bst AS KLivedeKas,
                            C.rowid    AS KLRowID, 
                            
                            /* Peilio info */
                            P.stns_oms AS PIPeilioConcat,
                            P.stns_rpn AS PIgmodID,
                            P.stn_vorm AS PIPeilioFormaID,
                            P.radius__ AS PIPeilio_R,
                            P.etiket_b AS PIplotis,
                            P.etiket_h AS PIilgis,
                            P.aantal_b AS PIkiekisPerPloti,
                            P.aantal_h AS PIkiekisPerIlgi,
                            P.marge__r AS PIdesineParaste,
                            P.marge__l AS PIkaireParaste,
                            P.kommen_1 AS PIpastaba,
                            P.fab__dat AS PIPagaminimoData,
                            P.aktief__ AS PIaktyvumas,
                            P.kom__akt AS PInaudojimoKomentaras,
                            P.user____ AS PIuzsake,
                            P.weblabel AS PIRodomasInternete,
                            
                            P.radius__ AS PIPeilio_R,


                            /* SPALVOS */
                            F.klcod_fr AS CA_ColorFront,
                            KFR.taal___1 AS KLR_SpalvuFRAprasymas,
                            KFR.antklr__ AS KLR_SpalvuFRSkaicius,
                            F.klcod_lm AS CA_ColorAdh,
                            KLM.taal___1 AS KLR_SpalvuLMAprasymas,
                            KLM.antklr__ AS KLR_SpalvuLMSkaicius,
                            F.klcod_rg AS CA_ColorBack,
                            KRG.taal___1 AS KLR_SpalvuRGAprasymas,
                            KRG.antklr__ AS KLR_SpalvuRGSkaicius,
                            F.klr__ref AS CA_ColorPaper,

                            
                            /* GAMYBOS */
                            G.omschr_7 AS KEGKPG,
                            G.omschr_9 AS KEGKPG_OLD,

                            J.oplage__ AS UzsakytaVnt,

                            P.rowid AS PIrowID

                        FROM "v4vrs___" AS J
                        LEFT JOIN "afgart__" AS A ON (A.afg__ref = J.afg__ref )
                        LEFT JOIN "tstval__" AS B ON (A.tstval01 = B.tstd_ref AND B.tabname_=\'afgart__\' AND B.fldname_=\'tstval01\')
                        LEFT JOIN "klabas__" AS C ON (A.kla__ref = C.kla__ref )
                        LEFT JOIN "prodkl__" AS I ON (I.prkl_ref = A.prkl_ref )
                        
                        LEFT JOIN "prodkl__" AS G ON (A.prkl_ref = G.prkl_ref )
                        LEFT JOIN "drgers__" AS M ON (A.drg__ref = M.drg__ref )
                        LEFT JOIN "order___" AS O ON (J.off__ref = O.ord__ref )
                        LEFT JOIN "v1eti___" AS F ON (O.off__ref = F.off__ref )
                        LEFT JOIN "klrcod__" AS KFR ON (F.klcod_fr = KFR.klcodref )
                        LEFT JOIN "klrcod__" AS KLM ON (F.klcod_lm = KLM.klcodref )
                        LEFT JOIN "klrcod__" AS KRG ON (F.klcod_rg = KRG.klcodref )

                        LEFT JOIN "stnspr__" AS P ON (F.stns_ref = P.stns_ref )
                        WHERE 
                            J.off__ref=\''. $GetData['JobID'].'\' AND O.type_ord = 2
                    ';
                    $ProductData = $mssql->querySql($sqlpr, 1);

                    if(is_array($ProductData) AND count($ProductData)>0){
                        foreach ($ProductData as $Pkey => $produktas) {


                                //Geri metrai pagal produkta
                                //TODO is clocingsu istraukti gerus metrus produktui.
                                //TODO panasiai kaip auksciau traukiu visas JOBui, bet dar reikia atsirinkti tik tam produktui
                                //TODO o atsirinkti panasiai kaip atsirenkama OEE, OEmE rodikliams (burtu knygoje prie OEE aprasymo), pagal vesija 001, 002 ... versijos gali buti kelios atskirtos kabliataskiu ar dvitaskiu (kai vienu metu spausdina kelis GAMus)
                                //TODO todel reikia parsinti ir ziureti.
                                $ProductData[$Pkey]['ZalPRODSunaudGeriMetrai']="";


                                //Produkto pardavimo kaina
                                //TODO istraukti kaina is S/F arba, jeigu ten jos nera tai is SalesOrder
                                //TODO kolkas sukuriu tik lauka
                                $ProdPrice = $this->getPrice($produktas['JobID'], $produktas['ProdID']);
                                $ProductData[$Pkey]['ProdPardavKainaUzMata'] = $ProdPrice['KainaUzMatBePVM_1'];
                                $ProductData[$Pkey]['ProdPardavMatas'] = $ProdPrice['KainosKategorija'];
                                $ProductData[$Pkey]['ProdPardavKainaUzMataSuPVM'] = $ProdPrice['KainaUzMatSuPVM_1'];
                                $ProductData[$Pkey]['ProdPardavKiekis'] = $ProdPrice['VisoKiekis'];
                                $ProductData[$Pkey]['ProdPardavKainaUzKieki'] = $ProdPrice['OrderioSuma'];




                                //Papildomi laikai
                                if($produktas['Anuliuotas']=='Y'){
                                    $ProductData[$Pkey]['Anuliuotas_txt']="Anuliuotas";
                                }elseif($produktas['Anuliuotas']=='N'){
                                    $ProductData[$Pkey]['Anuliuotas_txt']="Aktyvus";
                                }else{
                                    $ProductData[$Pkey]['Anuliuotas_txt']="NaN";
                                }
                                //!!!!!! DEBUG
                                //$this->var_dump($ProductData, "ProductData <hr>$sqlpr<hr> ");//-----------------DEBUG


                                $ProductData[$Pkey]['MatVNT_ID'] = $produktas['MatVNTID'];
                                $ProductData[$Pkey]['MatVNTID'] = $this->getVNT ($produktas['MatVNTID']);


                                //PRD_FotoZyme
                                switch ($produktas['FotoZyma']) {
                                    case '0000':
                                        $ProductData[$Pkey]['FotoZymaTxt']="X";
                                        break;
                                    case '1000':
                                        $ProductData[$Pkey]['FotoZymaTxt']="A";
                                        break;
                                    case '1100':
                                        $ProductData[$Pkey]['FotoZymaTxt']="B";
                                        break;
                                    case '1200':
                                        $ProductData[$Pkey]['FotoZymaTxt']="C";
                                        break;
                                    case '1300':
                                        $ProductData[$Pkey]['FotoZymaTxt']="D";
                                        break;
                                    case '1400':
                                        $ProductData[$Pkey]['FotoZymaTxt']="E";
                                        break;
                                    case '1500':
                                        $ProductData[$Pkey]['FotoZymaTxt']="F";
                                        break;
                                    case '1600':
                                        $ProductData[$Pkey]['FotoZymaTxt']="G";
                                        break;
                                    case '1700':
                                        $ProductData[$Pkey]['FotoZymaTxt']="H";
                                        break;
                                    
                                    default:
                                        $ProductData[$Pkey]['FotoZymaTxt']="X";
                                        break;
                                }//end switch


                                //Etiketes forma pagal Calculation
                                switch ($produktas['PIPeilioFormaID']) {
                                    case '1':
                                        $ProductData[$Pkey]['PIPeilioFormaTxt']="Stačiakampis";
                                        break;
                                    case '2':
                                        $ProductData[$Pkey]['PIPeilioFormaTxt']="Apvalus";
                                        break;
                                    case '3':
                                        $ProductData[$Pkey]['PIPeilioFormaTxt']="Forminis";
                                        break;
                                    case '2':
                                        $ProductData[$Pkey]['PIPeilioFormaTxt']="Ovalus";
                                        break;
                                    
                                    default:
                                        $ProductData[$Pkey]['PIPeilioFormaTxt']="nenurodyta";
                                        break;
                                }//end switch


                                //Gaunam pagaminta kieki
                                $sqlKiek = "
                                    SELECT 
                                    afg__ref AS ProdID,
                                    ord__ref AS JobID,
                                    SUM(aant__ex) AS Pagaminta
                                    FROM afgsku__ WHERE afg__ref='".$produktas['ProdID']."' AND ord__ref='".$produktas['JobID']."'
                                    GROUP BY afg__ref, ord__ref
                                ";

                                $PagamintaArray = $mssql->querySqlOneRow($sqlKiek, 1);

                                //!!!!!! DEBUG
                                //$this->var_dump($PagamintaArray, "PagamintaArray <hr>$sqlKiek<hr> ");//-----------------DEBUG
                                if(is_numeric($PagamintaArray['Pagaminta'])){
                                    $ProductData[$Pkey]['PagamintaKiekis']=$PagamintaArray['Pagaminta'];
                                }else{
                                    $ProductData[$Pkey]['PagamintaKiekis']=0;
                                }
                                if(is_numeric($produktas['UzsakytaVnt']) AND $produktas['UzsakytaVnt'] > 0){
                                    $ProductData[$Pkey]['PagamintaProc']=$PagamintaArray['Pagaminta']/$produktas['UzsakytaVnt']*100;
                                }else{
                                    $ProductData[$Pkey]['PagamintaProc']=0;
                                }


                                //Pirmo pajamavimo data

                                $sqlDat = "
                                    SELECT 
                                    afg__ref AS ProdID,
                                    ord__ref AS JobID,
                                    vrrd_dat AS PagData
                                    FROM afgsku__ WHERE afg__ref='".$produktas['ProdID']."' AND ord__ref='".$produktas['JobID']."'
                                    ORDER BY vrrd_dat ASC
                                ";

                                $PagDataArray = $mssql->querySqlOneRow($sqlDat, 1);

                                //!!!!!! DEBUG
                                //$this->var_dump($PagDataArray, "PagDataArray <hr>$sqlKiek<hr> ");//-----------------DEBUG
                                if($PagDataArray['PagData']){
                                    $ProductData[$Pkey]['PirmoPagamData']=substr($PagDataArray['PagData'],0,10);
                                }else{
                                    $ProductData[$Pkey]['PirmoPagamData']='0000-00-00';
                                }


                        }//end foreach
                    }else{
                        $ProductData = array();
                    }

                    $GetData['PRODUCT']=$ProductData;



                    //FINISHINGAS
                    if($this->is_ID($GetData['CalculationID'])){
                        //$mssql = DBMSSqlCERM::getInstance();

                        $sqlFin = "
                            SELECT  
                                G.art__ref AS ZalID, 
                                Z.art_oms1 AS ZalName, 
                                G.drg__ref AS SubstrateID, 
                                S.drg__oms AS SubstrateName, 
                                G.grd__oms, 
                                G.etap_ref, 
                                G.etap_typ, 
                                G.kpn__ref, 
                                H.omschr__ AS FinishName, 
                                H.etas_ref, 
                                H.kombrf_1 AS FinishShort
                            FROM v1etaf__ AS G
                            LEFT JOIN stdeap__ AS H ON (G.etap_ref = H.etap_ref )
                            LEFT JOIN drgers__ AS S ON (G.drg__ref = S.drg__ref )
                            LEFT JOIN artiky__ AS Z ON (G.art__ref = Z.art__ref )
                            WHERE off__ref = '".$GetData['CalculationID']."'
                        ";

                        $FinishData = $mssql->querySql($sqlFin, 1);

                        //!!!!!! DEBUG
                        //$this->var_dump($FinishData, "FinishData <hr>$sqlFin<hr> ");//-----------------DEBUG

                        //PAPILDOMOS ZALIAVOS
                        if(is_array($FinishData) AND count($FinishData)>0){
                            $i=0;
                            foreach ($FinishData as $key => $finDuom) {
                                if($finDuom['SubstrateName']){
                                    $GetData['PapildZaliav'][$i]['ZalID']=$finDuom['ZalID'];
                                    $GetData['PapildZaliav'][$i]['ZalName']=$finDuom['ZalName'];
                                    $GetData['PapildZaliav'][$i]['SubstrateID']=$finDuom['SubstrateID'];
                                    $GetData['PapildZaliav'][$i]['SubstrateName']=$finDuom['SubstrateName'];
                                    $i++;
                                }
                            }//end foreach
                        }//end if

                    }else{
                        $FinishData = array();
                    }//end else


            }//end if (nerado in DB tokio darbo)


        }else{//end if
            $GetData['afg__ref']="Nežinomas darbas";
        }

        $GetData['FINISHING'] = $FinishData;
        return $GetData;

    }//end function



    public function getPrice($JobID, $ProdID){

        if($this->is_ID($JobID) AND $this->is_ID($ProdID)){
                $mssql = DBMSSqlCERM::getInstance();

                $sql = '
                    SELECT 
                        ord__ref AS JOB_ID, 
                        vrs__ref AS JOB_VER, 
                        afg__ref AS PROD_ID,
                        fak__ref AS FakturaID,
                        bst__ref AS ORDER_ID,
                        lyn__ref,
                        
                        pr_excl_ AS KainaUzMatBePVM_1, 
                        pr_exclv AS KainaUzMatBePVM_2,
                        preexcl_ AS KainaUzPakuoteBePVM_1, /* pvz A4 kai kaina uz 1000 o pakuoteje 200 */
                        preexclv AS KainaUzPakuoteBePVM_4, /* pvz A4 kai kaina uz 1000 o pakuoteje 200 */
                        proexcl_ AS KainaUzMatBePVM_5, 
                        proexclv AS KainaUzMatBePVM_6, 
                        pr_incl_ AS KainaUzMatSuPVM_1,
                        pr_inclv AS KainaUzMatSuPVM_2, 

                        munt_ref AS Valiuta,

                        vpak_ref AS PakavProceduraID,
                        prys_srt AS KainosKategorijaID,
                        prys_typ AS KainosTipasID,  
                        prys_tst AS KainosStatusasID, /* 0- Not applicable, 1-Price to be termined, 2-Price determined */
                        bedr__bm AS OrderioSuma,
                        bedro_vm AS OrderintasKiekis,
                        b_aantal AS VisoKiekis,
                        beaantal AS VisoKiekis1,
                        
                        aant__e2 AS Pakuote2SupakuotaPoTiek,
                        aant__e3 AS Pakuote3SupakuotaPoTiek,
                        aant__e4 AS Pakuote4SupakuotaPoTiek,
                        aant__e5 AS Pakuote5SupakuotaPoTiek,
                        
                        l_aantal AS IssiustasKiekis,
                        f_aantal AS InvoicintasKiekis,
                        
                        b_netto_ AS SvorisNetto,
                        b_tarra_ AS PakuotesSvorisNumatytas,
                        l_tarra_ AS RealusTarosSvorisBuvo,
                        
                        dossier_ AS KEGKPG
                        

                        
                    FROM bstlyn__
                    WHERE ord__ref=\''.$JobID.'\' AND afg__ref = \''.$ProdID.'\'
                ';

                $mssql = DBMSSqlCERM::getInstance();
                $SalesOrderData = $mssql->querySqlOneRow($sql, 1);

                $SalesOrderData['KainosKategorija'] = $this->getVNT ($SalesOrderData['KainosKategorijaID']);

                /*
                if($this->is_ID($SalesOrderData['FakturaID'])){

                        $sqlFke = '
                            SELECT 
                                FAKEL.volgnr__ AS EilesNr,
                                FAKEL.lyn__ref,
                                FAKEL.ord__ref,
                                FAKEL.afg__ref,
                                FAKEL.dok__srt AS DokTipasID, 
                                
                                FAKEL.fkttxt1_ AS EilutesPavadinimas,
                                FAKEL.f_aantal AS IsrasytasKiekis,
                                FAKEL.stprysbm AS VntKaina,
                                FAKEL.prys_srt AS KainosKategorija,
                                FAKEL.btw_____ AS PVMKodas,
                                FAKEL.btw___vm AS PVMSuma,  
                                FAKEL.tota__bm AS KainosSuma,
                                FAKEL.tota__vm AS KainosSuma1

                            FROM hafgfl__ AS FAKEL
                            WHERE FAKEL.fak__ref = \''.$SalesOrderData['FakturaID'].'\' AND FAKEL.lyn__ref = \''.$SalesOrderData['lyn__ref'].'\' AND FAKEL.dok__srt=1
                        ';

                        // WHERE'i reikalinga ne tik lyn__ref, bet ir fakturos nr, nes gali buti, kad pagal lyn__ref bus kelios fakturos eilutes (pvz: lyn_ref=0000617875), gali buti su neigiamomis kainomis(grazinimai)
                        $mssql = DBMSSqlCERM::getInstance();
                        $FakturaEilData = $mssql->querySql($sqlFke, 1);


                }//end if
                */

        }//end if
        //!!!!!! DEBUG
        //$this->var_dump($SalesOrderData, "SalesOrderData <hr>$sql<hr> ");//-----------------DEBUG

        //!!!!!! DEBUG
        //$this->var_dump($FakturaEilData, "FakturaEilData <hr>$sqlFke<hr> ");//-----------------DEBUG

        return $SalesOrderData;
    }//end function




    public function getVNT ($KainosKategorijaID){

                switch ($KainosKategorijaID) {
                    case '0':
                        $KainosKategorija='text';
                        break;
                    case '1':
                        $KainosKategorija='Discount/Supplement';
                        break;
                    case '2':
                        $KainosKategorija='Fixed amount';
                        break;
                    case '3':
                        $KainosKategorija='/kg';
                        break;
                    case '4':
                        $KainosKategorija='/page';
                        break;
                    case '5':
                        $KainosKategorija='/pice';
                        break;
                    case '6':
                        $KainosKategorija='/100';
                        break;
                    case '7':
                        $KainosKategorija='/1000';
                        break;
                    case '8':
                        $KainosKategorija='/100000';
                        break;
                    case '9':
                        $KainosKategorija='/1000 kg';
                        break;
                    case 'B':
                        $KainosKategorija='/m';
                        break;
                    case 'C':
                        $KainosKategorija='/m2';
                        break;
                    
                    default:
                        $KainosKategorija='';
                        break;
                }//end switch

        return $KainosKategorija;
    }//end function
    

/* -------------------------------------------------------------------------- */


    //paima informacija apie PRODUCTa pagal Producto ID
    public function getProductDataByID($PROD_ID) {

        $mssql = DBMSSqlCERM::getInstance();

        if($this->is_ID($PROD_ID)){
            $sql = '
                SELECT 
                    A.afg__ref AS ProdID,
                    A.afg__rpn AS GAMNr,
                    A.versiref AS Versija,
                    A.user____ AS Aptarnaujantis,
                    A.dat_crea AS Sukurtas,
                    A.uitgeput AS Anuliuotas,
                    A.dat_uitg AS AnuliuotasData,
                    A.afg_oms1 AS Pavadinimas,
                    A.afg_oms2 AS StockM,
                    A.prkl_ref AS ProdGroupID,
                    A.srtdrkvl AS Type,
                    A.vrijdat1 AS DizPlanerData,

                    A.krit___1 AS EtikPlotis,
                    A.krit___2 AS EtikIlgis,
                    
                    A.tstval01 AS GamBusID,
                    B.omschr__ AS GamBusName,
                    A.tstval02 AS NaujKart,
                    A.tstval03 AS FotoZyma,
                    A.tstval04 AS Embozingas,
                    A.tstval05 AS FPKbusena,
                    A.tstval07 AS KlaiduBusena,
                                        
                    A.wij__dat AS IvedimoData,
                    A.kla__ref AS KlientasID,
                    A.kla__rpn AS KlientasShort,
                    A.knp__ref AS KontaktoPersona,
                    A.zynrefkl AS KlientoKodas,
                    
                    A.ext__ref AS GrupavKodas,
                    
                    A.vrt__ref AS VadybininkoGID,
                    A.munt_ref AS ValiutosKodas,
                    A.kolom_10 AS PrekKodas,
                    A.layoutnr AS Layoutas,
                    A.accoord_ AS Sutartas,
                    A.off1_ref AS EsamoSkaiciavimoID,
                    A.ordrefpp AS PrepressJob,
                    
                    A.vpak_ref AS PakavimoProcID,
                    A.aant__e2 AS EtikRitinelyje,
                    A.aant__e3 AS RitinDezej,
                    A.aant__e4 AS DezPleteje,
                    A.aant__e5 AS Paleciu,
                    
                    A.art__ref AS MedziagaID,
                    A.art_ref2 AS Medziaga2ID,
                    A.art_ref3 AS Medziaga3ID,
                    A.art_ref4 AS Medziaga4ID,
                    A.art_ref5 AS Medziaga5ID,
                    
                    A.vpakcom2 AS Pastaba2,
                    A.vpakcom3 AS Pastaba3,
                    A.vpakcom4 AS Pastaba4,
                    A.vpakcom5 AS Pastaba5,
                    
                    A.layoutnr AS Layout,
                                        
                    A.layvpk_2 AS LayOut2,
                    A.etiket_2 AS LayOut2ONOFF,
                    A.aanteti2 AS KiekisPak2,
                    
                    A.layvpk_3 AS LayOut3,
                    A.etiket_3 AS LayOut3ONOFF,
                    A.aanteti3 AS KiekisPak3,
                    
                    A.layvpk_4 AS LayOut4,
                    A.etiket_4 AS LayOut4ONOFF,
                    A.aanteti4 AS KiekisPak4,
                    
                    A.layvpk_5 AS LayOut5,
                    A.etiket_5 AS LayOut5ONOFF,
                    A.aanteti5 AS KiekisPak5,
                    
                    A.laypalet AS PaletesLayOutas,
                    
                    A.aant_rol AS KiekisRitinelyje,
                    A.diamt_mx AS DiametrasRitinel,
                    
                    A.dikteafg AS produktoStorisMcM,
                    A.kern____ AS IvoresDiam,
                    A.diktekrn AS IvoresStoris,
                    
                    A.afg_orig AS TevasProdukto,
                    
                    A.eti_vorm AS PeilioFormaID,
                    A.radius__ AS PeilioR,
                    A.etiket_b AS PeilPlotis,
                    A.etiket_h AS PeilIlgis,
                    A.krit___1 AS PlotisC,
                    A.krit___2 AS IlgisC,

                    A.rol____b AS RitinelPlotis,
                    A.lblgp___ AS TarpasTarpEtik,
                    A.lblgp_mn AS TarpasTarpEtikMin,
                    A.lblgp_mx AS TarpasTarpEtikMax,
                    A.m2__1000 AS m2__1000,
                    A.wikk____ AS SukimoID,
                    A.vrm__ref AS PeilioID,
                    A.pap__srt AS PopieriausTipasID,
                    A.drg__ref AS SubstrateID,
                    M.drg__oms AS SubstrateOMS,

                    A.art__ref AS MaterialID,
                    
                    A.commkern AS SubstrateKomentaras,
                    A.drg__las AS ArGalimMedzSujungimInRoll,
                    
                    A.kpnafw_1 AS NumeravimasPerforavimas,
                    A.kpnafw_2 AS Lakavimas,
                    A.kpnafw_3 AS Laminavimas,
                    A.kpnafw_4 AS Lankst,
                    A.kpnafw_6 AS Ikirtimas,
                    A.kpnafw_7 AS Isemimas,
                    A.kpnafw_8 AS Foil,
                    A.kpnafw_9 AS MaterialID,
                    
                    A.wij__dat AS PaskKeitimoData,
                    A.wij__usr AS PskutinisKeite,
                    
                    A.rowid AS ID,
                    
                    A.comm_chk AS KomentarasKiekiui,
                    
                    A.diamtmax AS MaxDiam,
                    A.diamtmin AS MinDiam,
                    
                    A.src_file AS SourceFile,
                    
                    A.prys_srt AS MatVNTID,

                    A.vrijveld AS KiekApmokestKlisiu,
                    
                    
                    C.naam____ AS KLpavad,
                    C.straat__ AS KLadresas,
                    C.land_ref AS KLsaliesKodas,
                    C.county__ AS KLapygarda,
                    C.post_ref AS KLpastoKodas,
                    C.postnaam AS KLmiestas,
                    C.kla__rpn AS KLtrumpas,
                    C.telefoon AS KLtel,
                    C.telefax_ AS KLfaksas,
                    C.telex___ AS KLemail,
                    C.taal_ref AS KLkalbaID,
                    C.kla__com AS KLkomentaras,
                    C.website_ AS KLweb,
                    C.munt_ref AS KLvaliutosKodas,
                    C.btw___nr AS KLPVMkodas,
                    C.btw_____ AS KLpvmY_N,
                    C.handelnr AS KLimonesKod,
                    C.klgr_ref AS KLklientoGrupesID,
                    C.vrt__ref AS KLatstovasID,
                    C.amo__akn AS KLapyvartaGal,
                    C.levtrref AS KLpageidaujamVezejasID,
                    C.geblokk_ AS KLblokavimasDarbui,
                    C.gblk_off AS KLblokavimasUzsakymui,
                    C.gblk_lev AS KLblokavimasPristatymui,
                    C.gblk_fak AS KLblokavimasInvoices,
                    C.gblk_bst AS KLblokavimasPardUzsak,
                    C.int_cont AS KLvidinisKontaktAsm,
                    C.wij__dat AS KLivedimoData,
                    C.gblk_bst AS KLivedeKas,
                    C.rowid    AS KLRowID, 
                    
                    
                    P.stns_oms AS PIPeilioConcat,
                    P.stns_rpn AS PIgmodID,
                    P.stn_vorm AS PIPeilioFormaID,
                    P.radius__ AS PIPeilio_R,
                    P.etiket_b AS PIplotis,
                    P.etiket_h AS PIilgis,
                    P.aantal_b AS PIkiekisPerPloti,
                    P.aantal_h AS PIkiekisPerIlgi,
                    P.marge__r AS PIdesineParaste,
                    P.marge__l AS PIkaireParaste,
                    P.kommen_1 AS PIpastaba,
                    P.fab__dat AS PIPagaminimoData,
                    P.aktief__ AS PIaktyvumas,
                    P.kom__akt AS PInaudojimoKomentaras,
                    P.user____ AS PIuzsake,
                    P.weblabel AS PIRodomasInternete,
                    P.rowid AS PIID,
                    P.radius__ AS PIPeilio_R,

                            /* SPALVOS */
                    
                    
                    

                            A.klcod_fr AS SpalvuKodPriekio,
                            KFR.taal___1 AS KLR_SpalvuFRAprasymas,
                            KFR.antklr__ AS KLR_SpalvuFRSkaicius,
                            A.klcod_lm AS SpalvuKodKliju,
                            KLM.taal___1 AS KLR_SpalvuLMAprasymas,
                            KLM.antklr__ AS KLR_SpalvuLMSkaicius,
                            A.klcod_rg AS SpalvuKodNugareles,
                            KRG.taal___1 AS KLR_SpalvuRGAprasymas,
                            KRG.antklr__ AS KLR_SpalvuRGSkaicius,



                    G.omschr_7 AS KEGKPG,
                    G.omschr_9 AS KEGKPG_OLD

                FROM "afgart__" AS A
                LEFT JOIN "tstval__" AS B ON (A.tstval01 = B.tstd_ref AND B.tabname_=\'afgart__\' AND B.fldname_=\'tstval01\')
                LEFT JOIN "klabas__" AS C ON (A.kla__ref = C.kla__ref )
                LEFT JOIN "stnspr__" AS P ON (A.vrm__ref = P.stns_ref )
                LEFT JOIN "prodkl__" AS G ON (A.prkl_ref = G.prkl_ref )
                LEFT JOIN "drgers__" AS M ON (A.drg__ref = M.drg__ref )

                        LEFT JOIN "klrcod__" AS KFR ON (A.klcod_fr = KFR.klcodref )
                        LEFT JOIN "klrcod__" AS KLM ON (A.klcod_lm = KLM.klcodref )
                        LEFT JOIN "klrcod__" AS KRG ON (A.klcod_rg = KRG.klcodref )

                WHERE 
                    A.afg__ref = '.$PROD_ID.' 
            ';


            $GamData = $mssql->querySqlOneRow($sql, 1);

            //produkto Grupes duomenis
            if($this->is_ID($GamData['ProdGroupID'])){

                $sqlm = '
                    SELECT 
                        G.prkl_ref AS PG_ProdKodID,
                        G.omschr__ AS PG_PavadinimasLT,
                        G.omschr_7 AS PG_Gamyba_KPG_KEG,
                        G.omschr_9 AS PG_Pakavimas_KPG_KEG,
                        G.omschr_8 AS PG_ROLL_BOX,
                        G.prys_typ AS PG_KainTip,
                        G.prys_srt AS PG_KainosTipas,

                        T.prd__rpn AS PT_Kodas,
                        T.uitgeput AS PT_Galioja,
                        T.omschr__ AS PT_TipoPavad,
                        T.prkl_ref AS PT_ProdGrupID,
                        T.opl__oms AS PT_MatVnt,
                        T.supp_ant AS PT_MatoKiekis,
                        T.kpn__srt AS PT_KindOfProduct,
                        T.vpak_ref AS PT_PakProcedura,
                        T.prys_srt AS PT_KainosKategorija,
                        T.omsaant_ AS PT_VntZodziu,
                        T.tmd__ref AS PT_JobTicketModel,
                        T.taal___1 AS PT_PavadinimasLt


                    FROM "prodkl__" AS G
                    LEFT JOIN "afgprd__" AS T ON (G.prkl_ref = T.prkl_ref )
                    WHERE 
                        G.prkl_ref = '.$GamData['ProdGroupID'].' 
                ';

                $ProdGrupData = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($ProdGrupData)){
                    $GamData = array_merge($GamData, $ProdGrupData);
                }else{//end if
                    //echo "----NERADAU ---";
                }

            }//end if


            //pasiimam medziaga1
            if($this->is_ID($GamData['MedziagaID'])){

                $sqlm = '
                    SELECT 
                        M.art__srt AS M1_MedzTipas,
                        M.art__rpn AS M1_MedzKodas,
                        M.art_oms1 AS M1_medzPavad,
                        M.drg__ref AS M1_substrateID,
                        M.gramm___ AS M1_gramatura,
                        M.breedte_ AS M1_ritinPlotis,
                        M.diktemic AS M1_mikroStoris,
                        M.uitgeput AS M1_Naudojamumas,
                        M.uitg_dat AS M1_naudojamasIki,
                        M.lev__ref AS M1_tiekejoID,
                        M.zyn__ref AS M1_tiekejoKodas,
                        M.std__bre AS M1_standartPlotis

                    FROM "artiky__" AS M
                    WHERE 
                        M.art__ref = '.$GamData['MedziagaID'].' 
                ';

                $Medz1Data = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($Medz1Data)){
                    $GamData = array_merge($GamData, $Medz1Data);
                }else{//end if
                    //$this->AddError("Neradau informacijos apie žaliavą; ");
                }
            }//end if

            if($this->is_ID($GamData['Medziaga2ID'])){

                $sqlm = '
                    SELECT 
                        M.art__srt AS M2_MedzTipas,
                        M.art__rpn AS M2_MedzKodas,
                        M.art_oms1 AS M2_medzPavad,
                        M.drg__ref AS M2_substrateID,
                        M.gramm___ AS M2_gramatura,
                        M.breedte_ AS M2_ritinPlotis,
                        M.diktemic AS M2_mikroStoris,
                        M.uitgeput AS M2_Naudojamumas,
                        M.uitg_dat AS M2_naudojamasIki,
                        M.lev__ref AS M2_tiekejoID,
                        M.zyn__ref AS M2_tiekejoKodas,
                        M.std__bre AS M2_standartPlotis

                    FROM "artiky__" AS M
                    WHERE 
                        M.art__ref = '.$GamData['Medziaga2ID'].' 
                ';


                $Medz2Data = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($Medz2Data)){
                    $GamData = array_merge($GamData, $Medz2Data);
                }else{//end if
                    //$this->AddError("Neradau informacijos apie žaliavą; ");
                }//end if
            }//end if

            if($this->is_ID($GamData['Medziaga3ID'])){

                $sqlm = '
                    SELECT 
                        M.art__srt AS M3_MedzTipas,
                        M.art__rpn AS M3_MedzKodas,
                        M.art_oms1 AS M3_medzPavad,
                        M.drg__ref AS M3_substrateID,
                        M.gramm___ AS M3_gramatura,
                        M.breedte_ AS M3_ritinPlotis,
                        M.diktemic AS M3_mikroStoris,
                        M.uitgeput AS M3_Naudojamumas,
                        M.uitg_dat AS M3_naudojamasIki,
                        M.lev__ref AS M3_tiekejoID,
                        M.zyn__ref AS M3_tiekejoKodas,
                        M.std__bre AS M3_standartPlotis

                    FROM "artiky__" AS M
                    WHERE 
                        M.art__ref = '.$GamData['Medziaga3ID'].' 
                ';


                $Medz3Data = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($Medz3Data)){
                    $GamData = array_merge($GamData, $Medz3Data);
                }else{//end if
                    //$this->AddError("Neradau informacijos apie žaliavą; ");
                }//end if
            }//end if

            if($this->is_ID($GamData['Medziaga4ID'])){

                $sqlm = '
                    SELECT 
                        M.art__srt AS M4_MedzTipas,
                        M.art__rpn AS M4_MedzKodas,
                        M.art_oms1 AS M4_medzPavad,
                        M.drg__ref AS M4_substrateID,
                        M.gramm___ AS M4_gramatura,
                        M.breedte_ AS M4_ritinPlotis,
                        M.diktemic AS M4_mikroStoris,
                        M.uitgeput AS M4_Naudojamumas,
                        M.uitg_dat AS M4_naudojamasIki,
                        M.lev__ref AS M4_tiekejoID,
                        M.zyn__ref AS M4_tiekejoKodas,
                        M.std__bre AS M4_standartPlotis

                    FROM "artiky__" AS M
                    WHERE 
                        M.art__ref = '.$GamData['Medziaga4ID'].' 
                ';


                $Medz4Data = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($Medz4Data)){
                    $GamData = array_merge($GamData, $Medz4Data);
                }else{//end if
                    //$this->AddError("Neradau informacijos apie žaliavą; ");
                }//end if
            }//end if

            if($this->is_ID($GamData['Medziaga5ID'])){

                $sqlm = '
                    SELECT 
                        M.art__srt AS M5_MedzTipas,
                        M.art__rpn AS M5_MedzKodas,
                        M.art_oms1 AS M5_medzPavad,
                        M.drg__ref AS M5_substrateID,
                        M.gramm___ AS M5_gramatura,
                        M.breedte_ AS M5_ritinPlotis,
                        M.diktemic AS M5_mikroStoris,
                        M.uitgeput AS M5_Naudojamumas,
                        M.uitg_dat AS M5_naudojamasIki,
                        M.lev__ref AS M5_tiekejoID,
                        M.zyn__ref AS M5_tiekejoKodas,
                        M.std__bre AS M5_standartPlotis

                    FROM "artiky__" AS M
                    WHERE 
                        M.art__ref = '.$GamData['Medziaga5ID'].' 
                ';


                $Medz5Data = $mssql->querySqlOneRow($sqlm, 1);

                if(is_array($Medz5Data)){
                    $GamData = array_merge($GamData, $Medz5Data);
                }else{//end if
                    //$this->AddError("Neradau informacijos apie žaliavą; ");
                }//end if
            }//end if
        }else{//
            $GamData = array();
        }


        return $GamData;
    }//end function




















/*
    public function getFullInfoByJobID ($JobID=0){ 

        $GUArray = array();
        if ($this->is_ID($ProdID)){

            $sql = '
                 SELECT 
                    A.afg__ref AS ProdID,
                    B.type_ord AS JOB_Type,
                    B.ord__ref AS OrderID,
                    A.off__ref AS JobID,
                    B.omschr__ AS JobPavad,
                    B.ord__rpn AS Raktazodis,
                    B.annul___ AS Anuliuotas,
                    
                    B.jobnr_vw AS ProcessID,
                    B.best_dat AS OrderDate,
                    B.kla__ref AS ClientID,
                    C.naam____ AS ClientName,
                    C.kla__rpn AS ClientNameShort,
                    C.telefoon AS ClientTel,
                    C.telex___ AS ClientEmail,
                    C.land_ref AS ClientKalba,
                    B.vrt__ref AS RepresentativeID,
                    D.naam____ AS RepresentativeName,
                    D.email___ AS RepresentativeEmail,
                    B.prd__ref AS ProductTypeID,
                    B.omschr__ AS Aprasymas,
                    B.Leverdat AS PristatymoData,
                    B.bon__ref AS SamatosID,
                    B.off__ref AS CalculationID,
                    B.tstval01 AS GamybosPlanID,
                    B.tstval02 AS DazuParuosID,
                    B.tstval03 AS JDFstatusID,
                    B.tstval04 AS FPKstatusID,
                    B.tstval05 AS DIZplanerID,
                    B.tstval06 AS PerkelimoBus,
                    B.tstval07 AS KlaiduBus,
                    B.prkl_ref AS KlientoOrderNr,

                    B.ord_begl AS Aptarnaujantis,
                    B.kalkulat AS SkaiciavoVardas,
                    B.int_cont AS VidinKontaktAsm,

                    E.uitgeput AS TypeEndOfLife,
                    E.opl__oms AS Vnt,
                    E.prys_srt AS KainosKategorija,
                    E.taal___1 AS TypePavanLT,
                    E.taal___2 AS TypePavadEN,
                    E.omschr__ AS TypeBazinPavad,

                                        P.off1_ref AS PRD_CalculationID,
                    P.zynrefkl AS PRD_klArikukNr,
                    P.drg__ref AS PRD_ZalID,
                    P.afg__rpn AS PRD_GmodNr,
                    P.afg_oms1 AS PRD_Pavad,
                    P.prkl_ref AS PRD_GroupID,
                    I.omschr__ AS PRD_GrupPavad,
                    P.afg_orig AS PRD_TevasID,
                    P.drg__ref AS PRD_SubstrateID,
                    P.art__ref AS PRD_Zal0,
                    P.art_ref1 AS PRD_Zal1,
                    P.art_ref2 AS PRD_Zal2,
                    P.art_ref3 AS PRD_Zal3,
                    P.art_ref4 AS PRD_Zal4,
                    P.art_ref5 AS PRD_Zal5,
                    P.dat_crea AS PRD_CreateDate,
                    P.dossier_ AS PRD_GamyklosID,
                    
                    P.tstval01 AS PRD_param1,
                    P.tstval02 AS PRD_param2,
                    P.tstval03 AS PRD_param3,
                    P.tstval04 AS PRD_param4,
                    P.tstval05 AS PRD_param5,

                    P.etiket_b AS PRD_Plotis,
                    P.etiket_h AS PRD_Ilgis,
                    P.kern____ AS PRD_IvoresDiam,
                    P.kla__ref AS PRD_KlientasID,
                    P.kla__rpn AS PRD_Klientas,
                    P.klcod_fr AS PRD_SpalvKodasFront,
                    P.klcod_lm AS PRD_SpalvKodasAntKliju,
                    P.klcod_rg AS PRD_SpalvKodasBacking,

                    P.kpnafw_1 AS PRD_NumberPerforate,
                    P.kpnafw_2 AS PRD_Varnish,
                    P.kpnafw_3 AS PRD_Laminating,
                    P.kpnafw_4 AS PRD_CreaseDie,
                    P.kpnafw_6 AS PRD_OtherFinishing,
                    P.kpnafw_7 AS PRD_Cutting,
                    P.kpnafw_8 AS PRD_Output,
                    P.kpnafw_9 AS PRD_FoilPrinting,

                    P.krit___1 AS PRD_KritPlotis,
                    P.krit___2 AS PRD_KritIlgis,
                    P.off1_ref AS PRD_CalculationID,
                    P.ordrefpp AS PRD_PrepressJob,
                    P.uitgeput AS PRD_EndOfLife,
                    P.wikk____ AS PRD_SukimasID,


                    F.ant_pltn AS CA_KlisiuKiekis,
                    F.art__ref AS CA_MateriailID,
                    Z.art_oms1 AS CA_MaterialKodas,
                    F.art_oms2 AS CA_SubstrateKodas,
                    F.asaf___b AS CA_ZingsnisHorizontalus,
                    F.breedte_ AS CA_BendrasPlotis,
                    F.drg__ref AS CA_SubstrateID,
                    F.eti_vorm AS CA_LabelFormID,
                    F.etiket_b AS CA_EtiketesPlotis,
                    F.etiket_h AS CA_EtiketesIlgis,
                    F.radius__ AS CA_Uzapvalinimas,
                    F.tssnaf_b AS CA_TarpasTarpEtikeciu,
                    F.tssnaf_h AS CA_TarpasPerPasikartojima,
                    
                    
                    F.klcod_fr AS CA_ColorFront,
                    KFR.taal___1 AS KLR_SpalvuFRAprasymas,
                    KFR.antklr__ AS KLR_SpalvuFRSkaicius,
                    F.klcod_lm AS CA_ColorAdh,
                    KLM.taal___1 AS KLR_SpalvuLMAprasymas,
                    KLM.antklr__ AS KLR_SpalvuLMSkaicius,
                    F.klcod_rg AS CA_ColorBack,
                    KRG.taal___1 AS KLR_SpalvuRGAprasymas,
                    KRG.antklr__ AS KLR_SpalvuRGSkaicius,
                    F.klr__ref AS CA_ColorPaper,

                    F.kpnafw_1 AS CA_PerforateNo,
                    F.kpnafw_2 AS CA_Varnish,                    
                    F.kpnafw_3 AS CA_Laminating,
                    F.kpnafw_4 AS CA_CreaseDie,
                    F.kpnafw_6 AS CA_OtherFinishing,
                    F.kpnafw_7 AS CA_Cutting,
                    F.kpnafw_8 AS CA_Output,
                    F.kpnafw_9 AS CA_FoilPrinting,
                    F.marge__l AS CA_ParasteLeft,
                    F.marge__r AS CA_ParasteRight,
                    
                    F.omtrek__ AS CA_VelenoIlgis,
                    F.trm__ref AS CA_Z,
                    F.prs__ref AS CA_PressID,
                    M.prs__oms AS CA_SpaudosMasina,
                    M.mxdrktor AS CA_SpaudosMasinStotys,
                    
                    
                    F.stns_ref AS CA_PeilioID,
                    F.aantal_b AS CA_EiliuSk,
                    F.aantal_h AS CA_PasikartojimuSk,

                    H.omschr__ AS ES_Pavad,
                    H.bon__ref AS ES_SamataNr,

                    DIE.stns_ref AS DIE_PeilioID,
                    DIE.stns_oms AS DIE_Concat,
                    DIE.kla__ref AS DIE_PeilSavinKlientasID,
                    DIE.stn_vorm AS DIE_FormaCode,
                    DIE.radius__ AS DIE_R,
                    DIE.materie_ AS DIE_Tipas,
                    DIE.etiket_b AS DIE_Plotis,
                    DIE.etiket_h AS DIE_Ilgis,
                    DIE.tssnaf_b AS DIE_ParasteB,
                    DIE.tssnaf_h AS DIE_ParasteH,
                    DIE.aantal_b AS DIE_EiliuB,
                    DIE.aantal_h AS DIE_PasikartojimuH,
                    DIE.omtrek__ AS DIE_Z,
                    DIE.tanden__ AS DIE_Dantu,
                    DIE.marge__l AS DIE_ParasteLeft,
                    DIE.marge__r AS DIE_ParasteRight,
                    DIE.margemnl AS DIE_ParasteLeftMin,
                    DIE.margemnr AS DIE_ParasteRightMin,
                    DIE.kommen_1 AS DIE_Komentaras,
                    DIE.aktief__ AS DIE_NaudojamasYN,
                    DIE.weblabel AS DIE_ToWeb,
                    DIEB.tstval01 AS DIE_BusenaID


                    
                FROM "v4vrs___" AS A
                LEFT JOIN "order___" AS B ON (A.off__ref = B.ord__ref )
                LEFT JOIN "klabas__" AS C ON (B.kla__ref = C.kla__ref )
                LEFT JOIN "verte___" AS D ON (B.vrt__ref = D.vrt__ref )
                LEFT JOIN "afgprd__" AS E ON (B.prd__ref = E.prd__ref )
                LEFT JOIN "afgart__" AS P ON (A.afg__ref = P.afg__ref )
                LEFT JOIN "v1eti___" AS F ON (P.off1_ref = F.off__ref )
                LEFT JOIN "v1off___" AS G ON (P.off1_ref = G.off__ref )
                LEFT JOIN "v1bon___" AS H ON (G.bon__ref = H.bon__ref )
                LEFT JOIN "prodkl__" AS I ON (I.prkl_ref = P.prkl_ref )
                LEFT JOIN "epersn__" AS M ON (M.prs__ref = F.prs__ref )
                LEFT JOIN "artiky__" AS Z ON (F.art__ref = Z.art__ref )
                LEFT JOIN "klrcod__" AS KFR ON (F.klcod_fr = KFR.klcodref )
                LEFT JOIN "klrcod__" AS KLM ON (F.klcod_lm = KLM.klcodref )
                LEFT JOIN "klrcod__" AS KRG ON (F.klcod_rg = KRG.klcodref )
                LEFT JOIN "stnspr__" AS DIE ON (DIE.stns_ref = F.stns_ref )
                LEFT JOIN "arthlp__" AS DIEB ON (DIE.stns_ref = DIEB.arth_ref )
                
                WHERE 
                    (A.afg__ref = \''.$ProdID.'\' OR A.off__ref=\''.$ProdID.'\') AND B.type_ord = 3
            ';

            //B.type_ord - 1-production, 3 prepress job
            //B.annul___ - N-aktyvus, Y-anuliuotas
            $mssql = DBMSSqlCERM::getInstance();
            $GetData = $mssql->querySqlOneRow($sql, 1);

            //!!!!!! DEBUG
            //$this->var_dump($GetData, "$GetData <hr>$sql<hr> ");//-----------------DEBUG


            $sqlLak='
                SELECT A.klpmsref, A.ink__ref, A.klr___nr, A.inkvrbpc, A.bedruk__, B.klpmsrpn, B.omschr__
                FROM v1kkl___ AS A
                LEFT JOIN klrpms__ AS B ON (A.klpmsref = B.klpmsref )
                WHERE (A.ink__ref LIKE \'UVV%\' OR A.ink__ref LIKE \'SBV%\') AND A.kpn__ref IN (SELECT kpn__ref FROM v1eti___ WHERE off__ref = \''.$GetData['PRD_CalculationID'].'\') AND A.klpmsref <> \'\'
                ORDER BY A.klr___nr 
                ';
            $GetLakArray= array();
            $GetLakArray = $mssql->querySql($sqlLak, 1);

            $GetData['LAKAI']=$GetLakArray;

            //!!!!!! DEBUG
            //$this->var_dump($GetLakArray, "$GetLakArray <hr>$sqlLak<hr> ");//-----------------DEBUG



            //Papildomi laikai
            if($GetData['Anuliuotas']=='Y'){
                $GetData['Anuliuotas_txt']="Anuliuotas";
            }else{
                $GetData['Anuliuotas_txt']="Aktyvus";
            }


            //APVALINAM
            $GetData['CA_Zingsnis']=round($GetData['CA_EtiketesIlgis'] + $GetData['CA_TarpasPerPasikartojima'],4);
            $GetData['CA_VelenoIlgis']=round($GetData['CA_VelenoIlgis'],2);
            $GetData['CA_EtiketesPlotis']=round($GetData['CA_EtiketesPlotis'],2);
            $GetData['CA_EtiketesIlgis']=round($GetData['CA_EtiketesIlgis'],2);
            $GetData['CA_Uzapvalinimas']=round($GetData['CA_Uzapvalinimas'],2);
            $GetData['PRD_KritPlotis']=round($GetData['PRD_KritPlotis'],2);
            $GetData['PRD_KritIlgis']=round($GetData['PRD_KritIlgis'],2);
            $GetData['CA_ZingsnisHorizontalus']=round($GetData['CA_ZingsnisHorizontalus'],2);
            $GetData['CA_TarpasTarpEtikeciu']=round($GetData['CA_TarpasTarpEtikeciu'],2);


            $GetData['CA_EiliuSk']=round($GetData['CA_EiliuSk'],2);
            $GetData['CA_PasikartojimuSk']=round($GetData['CA_PasikartojimuSk'],2);



            //Gamybos planavimo busenos
            switch ($GetData['GamybosPlanID']) {
                case '1000':
                    $GetData['GamybosPlanID_txt']="Nežinoma";
                    break;
                case '2000':
                    $GetData['GamybosPlanID_txt']="Galima planuoti";
                    break;
                case '3000':
                    $GetData['GamybosPlanID_txt']="Planuojamas (autom.)";
                    break;
                case '4000':
                    $GetData['GamybosPlanID_txt']="Planas patvirtintas";
                    break;
                
                default:
                    $GetData['GamybosPlanID_txt']="nenurodyta";
                    break;
            }//end switch



            //Gamybos planavimo busenos
            switch ($GetData['DazuParuosID']) {
                case '0100':
                    $GetData['DazuParuosID_txt']="Nežinoma";
                    break;
                case '0200':
                    $GetData['DazuParuosID_txt']="Trūksta formulės";
                    break;
                case '0300':
                    $GetData['DazuParuosID_txt']="Formulė žinoma";
                    break;
                case '0400':
                    $GetData['DazuParuosID_txt']="Formulė paruošta";
                    break;
                
                default:
                    $GetData['DazuParuosID_txt']="nenurodyta";
                    break;
            }//end switch



            //JDF statusas
            switch ($GetData['JDFstatusID']) {
                case '0500':
                    $GetData['JDFstatusID_txt']="NEišsiųsta";
                    break;
                case '1000':
                    $GetData['JDFstatusID_txt']="Kiekis pakeistas";
                    break;
                case '2000':
                    $GetData['JDFstatusID_txt']="Darbas pakeistas";
                    break;
                case '3000':
                    $GetData['JDFstatusID_txt']="JDF išsiųstas";
                    break;
                
                default:
                    $GetData['JDFstatusID_txt']="nenurodyta";
                    break;
            }//end switch



            //FPK statusas
            switch ($GetData['FPKstatusID']) {
                case '0500':
                    $GetData['FPKstatusID_txt']="Nežinoma";
                    break;
                case '0600':
                    $GetData['FPKstatusID_txt']="Klišės išskaidytos";
                    break;
                case '0700':
                    $GetData['FPKstatusID_txt']="Vokai paruošti";
                    break;
                case '1000':
                    $GetData['FPKstatusID_txt']="Klišės yra";
                    break;
                case '2000':
                    $GetData['FPKstatusID_txt']="Gaminti klišes";
                    break;
                case '3000':
                    $GetData['FPKstatusID_txt']="Perdaryti klišes";
                    break;
                case '5000':
                    $GetData['tstval04_txt']="Klišės pagamintos";
                    break;
                
                default:
                    $GetData['tstval04_txt']="nenurodyta";
                    break;
            }//end switch


            //PRD_FotoZyme
            switch ($GetData['PRD_param3']) {
                case '0000':
                    $GetData['PRD_FotoZyme']="X";
                    break;
                case '1000':
                    $GetData['PRD_FotoZyme']="A";
                    break;
                case '1100':
                    $GetData['PRD_FotoZyme']="B";
                    break;
                case '1200':
                    $GetData['PRD_FotoZyme']="C";
                    break;
                case '1300':
                    $GetData['PRD_FotoZyme']="D";
                    break;
                case '1400':
                    $GetData['PRD_FotoZyme']="E";
                    break;
                case '1500':
                    $GetData['PRD_FotoZyme']="F";
                    break;
                case '1600':
                    $GetData['PRD_FotoZyme']="G";
                    break;
                case '1700':
                    $GetData['PRD_FotoZyme']="H";
                    break;
                
                default:
                    $GetData['PRD_FotoZyme']="X";
                    break;
            }//end switch


            //DIZ planerio statusas
            switch ($GetData['DIZplanerID']) {
                case '0500':
                    $GetData['DIZplanerID_txt']="Nesuplanuota";
                    break;
                case '1000':
                    $GetData['DIZplanerID_txt']="Suplanuota";
                    break;
                
                default:
                    $GetData['DIZplanerID_txt']="nenurodyta";
                    break;
            }//end switch


            //Etiketes forma pagal Calculation
            switch ($GetData['CA_LabelFormID']) {
                case '1':
                    $GetData['CA_LabelFormName']="Stačiakampis";
                    break;
                case '2':
                    $GetData['CA_LabelFormName']="Apvalus";
                    break;
                case '3':
                    $GetData['CA_LabelFormName']="Forminis";
                    break;
                case '2':
                    $GetData['CA_LabelFormName']="Ovalus";
                    break;
                
                default:
                    $GetData['CA_LabelFormName']="nenurodyta";
                    break;
            }//end switch

            
            
            //Peilio busena
            switch ($GetData['DIE_BusenaID']) {
                case '1000':
                    $GetData['DIE_BusenaPav']="Užsakyti";
                    break;
                case '1500':
                    $GetData['DIE_BusenaPav']="Nubraižytas";
                    break;
                case '2000':
                    $GetData['DIE_BusenaPav']="Užsakytas";
                    break;
                case '3000':
                    $GetData['DIE_BusenaPav']="Sandėlyje";
                    break;
                case '4000':
                    $GetData['DIE_BusenaPav']="Perdaryti";
                    break;
                case '5000':
                    $GetData['DIE_BusenaPav']="Sugadintas";
                    break;
                case '5001':
                    $GetData['DIE_BusenaPav']="Not aplicable";
                    break;
                
                default:
                    $GetData['DIE_BusenaPav']="Naujas";
                    break;
            }//end switch


            //FINISHINGAS
            if($this->is_ID($GetData['PRD_CalculationID'])){
                //$mssql = DBMSSqlCERM::getInstance();

                $sqlFin = "
                    SELECT  
                        G.art__ref AS ZalID, 
                        Z.art_oms1 AS ZalName, 
                        G.drg__ref AS SubstrateID, 
                        S.drg__oms AS SubstrateName, 
                        G.grd__oms, 
                        G.etap_ref, 
                        G.etap_typ, 
                        G.kpn__ref, 
                        H.omschr__ AS FinishName, 
                        H.etas_ref, 
                        H.kombrf_1 AS FinishShort
                    FROM v1etaf__ AS G
                    LEFT JOIN stdeap__ AS H ON (G.etap_ref = H.etap_ref )
                    LEFT JOIN drgers__ AS S ON (G.drg__ref = S.drg__ref )
                    LEFT JOIN artiky__ AS Z ON (G.art__ref = Z.art__ref )
                    WHERE off__ref = '".$GetData['PRD_CalculationID']."'
                ";

                $FinishData = $mssql->querySql($sqlFin, 1);

            //!!!!!! DEBUG
            //$this->var_dump($FinishData, "FinishData <hr>$sqlFin<hr> ");//-----------------DEBUG

                //PAPILDOMOS ZALIAVOS
                if(is_array($FinishData) AND count($FinishData)>0){
                    $i=0;
                    foreach ($FinishData as $key => $finDuom) {
                        if($finDuom['SubstrateName']){
                            $GetData['PapildZaliav'][$i]['ZalID']=$finDuom['ZalID'];
                            $GetData['PapildZaliav'][$i]['ZalName']=$finDuom['ZalName'];
                            $GetData['PapildZaliav'][$i]['SubstrateID']=$finDuom['SubstrateID'];
                            $GetData['PapildZaliav'][$i]['SubstrateName']=$finDuom['SubstrateName'];
                            $i++;
                        }
                    }//end foreach
                }//end if

            }else{
                $FinishData = array();
            }
        }else{//end if
            $GetData['afg__ref']="Nežinomas darbas";
        }

        $GetData['FINISHING'] = $FinishData;
        return $GetData;

    }//end function
*/



/*
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
                // ------ $ret = $mysql->updateData($insert_arr, $options);

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
*/




    //------------------------------------
}//end class1
?>