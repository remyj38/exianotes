<?php
if (isset($_POST['passwd'])) {
    if ($auth->login($user, $passwd, $cookie))  {
        header('Location: ../');
    } else {
        afficher_login(1);
    }
} else {
    afficher_login();
}

?>