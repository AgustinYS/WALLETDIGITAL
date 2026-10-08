
<?php
require_once 'config.php';

header('Location: ' . (usuarioLogueado() ? 'dashboard.php' : 'login.php'));
exit;
