<?php

class auth {

    public function __construct() {
        if (!isset($_SESSION['Auth'])) {
            $this->restore_session();
        }
    }

    public function crypt($passwd, $restore = FALSE) { // Crypte le mot de passe entré
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
        if (!isset($_SESSION['Auth']['groupName'])) { // Si l'id du groupe est défini dans la session
            $bdd = get_db_connexion();
            $requete = $bdd->prepare("SELECT * FROM groups WHERE id_group = :id_group"); // récupération du nom correspontant à l'id
            $requete->execute(array(
                "id_group" => $_SESSION['Auth']['groups']
            ));
            $donnees = $requete->fetch();
            unset($bdd);
            if (isset($donnees['name'])) { // Si le nom n'est pas null
                $_SESSION['Auth']['groupName'] = $donnees['name']; // On le garde dans la session
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function login($user, $passwd, $cookie = 0, $restore = FALSE) { //loggue l'utilisateur avec email ou nom de compte et genere les cookies
        global $site;
        $bdd = get_db_connexion();
        $requete = $bdd->prepare('SELECT id, user, email, theme, rank, groups, name, firstname FROM users WHERE (user = :user OR email = :email) AND passwd = :passwd'); // Récupération des infos sur l'utilisateur en utilisant le pseudo ou le mail
        $requete->execute(array(
            'user' => $user,
            'email' => $user,
            'passwd' => $this->crypt($passwd)
        ));
        $donnees = $requete->fetch();
        if (isset($donnees)) { // Récupération de la première ligne
            $return = TRUE;
            $_SESSION['Auth'] = $donnees; // On garde les données récoltées dans la session
            register_ip($this->getUser()); // On enregistre l'ip du visiteur
            if ($cookie && !$restore) { // Si on a demandée la connexion avec cookies, on les enregistre
                $cookie_user = setcookie('user', $donnees['user'], time() + 31536000, $site['installDir']);
                $cookie_passwd = setcookie('passwd', $donnees['passwd'], time() + 31536000, $site['installDir']);
                if (!$cookie_user || !$cookie_passwd) {
                    $return = FALSE;
                }
            }
            $_SESSION['Auth']['rankName'] = $this->getRankName($donnees['rank']);
            if (!$this->get_groupName()) { // On verifie que l'utilisateur est bien loggé (en récupérant le nom de son groupe
                $return = FALSE;
            }
        } else {
            $return = FALSE;
        }
        unset($bdd);
        return $return;
    }

    public function restore_session() { // réstore la session a partir des cookies
        if (isset($_COOKIE['user']) && isset($_COOKIE['passwd']) && !isset($_SESSION['Auth'])) { // Si les cookies sont présents et si l'utilisateur n'est pas encore loggé
            if ($this->login($_COOKIE['user'], $_COOKIE['passwd'], true, true)) { // On fait un loggin a partir des cookies
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function logout() { // Déconnecte l'utilisateur
        global $site;
        session_destroy(); // On détruit la session
        if (isset($_COOKIE['user']) && isset($_COOKIE['passwd'])) { //Si les cookies sont définis, on les supprime
            $user = setcookie('user', NULL, -1, $site['installDir']);
            $passwd = setcookie('passwd', NULL, -1, $site['installDir']);
            if (!$user || !$passwd) { // Si la suppression a échouée, retourne faux
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

    private function getRankName($id) { // Permet de récuperer le nom du rang
        $bdd = get_db_connexion(); //Ouvre la connexion à la base de donnée
        $requete = $bdd->prepare('SELECT name FROM rank WHERE id_rank = :id');
        $requete->execute(array("id" => $id));
        $donnee = $requete->fetch();
        unset($bdd);
        if (isset($donnee['name'])) {
            return $donnee['name'];
        } else {
            return "<span class=\"rank_error\">Rang Inconnu !</span>";
        }
    }

}
?>

