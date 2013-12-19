<?php

class auth {

    private function crypt($passwd) { // Crypte le mot de passe entré
        return sha1(md5($passwd));
    }

    public function getUser() { // Récupère le nom d'utilisateur
        if (isset($_SESSION['Auth']['user'])) {
            return $_SESSION['Auth']['user'];
        } else {
            return "Invité";
        }
    }
    private function get_groupName() { // Récupère le nom du groupe de l'utilisateur
        if (!isset($_SESSION['Auth']['groupName'])) {
            $bdd = get_db_connexion();
            $requete = $bdd->prepare("SELECT INTO group WHEN id_group = :group");
            $requete->execute(array (
                "group" => $_SESSION['Auth']['group']
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

    public function login($user, $passwd, $cookie) { //loggue l'utilisateur avec email ou nom de compte et genere les cookies
        $bdd = get_db_connexion();
        $requete = $bdd->prepare("SELECT INTO users WHEN user = :user OR email = :email");
        $requete->execute(array(
            "user" => $user,
            "email" => $user
        ));
        $donnees = $requete->fetch();
        if ($donnees['passwd'] == $this->crypt($passwd)) {
            $return = TRUE;
            $_SESSION['Auth'] = $donnees;
            register_ip($this->getUser());
            if ($cookie) {
                $cookie_user = setcookie('user', $donnees['user']);
                $cookie_passwd = setcookie('passwd', $passwd);
            }
            if (!$cookie_user && !$cookie_passwd) {
                $return = FALSE;
            } else {
                $return = TRUE;
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

