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
	return vnlx_app_encrypt($rand.$expired_at.$g_sess_id);
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

function e(string $s): ?string
{
	return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, "UTF-8");
}

endif; /* SRC__HELPERS */
