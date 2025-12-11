<?php
/**
 * Logout
 * Menghancurkan session dan redirect ke halaman login
 */
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();

// Unset all session variables
$_SESSION = [];

// If session uses cookies, remove the cookie
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destroy the session data on the server
@session_destroy();

header('Location: login.php');
exit;
