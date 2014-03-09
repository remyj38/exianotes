        </div>
        <div id="popups">
            <?php
    echo $popups;
    ?>
        </div>
        <script type="text/javascript" src="<?php echo ROOT_DIR; ?>js/jquery.js" language="javascript"></script>
        <script type="text/javascript" src="<?php echo ROOT_DIR; ?>js/script.js" language="javascript"></script>
<?php if (isset($adminCssOn)) { echo '<script type="text/javascript" src="'.ROOT_DIR.'js/admin_scripts.js" language="javascript"></script>';}?>
    </body>
</html>
