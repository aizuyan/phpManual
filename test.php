<?php
include_once("./manual/phpManual.php");

$t = new phpManual();
$t->init('zh');
//echo $t->getInfos();

//echo $t->get("pack");

echo $t->get("unpack");
