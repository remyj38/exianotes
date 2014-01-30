<?php
require_once 'administration/fonctions.php';
$title = "Adminsitration";
if (!$auth) {
    error(403);
} else {
    if (!$auth->isAdmin()) {
        error(403);
    } else {
    }
}
?>