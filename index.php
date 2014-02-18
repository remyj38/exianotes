<?php

// Initialisation
session_start();
require_once 'config.php';
require_once 'fonctions.php';
init_classes();
$site = getSiteInfos();
$page_content = ""; // Initialisation du contenu de la page
$argumentsUrl = getArgumentsUrl(); // Récupération des arguments dans un tableau
//essais de connexion à la base de donnée.
test_sql();

$auth = new auth(); //authentification...
//Initialisation du thème de l'utilisateur

init_theme();

if ($auth->getUser() == "Invite") { //Si l'utilisateur n'est pas authentifié
    if (isset($argumentsUrl["page"])) { //  et que la page actuelle n'est pas celle de login, on le redirige dessus
        if ($argumentsUrl["page"] != "login") {
            header('Location: ' . ROOT_DIR . 'page/login/');
        } else {
            include 'pages/' . $argumentsUrl['page'] . '.php'; 
        }
    } else {
        header('Location: ' . ROOT_DIR . 'page/login/');
    }
} else {
    if (isset($argumentsUrl['page'])) { // Si l'argument page est inclu dans l'url
        if ($argumentsUrl["page"] == "admin") { // Si la page demandée est l'administration, on inclus le dossier administration
            include "administration/index.php";
        } else {
            if (file_exists('pages/' . $argumentsUrl['page'] . '.php')) { // et si la page demandée existe, on l'inclu
                include 'pages/' . $argumentsUrl['page'] . '.php';
            } else { // Sinon, on affiche l'erreur 404
                errors(404);
            }
        }
    } else { // Sinon, on affiche la page par défaut
        if ($argumentsUrl == 404) {
            errors(404);
        } else {
            include 'pages/resume.php';
        }
    }
}
// Affichage de la page
include './themes/' . $template['themedir'] . '/header.php';
echo $page_content;
include './themes/' . $template['themedir'] . '/footer.php';
?>
