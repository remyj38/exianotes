<?php
$title = "Déconnexion";
if ($auth->logout()) {
    $page_content .= "déconnecté";
} else {
    $page_content .= "echec de la déconnection";
}

?>