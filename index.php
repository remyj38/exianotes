<?php

session_start();
require_once 'config.php';
require_once 'fonctions.php';
init_classes();

//essais de connexion à la base de donnée.
//lol
test_sql();
$auth = new auth();
if (!$auth) {
}
include 'header.php';
?>