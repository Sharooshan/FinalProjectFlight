<?php
session_start();
session_destroy();
header("Location: /intelliflight/public/index.php");
exit();
