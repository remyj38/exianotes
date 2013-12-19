<?php
// Initialisation
session_start();
require_once 'config.php';
require_once 'fonctions.php';
init_classes();
$page_content = ""; // Initialisation du contenu de la page
$argumentsUrl = getArgumentsUrl(); // Récupération des arguments dans un tableau

//essais de connexion à la base de donnée.
test_sql();

$auth = new auth(); //authentification...



if ($auth->getUser() == "Invite" && $argumentsUrl["page"] != "login") { // Si l'utilisateur n'est pas authentifié et que la page actuelle n'est pas celle de login, on le redirige dessus
    header('Location: ' . ROOT_DIR . 'page/login/');
} else {
    if ($argumentsUrl["page"] == "admin") { // Si la page demandée est l'administration, on inclus le dossier administration
        include ROOT_DIR . "administration/index.php";
    } else {
        if (isset($argumentsUrl['page'])) {
            if (file_exists(ROOT_DIR . 'pages/' . $argumentsUrl['page'] . '.php')) {
                include ROOT_DIR . 'pages/' . $argumentsUrl['page'] . '.php';
            } else {
                
            }
        }
    }
}
// Affichage de la page
include 'header.php';
echo $page_content;
include 'footer.php';
?>
