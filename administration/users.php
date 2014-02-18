<?php
$admin_users = new admin_users();
if (isset($argumentsUrl['action'])) {
    switch ($argumentsUrl['action']) { // suivant l'action demandée'
        case 'add':
            $admin_users->afficher_formulaire_add_user();
            break;
        case 'modify':
            echo 'modify';
            break;
        case 'delete':
            echo 'delete';
            break;
        default:
            
            break;
    }
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des users
    $admin_users->afficher_liste_users();
}
?>

