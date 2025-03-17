<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 //ini_set("display_errors", 1);

$root_path = COMMON::getRootFolder();
require_once ($root_path . "modules/module.php");

class fileUpload_mod extends module {

    public $uid = 0; // user ID

    function __construct() {
        parent::__construct();

    }//end function 
    
    public function upload($nr){
        $type=0;
        if(!empty($_POST['type'])){$type=$_POST['type'];}
        
        if($type==0)$folder="auditas";
        if($type==1 || $type==2)$folder="pretenzija";
        if($type==3)$folder="tiekejas";
        if($type==4)$folder="vizitas";
        
        
        if(!empty($_POST['updatenr'])){
            $nr=$folder.$_POST['updatenr'];
        }
        
        //jei is susijusio asmens dadedam subfolderi
        if(!empty($_POST['updateuser'])){
            $nr2=$_POST['updateuser'];
        }
        

        
        $ds          = "/";  //1
         
        $storeFolder = 'uploads';   //2
        
        if (!empty($_FILES)) {
             
            $tempFile = $_FILES['file']['tmp_name'];          //3   
            
            if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr)) {
                mkdir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr, 0777, true);
            }    
            
            if(!empty($_POST['updateuser'])){
                if (!file_exists(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr .$ds .$nr2)) {
                mkdir(COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr .$ds .$nr2, 0777, true);
                } 
                $nr.="/".$nr2;    
            }      
              
            $targetPath = COMMON::getRootFolder() . $ds. $storeFolder . $ds . $nr .$ds;  //4
             
            $targetFile =  $targetPath. $_FILES['file']['name'];  //5
         
            move_uploaded_file($tempFile,$targetFile); //6
             
        } 
        return "jo";       
    }
    
    
    //------------------------------------
}//end class1
?>