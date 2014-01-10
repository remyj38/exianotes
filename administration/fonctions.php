<?php

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
    $requete = $bdd->prepare('SELECT id FROM users WHERE user = :user'); // Verification de l'existance de l'utilisateur créé
    $requete->execute(array('user' => $user));
    $donnees = $requete->fetch();
    if (isset($donnes['id'])) { // Si l'utilisateur existe, on retourne true
        return true;
    } else { // Sinon, on retourne false
        return false;
    }
}

function getUserInfo($pass = 0) { //Permet de récuperer les infos sur l'utilisateur demandé
    global $argumentsUrl;
    if ($pass) {
        $requete = $bdd->prepare('SELECT user, passwd, email, reinit_passwd, rank, groups, name, firstName FROM users WHERE id = :id'); // Récupération des infos sur l'user
    } else {
        $requete = $bdd->prepare('SELECT user, email, reinit_passwd, rank, groups, name, firstName FROM users WHERE id = :id'); // Récupération des infos sur l'user
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
    global $argumentsUrl;
    $old = getUserInfo(1); // Récupération des infos sur l'utilisateur
    $bdd = get_db_connexion();
    $requete = $bdd->prepare("UPDATE users SET user = :user, passwd = :passwd, reinit_passwd = :reinit_pwd, rank = :rank, groups = :groups, name = :name, firstName = :firstName WHERE id = :id");
    if ($auth->crypt($new_passwd) != $old['passwd']) { // Si le nouveau pass est différent de l'ancien, on le change
        $passwd = $auth->crypt($new_passwd);
    } else { // Sinon, on le laisse
        $passwd = $old['passwd'];
    }
    if (isset($reinit_passwd)) { // Si la case de rinitialisation de mot de passe est cochée
        if (isset ($old['reinit_passwd'])) { // Et si l'utilisateur avait déjà demandé une réinitialisation
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
    $requete = $bdd->prepare('DELETE FROM users WHERE id = :id');
    $requete->execute(array("id" => $id));
    $requete->closeCursor();
    
    /* Vérification de la suppression */
    $requete = $bdd->prepare('SELECT user FROM users WHERE id = :id');
    $requete->execute(array("id" => $id));
    $test = $requete->fetch();
    if (isset ($test['user'])) {
        return true;
    } else {
        return false;
    }
    
}

?>