<?php

class admin_users {

    private $nbr_utilisateurs;
    private $users;

    public function __construct() {
        $this->users = $this->getUsers();
        $this->nbr_utilisateurs = count($this->users);
    }

    /* Gestion des utilisateurs */

    public function addUser($user, $email, $rank, $groups, $nom, $prenom) { // Permet d'ajouter un utilisateur à la base de donnée
        global $page_content;
        global $adminMails;
        $reinit_passwd_key = generateReinitKey($user); // Génération d'une clé de réinitialisation
        if ($groups == 'aucun') {
            $groups = NULL;
        }
        if (isset($user, $email, $rank, $nom, $prenom)) {
            $bdd = get_db_connexion(); //Ouverture de la connexion
            $requete = $bdd->prepare('INSERT INTO users (user, email, reinit_passwd, rank, groups, name, firstName) VALUES ( :user, :email, :reinit_passwd, :rank, :groups, :name, :firstName)'); // Préparation de l'insertion dans la base de l'utilisateur
            $erreur = $requete->execute(array(
                'user' => $user,
                'reinit_passwd' => $reinit_passwd_key,
                'email' => $email,
                'rank' => $rank,
                'groups' => $groups,
                'name' => $nom,
                'firstName' => $prenom
            ));
        } else {
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        echo $erreur;
        if ($erreur && $erreur != 'champs') { // Si l'insertion de l'utilisateur s'est déroulé correctement, on envoie le mail
            if (!$adminMails->mailAdd(array('user' => $user, 'reinit_passwd' => $reinit_passwd_key, 'email' => $email, 'Uname' => $nom, 'firstName' => $prenom))) { // Si le mail ne s'est pas envoyé correctement, on modifie les erreurs
                $erreur = 'mail';
            }
        }

        if (!$erreur || $erreur == 'champs' || $erreur == 'mail') { // Si une erreur est apparue au cours de l'ajout de l'utilisateur
            errorsSQL($erreur, $requete);
            return false;
        }
        return true;
    }

    public function editUser($id, $new_user, $reinit_passwd, $new_email, $new_rank, $new_groups, $new_name, $new_firstName) { // Changement des infos utilisateur
        global $page_content;
        global $adminMails;
        if ($new_groups == 'aucun') {
            $new_groups = NULL;
        }
        $old = $this->getUserInfo($id, true); // Récupération des infos sur l'utilisateur
        $bdd = get_db_connexion();
        $requete = $bdd->prepare("UPDATE users SET user = :user, passwd = :passwd, email = :email, reinit_passwd = :reinit_passwd, rank = :rank, groups = :groups, name = :name, firstName = :firstName WHERE id_user = :id");
        if ($reinit_passwd) { // Si la case de rinitialisation de mot de passe est cochée
            if (isset($old['reinit_passwd'])) { // Et si l'utilisateur avait déjà demandé une réinitialisation
                $reinit = $old['reinit_passwd']; // On garde la réinitialisation
                $new_passwd = $old['passwd'];
            } else {
                $new_passwd = NULL;
                $reinit = generateReinitKey($new_user); // Sinon, on en génère une nouvelle
            }
        } else {
            $reinit = NULL;
        }
        $erreur = $requete->execute(array(
            "user" => $new_user,
            "passwd" => $new_passwd,
            "email" => $new_email,
            "reinit_passwd" => $reinit,
            "rank" => $new_rank,
            "groups" => $new_groups,
            "name" => $new_name,
            "firstName" => $new_firstName,
            "id" => $id
        )); // on execute la requete
        if (!$erreur) {
            errorsSQL($erreur, $requete);
            return FALSE;
        } else {
            if (!$adminMails->mailReinitPasswd(array("email" => $new_email, "reinit_passwd" => $reinit, "name" => $new_name, "firstName" => $new_firstName))) {
                $page_content .= "<span class=\"error\">Echec lors de l'envoi du mail de reinitialisation du mot de passe !</span>'";
            }
            return TRUE;
        }
    }

    public function delUser($id) { // Suppression d'un utilisateur
        /* Suppression ... */
        $bdd = get_db_connexion();
        $requete = $bdd->prepare('DELETE FROM users WHERE id_user = :id');
        $requete->execute(array("id" => $id));
        $requete->closeCursor();

        /* Vérification de la suppression */
        $requete = $bdd->prepare('SELECT user FROM users WHERE id_user = :id');
        $requete->execute(array("id" => $id));
        $test = $requete->fetch();
        if (!isset($test['user'])) { // Retourne true si l'utilisateur à été supprimé
            return true;
        } else {
            return false; // False si erreur lors de la suppression
        }
    }

    /* Récupération des infos utilisateurs */

    private function getUsers() { // Permet de récuperer tous les utilisateurs dans un tableau
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->query('SELECT id_user, user, email, groups.name AS Gname, ranks.name AS Rname, users.name AS Uname, firstName FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank;');
        while ($temp = $requete->fetch()) {
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) {
            return $datas;
        } else {
            return NULL;
        }
    }

    public function getUserInfo($id, $pass = false) { //Permet de récuperer les infos sur l'utilisateur demandé
        $bdd = get_db_connexion();
        if ($pass) {
            $requete = $bdd->prepare('SELECT user, passwd, email, reinit_passwd, change_email, rank, ranks.name AS Rname, users.groups, groups.name AS Gname, users.name AS Uname, firstName, themes.name AS Tname FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank INNER JOIN themes ON themes.id_theme = users.theme WHERE id_user = :id'); // Récupération des infos sur l'user avec mot de passe
        } else {
            $requete = $bdd->prepare('SELECT user, email, reinit_passwd, change_email, rank, ranks.name AS Rname, users.groups AS Ugroup, groups.name AS Gname, users.name AS Uname, firstName, themes.name AS Tname FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank INNER JOIN themes ON themes.id_theme = users.theme WHERE id_user = :id'); // Récupération des infos sur l'user avec mot de passe
        }
        $requete->execute(array('id' => $id));
        $donnees = $requete->fetch();
        if (isset($donnees['reinit_passwd'])) {
            $donnees['reinit_passwd'] = TRUE;
        } else {
            $donnees['reinit_passwd'] = FALSE;
        }
        if (isset($donnees['change_email'])) {
            $donnees['reinit_passwd'] = TRUE;
        } else {
            $donnees['reinit_passwd'] = FALSE;
        }
        if (!isset($donnees['Gname'])) {
            $donnees['Gname'] = 'Aucun';
        }
        if (isset($donnees['user'])) {
            return $donnees;
        } else {
            return FALSE;
        }
    }

    private function getIps($user, $nombre = 10) { // récupère les ips d'un utilisateur (par défaut : 10 ips)
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->query('SELECT * FROM ips;');
        while ($temp = $requete->fetch()) {
            $datas[$i] = $temp;
            $i++;
        }

        if (isset($datas)) {
            return $datas;
        } else {
            return FALSE;
        }
    }

    /* Affichages */

    public function afficher_liste_users() { // Affiche la liste des utilisateurs enregistrés
        global $page_content;
        $page_content.= '<table id="usersList"><caption>Liste des utilisateurs</caption><tr><th>Pseudo de l\'utilisateur</th><th>Adresse mail</th><th>Année</th><th>Rang</th></tr>';
        for ($i = 0; $i < $this->nbr_utilisateurs; $i++) {
            $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/users/action/info/id/' . $this->users[$i]['id_user'] . '" title="' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['user'] . '</a></td>
    <td><a href="mailto:' . $this->users[$i]['email'] . '" title="Envoyer un mail à ' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['email'] . '</a></td>
    <td>' . $this->users[$i]['Gname'] . '</td><td>' . $this->users[$i]['Rname'] . '</td></tr>';
        }
        $page_content.= '</table>';
    }

    public function afficher_formulaire_add_user($user = NULL, $email = NULL, $rank = NULL, $group = NULL, $nom = NULL, $prenom = NULL) { // Affichage du formulaire d'ajout d'utilisateur
        global $page_content;
        $ranks = getRanks(); // Récupérations des rangs
        $groups = getGroups(); // Récupérations des groupes
        $page_content .= '<form action="' . ROOT_DIR . 'admin/users/submit/adduser" method="post"><table class="addUser">
    <tr><td><label for="nom">Nom :</label></td><td><input type="text" id="nom" name="nom" value="' . $nom . '" required /></td></tr>
    <tr><td><label for="prenom">Prenom :</label></td><td><input type="text" id="prenom" name="prenom" value="' . $prenom . '" required /></td></tr>
    <tr><td><label for="user">Nom d\'utilisateur :</label></td><td><input type="text" id="user" name="user" value="' . $user . '" required /></td></tr>
    <tr><td><label for="password">Mot de passe :</label></td><td><span id="password">Un lien pour initialiser le mot de passe est envoyé par mail !</span></td></tr>
    <tr><td><label for="email">Adresse mail :</label></td><td><input type="email" id="email" name="email" value="' . $email . '" required /></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="rank" name="rank">';
        for ($i = 0; $i < count($ranks); $i++) { // Affichage des rangs
            $page_content .= '<option value="' . $ranks[$i]['id_rank'] . '"';
            if (isset($rank)) {
                if ($rank == $ranks[$i]['id_rank']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .='>' . $ranks[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td><label for="group">Groupe :</label></td><td><select id="group" name="group">
    <option value="aucun">Aucun</option>';
        for ($i = 0; $i < count($groups); $i++) { // Affichage des groupes
            $page_content .= '<option value="' . $groups[$i]['id_group'] . '"';
            if (isset($group)) {
                if ($group == $groups[$i]['id_group']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .= '>' . $groups[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer l\'utilisateur" />
</table></form>';
    }

    public function afficher_infos_user($id) { // Affichage de la page d'info d'un utilisateur
        global $page_content;
        $infos = $this->getUserInfo($id); // Récupération des infos sur l'utilisateur
        if (isset($infos)) { // Si les infos existent, on les affiche
            $page_content .= '<table id="info_user">
                  <tr><td>Nom :</td><td>' . $infos['Uname'] . '</td></tr>
    <tr><td>Prenom :</td><td>' . $infos['firstName'] . '</td></tr>
    <tr><td>Nom d\'utilisateur :</td><td>' . $infos['user'] . '</td></tr>';
            if ($infos['reinit_passwd']) {
                $page_content .= '<tr><td colspan="2"><span id="info_reinit_passwd">Cet utilisateur a demandé une réinitialisation de mot de passe !</span></td></tr>'; // Affichage d'une quelconque demande de réinitialisation de mot de passe
            }
            $page_content .= '<tr><td>Adresse mail :</td><td>' . $infos['email'];
            if ($infos['change_email']) {
                $page_content .= '<br /><span id="info_change_email">Cet utilisateur a demandé une réinitialisation de mot de passe !</span>'; // Affichage d'une quelconque demande de changement d'email
            }
            $page_content .= '</td></tr>
    <tr><td>Rang :</td><td>' . $infos['Rname'] . '</td></tr>
    <tr><td>Groupe :</td><td>' . $infos['Gname'] . '</td></tr>
    <tr><td colspan="2"><a href="../../edit/id/' . $id . '" class="bouton">Modifier l\'utilisateur</a></td></tr>
          </table>';
        }
    }

    public function afficher_formulaire_edit_user($id, $infos) { // Affichage du formulaire d'ajout d'utilisateur
        global $page_content;
        $ranks = getRanks(); // Récupérations des rangs
        $groups = getGroups(); // Récupérations des groupes
        $page_content .= '<form action="' . ROOT_DIR . 'admin/users/submit/edituser" method="post"><input type="hidden" name="id" value="' . $id . '" />
    <table class="addUser">
    <tr><td><label for="nom">Nom :</label></td><td><input type="text" id="nom" name="nom" value="' . $infos['Uname'] . '" required /></td></tr>
    <tr><td><label for="prenom">Prenom :</label></td><td><input type="text" id="prenom" name="prenom" value="' . $infos['firstName'] . '" required /></td></tr>
    <tr><td><label for="user">Nom d\'utilisateur :</label></td><td><input type="text" id="user" name="user" value="' . $infos['user'] . '" required /></td></tr>
    <tr><td colspan="2"><input type="checkbox" id="reinit_passwd" name="reinit_passwd"/><label for="reinit_passwd">Envoyer un mail de réinitialisation de mot de passe </label></td></tr>
    <tr><td><label for="email">Adresse mail :</label></td><td><input type="email" id="email" name="email" value="' . $infos['email'] . '" required /></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="rank" name="rank">';
        for ($i = 0; $i < count($ranks); $i++) { // Affichage des rangs
            $page_content .= '<option value="' . $ranks[$i]['id_rank'] . '"';
            if (isset($infos['rank'])) {
                if ($infos['rank'] == $ranks[$i]['id_rank']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .='>' . $ranks[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td><label for="group">Groupe :</label></td><td><select id="group" name="group">
    <option value="aucun">Aucun</option>';
        for ($i = 0; $i < count($groups); $i++) { // Affichage des groupes
            $page_content .= '<option value="' . $groups[$i]['id_group'] . '"';
            if (isset($infos['Ugroup'])) {
                if ($infos['Ugroup'] == $groups[$i]['id_group']) {
                    $page_content .= ' selected ';
                }
            }
            $page_content .= '>' . $groups[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer l\'utilisateur" />
</table></form>';
    }

}
