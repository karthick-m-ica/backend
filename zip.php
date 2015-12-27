<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// Zipping the attachements that was uploaded while Form submitting
include_once '../../../wp-load.php'; 

$where = '';
    
$dateFrom = (isset($_REQUEST['date1']) && $_REQUEST['date1'] != "") ? date("Y-m-d", strtotime($_REQUEST['date1'])) : null;
$dateTo = (isset($_REQUEST['date2']) && $_REQUEST['date2'] != "") ? date("Y-m-d", strtotime($_REQUEST['date2'])) : null;
    
if ($dateFrom != null && $dateTo != null) {
  $where .= " AND DATE(date_submitted) BETWEEN '".$dateFrom."' AND '".$dateTo."' ";
}
else
{
   $where .= $wpdb->prepare( 'AND entry_approved = %d', 1 );
}

$applicants = array();
$cols = $wpdb->get_results("SELECT * FROM wp_visual_form_builder_entries WHERE 1=1 $where and category!='' ORDER BY category ASC");
  
foreach ($cols as $key => $value) {
    $cat_value = $value->category;
}

// filter
$updatedcols = array();
$selectedZip = isset( $_REQUEST['zip1'] ) ? $_REQUEST['zip1'] : 99;
// sorting uploads based on zipocdes
$zip_code = "641016 641015 641014 641011 641012 641013";  
$zip_code_E = "641019 641015 641014";
$zip_code_F = "641018 641013";
$arr = explode(" ",$zip_code);
$arr1 = explode(" ",$zip_code_E);
$arr2 = explode(" ",$zip_code_F);
$cats = array("cates1","cates2","cates3","cates4");
$cate = array("cate1","cate2","cate3","cate4","cate5","cate6","cate7","cate8");

if(empty($selectedZip)) {
  $selectedZip=99;
}
//$value->data)[10]['value'] dropdown value
if($selectedZip==1) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        if(unserialize($value->data)[10]['value'] == "model1" && in_array($code, $arr)) {                    
            $id=$value->entries_id."-".$value->category;
            $applicants[$id] = unserialize($value->data);
        }          
    }       
}

if($selectedZip==2) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        if(unserialize($value->data)[10]['value'] == "cate6" && in_array($code, $arr)) {                     
            $id=$value->entries_id."-".$value->category;
            $applicants[$id] = unserialize($value->data);
        }        
    }        
}

if($selectedZip==3) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        $entry=unserialize($value->data)[10]['value'];
        $ent=explode(" ",$entry);
            if(empty($ent[2])){
                 $cat_g=$ent[0];
            }
            else{
                $cat_g=$ent[2];
            }
        if(in_array($cat_g, $cats)) {
            if(in_array($code, $arr) ) {
                $id=$value->entries_id."-".$value->category;
                $applicants[$id] = unserialize($value->data);
            }
        }
    }        
}

if($selectedZip==4) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        if(unserialize($value->data)[10]['value'] == "cate7" || unserialize($value->data)[10]['value'] == "cate8" ) {
            if(in_array($code, $arr)) {
                $id=$value->entries_id."-".$value->category;
                $applicants[$id] = unserialize($value->data);
            }
        }          
    }      
}

if($selectedZip==5) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        if((unserialize($value->data)[4]['value'] == "cate4") && (in_array($code, $arr1))) {
            $id=$value->entries_id."-".$value->category;
            $applicants[$id] = unserialize($value->data);                        
        }           
    }      
}

if($selectedZip==6) {
    $code=null;
    foreach($cols as $key=>$value) {
        $code=unserialize($value->data)[3]['value'];
        $entry=unserialize($value->data)[10]['value'];
        $ent=explode(" ",$entry);
            if(empty($ent[2])){
                 $cat_g=$ent[0];
            }
            else{
                $cat_g=$ent[2];
            }
        if((in_array($code, $arr2)) && (in_array($cat_g, $cate))) {                        
            $id=$value->entries_id."-".$value->category;
            $applicants[$id] = unserialize($value->data);                        
        }         
    }        
}

if($selectedZip==99) {                
    $cols = $wpdb->get_results("SELECT * FROM wp_visual_form_builder_entries WHERE 1=1 $where and category!='' ORDER BY category ASC");
    foreach($cols as $key1=>$value1) {
            $id=$value1->entries_id."-".$value1->category;                
          $applicants[$id] = unserialize($value1->data);     
    }
}
// Code for Getting links (Path)of Attachements 
$list = array();
// $count = 1;
foreach($applicants as $key=>$val) {
    $cat_id =explode("-", $key);   
      foreach($val as $appkey=>$appval) { 
          // if(($appval['name'])=='Navn') {
          //   $names = $count . '_' . $cat_id[1] . '_' . stripslashes($appval['value']) ;       
          // }
          if(($appval['name']) == ' (PDF)') {
            $attach = stripslashes($appval['value']) ;
            $list[] = $cat_id[0].'-'. $cat_id[1].'/' . $attach; // Attaching ID and Category to attachement file name
          }
      }
  // $count++;   
} 

$paths = array();
foreach ($list as $list) {
    $data = explode('/',$list);

    $pdfpath='../../' . $data[7] . '/' . $data[8] . '/' . $data[9] . '/' . $data[10];
    $name=(string)$data[0] . '_' . $data[10];

    $paths[] = $pdfpath."*".$name; 
  }

$zip = new ZipArchive;
$rand = rand(0,5000);
// Generating random file name for zipcode
if ($zip->open($rand . 'attachments.zip',  ZipArchive::CREATE)) {

  // add files to zip from the path i.e uplaods(Folder) if file exists
  foreach ($paths as  $value ) { 
    $path=explode("*", $value);
      if(file_exists($path[0])) {
        $zip->addFile($path[0],$path[1]);
      }     
  }
  $zip->close();
    
    //if(!empty($list)){
    echo 'Archive created!' . '<br>';
    echo "To download zip file, Please" . " " . "<a href='http://localhost/wp-content/themes/mytheme/" . "$rand" . "attachment.zip'>" . "click here" . "</a>" . '<br>';
    

} else {
    echo 'Failed!';
}

?>
