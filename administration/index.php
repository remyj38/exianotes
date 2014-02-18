<?php
require_once 'administration/fonctions.php';
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