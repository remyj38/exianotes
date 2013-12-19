<?php

session_start();
require_once 'config.php';
require_once 'fonctions.php';
init_classes();
$argumentsUrl = recupererArgumentsUrl(); // Récupération des arguments dans un tableau

//essais de connexion à la base de donnée.
test_sql();
$auth = new auth(); //authentification...
if ($auth->getUser() == "Invite" && $argumentsUrl["page"] != "login") {
    //header('Location: page/login/');
}

include 'header.php';
?>
