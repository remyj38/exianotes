<?php
if (isset($_POST['passwd'])) {
    if ($auth->login($user, $passwd, $cookie))  {
        header('Location: ../');
    } else {
        $page_content .= afficher_login(1);
    }
} else {
    $page_content .= afficher_login();
}

?>