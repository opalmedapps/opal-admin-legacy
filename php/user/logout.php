<?php
    /* Simple logout script */ 
    include_once('user.inc');

    header("Location: ".FRONTEND_REL_URL."main.php#/"); // Redirect page
?>
