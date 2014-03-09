<?php
require_once './administration/classes/admin_controls.class.php';

$title = 'Gestion des controles';
$admin_controls = new admin_controls(); // Récupération de la classe contenant les fonctions de gestion des controles
if (isset($argumentsUrl['action'])) { // Si une action est sélectionnée
    switch ($argumentsUrl['action']) { // suivant l'action demandée
        case 'add': // Ajout d'un controle
            $admin_controls->show_add_control_form(); // Affichage du formulaire
            break;
        case 'edit': // Edition d'un controle
            if (isset($argumentsUrl['id'])) { // Si un controle est sélectionné, on affiche le formulaire d'édition
                $admin_controls->show_edit_control_form($argumentsUrl['id'], $admin_controls->getControlInfos($argumentsUrl['id']));
            } else { // Sinon, on averti
                $admin_controls->no_control_selected();
            }
            break;
        case 'delete': // Suppression d'un controle
            if (isset($argumentsUrl['id'])) { // Si un controle est sélectionné, on le supprime
                if (($erreur = $admin_controls->delControl($argumentsUrl['id'])) === true) { // Si la suppression s'est correctement effectuée
                    $page_content.='<span class="box success">Le controle a été correctement supprimé !</span>';
                } else if ($erreur === false) {
                    $page_content.='<span class="box error">Echec lors de la suppression du controle</span>';
                }
            } else { // Sinon, on averti
                $admin_controls->no_control_selected();
            }
            $page_content .= '<a href="' . ROOT_DIR . 'admin/controls/" title="Retour à la gestion des controles" id="submit_return">Retour</a>';

            break;
        case 'info': // Informations sur un controle
            if (isset($argumentsUrl['id'])) { // Si un controle est sélectionné, on affiche ses infos
                $admin_controls->show_control_infos($argumentsUrl['id']);
            } else {
                $admin_controls->no_control_selected();
            }
            break;
        case 'notes': // Modification des notes du controle
            if (isset($argumentsUrl['id'])) { // Si un controle est sélectionné, on affiche le formulaire d'édition des notes
                $admin_controls->show_edit_notes_form($argumentsUrl['id'], $admin_controls->getNotes($argumentsUrl['id']));
            } else {
                $admin_controls->no_control_selected();
            }
            break;
        default:
            $admin_controls->show_controls_list();
            break;
    }
} else if (isset($argumentsUrl['submit'])) { // Sinon, si un envoi est effectué
    if (isset($_POST)) { // Si l'utilisateur a envoyé des données
        switch ($argumentsUrl['submit']) { // Suivant le type d'envoi
            case 'addcontrol': // Ajout d'un controle
                if ($admin_controls->addControl($_POST['name'], $_POST['module'], $_POST['coef'])) { // Si l'ajout a été effectué avec succès
                    $page_content .= '<span class="box success">Le controle a été correctement ajouté</span>';
                } else {
                    $admin_controls->show_add_user_form($_POST['name'], $_POST['module'], $_POST['coef']); // Sinon, on affiche le forumulaire avec les infos précedement entrées
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/controls/" title="Retour à la gestion des controles" id="submit_return">Retour</a>';
                break;
            case 'editcontrol': // Edition d'un controle
                if ($admin_controls->editControl($_POST['id'], $_POST['name'], $_POST['module'], $_POST['coef'])) { // Si l'édition a été effectuée avec succès
                    $page_content .= '<span class="box success">Le controle a été correctement modifié !</span>';
                } else {
                    $admin_controls->show_edit_control_form($_POST['id'], array('name' => $_POST['name'], 'module' => $_POST['module'], 'coef' => $_POST['coef'])); // Sinon, on affiche le formulaire d'édition avec les informations
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/controls/" title="Retour à la gestion des controles" id="submit_return">Retour</a>';
                break;
            case 'notes':
                if ($admin_controls->saveNotes()) { // Si l'édition a été effectuée avec succès
                    $page_content .= '<span class="box success">Les notes ont été correctement modifiées !</span>';
                } else {
                    $admin_controls->show_edit_control_form($_POST['id'], array('name' => $_POST['name'], 'module' => $_POST['module'], 'coef' => $_POST['coef'])); // Sinon, on affiche le formulaire d'édition avec les informations
                }
                $page_content .= '<a href="' . ROOT_DIR . 'admin/controls/" title="Retour à la gestion des controles" id="submit_return">Retour</a>';
                break;
            default: // Si un envoi inconnu est effectué, on affiche la liste des controles
                $admin_controls->show_controls_list();
                break;
        }
    } else { // Sinon, on affiche la liste des controles
        $admin_controls->show_controls_list();
    }
} else { // Si aucune action n'est demandée, on affiche l'accueil de la gestion des controles
    $admin_controls->show_controls_list();
}

