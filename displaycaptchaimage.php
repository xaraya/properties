<?php
    include_once('class/securimage/securimage.php');
    $img = new securimage();
    $img->show(); // alternate use:  $img->show('/path/to/background.jpg');
    exit();
?>