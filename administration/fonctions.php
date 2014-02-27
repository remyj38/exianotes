<?php

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
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT id_note, user, note, coef, name FROM notes JOIN modules ON module=id_module;');
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

function getModules() { // Permet de récuperer tous les modules dans un tableau
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT * FROM modules;');
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

function getGroups() { // Permet de récuperer tous les groupes dans un tableau
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT * FROM groups;');
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

function getRanks() { // Permet de récuperer tous les rangs dans un tableau
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT * FROM ranks ORDER BY name ASC;');
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

function errorsSQL($error, $requete) { // Permet d'afficher l'erreur SQL survenue
    global $page_content;
    global $adminMails;
    if (!$error) { // S'il y a eu une erreur sur la requete d'insertion, on récupère ses infos
                $erreurs = $requete->errorInfo(); // Récupère les infos sur l'erreur dans le but de les envoyer au développeur
                $error = $erreurs[1]; // Enregistre le numéro d'erreur dans la variable erreur
            }
            switch ($error) { // Suivant le code d'erreur de l'erreur, on affiche le message correspondant
                case 1062:
                    $page_content .= '<span class="sql_erreur">Le nom d\'utilisateur ou l\'email est déjà utilisé</span>';
                    break;
                case 'champs':
                    $page_content .= '<span class="sql_erreur">Merci de remplir tous les champs !</span>';
                    break;
                case 'mail':
                    $page_content .= '<span class="sql_erreur">Echec lors de l\'envoi du mail !</span>';
                    break;
                default:
                    $page_content .= '<span class="sql_erreur">Erreur inconnue !</span><br />L\'erreur vient d\'être rapportée au développeur !';
                    $adminMails->reportSQLError($errors);
                    break;
            }
}

?>