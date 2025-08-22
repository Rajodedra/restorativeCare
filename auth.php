<?php
// auth.php — Centralized session, CSRF & role enforcement

/* ----------------- Session cookie hardening (set BEFORE session_start) ----------------- */
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ----------------- One-time session initialization ----------------- */
if (!isset($_SESSION['__initialized'])) {
    session_regenerate_id(true);
    $_SESSION['__initialized'] = time();
    $_SESSION['__nonce'] = bin2hex(random_bytes(16));
}

/* ----------------- Settings ----------------- */
const SESSION_IDLE_TIMEOUT = 1800; // 30 minutes
const SESSION_REGEN_INTERVAL = 600; // 10 minutes

/* ----------------- Utilities ----------------- */
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(){
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check($t){
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t);
}

/* ----------------- Session guards ----------------- */
function ensureSessionFreshness() {
    // Idle timeout
    $now = time();
    $last = $_SESSION['__last_active'] ?? $now;
    if (($now - $last) > SESSION_IDLE_TIMEOUT) {
        logout(true);
    }
    $_SESSION['__last_active'] = $now;

    // Periodic regeneration
    $lastReg = $_SESSION['__last_regen'] ?? 0;
    if (($now - $lastReg) > SESSION_REGEN_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['__last_regen'] = $now;
    }

    // Bind to IP + UA (lightweight)
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
    if (!isset($_SESSION['__ip'])) { $_SESSION['__ip'] = $ip; }
    if (!isset($_SESSION['__ua'])) { $_SESSION['__ua'] = $ua; }

    if ($_SESSION['__ip'] !== $ip || $_SESSION['__ua'] !== $ua) {
        logout(true);
    }
}

function currentUser(){ return $_SESSION['user'] ?? null; }

function requireLogin(){
    ensureSessionFreshness();
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles){
    requireLogin();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, (array)$roles, true)) {
        http_response_code(403);
        echo "⛔ Access denied for role: ".esc($role);
        exit;
    }
}

function isAdmin(){
    $u = currentUser();
    if (!$u) return false;
    return in_array($u['role'], ['admin','superadmin'], true);
}

function redirectForRole($role){
    if (in_array($role, ['admin','superadmin'], true)) {
        header('Location: admin.php'); exit;
    }
    header('Location: dashboard.php'); exit;
}

function logout($silent=false){
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    if (!$silent) {
        header('Location: login.php');
        exit;
    }
}
