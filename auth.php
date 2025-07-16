<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Regenera o ID da sessão para evitar fixação de sessão
if (!isset($_SESSION["initiated"])) {
    session_regenerate_id(true);
    $_SESSION["initiated"] = true;
}

function isAdmin() {
    return isset($_SESSION["usuario_nivel"]) && $_SESSION["usuario_nivel"] === "admin";
}
?>