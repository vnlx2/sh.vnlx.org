<style type="text/css">
#navbar {
	display: flex;
	justify-content: space-between;
	align-items: center;
	background-color: #333;
	color: white;
	padding: 10px;
}

#navbar a {
	color: white;
	text-decoration: none;
	padding: 10px;
}

#navbar a:hover {
	background-color: #555;
}

#navbar a.active {
	background-color: #555;
}
</style>

<div id="navbar">
	<div id="navbar-left">
		<a href="<?= APP_URL_PATH ?>/home.php" class="<?= $active == "home" ? "active" : ""; ?>">Home</a>
		<a href="<?= APP_URL_PATH ?>/profile.php" class="<?= $active == "profile" ? "active" : ""; ?>">Profile</a>
		<a href="<?= APP_URL_PATH ?>/settings.php" class="<?= $active == "settings" ? "active" : ""; ?>">Settings</a>
	</div>
	<div id="navbar-right">
		<a href="<?= APP_URL_PATH ?>/logout.php">Logout</a>
	</div>
</div>
