<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

/* ******************************************
** 2018 03 29
** Saugomos darbuotoju teises NZ zurnale
** MySQL.DarbuotojaiInfo.NZTeises -> 'ADMIN'
********************************************* */
class teises_mod extends module {

    public $uid = 0; // user ID


    function __construct() {
        //surasom adminu uid
        parent::__construct();

        $mysql = DBMySql::getInstance();
        $sqlg = "
                    select uid, NZTeises, DarbuotojasInfoID, CERM_ID,DarbuotojasID,Vardas,Pavarde
                    FROM DarbuotojaiInfoPT5021
                    WHERE NZTeises = 'ADMIN'
            ";

        $NZAdminai = $mysql->querySql($sqlg,1);

        $this->admins = [];
        if(is_array($NZAdminai) AND count($NZAdminai)>0){
            foreach ($NZAdminai as $key => $UserData) {
                $this->admins[]=$UserData['uid'];
            }//end foreach
        }//end if
        unset ($NZAdminai);

        //viska gali daryti sie vartotojai
        /* Pakeista i nuskaityma is DB, nes taip sunku administruoti
        $this->admins[]='882';//jaunareklama
        $this->admins[]='220';//neringa orintiene
        $this->admins[]='432';//Martynas Ožekauskas 
        $this->admins[]='19';//Vita Gudėnaitė 
        $this->admins[]='485';//Modestas Radevičius 
        $this->admins[]='741';//Justina Balčiūtė 
        $this->admins[]='501';//Arnoldas Ramonas
        $this->admins[]='673';//Indre Bilvinaite
        $this->admins[]='849';//Jurgita Ražauskienė 
        $this->admins[]='686';//Jurgita Miceikaitė 
        $this->admins[]='558';//Aurelija Deinarytė
        $this->admins[]='648';//Andžela Kratauskienė
        $this->admins[]='653';//Sandra Balsienė 
        $this->admins[]='694';//Rasa Šiupienienė
        $this->admins[]='890';//Valerija Alsytė 
        $this->admins[]='421';//Neringa Katkauskienė
        $this->admins[]='921';//Asta Gudėnienė
        $this->admins[]='745';//Inga Linkauskiene
        */
        
        //!!!!!! DEBUG
        //$this->var_dump($this->admins, "DebugJobArray duom");//-----------------DEBUG


    }//end function 
    
    public function is_admin($userid){
        if(in_array($userid,$this->admins)) 
            return true;
        else
            return false; 
    }
    //------------------------------------
}//end class1
?>