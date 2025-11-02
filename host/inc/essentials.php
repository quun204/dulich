<?php
require_once __DIR__ . '/../../admin/inc/essentials.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function hostLogin()
{
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        redirect('../index.php');
    }

    if (empty($_SESSION['isHost']) && ($_SESSION['hostStatus'] ?? null) === 'approved') {
        $_SESSION['isHost'] = 1;
    }

    if (empty($_SESSION['isHost'])) {
        redirect('../index.php');
    }
}

function hostLoginAjax()
{
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        echo 'invalid_session';
        exit;
    }

    if (empty($_SESSION['isHost']) && ($_SESSION['hostStatus'] ?? null) === 'approved') {
        $_SESSION['isHost'] = 1;
    }

    if (empty($_SESSION['isHost'])) {
        echo 'invalid_session';
        exit;
    }
}

?>
