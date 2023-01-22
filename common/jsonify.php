<?php

function jsonify($a_output){
#$a_output = explode("\n",$cmd_output);

$j_output="";
foreach ($a_output as $line) {
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
