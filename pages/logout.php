<?php
$title = "Déconnexion";
if ($auth->logout()) {
    echo "déconnecté";
} else {
    echo "echec de la déconnection";
}

?>