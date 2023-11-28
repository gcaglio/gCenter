<?php

function jsonify($a_output){

$custom_tag_type="gcenter_type";	
# first scan all the lines to split lines as
#      (my.type.of.node) {
# adding a special key_value 'gcenter_type' : 'my.type.of.node'
$b_output=array();
foreach($a_output as $line){

  if (trim(strlen($line))>0){
    if (preg_match('@(\\(.*?\\)) *({)@',$line) ){
     // match the case we need to split the lines adding the custom tag
     $new_line=preg_replace('@(\\(.*?\\)) *({)@', '${2} '.$custom_tag_type.' = "${1}",', $line);
#     echo "PRELINE : $line\r\n";
#     echo "NEWLINE : $new_line\r\n";

     $gcenter_type_line=substr($new_line,strpos($new_line,$custom_tag_type));
     $gcenter_type_line=str_replace("(","",$gcenter_type_line);
     $gcenter_type_line=str_replace(")","",$gcenter_type_line);

     $gcenter_type_line_pre=substr($new_line,0, strpos($new_line,$custom_tag_type));
     echo "INFO : pre_line = $gcenter_type_line_pre\r\n";
     echo "INFO : line = $gcenter_type_line\r\n";

     $b_output[count($b_output)] = $gcenter_type_line_pre;
     $b_output[count($b_output)] = $gcenter_type_line;

    }else{
      $b_output[count($b_output)] = $line;
    }
  }
}



$j_output="";
$line_num=0;
foreach ($b_output as $line) {
  $line_num++;

  if (trim(strlen($line))>0){
     $n_line=preg_replace("@\(.*?\)@", "", $line);

     $n2_line=preg_replace("/=/", ":", $n_line);
     $n3_line=preg_replace("/ +/", " ", $n2_line);
     $n4_line=preg_replace("/^ +/", "", $n3_line);

     $n6_line=$n4_line;
     if (strpos($n4_line,":")>1){
       $idx_2points=strpos($n4_line,":");
       $key=trim(substr($n4_line,0,$idx_2points));
       $value=trim(substr($n4_line,$idx_2points+1));

       if (substr($value,0,1)=="'"){
         $value=substr($value,1);
       }
       if (substr($value,strlen($value)-2)=="',"){
         $value=substr($value,0,strlen($value)-2).",";
       }

       $n5_line="\"".$key."\" : ".$value;
       if ( $value !== "[" && $value !== "{" && $value !== "(" && substr($value,0,1) !== "\""){
         $n5_line="\"".$key."\" : \"".$value."\"";
         
       }

       $n6_line=preg_replace("/,\"/", "\",", $n5_line);
     }else{
       
     }
     $j_output.="$n6_line\n";
  }
}

return  substr($j_output,strpos($j_output,"{"));
}

?>
