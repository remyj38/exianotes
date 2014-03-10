<?php

class admin_groups {

    private $groups;

    public function __construct() {
        $this->groups = getGroups(); // On récupère la liste des groupes
    }

    /* Gestion des groupes */

    public function addGroup($name) { // Permet d'ajouter un groupe à la base de donnée
        if (isset($name)) { // Si tous les champs sont remplis, on ajoute le groupe
            $bdd = get_db_connexion(); //Ouverture de la connexion
            $requete = $bdd->prepare('INSERT INTO groups (name) VALUES ( :name);'); // Préparation de l'insertion dans la base du groupe
            $erreur = $requete->execute(array('name' => $name)); // Execution de la requête avec les données collectées
        } else { // Sinon, on stoque l'erreur (les champs ne sont pas tous remplis
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        if ($erreur != true) { // Si une erreur est apparue au cours de l'ajout du groupe
            errorsSQL($erreur, $requete); // Affichage de l'erreur générée
            return false;
        }
        return true;
    }

    public function editGroup($id, $name) { // Changement du nom du groupe
        global $page_content;
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE groups SET name = :name WHERE id_group = :id"); // Préparation de la requête
        $erreur = $requete->execute(array("name" => $name, "id" => $id)); // on execute la requete avec le nouveau nom
        if (!$erreur) { // Si une erreur est parvenue durant l'execution de la requête, on l'affiche
            errorsSQL($erreur, $requete);
            return FALSE;
        } else { // Sinon, on retourne vrai
            return TRUE;
        }
    }

    public function delGroup($id) { // Suppression d'un groupe
        /* Verification de l'existance du groupe */
        $name = $this->getGroupName($id);
        if (!$name) {
            $this->unknown_group();
            return 'unknown';
        } else {
            /* Suppression ... */
            $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
            $requete = $bdd->prepare('DELETE FROM groups WHERE id_group = :id'); // Préparation de la requête
            $error = $requete->execute(array("id" => $id)); // Execution de la requête avec l'id du groupe à supprimer
            $requete->closeCursor(); // Fermeture de la requête
            if ($error) { // Retourne true si le groupe a été supprimé
                return true;
            } else {
                errorsSQL($error, $requete);
                return false; // False si erreur lors de la suppression
            }
        }
    }

    /* Récupération des infos des groupes */

    public function getGroupName($id) {
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        $requete = $bdd->prepare('SELECT name FROM groups WHERE id_group = :id'); // Préparation de la requête
        $requete->execute(array("id" => $id)); // Execution de la requête
        $name = $requete->fetch(); // Récupération du nom
        if (isset($name)) { // Si le nom a été récupérées, on le retourne
            return $name['name'];
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    }

    /* Affichages */

    public function show_groups_list() { // Affiche la liste des groupes enregistrés
        global $page_content;
        $page_content.= '<div id="groupsList"><table ><caption>Liste des groupes</caption><tr><th>Nom du groupe</th></tr>'; // Affichage des en-têtes du tableau
        if ($this->groups !== NULL) {
            for ($i = 0; $i < count($this->groups); $i++) { // Pour chaque groupes, on ajoute sa ligne
                $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/groups/action/info/id/' . $this->groups[$i]['id_group'] . '" title="' . $this->groups[$i]['name'] . '">' . $this->groups[$i]['name'] . '</a></td></tr>';
            }
        } else {
            $page_content .= "<tr><td colspan=2><span class=\"box warning\">Aucun groupe n'est encore enregistré</span></td></tr>";
        }
        $page_content.= '</table>'; // On ferme le tableau
        $page_content .= '<a href="' . ROOT_DIR . 'admin/groups/action/add/" title="Ajouter un groupe"><button class="admin">Ajouter un groupe</button></a></div>';
    }

    public function show_add_group_form($name = NULL) { // Affichage du formulaire d'ajout d'un groupe
        global $page_content;
        /* Affichage du formulaire */
        $page_content .= '<form action="' . ROOT_DIR . 'admin/groups/submit/addgroup" method="post" id="addGroup"><table class="addGroup">
    <tr><td><label for="name">Nom du groupe:</label></td><td><input type="text" id="name" name="name" value="' . $name . '" required /></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le groupe" />
</table></form>'; // Fin du formulaire
    }

    public function show_group_infos($id) { // Affichage de la page d'info d'un groupe
        global $page_content;
        global $popups;
        $members = getMembers($id);
        $name = $this->getGroupName($id);
        if ($name) { // Si le groupe existe, on l'affiche
            $page_content .= '<h1>Liste des utilisateurs du groupe ' . $name . '</h1>
                <span class="buttons"><a href="' . ROOT_DIR . 'admin/groups/action/edit/id/' . $id . '"><button class="admin">Modifier le nom du groupe</button></a> <button class="admin disabled" onclick="toggle_popup(\'delete_group\');" disabled>Supprimer le groupe</button> <a href="' . ROOT_DIR . 'admin/groups/" title="Retourner à la gestion des groupes"><button class="admin">Retour à la gestion des groupes</button></a></span>';
            if ($members) {
                $page_content .= '<table id="usersList">';
                for ($i = 0; $i < count($members); $i++) {
                    $page_content .= '<tr><td><a href="' . ROOT_DIR . 'admin/users/action/info/id/' . $members[$i]['id_user'] . '" title="' . $members[$i]['name'] . ' ' . $members[$i]['firstName'] . '">' . $members[$i]['name'] . ' ' . $members[$i]['firstName'] . '</a></td></tr>';
                }
                $page_content .= '</table>';
            } else {
                $page_content .= '<span class="box warning">Aucun utilisateur ne fait parti de ce groupe !</span>';
            }

            $popups .= '<div class="popup" id="popup_delete_group" style="display:none;"><center>Etes-vous sur de vouloir supprimer ce groupe ?<br /><button class="admin" onclick="toggle_popup(\'delete_group\');">Annuler la suppression</button><a href="' . ROOT_DIR . 'admin/groups/action/delete/id/' . $id . '" class="bouton"><button class="admin">Confirmer</button></a></center></div>';
        } else { // Sinon, on affiche le message d'erreur
            $this->unknown_group();
        }
    }

    public function show_edit_group_form($id, $name) { // Affichage du formulaire d'édition d'un groupe
        global $page_content;
        /* Affichage du formulaire */
        if (!$name) {
            $this->unknown_group();
        } else {
            $page_content .= '<form action="' . ROOT_DIR . 'admin/groups/submit/editgroup" method="post" id="edit_group"><input type="hidden" name="id" value="' . $id . '" />
    <table class="editGroup">
    <tr><td><label for="name">Nom du groupe:</label></td><td><input type="text" id="name" name="name" value="' . $name . '" required /></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer le groupe" /></td></tr>
</table></form>'; // Fin du formulaire
        }
    }

    public function no_group_selected() { // Si aucun groupe n'est sélectonné, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Aucun groupe selectionné !</span>";
        $this->show_groups_list(); // On affiche la liste des groupes
    }

    public function unknown_group() {
        global $page_content;
        $page_content .="<span class=\"box warning\">Le groupe selectionné n'exite pas !</span>";
        $this->show_groups_list(); // On affiche la liste des utilisateurs
    }

}
