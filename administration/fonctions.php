<?php

/* Gestion des utilisateurs */

function addUser($user, $email, $rank, $groups, $nom, $prenom) { // Permet d'ajouter un utilisateur à la base de donnée
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

function getUserInfo($pass = 0) { //Permet de récuperer les infos sur l'utilisateur demandé
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

function editUser($id, $new_user, $new_passwd, $reinit_passwd, $new_rank, $new_groups, $new_name, $new_firstName) { // Changement des infos utilisateur
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

function delUser($id) { // Suppression d'un utilisateur
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

/* Gestion des notes */

function addNote($user, $note, $coef, $module) { // Permet d'ajouter une note
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('INSERT INTO notes (user, note, coef, module) VALUES ( :user, :note, :coef, :module)'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'user' => $user,
        'note' => $note,
        'coef' => $coef,
        'module' => $module
    ));
    $requete->closeCursor(); // Fermeture de la requete
}

function editNote($user, $note, $coef, $module, $id) { // Permet d'éditer une note entrée
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('UPDATE notes SET user = :user ,note = :note ,coef = :coef , module = :module WHERE id_note = :id_note'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'user' => $user,
        'note' => $note,
        'coef' => $coef,
        'module' => $module,
        'id_note' => $id
    ));
    $requete->closeCursor(); // Fermeture de la requete
}

function deleteNote($id) { // Permet de supprimer une note
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('DELETE FROM notes WHERE id_note = :id_note'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'id_note' => $id
    ));
    $requete->closeCursor(); // Fermeture de la requete
    /* Vérification de la suppression */
    $requete = $bdd->prepare('SELECT note FROM notes WHERE id_note = :id_note');
    $requete->execute(array("id_note" => $id));
    $test = $requete->fetch();
    if (!isset($test['note'])) {
        return true;  //Retourne true si la note a bien été supprimée
    } else {
        return false; // False si la note n'a pas été supprimée
    }
}

/* Gestion des modules */

function addModule($year, $name) {
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('INSERT INTO modules (year, name) VALUES ( :year, :name)'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'year' => $year,
        'name' => $name
    ));
    $requete->closeCursor(); // Fermeture de la requete
}

function editModule($year, $name, $id) { // Permet d'éditer une note entrée
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('UPDATE modules SET year = :year ,name = :name WHERE id_module = :id'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'year' => $year,
        'name' => $name,
        'id' => $id
    ));
    $requete->closeCursor(); // Fermeture de la requete
}

function deleteModule($id) { // Permet de supprimer une note
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->prepare('DELETE FROM modules WHERE id_module = :id'); // Préparation de l'insertion dans la base de la note
    $requete->execute(array(
        'id' => $id
    ));
    $requete->closeCursor(); // Fermeture de la requete
    /* Vérification de la suppression */
    $requete = $bdd->prepare('SELECT name FROM modules WHERE id_module = :id');
    $requete->execute(array("id" => $id));
    $test = $requete->fetch();
    if (!isset($test['name'])) {
        return true;  //Retourne true si la note a bien été supprimée
    } else {
        return false; // False si la note n'a pas été supprimée
    }
}

/* Récupération pour affichage */

function getNotes() { // Permet de récuperer toutes les notes dans un tableau
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT id_note, user, note, coef,  FROM notes;');
    for($i = 0; $temp = $requete->fetch(); $i++) {
        $datas[$i]=$temp;
    }
    return $datas;
}

function getModules() { // Permet de récuperer tous les modules dans un tableau
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT * FROM modules;');
    for($i = 0; $temp = $requete->fetch(); $i++) {
        $datas[$i]=$temp;
    }
    return $datas;
}


?>