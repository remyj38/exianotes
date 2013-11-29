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
                setcookie('user', $donnees['user']);
                setcookie('passwd', $passwd);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function restore_session() {
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

    public function __construct() {
        $this->restore_session();
    }

}
?>

