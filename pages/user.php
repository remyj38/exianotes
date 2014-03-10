<?php

$title = 'Utilisateurs';
$page_content .= 'Attention, page faite à l\'arrache seulement pour avoir une création de compte qui marche !<br/><br/>';
if (isset($_POST['passwd'], $_POST['confirm'])) {
    if ($_POST['passwd'] == $_POST['confirm']) {
        $bdd = get_db_connexion(); // Connexion à la base de donnée
        $requete = $bdd->prepare("UPDATE users SET passwd = :passwd, reinit_passwd = NULL WHERE reinit_passwd = :id"); // Préparation de la requête
        $erreur = $requete->execute(array('id' => $_SESSION['changepasswd'], 'passwd' => $auth->crypt($_POST['passwd'])));
        if ($erreur) {
            $page_content .= 'mot de passe changé avec succès !';
            unset($_SESSION['changepasswd']);
        } else {
            errorsSQL($erreur, $requete);
        }
    } else {
        $page_content .= 'Les MDP ne correspondent pas !';
    }
} else {
    $_SESSION['changepasswd'] = $argumentsUrl['changepassword'];
    $page_content .= '<form method="post" action="./"><label>Nouveau mot de passe :<input type="password" name="passwd" required/></label><br /><label>Confirmation :<input type="password" name="confirm" selected required/></label><br/><input type="submit" value="changer le mot de passe" /></form>';
}
