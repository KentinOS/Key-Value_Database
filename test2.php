<?php
include "KVDB.php";



function _hash($string){
    //times33算法
    $string = substr(md5($string),0,8);
    $hash = 0;
    for($i=0;$i<8;$i++){
        $hash += $hash*33+ord($string{$i});
    }
    return $hash & 0x7fffffff;
}

var_dump(_hash('erresfggfdsgfx'));