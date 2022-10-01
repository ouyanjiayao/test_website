<?php
echo "输入文件路径:";
$paths = getopt('p:');
$path = $paths['p'];
// $path = "D:/project/abc/app/backend/controllers/";
$a = '';
// $a = 'after/';
$files = glob($path.$a."*.php");
foreach($files as &$file) {
    $contents = file_get_contents($file);
    $ct = getCharpos($contents,'n action');
    $pattern = [];
    $str = [];
    $matches = [];
    $filen = [];
    $filename = [];
    $auth_name = [];
    $lstr = [];
    $lstrs = [];
    foreach ($ct as $key => $value) {
    	$str[$key] = substr($contents,$value,100);
	    $pattern[$key] = "/^([\action]*)(.*?)[\(]/is";
		preg_match_all($pattern[$key], $str[$key], $matches[$key]);
	    // $st = strpos($contents, 'n action');
	  	$filen[$key] = str_replace($path,'',$file);
	  	$filen[$key] = str_replace('.php','',$filen[$key]);
	    $filename[$key] = strtolower(str_replace('Controller','',$filen[$key]));
	    $lstr[$key] = lcfirst(str_replace('action', '', trim($matches[$key][2][0])));
	    $lstrs[$key] =  strtolower(preg_replace('/(?<!\ )[A-Z]/', "-$0", $lstr[$key]));
	    $auth_name[$key] = $filename[$key].'/'.$lstrs[$key];
	    echo $auth_name[$key],PHP_EOL;
    }
  
   	
}


function getCharpos($str, $char){

       $j = 0;

       $arr = array();

       $count = substr_count($str, $char);

       for($i = 0; $i < $count; $i++){

             $j = strpos($str, $char, $j);

             $arr[] = $j;

             $j = $j+1;

       }

       return $arr;

}
