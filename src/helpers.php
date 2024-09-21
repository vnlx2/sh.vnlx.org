<?php
// SPDX-License-Identifier: GPL-2.0-only

if (!defined("SRC__HELPERS")):

$g_sess_id = NULL;
$g_user_ss = NULL;

function vnlx_app_encrypt(string $data): ?string
{
	$is_compressed = false;

	$dz = gzdeflate($data, 9);
	if (strlen($dz) < strlen($data)) {
		$data = $dz;
		$is_compressed = true;
	}

	$iv  = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$key = APP_KEY;
	$enc = openssl_encrypt($data, "aes-128-cbc", $key, OPENSSL_RAW_DATA, $iv);
	if (!$enc)
		return NULL;

	return base64_encode(($is_compressed ? "\0" : "\1") . $enc);
}

function vnlx_app_decrypt(string $data): ?string
{
	$data = base64_decode($data);
	if (!$data)
		return NULL;

	$is_compressed = $data[0] === "\0";
	$data = substr($data, 1);

	$iv  = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$key = APP_KEY;
	$dec = openssl_decrypt($data, "aes-128-cbc", $key, OPENSSL_RAW_DATA, $iv);
	if (!$dec)
		return NULL;

	if ($is_compressed) {
		$dec = gzinflate($dec);
		if (!$dec)
			return NULL;
	}

	return $dec;
}

function rstr(int $n, ?string $c = NULL): string
{
	if ($c === NULL)
		$c = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.-";

	$l = strlen($c) - 1;
	$r = "";
	for ($i = 0; $i < $n; $i++)
		$r .= $c[random_int(0, $l)];

	return $r;
}

function tne(string $msg, int $code = 0)
{
	throw new Exception($msg, $code);
}

function pdo(): PDO
{
	static $pdo = NULL;

	if ($pdo === NULL)
		$pdo = new PDO(...PDO_INIT_ARGS);

	return $pdo;
}

define("CKP", COOKIE_PREFIX);

function sess_start(): void
{
	global $g_sess_id;
	global $g_user_ss;

	$ck_sess_id = &$_COOKIE[CKP."sess_id"];
	$ck_user_ss = &$_COOKIE[CKP."user_ss"];

	if (isset($ck_sess_id)) {
		$sess_id = vnlx_app_decrypt($ck_sess_id);
		if ($sess_id)
			$g_sess_id = $sess_id;
	}

	if (!$g_sess_id) {
		$g_sess_id = rstr(32);
		setcookie(CKP."sess_id", vnlx_app_encrypt($g_sess_id), 0, "/");
	}

	if (isset($ck_user_ss)) {
		$user_ss = vnlx_app_decrypt($ck_user_ss);
		if ($user_ss)
			$g_user_ss = $user_ss;
	}
}

function csrf_token(): string
{
	global $g_sess_id;

	$rand = rstr(4);
	$expired_at = time() + 3600;
	return vnlx_app_encrypt("{$rand}#{$expired_at}#{$g_sess_id}");
}

function sess_destroy(): void
{
	global $g_sess_id;
	global $g_user_ss;

	$ck_sess_id = &$_COOKIE[CKP."sess_id"];
	$ck_user_ss = &$_COOKIE[CKP."user_ss"];

	if (isset($ck_sess_id)) {
		setcookie(CKP."sess_id", "", 0, "/");
		$g_sess_id = NULL;
	}

	if (isset($ck_user_ss)) {
		setcookie(CKP."user_ss", "", 0, "/");
		$g_user_ss = NULL;
	}
}

function validate_csrf($csrf): bool
{
	global $g_sess_id;

	if (!is_string($csrf))
		tne("csrf: Invalid CSRF token", 400);

	$csrf = vnlx_app_decrypt($csrf);
	if (!$csrf)
		tne("csrf: Invalid CSRF token", 400);

	$parts = explode("#", $csrf);
	if (count($parts) !== 3)
		tne("csrf: Invalid CSRF token", 400);

	$rand = $parts[0];
	$expired_at = (int)$parts[1];
	$sess_id = $parts[2];

	if ($sess_id !== $g_sess_id)
		tne("csrf: sess_id mismatch", 400);

	if ($expired_at < time())
		tne("csrf: CSRF token expired", 400);

	return true;
}

