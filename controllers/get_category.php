<?php
    class get_category extends Controller{
             function run($xml){
             global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN, $arr;
             
             $file = $xml->filename;
             /*Categorize::$arr;*/
             /*$Categorize = new Categorize();
             echo $Categorize->arr;*/
             $arrlength = count($arr);
             $response = "<parameters>\n<requestID>" . $REQ_ID ."</requestID>\n<requestType>getCategory</requestType>";
             
             for($i = 0; $i < $arrlength; $i++){
             $lengthcolumn = count($arr[$i]);
                for($j = 0; $j < $lengthcolumn; $j++){
                    if($arr[$i][$j] == $file){
                    echo $arr[$i][$j];
                    $response .= "<resource><id>" . $arr[$i][$j] . "</id>";
		    $response .= "</resource>";
                    }
                
                }
             
             }
             
             
            
             
            return $response; 
             
             
             
             }
    
    
    
    
    
    
    
    
    }
