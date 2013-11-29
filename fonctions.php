<?php

function get_db_connexion() { //Connexion à la bdd
    try {
        $bdd = new PDO(DB_DNS, DB_USER, DB_PASSWD);
        return $bdd;
    } catch (Exception $e) {

        return "failed";
    }
}

function test_sql() { //test de la connexion à la bdd
    if (get_db_connexion() == "failed") {
        include 'header.php';
        echo "<center>Connexion à la base de donnée impossible</center>";
        include 'footer.php';
        exit();
    }
}

function init_classes() {
    $dir_nom = './class';
    $dir = opendir($dir_nom);
    $fichier = array(); // on déclare le tableau contenant le nom des fichiers

    while ($element = readdir($dir)) {
        if ($element != '.' && $element != '..') {
            if (!is_dir($dir_nom . '/' . $element)) {
                require_once './class/' . $element;
            }
        }
    }

    closedir($dir);
}

function page() {
    $option = option();
    $valeur = valeur();
    if (($option != null) && ($valeur != null)) {


        if ($option == erreur) {
            $page = $remotedir . 'pagesErreurs/' . $valeur . '.php';
        }
        if ($option == page) {
            $page = $remotedir . 'pages/' . $valeur . '.php';
        }
        if ($option == action) {
            $page = $remotedir . 'actions/' . $valeur . '.php';
        }
        if (!file_exists($page)) {
            $page = $remotedir . 'pageserreur/404.php';
        }
        return $page;
    } else {
        return 0;
    }
}

function option() {
    $URL = $_SERVER['REQUEST_URI'];
    $optionpart1 = strlen(substr($URL, 0, -strlen(stristr($URL, '?'))) . '?');
    $optionpart2 = strlen($URL) - strlen(substr($URL, 0, -strlen(stristr($URL, '='))));
    $option = substr($URL, $optionpart1, -$optionpart2);
    return $option;
}

function valeur() {
    $URL = $_SERVER['REQUEST_URI'];
    $valeur = strlen($URL) - strlen(stristr($URL, '=')) + 1;
    $valeur = substr($URL, $valeur);
    return $valeur;
}

function erreurs($id) {
    switch ($id) {
        case 403 :
            echo "Accès refusé !";
            break;
        case 404 :
            echo "Page introuvable :(";
            break;
    }
    return 1;
}

function register_ip($bdd, $auth) {
    if (!isset($_SESSION['new'])) {
        $bdd = get_db_connexion();
        $connexion = $bdd->prepare('INSERT INTO ip(user, time, ip) VALUES (:user, :time, :ip)');
        $connexion->execute(array(
            'user' => $auth->getUser(),
            'time' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ));
        $_SESSION['new'] = true;
    }
}

function afficher_login($erreur = 0) {
    echo '<center>Merci de vous authentifier :';
    if ($erreur) {
        echo '<span class="login_erreur">Echec d\'authentification.<br>Merci de réessayer !</span>';
    }
    echo '<form action="' . ROOT_DIR . '" method="post">';
    echo '
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
                <input name="passwd" type="passwd" required />
            </td>
        </tr>
    </table>
</form></center>';

}
?>