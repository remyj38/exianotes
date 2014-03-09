<?php
require_once './administration/classes/admin_groups.class.php';

$title = 'Gestion des groupes';
$admin_groups = new admin_groups(); // Récupération de la classe contenant les fonctions de gestion des groups
if (isset($argumentsUrl['action'])) { // Si une action est sélectionnée
    switch ($argumentsUrl['action']) { // suivant l'action demandée
        case 'add': // Ajout d'un groupe
            $admin_groups->show_add_group_form(); // Affichage du formulaire
            break;
        case 'edit': // Edition d'un groupe
            if (isset($argumentsUrl['id'])) { // Si un groupe est sélectionné, on affiche le formulaire d'édition
                $admin_groups->show_edit_group_form($argumentsUrl['id'], $admin_groups->getGroupName($argumentsUrl['id']));
            } else { // Sinon, on averti
                $admin_groups->no_group_selected();
            }
            break;
        case 'delete': // Suppression d'un groupe
            if (isset($argumentsUrl['id'])) { // Si un groupe est sélectionné, on le supprime
                if (($error = $admin_groups->delGroup($argumentsUrl['id'])) === TRUE) { // Si la suppression s'est correctement effectuée
                    $page_content.='<span class="box success">Le groupe a été correctement supprimé !</span>';
                } else if ($error === false) {
                    $page_content.='<span class="box error">Echec lors de la suppression du groupe</span>';
                }
            } else { // Sinon, on averti
                $admin_groups->no_group_selected();
            }
            $page_content .= '<a href="' . ROOT_DIR . 'admin/groups/" title="Retour à la gestion des groupes" id="submit_return">Retour</a>';

            break;
        case 'info': // Informations sur un groupe
            if (isset($argumentsUrl['id'])) { // Si un groupe est sélectionné, on affiche ses infos
                $admin_groups->show_group_infos($argumentsUrl['id']);
            } else {
                $admin_groups->no_group_selected();
            }
            break;
        default:
            $admin_groups->show_groups_list();
            break;
    }
} else if (isset($argumentsUrl['submit'])) { // Sinon, si un envoi est effectué
    if (isset($_POST)) { // Si l'utilisateur a envoyé des données
        switch ($argumentsUrl['submit']) { // Suivant le type d'envoi
            case 'addgroup': // Ajout d'un groupe
                if ($admin_groups->addGroup($_POST['name'])) { // Si l'ajout a été effectué avec succès
                    $page_content .= '<span class="box success">Le groupe a été correctement ajouté</span>';
                } else {
                    $admin_groups->show_add_group_form($_POST['name']); // Sinon, on affiche le forumulaire avec les infos précedement entrées
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/groups/" title="Retour à la gestion des groupes" id="submit_return">Retour</a>';
                break;
            case 'editgroup': // Edition d'un groupe
                if ($admin_groups->editGroup($_POST['id'], $_POST['name'])) { // Si l'édition a été effectuée avec succès
                    $page_content .= '<span class="box success">Le groupe a été correctement modifié !</span>';
                } else {
                    $admin_groups->show_edit_group_form($_POST['id'], $_POST['name']); // Sinon, on affiche le formulaire d'édition avec les informations
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/groups/" title="Retour à la gestion des groupes" id="submit_return">Retour</a>';
                break;
            default: // Si un envoi inconnu est effectué, on affiche la liste des groupes
                $admin_groups->show_groups_list();
                break;
        }
    } else { // Sinon, on affiche la liste des groupes
        $admin_groups->show_groups_list();
    }
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des groupes
    $admin_groups->show_groups_list();
}

