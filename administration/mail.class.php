<?php

class mail {

    private $expediteur;

    public function __construct() {
        global $site;
        $this->expediteur = $site['name'] . "<" . $site['admin_email'] . ">";
    }

    public function mailAdd($user) {
        global $site;
        $sujetMail = "Nouveau compte sur " . $site['name']; // Initialise le sujet du mail
        $corpsMail = "Bonjour " . $user['Uname'] . " " . $user['firstName'] . "<br />
Un compte a été créé pour vous sur le site " . $site['name'] . ".<br />
Afin de choisir votre mot de passe et valider votre compte, merci de cliquer sur le lien ci-dessous :<br />
" . ROOT_DIR . "user/changepassword/" . $user['reinit_passwd'] . "<br />
    <br />
À bientôt sur " . $site['name'] . ".<br />
<br />
(Ceci est un mail automatique, merci de ne pas y répondre)"; // Corps du message envoyé
        $headersmail = "From: " . $this->expediteur . "\r\n"; // Expéditeur du message
        $headersmail .= "Content-Type: text/html; charset=utf-8 \r\n"; // Type de mail (html)
        $headersmail .= "MIME-Version: 1.0 ";
        return mail($user['email'], $sujetMail, $corpsMail, $headersmail); // Envoi du mail
    }
    public function mailReinitPasswd($infos) {
        global $site;
        $sujetMail = "Réinitialisation de votre mot de passe"; // Initialise le sujet du mail
        $corpsMail = "Bonjour " . $infos['name'] . " " . $infos['firstName'] . "<br />
Un administrateur de " . $site['name'] . " a demandé une réinitialisation de mot de passe pour votre compte.<br />
Afin de choisir votre nouveau mot de passe et pouvoir de nouveau vous connecter sur votre compte, merci de cliquer sur le lien ci-dessous :<br />
" . ROOT_DIR . "user/changepassword/" . $infos['reinit_passwd'] . "<br />
    <br />
À bientôt sur " . $site['name'] . ".<br />
<br />
(Ceci est un mail automatique, merci de ne pas y répondre)"; // Corps du message envoyé
        $headersmail = "From: " . $this->expediteur . "\r\n"; // Expéditeur du message
        $headersmail .= "Content-Type: text/html; charset=utf-8 \r\n"; // Type de mail (html)
        $headersmail .= "MIME-Version: 1.0 ";
        return mail($user['email'], $sujetMail, $corpsMail, $headersmail); // Envoi du mail
    }
    
    
    
    
    
    public function reportSQLError($erreurInfos) { // Email envoyé lorsqu'une erreur sql inconnue est détectée
        $destinataire = 'remy.jacquin@viacesi.fr';
        $sujetMail = "Nouvelle erreur SQL détectée !"; // Initialise le sujet du mail
        $corpsMail = "Un nouvel id d'erreur SQL vient d'apparaitre : " . $erreurInfos[1] . "<br>
Informations sur l'erreur :<br>
" . print_r($erreurInfos, true); // Corps du message envoyé
        $headersmail = "From: " . $this->expediteur . "\r\n"; // Expéditeur du message
        $headersmail .= 'Content-Type: text/html; charset=utf-8 \r\n'; // Type de mail (html)
        $headersmail .= "MIME-Version: 1.0 ";
        return mail($destinataire, $sujetMail, $corpsMail, $headersmail); // Envoi du mail
    }

}
