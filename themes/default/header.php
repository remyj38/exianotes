<!DOCTYPE HTML>
<html>
    <head>
        <title><?php echo $site['name'] . " | " . $title; ?></title> <!-- Affichage sous forme nom du site | nom de la page !-->
        <link rel="stylesheet" href="<?php echo ROOT_DIR . 'themes/' . $template['themedir']; ?>/style.css" type="text/css" />
        <?php
        if (isset($adminCssOn)) {
            echo '<link rel="stylesheet" href="' . ROOT_DIR . 'themes/administration.css" type="text/css" />';
        }
        ?>
    </head>
    <body>
        <div id="page">