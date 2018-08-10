<?php
include_once("KVDB.php");
$db=new Database();
$res=$db->open('G:\test');
$res=$db->insert('INVALID_SET','helloworld');
var_dump($res);
$res=$db->delete('INVALID_SET');
var_dump($res);
$res=$db->fetch('INVALID_SET');
var_dump($res);
$res=$db->close();
var_dump($res);
