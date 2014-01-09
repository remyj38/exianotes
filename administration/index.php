<?php
$title = "Adminsitration";
if (!$auth) {
    echo 'Accès interdit';
    exit();
}
if (!$auth->isAdmin()) {
    
}

?>