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

function init_classes() { // Charges toutes les classes présentes dans le dossier class
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

function getArgumentsUrl() { // Récupère les arguments dans l'url
    if (isset($_GET["url"])) {
        $temp = explode("/", $_GET["url"]);
        for ($i = 0; $i < count($temp); $i+=2) {
            if ($temp[$i] != "") {
                $param = strtolower($temp[$i]);
                $valeur = strtolower($temp[$i + 1]);
                $urls[$param] = $valeur;
            }
        }
    } else {
        $urls = NULL;
    }
    return $urls;
}

function option() { // Récupère le premier argument dans l'url
    $URL = $_SERVER['REQUEST_URI'];
    $optionpart1 = strlen(substr($URL, 0, -strlen(stristr($URL, '?'))) . '?');
    $optionpart2 = strlen($URL) - strlen(substr($URL, 0, -strlen(stristr($URL, '='))));
    $option = substr($URL, $optionpart1, -$optionpart2);
    return $option;
}

function valeur() { // Recupère la valeur du premier argument de l'url
    $URL = $_SERVER['REQUEST_URI'];
    $valeur = strlen($URL) - strlen(stristr($URL, '=')) + 1;
    $valeur = substr($URL, $valeur);
    return $valeur;
}

function errors($id) { // Affiche les erreurs suivant le type
    global $title;
    switch ($id) {
        case 403 :
            echo "Accès refusé !";
            break;
        case 404 :
            echo "Page introuvable :(";
            break;
    }
    $title = "Erreur " . $id;
    return 1;
}

function register_ip($user) { // Sauvegarde l'ip utilisé à la connexion
    $bdd = get_db_connexion();
    $connexion = $bdd->prepare('INSERT INTO ip(user, ip) VALUES (:user, :ip)');
    $connexion->execute(array(
        'user' => $user,
        'ip' => $_SERVER['REMOTE_ADDR']
    ));
}

function afficher_login($erreur = FALSE) { // Affiche le formulaire de login
    global $page_content;
    $page_content.= '<center>Merci de vous authentifier :';
    if ($erreur) {
        $page_content.= '<span class="login_erreur">Echec d\'authentification.<br>Merci de r&eacute;essayer !</span>';
    }
    $page_content.= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
    $page_content.= '
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

function getSiteInfos() {
    $bdd = get_db_connexion();
    $result = $bdd->query("SELECT * FROM infos");
    $datas = $result->fetch();
    return $datas;
}
?>