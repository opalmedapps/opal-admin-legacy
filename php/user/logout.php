<?php
    /* Simple logout script */ 

    session_start();
    session_destroy(); // Remove session

    header("Location: ".FRONTEND_REL_URL."main.php#/login"); // Redirect page
?>
