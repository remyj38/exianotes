<?php

class auth {

    private function crypt($passwd) {
        return sha1(md5($passwd));
    }

    public function getUser() {
        if (isset($_SESSION['Auth']['user'])) {
            return $_SESSION['Auth']['user'];
        } else {
            return "Invité";
        }
    }

    public function login($user, $passwd, $cookie) { //loggue l'utilisateur avec email ou nom de compte et genere les cookies
        $bdd = get_db_connexion();
        $requete = $bdd->prepare("SELECT INTO users WHEN user = :user OR email = :email");
        $requete->execute(array(
            "user" => $user,
            "email" => $user
        ));
        $donnees = $requete->fetch();
        if ($donnees['passwd'] == $this->crypt($passwd)) {
            $_SESSION['Auth'] = $donnees;
            if ($cookie) {
                $cookie_user = setcookie('user', $donnees['user']);
                $cookie_passwd = setcookie('passwd', $passwd);
            }
            if (!$cookie_user && !$cookie_passwd) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public function restore_session() {
        if (isset($_COOKIE['user']) && isset($_COOKIE['passwd']) && !isset($_SESSION['Auth'])) {
            if ($this->login($_COOKIE['user'], $_COOKIE["passwd"], true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function logout() {
        $_SESSION['auth'] = NULL;
        if (isset($_COOKIE['user']) && isset($_COOKIE['passwd'])) {
            $user = setcookie("user", NULL, -1);
            $passwd = setcookie("passwd", NULL, -1);
        }
        if ($user && $passwd) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function __construct() {
        $this->restore_session();
    }

}
?>

