<?php

require_once 'administration/fonctions.php'; // On inclu les fonctions d'administration
require_once 'classes/mail.class.php'; // On inclu les fonctions de mail
$adminMails = new mail(); // On crée l'objet des mails
$title = "Adminsitration";
$adminCssOn = true; // On active le CSS et script js de l'administration
if (!$auth) { // Si l'utilisateur n'est pas authentifié, on affiche la page d'accès non autorisé
    error(403);
} else if (!$auth->isAdmin()) { // Sinon, si l'utilisateur n'est pas un administrateur, on lui refuse également l'accès
    errors(403);
} else { // Sinon, on affiche la page demandée si elle existe
    if (isset($argumentsUrl['admin'])) {
        if (file_exists('administration/' . $argumentsUrl['admin'] . '.php')) {
            include $argumentsUrl['admin'] . '.php';
        } else {
            errors(404);
        }
    } else {
        include 'accueil.php';
    }
}
