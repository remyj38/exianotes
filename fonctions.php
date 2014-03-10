<?php

function get_db_connexion() { //Connexion à la bdd
    try { // Essai de connexion à la BDD
        $bdd = new PDO(DB_DNS, DB_USER, DB_PASSWD);
        return $bdd; // Si connexion réussie, retour du PDO
    } catch (Exception $e) { // Sinon, affichage d'un message d'erreur
        $site['name'] = "Erreur";
        $title = "Echec de connexion à la base de donnée !";
        include 'header.php';
        echo "<center>Connexion à la base de donnée impossible</center>";
        include 'footer.php';
        exit(); // Et arret du script
    }
}

function test_sql() { //test de la connexion à la bdd
    get_db_connexion();
}

function init_classes() { // Charges toutes les classes présentes dans le dossier class
    $dir_nom = './classes';
    $dir = opendir($dir_nom);
    $fichier = array(); // on déclare le tableau contenant le nom des fichiers

    while ($element = readdir($dir)) { //pour chaque élément du tableau
        if ($element != '.' && $element != '..') { // Si l'élément n'est pas le répertoire courrant ou le parent
            if (!is_dir($dir_nom . '/' . $element)) { // Si l'élément est un fichier
                require_once $dir_nom . '/' . $element; // On l'inclu au script
            }
        }
    }
    closedir($dir); // On ferme le répertoire
}

function getArgumentsUrl() { // Récupère les arguments dans l'url
    if (isset($_GET["url"])) { // Si il y a un parametre d'url
        if ($_GET['url'] != NULL) { // Si les arguments de sont pas nuls
            $temp = explode("/", $_GET["url"]); // On explose le parametre
            if (count($temp) == 1 || $temp[1] == NULL) { // Si le tableau ne contient qu'un seul élément, on le retourne dans l'index page
                $urls['page'] = $temp[0];
            } else { // Sinon ...
                $start = 0;
                if (!isset($urls['page']) && file_exists('pages/' . $temp[0] . '.php')) {
                    $urls['page'] = $temp[0];
                    $start = 1;
                }
                for ($i = $start; $i < count($temp); $i+=2) { // Pour chaque élément du tableau
                    if (isset($temp[$i]) && isset($temp[$i + 1])) { // Si l'élément est suivi d'un autre
                        $param = strtolower($temp[$i]);
                        $valeur = strtolower($temp[$i + 1]);
                        $urls[$param] = $valeur; // On garde les deux dans le tableau
                    }
                }
            }
            if (isset($urls['admin']) && !isset($urls['page'])) {
                $urls['page'] = 'admin';
            }
        } else { // Sinon, on ne retourne rien
            return NULL;
        }
    } else { // Sinon, on ne retourne rien
        return NULL;
    }
    return $urls; // On retourne le tableau créé
}

function init_theme() {
    global $template;
    if (isset($_SESSION['Auth']['theme_dir'])) { // Si l'utilisateur est connecté, on affiche sont thème
        $template['themedir'] = $_SESSION['Auth']['theme_dir'];
    } else { // Sinon, on affiche le thème par défaut
        $template['themedir'] = 'default';
    }
}

function errors($id) { // Affiche les erreurs suivant le type
    global $title;
    global $page_content;
    switch ($id) { // Suivant l'id de l'erreur, on retourne le message d'erreur
        case 403 :
            $page_content = "<span class=\"box error\">Accès refusé !</span>";
            break;
        case 404 :
            $page_content = "<span class=\"box warning\">Page introuvable :(</span>";
            break;
        default :
            $page_content = "<span class=\"box error\">Erreur inconnue, merci de contacter le webmaster !</span>";
    }
    $title = "Erreur " . $id;
    return 1;
}

function get_ip() {
    // IP si internet partagé
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // IP derrière un proxy
    elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    // Sinon : IP normale
    else {
        return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    }
}

function register_ip() { // Sauvegarde l'ip utilisé à la connexion
    $bdd = get_db_connexion(); // ouverture de la connexion à la base de données
    $ip = get_ip(); // Récupération de l'ip
    $connexion = $bdd->prepare('INSERT INTO ips (user, ip, host_name) VALUES (:user, :ip, :host);'); // Insertion dans la BDD de l'ip
    $connexion->execute(array(
        'user' => $_SESSION['Auth']['id_user'],
        'ip' => $ip,
        'host' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
    ));
    $connexion->closeCursor();
    deleteIps(); // Suppression des ips en trop
}

function deleteIps() { // Supprime les ips en trop (20 ips gardées)
    $bdd = get_db_connexion(); // Ouverture de la connexion à la BDD
    $connexion = $bdd->prepare('SELECT id_ip FROM ips WHERE user = :user ORDER BY id_ip ASC;'); // Préparation de la requete de selection des ips
    $connexion->execute(array('user' => $_SESSION['Auth']['id_user'])); // Execution de la requete
    $i = 0;
    while ($donnee = $connexion->fetch()) { // On stoque les id d'ips de facon simple
        $ips[$i] = $donnee['id_ip'];
        $i++;
    }
    $connexion->closeCursor();
    for ($i = 0; $i < (count($ips) - 20); $i++) { // Pour chaque ip qui n'est pas dans les 20 dernieres, on la supprime
        $connexion = $bdd->prepare('DELETE FROM ips WHERE id_ip = :id'); // Préparation de la requete de selection de l'ip
        $connexion->execute(array('id' => $ips[$i])); // Execution de la requete
    }
}

function afficher_login($erreur = FALSE) { // Affiche le formulaire de login
    global $page_content;
    if ($erreur) { // En cas d'echec d'authentification, on affiche qu'il y a une erreur
        $page_content.= '<span class="box error">Echec d\'authentification.<br>Merci de r&eacute;essayer !</span>';
    }
    $page_content.= '<center>Merci de vous authentifier :';

    $page_content.= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
    <table id="login">
        <tr>
            <td>
                <label for ="user">Nom d\'utilisateur<br>ou email</label>
            </td><td>
                <input name="user" type="text" required />
            </td>
        </tr>
        <tr>
            <td>
                <label for="passwd">Mot de passe</label>
            </td><td>
                <input name="passwd" type="password" required />
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <input name="cookie" id="cookie" type="checkbox" checked/> <label for="cookie">Rester connecter</label>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <input type="submit" value="Se Connecter" />
            </td>
        </tr>
    </table>
</form></center>';
}

function getSiteInfos() { // Récupération des infos sur le site (nom, mail du webmaster, ...)
    $bdd = get_db_connexion(); // Connexion à la base de données
    $result = $bdd->query("SELECT * FROM infos"); // Récupération des infos
    $datas = $result->fetch();
    return $datas; // Retour des infos
}

function generateReinitKey($user) { // Génère une clé de réinitialisation aléatoirement
    $user .= uniqid(); // Concaténation d'une clé unique avec le nom d'utilisateur (pour être sur de n'avoir pas de doublons)
    return md5($user); // Retour de la clé hashée en MD5
}

?>