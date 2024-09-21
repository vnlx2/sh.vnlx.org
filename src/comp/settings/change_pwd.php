<style type="text/css">
.cpwd {
	border: 1px solid #000;
	padding: 20px;
}
.cpwd table {
	margin: 0 auto;
}
</style>

<div class="cpwd">
	<form id="change_pwd_form" action="javascript:void(0)" method="POST">
	<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"/>
	<table>
		<tbody>
			<tr><td>Enter current password</td><td>:</td><td><input type="password" name="old_pwd" required/></td></tr>
			<tr><td>Enter new password</td><td>:</td><td><input type="password" name="new_pwd" required/></td></tr>
			<tr><td>Retype new password</td><td>:</td><td><input type="password" name="cnew_pwd" required/></td></tr>
			<tr><td colspan="3" align="center"><button type="submit">Change Password</button></td></tr>
		</tbody>
	</table>
	</form>
</div>

<script type="text/javascript">
const change_pwd_form = gid("change_pwd_form");
function submit_change_pwd()
{
	let fr = new FormData(change_pwd_form);
	if (fr.get("new_password") !== fr.get("cnew_password")) {
		alert("New passwords do not match");
		return;
	}

	let xhr = new XMLHttpRequest();
	xhr.withCredentials = true;
	xhr.open("POST", "settings.php?action=change_pwd");
	xhr.onload = function() {
		try {
			let j = JSON.parse(this.responseText);
			if (j.code == 200) {
				alert("Password changed successfully.");
				window.location.reload();
			} else {
				alert("Error: " + j.data.msg);
			}
		} catch (e) {
			alert("Error: " + e);
		}
	};
	xhr.send(fr);
}

change_pwd_form.addEventListener("submit", submit_change_pwd);
</script>
