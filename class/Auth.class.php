<?php

class auth {

    private function crypt($passwd, $restore = FALSE) { // Crypte le mot de passe entré
        if ($restore) {
            return $passwd;
        } else {
            return sha1(md5($passwd));
        }
    }

    public function getUser() { // Récupère le nom d'utilisateur
        if (isset($_SESSION['Auth']['user'])) {
            return $_SESSION['Auth']['user'];
        } else {
            return "Invite";
        }
    }

    private function get_groupName() { // Récupère le nom du groupe de l'utilisateur
        if (!isset($_SESSION['Auth']['groupName'])) {
            $bdd = get_db_connexion();
            $requete = $bdd->prepare("SELECT * FROM groups WHERE id_group = :id_group");
            $requete->execute(array(
                "id_group" => $_SESSION['Auth']['groups']
            ));
            $donnees = $requete->fetch();
            if (isset($donnees['name'])) {
                $_SESSION['Auth']['groupName'] = $donnees['name'];
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function login($user, $passwd, $cookie = 0, $restore = FALSE) { //loggue l'utilisateur avec email ou nom de compte et genere les cookies
        $bdd = get_db_connexion();
        $requete = $bdd->prepare('SELECT * FROM users WHERE user = :user OR email = :email');
        $requete->execute(array(
            'user' => $user,
            'email' => $user
        ));
        $donnees = $requete->fetch();
        if ($donnees['passwd'] == $this->crypt($passwd, $restore)) {
            $return = TRUE;
            $_SESSION['Auth'] = $donnees;
            register_ip($this->getUser());
            if ($cookie) {
                $cookie_user = setcookie('user', $donnees['user'], time()+31536000, ROOT_DIR);
                $cookie_passwd = setcookie('passwd', $passwd, time()+31536000, ROOT_DIR);
                if (!$cookie_user || !$cookie_passwd) {
                    $return = FALSE;
                }
            }
            if (!$this->get_groupName()) {
                $return = FALSE;
            }
        } else {
            $return = FALSE;
        }
        return $return;
    }

    public function restore_session() { // réstore la session a partir des cookies
        if (isset($_COOKIE['user']) && isset($_COOKIE['passwd']) && !isset($_SESSION['Auth'])) {
            if ($this->login($_COOKIE['user'], $_COOKIE["passwd"], true, true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function logout() {
        unset($_SESSION['Auth']);
        if (isset($_COOKIE['user']) || isset($_COOKIE['passwd'])) {
            $user = setcookie("user", NULL, -1);
            $passwd = setcookie("passwd", NULL, -1);
            if (!$user || !$passwd) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function isAdmin() { // Retourne 1 si l'utilisateur est administrateur
        if ($_SESSION['Auth']['rank'] == 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function __construct() {
        if (!isset($_SESSION['Auth'])) {
            $this->restore_session();
        }
    }

}
?>

