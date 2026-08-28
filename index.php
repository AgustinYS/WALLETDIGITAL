<?php
require 'config.php';
header('Location: ' . (usuarioLogueado() ? 'dashboard.php' : 'login.php'));
exit;
