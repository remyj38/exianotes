<?php

class admin_modules {

    private $modules;

    public function __construct() {
        $this->modules = $this->getModules(); // On récupère la liste des modules
    }

    /* Gestion des modules */

    public function addModule($name, $group) { // Permet d'ajouter un module à la base de donnée
        if (isset($name, $group)) { // Si tous les champs sont remplis, on ajoute le module
            $bdd = get_db_connexion(); //Ouverture de la connexion
            $requete = $bdd->prepare('INSERT INTO modules (name, id_group) VALUES ( :name, :group)'); // Préparation de l'insertion dans la base
            $erreur = $requete->execute(array(
                'name' => $name,
                'group' => $group
            )); // Execution de la requête avec les données collectées
        } else { // Sinon, on stoque l'erreur (les champs ne sont pas tous remplis
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        if ($erreur != true) { // Si une erreur est apparue au cours de l'ajout du module
            errorsSQL($erreur, $requete); // Affichage de l'erreur générée
            return false;
        }
        return true;
    }

    public function editModule($id, $new_name, $new_group) { // Changement des infos du module
        global $page_content;
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE modules SET name = :name, id_group = :group WHERE id_module = :id"); // Préparation de la requête
        $erreur = $requete->execute(array(
            'name' => $new_name,
            'group' => $new_group,
            'id' => $id
        )); // on execute la requete avec les nouvelles données
        if (!$erreur) { // Si une erreur est parvenue durant l'execution de la requête, on l'affiche
            errorsSQL($erreur, $requete);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function delModule($id) { // Suppression d'un module
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        /* Vérification de l'existance du module */
        $module = $this->getModuleInfos($id);
        if (!$module) {
            $this->unknown_module();
            return 'unknown';
        } else {
            /* Suppression ... */
            $requete = $bdd->prepare('DELETE FROM modules WHERE id_module = :id'); // Préparation de la requête
            $error = $requete->execute(array("id" => $id)); // Execution de la requête avec l'id du module à supprimer
            $requete->closeCursor(); // Fermeture de la requête
            if ($error) { // Retourne true si le module a été supprimé
                return true;
            } else {
                errorsSQL($error, $requete);
                return false; // False si erreur lors de la suppression
            }
        }
    }

    /* Récupération des infos modules */

    public function getModuleInfos($id) { // Permet de récupérer les informations du module
        for ($i = 0; $i < count($this->modules); $i++) {// Récupération des infos sur le module
            if ($this->modules[$i]['id_module'] == $id) {
                return $this->modules[$i];
                break;
            }
        }
        return false;
    }
    
    private function getModules() { // Permet de récuperer tous les modules dans un tableau
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->query('SELECT id_module, modules.name AS name, modules.id_group AS id_group, groups.name AS Gname FROM modules INNER JOIN groups ON modules.id_group=groups.id_group;'); // Préparation de la requete
        while ($temp = $requete->fetch()) { // Pour chaque ligne de résultats, on stocke le module
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) { // Si des données ont été récupérées, on les retourne
            return $datas;
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    }

    private function getControls($id) {
        $i = 0;
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        $requete = $bdd->prepare('SELECT id_control, name, coef FROM controls WHERE module = :id'); // Préparation de la requête
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

    public function show_modules_list() { // Affiche la liste des modules enregistrés
        global $page_content;
        $page_content.= '<div id="modulesList"><table><caption>Liste des modules</caption><tr><th>Nom du module</th><th>Groupe du module</th></tr>'; // Affichage des en-têtes du tableau
        if ($this->modules !== NULL) {
            for ($i = 0; $i < count($this->modules); $i++) { // Pour chaque module, on ajoute sa ligne avec ses infos
                $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/modules/action/info/id/' . $this->modules[$i]['id_module'] . '" title="' . $this->modules[$i]['name'] . '">' . $this->modules[$i]['name'] . '</a></td>
    <td>' . $this->modules[$i]['Gname'] . '</td></tr>';
            }
        } else {
            $page_content .= "<tr><td colspan=2><span class=\"box warning\">Aucun controle n'est encore enregistré</span></td></tr>";
        }
        $page_content.= '</table>'; // On ferme le tableau
        $page_content .= '<a href="' . ROOT_DIR . 'admin/modules/action/add" title="Ajouter un module"><button class="admin">Ajouter un module</button></a></div>';
    }

    public function show_add_module_form($name = NULL, $group = NULL) { // Affichage du formulaire d'ajout d'un module
        global $page_content;
        $groups = getGroups(); // Récupérations des groupes
        /* Affichage du formulaire */
        $page_content .= '<form action="' . ROOT_DIR . 'admin/modules/submit/addmodule" method="post"><table class="addModule">
    <tr><td><label for="name">Nom du module:</label></td><td><input type="text" id="name" name="name" value="' . $name . '" required /></td></tr>
    <tr><td><label for="group">Groupe du module:</label></td><td><select id="group" name="group">';
        for ($i = 0; $i < count($groups); $i++) { // Affichage de la liste des groupes
            $page_content .= '<option value="' . $groups[$i]['id_group'] . '"';
            if ($group !== NULL) {
                if ($group == $groups[$i]['id_group']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .= '>' . $groups[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le module" />
</table></form>'; // Fin du formulaire
    }

    public function show_module_infos($id) { // Affichage de la page d'info d'un module
        global $page_content;
        global $popups;
        $infos = $this->getModuleInfos($id);
        if ($infos) { // Si les infos existent, on les affiche
            $controls = $this->getControls($id);
            $page_content .= '<h1>Liste des controles du module <span id="moduleName">' . $infos['name'] . '</span></h1>
                <span class="buttons"><a href="' . ROOT_DIR . 'admin/modules/action/edit/id/' . $id . '"><button class="admin">Modifier les infos du module</button></a> <button class="admin disabled" onclick="toggle_popup(\'delete_module\');" disabled>Supprimer le module</button> <a href="' . ROOT_DIR . 'admin/modules/" title="Retourner à la gestion des modules"><button class="admin">Retour à la gestion des modules</button></a></span>';
            if ($controls) {
                $page_content .= '<table id="controlsList">';
                for ($i = 0; $i < count($controls); $i++) {
                    $page_content .= '<tr><td><a href="' . ROOT_DIR . 'admin/controls/action/info/id/' . $controls[$i]['id_control'] . '" title="' . $controls[$i]['name'] . '">' . $controls[$i]['name'] . '</a></td></tr>';
                }
                $page_content .= '</table>';
            } else {
                $page_content .= '<span class="box warning">Aucun controle n\'est encore enregistré pour ce module !</span>';
            }

            $popups .= '<div class="popup" id="popup_delete_module" style="display:none;"><center>Etes-vous sur de vouloir supprimer ce module ?<br />(Tous les controles en rapport avec le module seront supprimés ! )<br /><button class="admin" onclick="toggle_popup(\'delete_module\');">Annuler la suppression</button><a href="' . ROOT_DIR . 'admin/modules/action/delete/id/' . $id . '" class="bouton"><button class="admin">Confirmer</button></a></center></div>';
        } else { // Sinon, on affiche le message d'erreur
            $this->unknown_module();
        }
    }

    public function show_edit_module_form($id, $infos) { // Affichage du formulaire d'édition d'un module
        global $page_content;
        if (!$infos) {
            $this->unknown_module();
        } else {
            $groups = getGroups(); // Récupérations des groupes
            /* Affichage du formulaire */
            $page_content .= '<form action="' . ROOT_DIR . 'admin/modules/submit/editmodule" method="post" id="edit_modules"><input type="hidden" name="id" value="' . $id . '" />
    <table class="editmodule">
    <tr><td><label for="name">Nom du module:</label></td><td><input type="text" id="name" name="name" value="' . $infos['name'] . '" required /></td></tr>
    <tr><td><label for="group">Groupe du module:</label></td><td><select id="group" name="group">';
            for ($i = 0; $i < count($groups); $i++) { // Affichage de la liste des groupes
                $page_content .= '<option value="' . $groups[$i]['id_group'] . '"';
                if ($infos['id_group'] !== NULL) {
                    if ($infos['id_group'] == $groups[$i]['id_group']) {
                        $page_content .= ' selected ';
                    }
                }
                $page_content .= '>' . $groups[$i]['name'] . '</option>';
            }
            $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le module" /></td></tr>
</table></form>'; // Fin du formulaire
        }
    }

    public function no_module_selected() { // Si aucun module n'est sélectonné, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Aucun module selectionné !</span>";
        $this->show_modules_list(); // On affiche la liste des modules
    }

    public function unknown_module() { // Si le module est inexistant, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Le module selectionné n'exite pas !</span>";
        $this->show_modules_list(); // On affiche la liste des modules
    }

}
