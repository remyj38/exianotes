<?php

class admin_controls {

    private $controls;

    public function __construct() {
        $this->controls = $this->getControls(); // On récupère la liste des controles
    }

    /* Gestion des controles */

    public function addControl($name, $module, $coef) { // Permet d'ajouter un controle à la base de donnée
        if (isset($name, $module, $coef)) { // Si tous les champs sont remplis, on ajoute le controle
            $bdd = get_db_connexion(); //Ouverture de la connexion
            $requete = $bdd->prepare('INSERT INTO controls (name, module, coef) VALUES ( :name, :module, :coef)'); // Préparation de l'insertion dans la base
            $erreur = $requete->execute(array(
                'name' => $name,
                'module' => $module,
                'coef' => $coef
            )); // Execution de la requête avec les données collectées
        } else { // Sinon, on stoque l'erreur (les champs ne sont pas tous remplis
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        if ($erreur != true) { // Si une erreur est apparue au cours de l'ajout du controle
            errorsSQL($erreur, $requete); // Affichage de l'erreur générée
            return false;
        }
        return true;
    }

    public function editControl($id, $new_name, $new_module, $new_coef) { // Changement des infos du controle
        global $page_content;
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE controls SET name = :name, module = :module, coef = :coef WHERE id_control = :id"); // Préparation de la requête
        $erreur = $requete->execute(array(
            'name' => $new_name,
            'module' => $new_module,
            'coef' => $new_coef,
            'id' => $id
        )); // on execute la requete avec les nouvelles données
        if (!$erreur) { // Si une erreur est parvenue durant l'execution de la requête, on l'affiche
            errorsSQL($erreur, $requete);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function delControl($id) { // Suppression d'un controle
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        /* Vérification de l'existance du controle */
        $control = $this->getControlInfos($id);
        if (!$control) {
            $this->unknown_control();
            return 'unknown';
        } else {
            /* Suppression ... */
            $requete = $bdd->prepare('DELETE FROM controls WHERE id_control = :id'); // Préparation de la requête
            $error = $requete->execute(array("id" => $id)); // Execution de la requête avec l'id du controle à supprimer
            $requete->closeCursor(); // Fermeture de la requête
            if ($error) { // Retourne true si le controle a été supprimé
                return true;
            } else {
                errorsSQL($error, $requete);
                return false; // False si erreur lors de la suppression
            }
        }
    }

    /* Gestion des notes */

    private function addNote($user, $note, $control, $absent) { // Permet d'ajouter une note à la base de donnée
        if (isset($user, $note, $control, $absent)) { // Si tous les champs sont remplis, on ajoute la note
            $bdd = get_db_connexion(); //Ouverture de la connexion
            $requete = $bdd->prepare('INSERT INTO notes (user, note, control, absent) VALUES ( :user, :note, :control, :absent)'); // Préparation de l'insertion dans la base
            $erreur = $requete->execute(array(
                'user' => $user,
                'note' => $note,
                'control' => $control,
                'absent' => $absent
            )); // Execution de la requête avec les données collectées
        } else { // Sinon, on stoque l'erreur (les champs ne sont pas tous remplis)
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        if ($erreur != true) { // Si une erreur est apparue au cours de l'ajout de la note
            errorsSQL($erreur, $requete); // Affichage de l'erreur générée
            return false;
        }
        return true;
    }

    private function editNote($id, $new_note, $new_absent) { // Changement des infos du controle
        global $page_content;
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE notes SET note = :note, absent = :absent WHERE id_note = :id"); // Préparation de la requête
        $erreur = $requete->execute(array(
            'note' => $new_note,
            'absent' => $new_absent,
            'id' => $id
        )); // on execute la requete avec les nouvelles données
        if (!$erreur) { // Si une erreur est parvenue durant l'execution de la requête, on l'affiche
            errorsSQL($erreur, $requete);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function saveNotes() { // Permet de sauvegarder les notes éditées
        $notes = $_POST['notes']; // On récupère le tableau des notes
        if ($_POST['new'] == 'true') { // Si les notes sont nouvelles on les ajoute
            for ($i = 0; $i < count($notes); $i++) { // Pour chaque note, on l'ajoute
                $absent = (isset($notes[$i]['absent'])) ? true : false;
                if (!$this->addNote($notes[$i]['id_user'], $notes[$i]['note'], $_POST['id'], $absent)) { // Si l'ajout s'est mal déroulé, on arrête tout
                    return false;
                }
            }
        } else {
            for ($i = 0; $i < count($notes); $i++) { // Pour chaque note, on l'édite
                $absent = (isset($notes[$i]['absent'])) ? true : false;
                if (!$this->editNote($notes[$i]['id_note'], $notes[$i]['note'], $absent)) { // Si l'édition s'est mal déroulée, on arrête tout
                    return false;
                }
            }
        }
        return true;
    }

    /* Récupération des infos */

    public function getControlInfos($id) { // Permet de récupérer les informations du controle
        for ($i = 0; $i < count($this->controls); $i++) {// Récupération des infos sur le controle
            if ($this->controls[$i]['id_control'] == $id) {
                return $this->controls[$i];
                break;
            }
        }
        return false;
    }

    private function getControls() { // Permet de récuperer tous les controles dans un tableau
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->query('SELECT id_control, controls.name AS name, coef, controls.module AS id_module, modules.name AS Mname, modules.id_group AS id_group FROM controls INNER JOIN modules ON controls.id_control=modules.id_module;'); // Préparation de la requete
        while ($temp = $requete->fetch()) { // Pour chaque ligne de résultats, on stocke le controle
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) { // Si des données ont été récupérées, on les retourne
            return $datas;
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    }

    public function getNotes($id) {
        $i = 0;
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        $requete = $bdd->prepare('SELECT id_note, note, notes.user AS id_user, users.name AS name, users.firstName AS firstName, absent FROM notes INNER JOIN users ON notes.user = users.id_user WHERE control = :id'); // Préparation de la requête
        $requete->execute(array("id" => $id)); // Execution de la requête
        while ($temp = $requete->fetch()) { // Pour chaque ligne de résultats, on stocke le controle
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) { // Si des données ont été récupérées, on les retourne
            return $datas;
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    }

    /* Affichages */

    public function show_controls_list() { // Affiche la liste des controles enregistrés
        global $page_content;
        $page_content.= '<div id="controlsList"><table><caption>Liste des controles</caption><tr><th>Nom du controle</th><th>Groupe du module</th></tr>'; // Affichage des en-têtes du tableau
        if ($this->controls !== NULL) {
            for ($i = 0; $i < count($this->controls); $i++) { // Pour chaque controle, on ajoute sa ligne avec ses infos
                $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/controls/action/info/id/' . $this->controls[$i]['id_control'] . '" title="' . $this->controls[$i]['name'] . '">' . $this->controls[$i]['name'] . '</a></td>
    <td>' . $this->controls[$i]['Mname'] . '</td></tr>';
            }
        } else {
            $page_content .= "<tr><td colspan=2><span class=\"box warning\">Aucun controle n'est encore enregistré</span></td></tr>";
        }
        $page_content.= '</table>'; // On ferme le tableau
        $page_content .= '<a href="' . ROOT_DIR . 'admin/controls/action/add" title="Ajouter un controle"><button class="admin">Ajouter un controle</button></a></div>';
    }

    public function show_add_control_form($name = NULL, $module = NULL, $coef = NULL) { // Affichage du formulaire d'ajout d'un controle
        global $page_content;
        $modules = getModules(); // Récupérations des groupes
        /* Affichage du formulaire */
        $page_content .= '<form action="' . ROOT_DIR . 'admin/controls/submit/addcontrol" method="post"><table class="addControl">
    <tr><td><label for="name">Nom du controle:</label></td><td><input type="text" id="name" name="name" value="' . $name . '" required /></td></tr>
    <tr><td><label for="coef">Coeficient du controle:</label></td><td><input type="number" id="coef" name="coef" value="' . $coef . '" required /></td></tr>
    <tr><td><label for="module">Module du controle:</label></td><td><select id="module" name="module">';
        for ($i = 0; $i < count($modules); $i++) { // Affichage de la liste des modules
            $page_content .= '<option value="' . $modules[$i]['id_module'] . '"';
            if ($module !== NULL) {
                if ($module == $modules[$i]['id_module']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .= '>' . $modules[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le controle" />
</table></form>'; // Fin du formulaire
    }

    public function show_control_infos($id) { // Affichage de la page d'info d'un controle
        global $page_content;
        global $popups;
        $infos = $this->getControlInfos($id);
        if ($infos) { // Si les infos existent, on les affiche
            $notes = $this->getNotes($id);
            $page_content .= '<h1>Liste des notes du controle <span id="controlName">' . $infos['name'] . '</span></h1>
                <span class="buttons"><a href="' . ROOT_DIR . 'admin/controls/action/edit/id/' . $id . '"><button class="admin">Modifier les infos du controle</button></a> <a href="' . ROOT_DIR . 'admin/controls/action/notes/id/' . $id . '"><button class="admin">Modifier les notes du controle</button></a><br />
                <button class="admin disabled" onclick="toggle_popup(\'delete_control\');" disabled>Supprimer le controle</button> <a href="' . ROOT_DIR . 'admin/controls/" title="Retourner à la gestion des controles"><button class="admin">Retour à la gestion des controles</button></a></span>';
            if ($notes) {
                $page_content .= '<table id="notesList"><tr><th>Etudiant</th><th>Note</th></tr>';
                for ($i = 0; $i < count($notes); $i++) {
                    $page_content .= '<tr><td>' . $notes[$i]['name'] . ' ' . $notes[$i]['firstName'] . '</td><td>';
                    if ($notes[$i]['absent']) {
                        $page_content .= '<span class="absent">ABSENT</span>';
                    } else {
                        $page_content .= $notes[$i]['note'] . '</td></tr>';
                    }
                }
                $page_content .= '</table>';
            } else {
                $page_content .= '<span class="box warning">Aucune note n\'est encore enregistré pour ce controle !</span>';
            }

            $popups .= '<div class="popup" id="popup_delete_control" style="display:none;"><center>Etes-vous sur de vouloir supprimer ce controle ?<br />(Toutes les notes en rapport avec le controle seront supprimées ! )<br /><button class="admin" onclick="toggle_popup(\'delete_control\');">Annuler la suppression</button><a href="' . ROOT_DIR . 'admin/controls/action/delete/id/' . $id . '" class="bouton"><button class="admin">Confirmer</button></a></center></div>';
        } else { // Sinon, on affiche le message d'erreur
            $this->unknown_control();
        }
    }

    public function show_edit_control_form($id, $infos) { // Affichage du formulaire d'édition d'un controle
        global $page_content;
        if (!$infos) {
            $this->unknown_control();
        } else {
            $modules = getModules(); // Récupérations des groupes
            /* Affichage du formulaire */
            $page_content .= '<form action="' . ROOT_DIR . 'admin/controls/submit/editcontrol" method="post" id="edit_control"><input type="hidden" name="id" value="' . $id . '" />
    <table class="editcontrol">
    <tr><td><label for="name">Nom du controle:</label></td><td><input type="text" id="name" name="name" value="' . $infos['name'] . '" required /></td></tr>
    <tr><td><label for="coef">Coeficient du controle:</label></td><td><input type="number" id="coef" name="coef" value="' . $infos['coef'] . '" required /></td></tr>
    <tr><td><label for="module">Module du controle:</label></td><td><select id="module" name="module">';
            for ($i = 0; $i < count($modules); $i++) { // Affichage de la liste des modules
                $page_content .= '<option value="' . $modules[$i]['id_module'] . '"';
                if ($infos['module'] == $modules[$i]['id_module']) {
                    $page_content .= ' selected ';
                }
                $page_content .= '>' . $modules[$i]['name'] . '</option>';
            }
            $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le controle" /></td></tr>
</table></form>'; // Fin du formulaire
        }
    }

    public function show_edit_notes_form($id, $notes = NULL, $new = false) { // Affichage du formulaire d'édition des notes d'un controle
        global $page_content;
        $infos = $this->getControlInfos($id);
        if (!$infos) { // Si le controle n'existe pas, on averti
            $this->unknown_control();
        } else {
            /* Affichage du formulaire */
            $page_content .= '<form action="' . ROOT_DIR . 'admin/controls/submit/notes" method="post" id="edit_notes"><input type="hidden" name="id" value="' . $id . '" />';
            if ($new || !$notes) { // Si c'est une nouvelle entrée de notes
                $page_content .= '<input type="hidden" value="true" name="new" />';
            } else {
                $page_content .= '<input type="hidden" value="false" name="new" />';
            }
            $page_content .= '<table class = "editnotes"><tr><th>Etudiant</th><th>Note</th><th>Statut</th></tr>'; // Entêtes du tableau
            if ($notes) { // Si les notes sont déjà remplies, on les affiche
                for ($i = 0; $i < count($notes); $i++) {
                    $page_content .= '<tr><td><input type = "hidden" value = "' . $notes[$i]['id_note'] . '" name = "notes[' . $i . '][id_note]" />' . $notes[$i]['name'] . ' ' . $notes[$i]['firstName'] . '</td><td><input type = "number" name = "notes[' . $i . '][note]" value = "' . $notes[$i]['note'] . '" /></td><td><input type = "checkbox" name = "notes[' . $i . '][absent]" ';
                    $page_content .= ($notes[$i]['absent']) ? ' checked ' : '';
                    $page_content .= '/></td></tr>';
                }
            } else {
                $members = getMembers($infos['id_group']); // Sinon, on récupère les membres du groupe, pour ensuite afficher les champs
                for ($i = 0; $i < count($members); $i++) {
                    $page_content .= '<tr><td><input type = "hidden" value = "' . $members[$i]['id_user'] . '" name = "notes[' . $i . '][id_user]" />' . $members[$i]['name'] . ' ' . $members[$i]['firstName'] . '</td><td><input type = "number" name = "notes[' . $i . '][note]" /></td><td><label><input type = "checkbox" name = "notes[' . $i . '][absent]" /> Absent</label></td></tr>';
                }
            }
            $page_content .= '<tr><td colspan = "2"><input type = "submit" value = "Enregistrer les notes" /></td></tr>
            </table></form>'; // Fin du formulaire
        }
    }

    public function no_control_selected() { // Si aucun controle n'est sélectonné, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Aucun controle selectionné !</span>";
        $this->show_controls_list(); // On affiche la liste des controles
    }

    public function unknown_control() { // Si le controle est inexistant, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Le controle selectionné n'exite pas !</span>";
        $this->show_controls_list(); // On affiche la liste des controles
    }

}
