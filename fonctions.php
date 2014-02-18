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
    $dir_nom = './class';
    $dir = opendir($dir_nom);
    $fichier = array(); // on déclare le tableau contenant le nom des fichiers

    while ($element = readdir($dir)) { //pour chaque élément du tableau
        if ($element != '.' && $element != '..') { // Si l'élément n'est pas le répertoire courrant ou le parent
            if (!is_dir($dir_nom . '/' . $element)) { // Si l'élément est un fichier
                require_once './class/' . $element; // On l'inclu au script
            }
        }
    }

    closedir($dir); // On ferme le répertoire
}

function getArgumentsUrl() { // Récupère les arguments dans l'url
    if (isset($_GET["url"])) { // Si il y a un parametre d'url
        $temp = explode("/", $_GET["url"]); // On explose le parametre
        if (count($temp) == 1) { // Si le tableau ne contient qu'un seul élément, on le retourne dans l'index page
            $urls['page'] = $temp[0];
        } else { // Sinon ...
            for ($i = 0; $i < count($temp); $i+=2) { // Pour chaque élément du tableau
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
        if (!isset($urls)) { // Si le tableau est vide, on retourne une page 404
            return 404;
        }
    } else { // Sinon, on ne retourne rien
        return NULL;
    }
    return $urls; // On retourne le tableau créé
}

function init_theme(){
    global $template;
    if (isset($_SESSION['Auth']['theme_dir'])) {
        $template['themedir']=$_SESSION['Auth']['theme_dir'];
    } else {
        $template['themedir']='default';
    }
}

function errors($id) { // Affiche les erreurs suivant le type
    global $title;
    global $page_content;
    switch ($id) { // Suivant l'id de l'erreur, on retourne le message d'erreur
        case 403 :
            $page_content = "Accès refusé !";
            break;
        case 404 :
            $page_content = "Page introuvable :(";
            break;
        default :
            $page_content = "Erreur inconnue, merci de contacter le webmaster !";
    }
    $title = "Erreur " . $id;
    return 1;
}

function register_ip() { // Sauvegarde l'ip utilisé à la connexion
    $bdd = get_db_connexion();
    $connexion = $bdd->prepare('INSERT INTO ips (user, ip) VALUES (:user, :ip)'); // Insertion dans la BDD de l'ip
    $connexion->execute(array(
        'user' => $_SESSION['Auth']['id_user'],
        'ip' => $_SERVER['REMOTE_ADDR']
    ));
}

function afficher_login($erreur = FALSE) { // Affiche le formulaire de login
    global $page_content;
    global $template;
    if ($erreur) { // En cas d'echec d'authentification, on affiche qu'il y a une erreur
        $page_content.= '<span class="login_erreur">Echec d\'authentification.<br>Merci de r&eacute;essayer !</span>';
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
    $bdd = get_db_connexion();
    $result = $bdd->query("SELECT * FROM infos");
    $datas = $result->fetch();
    return $datas;
}

function generateReinitKey($user) { // Génère une clé de réinitialisation de mot de passe aléatoirement
    $user .= uniqid();
    return md5($user);
}

?>