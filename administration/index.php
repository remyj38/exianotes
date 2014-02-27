<?php
require_once 'administration/fonctions.php';
require_once 'mail.class.php';
$adminMails = new mail();
$title = "Adminsitration";
$adminCssOn = true;
if (!$auth) {
    error(403);
} else {
    if (!$auth->isAdmin()) {
        errors(403);
    } else {
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
}
?>