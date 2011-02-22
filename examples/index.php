<?php

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

require_once dirname(__FILE__).'/simpleExample.php';

$example = new simpleExample();
$example->execute();

die($example->getOutput());