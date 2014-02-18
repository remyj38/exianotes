<?php

class admin_users {

    private $nbr_utilisateurs;
    private $users;

    /* Gestion des utilisateurs */

    public function addUser($user, $email, $rank, $groups, $nom, $prenom) { // Permet d'ajouter un utilisateur à la base de donnée
        $bdd = get_db_connexion(); //Ouverture de la connexion
        $requete = $bdd->prepare('INSERT INTO users (user, email, reinit_passwd, rank, groups, name, firstName) VALUES ( :user, :email, :reinit_passwd, :rank, :groups, :name, :firstName)'); // Préparation de l'insertion dans la base de l'utilisateur
        $requete->execute(array(
            'user' => $user,
            'reinit_passwd' => generateReinitKey($user), // Génération d'une clé de réinitialisation
            'email' => $email,
            'rank' => $rank,
            'groups' => $groups,
            'name' => $nom,
            'firstName' => $prenom
        ));
        $requete->closeCursor(); // Fermeture de la requete
        $requete = $bdd->prepare('SELECT id_user FROM users WHERE user = :user'); // Verification de l'existance de l'utilisateur créé
        $requete->execute(array('user' => $user));
        $donnees = $requete->fetch();
        if (isset($donnes['id_user'])) { // Si l'utilisateur existe, on retourne true
            return true;
        } else { // Sinon, on retourne false
            return false;
        }
    }

    public function editUser($id, $new_user, $new_passwd, $reinit_passwd, $new_rank, $new_groups, $new_name, $new_firstName) { // Changement des infos utilisateur
        global $auth;
        $old = getUserInfo(1); // Récupération des infos sur l'utilisateur
        $bdd = get_db_connexion();
        $requete = $bdd->prepare("UPDATE users SET user = :user, passwd = :passwd, reinit_passwd = :reinit_pwd, rank = :rank, groups = :groups, name = :name, firstName = :firstName WHERE id_user = :id");
        if ($auth->crypt($new_passwd) != $old['passwd']) { // Si le nouveau pass est différent de l'ancien, on le change
            $passwd = $auth->crypt($new_passwd);
        } else { // Sinon, on le laisse
            $passwd = $old['passwd'];
        }
        if (isset($reinit_passwd)) { // Si la case de rinitialisation de mot de passe est cochée
            if (isset($old['reinit_passwd'])) { // Et si l'utilisateur avait déjà demandé une réinitialisation
                $reinit = $old['reinit_passwd']; // On garde la réinitialisation
            } else {
                $reinit = generateReinitKey($new_user); // Sinon, on en génère une nouvelle
            }
        }
        $requete->execute(array(
            "user" => $new_user,
            "passwd" => $passwd,
            "reinit_passwd" => $reinit,
            "rank" => $new_rank,
            "groups" => $new_groups,
            "name" => $new_name,
            "firstName" => $new_firstName
        )); // on execute la requete
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
        $requete = $bdd->query('SELECT id_user, user, email, groups.name AS Gname, ranks.name AS Rname, users.name AS Uname, firstName FROM users INNER JOIN groups ON users.groups=groups.id_group INNER JOIN ranks ON users.rank=ranks.id_rank;');
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

    public function getUserInfo($pass = 0) { //Permet de récuperer les infos sur l'utilisateur demandé
        if ($pass) {
            $requete = $bdd->prepare('SELECT user, passwd, email, reinit_passwd, rank, groups, name, firstName FROM users WHERE id_user = :id'); // Récupération des infos sur l'user avec mot de passe
        } else {
            $requete = $bdd->prepare('SELECT user, email, reinit_passwd, rank, groups, name, firstName FROM users WHERE id_user = :id'); // Récupération des infos sur l'user sans mot de passe
        }
        $requete->execute(array('id' => $id));
        $donnees = $requete->fetch();
        if (isset($donnees['reinit_passwd'])) {
            $donnees['reinit_passwd'] = TRUE;
        } else {
            $donnees['reinit_passwd'] = FALSE;
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
        for ($i = 0; $i<$this->nbr_utilisateurs; $i++) {
            $page_content.= '<tr>
    <td><a href="' . ROOT_DIR . 'admin/users/action/info/id/'. $this->users[$i]['id_user'] . '" title="' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['user'] . '</a></td>
    <td><a href="mailto:' . $this->users[$i]['email'] . '" title="Envoyer un mail à ' . $this->users[$i]['Uname'] . ' ' . $this->users[$i]['firstName'] . '">' . $this->users[$i]['email'] . '</a></td>
    <td>' . $this->users[$i]['Gname'] . '</td><td>' . $this->users[$i]['Rname'] . '</td></tr>';
        }
        $page_content.= '</table>';
    }
    
    public function afficher_formulaire_add_user() {
        global $page_content;
        $ranks = getRanks();
        $groups = getGroups();
        $page_content .= '<form action="../submit/adduser" method="post"><table class="addUser">
    <tr><td><label for="nom">Nom :</label></td><td><input type="text" id="nom" name="nom" required /></td></tr>
    <tr><td><label for="prenom">Prenom :</label></td><td><input type="text" id="prenom" name="prenom" required /></td></tr>
    <tr><td><label for="user">Nom d\'utilisateur :</label></td><td><input type="text" id="user" name="user" required /></td></tr>
    <tr><td><label for="password">Nom d\'utilisateur :</label></td><td><span id="password">Le mot de passe est généré automatiquement et envoyé par mail !</span></td></tr>
    <tr><td><label for="email">Adresse mail :</label></td><td><input type="email" id="email" name="email" required /></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="rank" name="rank">';
        for ($i=0; $i<count($ranks); $i++) {
            $page_content .= '<option value="' . $ranks[$i]['id_rank'] . '">' . $ranks[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td><label for="rank">Rang :</label></td><td><select id="group" name="group">';
        for ($i=0; $i<count($groups); $i++) {
            $page_content .= '<option value="' . $groups[$i]['id_group'] . '">' . $groups[$i]['name'] . '</option>';
        }
        $page_content .= '</select></td></tr>
    <tr><td colspan="2"><input type="submit" value="Enregistrer l\'utilisateur" />
</table></form>';
        
    }
    
    public function __construct() {
        $this->users = $this->getUsers();
        $this->nbr_utilisateurs = count($this->users);
    }

}
