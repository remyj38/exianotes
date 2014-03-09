<?php
require_once './administration/classes/admin_users.class.php';
$title = 'Gestion des utilisateurs';
$admin_users = new admin_users(); // Récupération de la classe contenant les fonctions de gestion des utilisateurs
if (isset($argumentsUrl['action'])) { // Si une action est sélectionnée
    switch ($argumentsUrl['action']) { // suivant l'action demandée
        case 'add': // Ajout d'un utilisateur
            $admin_users->show_add_user_form(); // Affichage du formulaire
            break;
        case 'edit': // Edition d'un utilisateur
            if (isset($argumentsUrl['id'])) { // Si un utilisateur est sélectionné, on affiche le formulaire d'édition
                $admin_users->show_edit_user_form($argumentsUrl['id'], $admin_users->getUserInfo($argumentsUrl['id']));
            } else { // Sinon, on averti
                $admin_users->no_user_selected();
            }
            break;
        case 'delete': // Suppression d'un utilisateur
            if (isset($argumentsUrl['id'])) { // Si un utilisateur est sélectionné, on le supprime
                if (($erreur = $admin_users->delUser($argumentsUrl['id'])) === true) { // Si la suppression s'est correctement effectuée
                    $page_content.='<span class="box success">L\'utilisateur a été correctement supprimé !</span>';
                } else if ($erreur === false) {
                    $page_content.='<span class="box error">Echec lors de la suppression de l\'utilisateur</span>';
                }
            } else { // Sinon, on averti
                $admin_users->no_user_selected();
            }
            $page_content .= '<a href="' . ROOT_DIR . 'admin/users/" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';

            break;
        case 'info': // Informations sur un utilisateur
            if (isset($argumentsUrl['id'])) { // Si un utilisateur est sélectionné, on affiche ses infos
                $admin_users->show_user_infos($argumentsUrl['id']);
                $page_content .= '<br /><a href="' . ROOT_DIR . 'admin/users/" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';
            } else {
                $admin_users->no_user_selected();
            }
            break;
        default:
            $admin_users->show_users_list();
            break;
    }
} else if (isset($argumentsUrl['submit'])) { // Sinon, si un envoi est effectué
    if (isset($_POST)) { // Si l'utilisateur a envoyé des données
        switch ($argumentsUrl['submit']) { // Suivant le type d'envoi
            case 'adduser': // Ajout d'un utilisateur
                if ($admin_users->addUser($_POST['user'], $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom'])) { // Si l'ajout a été effectué avec succès
                    $page_content .= '<span class="box success">L\'utilisateur a été correctement ajouté</span>';
                } else {
                    $admin_users->show_add_user_form($_POST['user'], $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom']); // Sinon, on affiche le forumulaire avec les infos précedement entrées
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/users/" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';
                break;
            case 'edituser': // Edition d'un utilisateur
                if (isset($_POST['reinit_passwd'])) { // Si on a demandé une réinitialisation de mot de passe
                    $reinit_passwd = TRUE;
                } else {
                    $reinit_passwd = FALSE;
                }
                if ($admin_users->editUser($_POST['id'], $_POST['user'], $reinit_passwd, $_POST['email'], $_POST['rank'], $_POST['group'], $_POST['nom'], $_POST['prenom'])) { // Si l'édition a été effectuée avec succès
                    $page_content .= '<span class="box success">L\'utilisateur a été correctement modifié !</span>';
                } else {
                    $admin_users->show_edit_user_form($_POST['id'], array('user' => $_POST['user'], 'reinit_passwd' => $reinit_passwd, 'email' => $_POST['email'], 'rank' => $_POST['rank'], 'Ugroup' => $_POST['group'], 'Uname' => $_POST['nom'], 'firstName' => $_POST['prenom'])); // Sinon, on affiche le formulaire d'édition avec les informations
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/users" title="Retour à la gestion des utilisateurs" id="submit_return">Retour</a>';
                break;
            default: // Si un envoi inconnu est effectué, on affiche la liste des utilisateurs
                $admin_users->show_users_list();
                break;
        }
    } else { // Sinon, on affiche la liste des utilisateurs
        $admin_users->show_users_list();
    }
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des users
    $admin_users->show_users_list();
}