function db_username_exists(PDO $pdo, string $username): bool
{
	$st = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1;");
	$st->execute([$username]);
	$r = $st->fetch(PDO::FETCH_NUM);
	return !!$r;
}

function db_email_exists(PDO $pdo, string $email): bool
{
	$st = $pdo->prepare("SELECT id FROM user_emails WHERE email = ? LIMIT 1;");
	$st->execute([$email]);
	$r = $st->fetch(PDO::FETCH_NUM);
	return !!$r;
}

/**
 * @param PDO $pdo
 * @param array $d
 * @return int
 * @throws Exception
 */
function db_user_register(PDO $pdo, $d): int
{
	$req_string_fields = [
		"fullname",
		"username",
		"email",
		"password",
		"cpassword"
	];

	$trim_fields = [
		"fullname",
		"username",
		"email"
	];

	foreach ($req_string_fields as $k) {
		if (!isset($d[$k]) || !is_string($d[$k]))
			tne("Missing string field: {$k}", 400);
	}

	foreach ($trim_fields as $k)
		$d[$k] = trim($d[$k]);

	$c = strlen($d["fullname"]);
	if ($c < 3)
		tne("The full name must be at least 3 characters long", 400);

	$c = strlen($d["username"]);
	if ($c < 4)
		tne("The username must be at least 4 characters long", 400);
	if ($c > 64)
		tne("The username cannot be more than 64 characters long");
	if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\.\-\_]{2,62}[a-zA-Z0-9]$/", $d["username"]))
		tne("The username must match the following regex pattern: /^[a-zA-Z0-9][a-zA-Z0-9\.\-\_]{2,62}[a-zA-Z0-9]$/", 400);

	$c = strlen($d["email"]);
	if ($c < 6)
		tne("The e-mail must be at least 5 characters long", 400);
	if ($c > 200)
		tne("The e-mail cannot be more than 200 characters long", 400);
	if (!filter_var($d["email"], FILTER_VALIDATE_EMAIL))
		tne("The e-mail is not valid", 400);

	$c = strlen($d["password"]);
	if ($c < 4)
		tne("The password must be at least 4 characters long", 400);
	if ($c > 512)
		tne("The password cannot be more than 512 characters long", 400);

	if ($d["password"] !== $d["cpassword"])
		tne("The password and retype password must be the same", 400);

	try {
		$pdo->beginTransaction();

		if (db_username_exists($pdo, $d["username"]))
			tne("The username is already in use", 400);

		if (db_email_exists($pdo, $d["email"]))
			tne("The e-mail is already in use", 400);

		$nw = date("Y-m-d H:i:s");
		$hp = password_hash($d["password"], PASSWORD_BCRYPT);

		$st = $pdo->prepare("INSERT INTO users (fullname, username, password, status, created_at) VALUES (?, ?, ?, 'active', ?);");
		$st->execute([$d["fullname"], $d["username"], $hp, $nw]);
		$uid = $pdo->lastInsertId();

		$st = $pdo->prepare("INSERT INTO user_emails (user_id, email, is_verified, created_at) VALUES (?, ?, '0', ?);");
		$st->execute([$uid, $d["email"], $nw]);
		$eid = $pdo->lastInsertId();

		$st = $pdo->prepare("UPDATE users SET primary_email = ? WHERE id = ? LIMIT 1;");
		$st->execute([$eid, $uid]);

		$pdo->commit();
		return (int)$uid;
	} catch (PDOException $e) {
		$pdo->rollback();
		tne("500 server error: {$e->getMessage()}", 500);
	}
}

function json_api_res($code, $data)
{
	http_response_code($code);
	header("Content-Type: application/json");
	echo json_encode([
		"code" => $code,
		"data" => $data
	]);
}

function e(string $s): ?string
{
	return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, "UTF-8");
}

endif; /* SRC__HELPERS */
