<?php
require_once './administration/classes/admin_modules.class.php';

$title = 'Gestion des modules';
$admin_modules = new admin_modules(); // Récupération de la classe contenant les fonctions de gestion des modules
if (isset($argumentsUrl['action'])) { // Si une action est sélectionnée
    switch ($argumentsUrl['action']) { // suivant l'action demandée
        case 'add': // Ajout d'un module
            $admin_modules->show_add_module_form(); // Affichage du formulaire
            break;
        case 'edit': // Edition d'un module
            if (isset($argumentsUrl['id'])) { // Si un utilisateur est sélectionné, on affiche le formulaire d'édition
                $admin_modules->show_edit_module_form($argumentsUrl['id'], $admin_modules->getModuleInfos($argumentsUrl['id']));
            } else { // Sinon, on averti
                $admin_modules->no_module_selected();
            }
            break;
        case 'delete': // Suppression d'un module
            if (isset($argumentsUrl['id'])) { // Si un module est sélectionné, on le supprime
                if (($erreur = $admin_modules->delModule($argumentsUrl['id'])) === true) { // Si la suppression s'est correctement effectuée
                    $page_content.='<span class="box success">Le module a été correctement supprimé !</span>';
                } else if ($erreur === false) {
                    $page_content.='<span class="box error">Echec lors de la suppression du module</span>';
                }
            } else { // Sinon, on averti
                $admin_modules->no_module_selected();
            }
            $page_content .= '<a href="' . ROOT_DIR . 'admin/modules/" title="Retour à la gestion des modules" id="submit_return">Retour</a>';

            break;
        case 'info': // Informations sur un module
            if (isset($argumentsUrl['id'])) { // Si un module est sélectionné, on affiche ses infos
                $admin_modules->show_module_infos($argumentsUrl['id']);
            } else {
                $admin_modules->no_module_selected();
            }
            break;
        default:
            $admin_modules->show_modules_list();
            break;
    }
} else if (isset($argumentsUrl['submit'])) { // Sinon, si un envoi est effectué
    if (isset($_POST)) { // Si l'utilisateur a envoyé des données
        switch ($argumentsUrl['submit']) { // Suivant le type d'envoi
            case 'addmodule': // Ajout d'un module
                if ($admin_modules->addModule($_POST['name'], $_POST['group'])) { // Si l'ajout a été effectué avec succès
                    $page_content .= '<span class="box success">Le module a été correctement ajouté</span>';
                } else {
                    $admin_modules->show_add_user_form($_POST['name'], $_POST['group']); // Sinon, on affiche le forumulaire avec les infos précedement entrées
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/modules/" title="Retour à la gestion des modules" id="submit_return">Retour</a>';
                break;
            case 'editmodule': // Edition d'un module
                if ($admin_modules->editModule($_POST['id'], $_POST['name'], $_POST['group'])) { // Si l'édition a été effectuée avec succès
                    $page_content .= '<span class="box success">Le module a été correctement modifié !</span>';
                } else {
                    $admin_modules->show_edit_module_form($_POST['id'], array('name' => $_POST['name'], 'group' => $_POST['group'])); // Sinon, on affiche le formulaire d'édition avec les informations
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/modules/" title="Retour à la gestion des modules" id="submit_return">Retour</a>';
                break;
            default: // Si un envoi inconnu est effectué, on affiche la liste des modules
                $admin_modules->show_modules_list();
                break;
        }
    } else { // Sinon, on affiche la liste des modules
        $admin_modules->show_modules_list();
    }
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des modules
    $admin_modules->show_modules_list();
}

