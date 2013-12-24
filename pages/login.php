<?php

$title = "Login"; // Titre de la page
if (isset($_POST['passwd'])) { // Si l'utilisateur a rempli le formulaire
    if ($auth->login($_POST['user'], $_POST['passwd'], $_POST['cookie'])) { // Si le login a marché, on redirige vers l'accueil
        header('Location: ' . ROOT_DIR);
    } else { // Sinon, on réaffiche le formulaire avec l'erreur
        afficher_login(TRUE);
    }
} else { // Sinon,
    if (isset($_SESSION['Auth']['user'])) { // Si l'utilisateur est déjà loggé, redirection vers l'accueil
        header('Location: ' . ROOT_DIR);
    } else { // Sinon, affichage du formulaire de login
        afficher_login();
    }
}
?>