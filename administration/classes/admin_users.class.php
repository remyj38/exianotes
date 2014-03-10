<?php

class admin_users {

    private $users;

    public function __construct() {
        $this->users = $this->getUsers(); // On récupère la liste des utilisateurs
    }

    /* Gestion des utilisateurs */

    public function addUser($user, $email, $rank, $groups, $nom, $prenom) { // Permet d'ajouter un utilisateur à la base de donnée
        global $adminMails;
        $reinit_passwd_key = generateReinitKey($user); // Génération d'une clé de réinitialisation
        if ($groups == 'aucun') {
            $groups = NULL;
        }
        if (isset($user, $email, $rank, $nom, $prenom)) { // Si tous les champs sont remplis, on ajoute l'utilisateur
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
            )); // Execution de la requête avec les données collectées
        } else { // Sinon, on stoque l'erreur (les champs ne sont pas tous remplis
            $erreur = 'champs';
        }
        $requete->closeCursor(); // Fermeture de la requete
        if ($erreur == true) { // Si l'insertion de l'utilisateur s'est déroulé correctement, on envoie le mail
            if (!$adminMails->mailAdd(array('user' => $user, 'reinit_passwd' => $reinit_passwd_key, 'email' => $email, 'Uname' => $nom, 'firstName' => $prenom))) { // Si le mail ne s'est pas envoyé correctement, on modifie les erreurs
                $erreur = 'mail';
            }
        }
        if ($erreur != true) { // Si une erreur est apparue au cours de l'ajout de l'utilisateur
            errorsSQL($erreur, $requete); // Affichage de l'erreur générée
            return false;
        }
        return true;
    }

    public function editUser($id, $new_user, $reinit_passwd, $new_email, $new_rank, $new_groups, $new_name, $new_firstName) { // Changement des infos utilisateur
        global $page_content;
        global $adminMails;
        if ($new_groups == 'aucun') { // Si le nouveau groupe est aucun
            $new_groups = NULL;
        }
        $old = $this->getUserInfo($id, true); // Récupération des infos sur l'utilisateur
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE users SET user = :user, passwd = :passwd, email = :email, reinit_passwd = :reinit_passwd, rank = :rank, groups = :groups, name = :name, firstName = :firstName WHERE id_user = :id"); // Préparation de la requête
        if ($old['reinit_passwd']) { // si l'utilisateur avait déjà demandé une réinitialisation
            $reinit = $old['reinit_passwd']; // On garde la réinitialisation
            $new_passwd = $old['passwd']; // On garde l'ancien mot de passe
            $mail_reinit = false;
        } else if ($reinit_passwd) { // Si la case de réinitialisation de mot de passe est cochée
            $new_passwd = NULL;
            $reinit = generateReinitKey($new_user); // Sinon, on en génère une nouvelle
            $mail_reinit = true;
        } else { // On garde les infos
            $new_passwd = $old['passwd'];
            $reinit = NULL;
            $mail_reinit = false;
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
        )); // on execute la requete avec les nouvelles données
        if (!$erreur) { // Si une erreur est parvenue durant l'execution de la requête, on l'affiche
            errorsSQL($erreur, $requete);
            return FALSE;
        } else if ($mail_reinit) { // Sinon, si on a demandé un envoi de email de réinitialisation, on l'envoi
            if (!$adminMails->mailReinitPasswd(array("email" => $new_email, "reinit_passwd" => $reinit, "name" => $new_name, "firstName" => $new_firstName))) { // Si le mail n'a pas pu s'envoyer, on affiche l'erreur
                $page_content .= "<span class=\"box error\">Echec lors de l'envoi du mail de reinitialisation du mot de passe !</span>'";
                return false;
            }
        } else {
            return TRUE;
        }
    }

    public function delUser($id) { // Suppression d'un utilisateur
        $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
        /* Vérification de l'existance de l'utilisateur */
        $user = $this->getUserInfo($id);
        if (!$user) {
            $this->unknown_user();
            return 'unknown';
        } else {
            /* Suppression ... */
            $requete = $bdd->prepare('DELETE FROM users WHERE id_user = :id'); // Préparation de la requête
            $error = $requete->execute(array("id" => $id)); // Execution de la requête avec l'id de l'utilisateur à supprimer
            $requete->closeCursor(); // Fermeture de la requête
            if ($error) { // Retourne true si l'utilisateur a été supprimé
                return true;
            } else {
                errorsSQL($error, $requete);
                return false; // False si erreur lors de la suppression
            }
        }
    }

    /* Récupération des infos utilisateurs */

    private function getUsers() { // Permet de récuperer tous les utilisateurs dans un tableau
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->query('SELECT id_user, user, email, groups.name AS Gname, ranks.name AS Rname, users.name AS Uname, firstName FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank;'); // Préparation de la requete
        while ($temp = $requete->fetch()) { // Pour chaque ligne de résultats, on stocke l'utilisateur
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) { // Si des données ont été récupérées, on les retourne
            return $datas;
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    }

    public function getUserInfo($id, $pass = false) { //Permet de récuperer les infos sur l'utilisateur demandé
        $bdd = get_db_connexion(); // ouverture de la connexion à la base de données
        if ($pass) { // Si on demande de récuperer le mot de passe, on le récupère
            $requete = $bdd->prepare('SELECT user, passwd, email, reinit_passwd, change_email, rank, ranks.name AS Rname, users.groups, groups.name AS Gname, users.name AS Uname, firstName, themes.name AS Tname FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank INNER JOIN themes ON themes.id_theme = users.theme WHERE id_user = :id'); // Récupération des infos sur l'user avec mot de passe
        } else {
            $requete = $bdd->prepare('SELECT user, email, reinit_passwd, change_email, rank, ranks.name AS Rname, users.groups AS Ugroup, groups.name AS Gname, users.name AS Uname, firstName, themes.name AS Tname FROM users LEFT JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank INNER JOIN themes ON themes.id_theme = users.theme WHERE id_user = :id'); // Récupération des infos sur l'user sans mot de passe
        }
        $requete->execute(array('id' => $id)); // Execution de la requête
        $donnees = $requete->fetch(); // Récupération du premier résultat
        if (isset($donnees['user'])) { // Si l'utilisateur a été trouvé, on traite les données avant de les retourner
            if (isset($donnees['reinit_passwd'])) { // On remplasse la clé de réinitialisation par vrai ou faux
                $donnees['reinit_passwd'] = TRUE;
            } else {
                $donnees['reinit_passwd'] = FALSE;
            }
            if (isset($donnees['change_email'])) { // On remplasse la clé de réinitialisation d'email par vrai ou faux
                $donnees['reinit_passwd'] = TRUE;
            } else {
                $donnees['reinit_passwd'] = FALSE;
            }
            if (!isset($donnees['Gname'])) { // Si l'utilisateur n'a pas de groupe
                $donnees['Gname'] = 'Aucun';
            }
            return $donnees;
        } else { // Sinon, on retourne faux
            return false;
        }
    }

    private function getIps($user, $nombre = 10) { // récupère les ips d'un utilisateur (par défaut : 10 ips)
        $i = 0;
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->prepare('SELECT UNIX_TIMESTAMP(date_ip) AS date_ip, ip, host_name FROM ips WHERE user = :id ORDER BY date_ip DESC LIMIT 0, 10;'); // Execution de la requête
        $requete->execute(array('id' => $user));
        while ($temp = $requete->fetch()) { // Pour chaque enregistrement, on l'enregistre
            $temp['date_ip'] = date("d-m-Y  à G:i:s", $temp['date_ip']);
            $datas[$i] = $temp;
            $i++;
        }
        if (isset($datas)) { // Si des données sont présentes, on les retourne
            return $datas;
        } else { // Sinon, on retourne faux
            return FALSE;
        }
    }

    /* Affichages */

    public function show_users_list() { // Affiche la liste des utilisateurs enregistrés
        global $page_content;
        $page_content.= '<div id="usersList"><table><caption>Liste des utilisateurs</caption><tr><th>Pseudo de l\'utilisateur</th><th>Adresse mail</th><th>Année</th><th>Rang</th></tr>'; // Affichage des en-têtes du tableau
        if ($this->users !== NULL) {
            for ($i = 0; $i < count($this->users); $i++) { // Pour chaque utilisateur, on ajoute sa ligne avec ses infos
                $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/users/action/info/id/' . $this->users[$i]['id_user'] . '" title="' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['user'] . '</a></td>
    <td><a href="mailto:' . $this->users[$i]['email'] . '" title="Envoyer un mail à ' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['email'] . '</a></td>
    <td>' . $this->users[$i]['Gname'] . '</td><td>' . $this->users[$i]['Rname'] . '</td></tr>';
            }
        } else {
            $page_content .= "<tr><td colspan=2><span class=\"box warning\">Aucun controle n'est encore enregistré</span></td></tr>";
        }
        $page_content.= '</table>'; // On ferme le tableau
        $page_content .= '<a href="' . ROOT_DIR . 'admin/users/action/add" title="Ajouter un utilisateur"><button class="admin">Ajouter un utilisateur</button></a></div>';
    }

    public function show_add_user_form($user = NULL, $email = NULL, $rank = NULL, $group = NULL, $nom = NULL, $prenom = NULL) { // Affichage du formulaire d'ajout d'utilisateur
        global $page_content;
        $ranks = getRanks(); // Récupérations des rangs
        $groups = getGroups(); // Récupérations des groupes
        /* Affichage du formulaire */
        $page_content .= '<form action="' . ROOT_DIR . 'admin/users/submit/adduser" method="post"><table class="addUser">
    <tr><td><label for="nom">Nom :</label></td><td><input type="text" id="nom" name="nom" value="' . $nom . '" required /></td></tr>
    <tr><td><label for="prenom">Prenom :</label></td><td><input type="text" id="prenom" name="prenom" value="' . $prenom . '" required /></td></tr>
    <tr><td><label for="user">Nom d\'utilisateur :</label></td><td><input type="text" id="user" name="user" value="' . $user . '" required /></td></tr>
    <tr><td><label for="password">Mot de passe :</label></td><td><span id="password">Un lien pour initialiser le mot de passe est envoyé par mail !</span></td></tr>
    <tr><td><label for="email">Adresse mail :</label></td><td><input type="email" id="email" name="email" value="' . $email . '" required /></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="rank" name="rank">';
        for ($i = 0; $i < count($ranks); $i++) { // Affichage de la liste des rangs
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
        for ($i = 0; $i < count($groups); $i++) { // Affichage de la liste des groupes
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
</table></form>'; // Fin du formulaire
    }

    public function show_user_infos($id) { // Affichage de la page d'info d'un utilisateur
        global $page_content;
        global $popups;
        $infos = $this->getUserInfo($id); // Récupération des infos sur l'utilisateur
        if ($infos) { // Si les infos existent, on les affiche
            $ips = $this->getIps($id);
            $page_content .= '<section class="infos"><article id="infos"><table id="info_user">
                  <tr><td class="align_right">Nom :</td><td>' . $infos['Uname'] . '</td></tr>
    <tr><td class="align_right">Prenom :</td><td>' . $infos['firstName'] . '</td></tr>
    <tr><td class="align_right">Nom d\'utilisateur :</td><td>' . $infos['user'] . '</td></tr>';
            if ($infos['reinit_passwd']) {
                $page_content .= '<tr><td colspan="2"><span id="info_reinit_passwd">Cet utilisateur a demandé une réinitialisation de mot de passe !</span></td></tr>'; // Affichage d'une quelconque demande de réinitialisation de mot de passe
            }
            $page_content .= '<tr><td>Adresse mail :</td><td>' . $infos['email'];
            if ($infos['change_email']) {
                $page_content .= '<br /><span id="info_change_email">Cet utilisateur a demandé une réinitialisation de mot de passe !</span>'; // Affichage d'une quelconque demande de changement d'email
            }
            $page_content .= '</td></tr>
    <tr><td class="align_right">Rang :</td><td>' . $infos['Rname'] . '</td></tr>
    <tr><td class="align_right">Groupe :</td><td>' . $infos['Gname'] . '</td></tr>
    <tr id="optionsUsers"><td><a href="' . ROOT_DIR . 'admin/users/action/edit/id/' . $id . '"><button class="admin">Modifier</button></a></td><td><button class="admin disabled" onclick="toggle_popup(\'supprime_user\');" disabled>Supprimer</button></td></tr>
          </table></article>
          <article id="ips"><table id="ips_user"><tr><th>Date</th><th>Ip</th><th>Hôte</th></tr>';
            if ($ips) { // Si l'utilisateur s'est déjà connectée, on liste ses ips
                for ($i = 0; $i < count($ips); $i++) { // Pour chaque ip, on l'affiche
                    $page_content .= '<tr><td>' . $ips[$i]['date_ip'] . '</td><td>' . $ips[$i]['ip'] . '</td><td>' . $ips[$i]['host_name'] . '</td></tr>';
                }
            } else { // Sinon, on informe qu'il n'y a pas d'ip d'enregistrée
                $page_content .= '<tr><td colspan="3">Aucune IP n\'est encore enregistrée !</td></tr>';
            }
            $page_content .= '</table></article></section>';
            $this->show_notes_user($id);
            $popups .= '<div class="popup" id="popup_supprime_user" style="display:none;"><center>Etes-vous sur de vouloir supprimer cet utilisateur ?<br />(Toutes notes et informations sur cet utilisateur seront supprimées !)<br /><button class="admin" onclick="toggle_popup(\'supprime_user\');">Annuler la suppression</button><a href="' . ROOT_DIR . 'admin/users/action/delete/id/' . $id . '" class="bouton"><button class="admin">Confirmer</button></a></center></div>';
        } else { // Sinon, on affiche le message d'erreur
            $this->unknown_user();
        }
    }

    public function show_edit_user_form($id, $infos) { // Affichage du formulaire d'édition d'utilisateur
        global $page_content;
        global $popups;
        if (!$infos) {
            $this->unknown_user();
        } else {
            $ranks = getRanks(); // Récupérations des rangs
            $groups = getGroups(); // Récupérations des groupes
            /* Affichage du formulaire */
            $page_content .= '<form action="' . ROOT_DIR . 'admin/users/submit/edituser" method="post" id="edit_user"><input type="hidden" name="id" value="' . $id . '" />
    <table class="editUser">
    <tr><td><label for="nom">Nom :</label></td><td><input type="text" id="nom" name="nom" value="' . $infos['Uname'] . '" required /></td></tr>
    <tr><td><label for="prenom">Prenom :</label></td><td><input type="text" id="prenom" name="prenom" value="' . $infos['firstName'] . '" required /></td></tr>
    <tr><td><label for="user">Nom d\'utilisateur :</label></td><td><input type="text" id="user" name="user" value="' . $infos['user'] . '" required /></td></tr>
    <tr><td colspan="2"><input type="checkbox" id="reinit_passwd" name="reinit_passwd" ';
            $page_content .= ($infos['reinit_passwd']) ? 'checked ' : '';
            $page_content .= '/><label for="reinit_passwd">Envoyer un mail de réinitialisation de mot de passe </label></td></tr>
    <tr><td><label for="email">Adresse mail :</label></td><td><input type="email" id="email" name="email" value="' . $infos['email'] . '" required /></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="rank" name="rank">';
            for ($i = 0; $i < count($ranks); $i++) { // Affichage de la liste des rangs
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
            for ($i = 0; $i < count($groups); $i++) { // Affichage de la liste des groupes
                $page_content .= '<option value="' . $groups[$i]['id_group'] . '"';
                if (isset($infos['Ugroup'])) {
                    if ($infos['Ugroup'] == $groups[$i]['id_group']) {
                        $page_content .= ' selected ';
                    }
                }
                $page_content .= '>' . $groups[$i]['name'] . '</option>';
            }
            $page_content .= '</select></td></tr>
    <tr><td colspan="2"><!--<button class="admin disabled" onclick="checkreinit(\'edit_user_reinit\', \'edit_user\');" disabled>Enregistrer l\'utilisateur</button>!--><input type="submit" value="Enregistrer l\'utilisateur" /></td></tr>
</table></form>'; // Fin du formulaire
            $popups.= '<div class="popup" id="popup_edit_user_reinit" style="display:none;"><center>Etes-vous sur de vouloir réinitialiser le mot de passe de cet utilisateur ?<br />(Son mot de passe actuel sera supprimé. Il ne pourra plus se connecter avec son ancien mot de passe !)<br /><button class="admin" onclick="toggle_popup(\'edit_user_reinit\');">Annuler la réinitialisation</button><button class="admin" onclick="document.forms[\'edit_user\'].submit();">Confirmer</button></center></div>';
        }
    }

    public function show_notes_user($id) {
        global $page_content;
        $page_content .= '<section class="infos"><hr />Module de la V2<hr /></section>';
    }

    public function no_user_selected() { // Si aucun utilisateur n'est sélectonné, on affiche l'erreur
        global $page_content;
        $page_content .="<span class=\"box warning\">Aucun utilisateur selectionné !</span>";
        $this->show_users_list(); // On affiche la liste des utilisateurs
    }

    public function unknown_user() {
        global $page_content;
        $page_content .="<span class=\"box warning\">L'utilisateur selectionné n'exite pas !</span>";
        $this->show_users_list(); // On affiche la liste des utilisateurs
    }

}
