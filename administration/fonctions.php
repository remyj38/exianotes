<?php

/* Récupération pour affichage */

function getMembers($id) { // Récupère les membres d'un groupe
    $i = 0;
    $bdd = get_db_connexion(); // Ouverture de la connexion avec la base de donnée
    $requete = $bdd->prepare('SELECT id_user, email, name, firstName FROM users WHERE groups = :id'); // Préparation de la requête
    $requete->execute(array("id" => $id)); // Execution de la requête
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

function getModules() { // Permet de récuperer tous les modules dans un tableau
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT id_module, name FROM modules;'); // Préparation de la requete
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

function getGroups() { // Permet de récuperer tous les groupes dans un tableau
    $i = 0;
    $bdd = get_db_connexion(); //Ouverture de la connexion
    $requete = $bdd->query('SELECT id_group, name FROM groups;'); // Préparation de la requete
    while ($temp = $requete->fetch()) { // Pour chaque ligne de résultats, on stocke le groupe
        $datas[$i] = $temp;
        $i++;
    }
    if (isset($datas)) { // Si des données ont été récupérées, on les retourne
        return $datas;
    } else { // Sinon, on ne retourne rien
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
    if (isset($datas)) { // Si des rangs sont présents, on les retourne
        return $datas;
    } else { // Sinon, on ne retourne rien
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
            $page_content .= '<span class="box error">Le nom d\'utilisateur ou l\'email est déjà utilisé</span>';
            break;
        case 'champs':
            $page_content .= '<span class="box warning">Merci de remplir tous les champs !</span>';
            break;
        case 'mail':
            $page_content .= '<span class="box error">Echec lors de l\'envoi du mail !</span>';
            break;
        default:
            $page_content .= '<span class="box error">Erreur inconnue !</span><br />L\'erreur vient d\'être rapportée au développeur et ne devrait plus être présente d\'ici peut !';
            $adminMails->reportSQLError($erreurs);
            break;
    }
}
