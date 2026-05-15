<?php

// logout
session_start();
session_destroy();
header('Location: /smart-water-billing/views/login.php');
exit;
