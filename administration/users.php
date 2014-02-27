<?php
$title = 'Gestion des utilisateurs';
$admin_users = new admin_users();
if (isset($argumentsUrl['action'])) {
    switch ($argumentsUrl['action']) { // suivant l'action demandée
        case 'add':
            $admin_users->afficher_formulaire_add_user();
            break;
        case 'edit':
            if (isset($argumentsUrl['id'])) {
            $admin_users->afficher_formulaire_edit_user($argumentsUrl['id'], $admin_users->getUserInfo($argumentsUrl['id']));
            } else {
                $page_content .="<br /><span id=\"no_id\">Aucun utilisateur selectionné !</span>";
            }
            break;
        case 'delete':
            echo 'delete';
            break;
        case 'info':
            if (isset($argumentsUrl['id'])) {
            $admin_users->afficher_infos_user($argumentsUrl['id']);
            } else {
                $page_content .="<br /><span id=\"no_id\">Aucun utilisateur selectionné !</span>";
            }
            break;
        default:
            
            break;
    }
} else if (isset($argumentsUrl['submit'])) {
    if (isset($_POST)) {
        switch ($argumentsUrl['submit']) {
            case 'adduser':
                if ($admin_users->addUser($_POST['user'], $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom'])) {
                    $page_content .= '<br />L\'utilisateur a été correctement ajouté';
                } else {
                    $admin_users->afficher_formulaire_add_user($_POST['user'], $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom']);
                }
                $page_content .= '<a href="../" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';
                break;
            case 'edituser':
                if (isset($_POST['reinit_passwd'])) {
                    $reinit_passwd = TRUE;
                } else {
                    $reinit_passwd = FALSE;
                }
                if ($admin_users->editUser($_POST['id'], $_POST['user'], $reinit_passwd, $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom'])) {
                    $page_content .= '<br />L\'utilisateur a été correctement modifié !';
                } else {
                    $admin_users->afficher_formulaire_add_user($_POST['user'], $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom']);
                }
                $page_content .= '<a href="../" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';
                break;
            case 'deluser':
                break;
            default:
                $admin_users->afficher_liste_users();
                break;
        }
    } else {
        $admin_users->afficher_liste_users();
    }
        
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des users

    $admin_users->afficher_liste_users();
}
?>

