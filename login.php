<?php

$base_url = "./";
require_once (dirname(__FILE__) . '/includes/utils.php');
require_once (dirname(__FILE__) . '/config.php');
require_once (dirname(__FILE__) . '/strings.php');

$login_error = FALSE;

if(isset($_POST['username']) && isset($_POST['password'])) {
	
	// Проверка учетных данных
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	if($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
		
		// Начало сеанса
		session_start();
		$_SESSION['username'] = $username;
		$_SESSION['admin'] = "TRUE";
		session_write_close();

		// Перенаправление на страницу администратора
		global $ADMIN_FOLDER;
		relative_location($ADMIN_FOLDER . "/");
		exit();

	} else {
		
		$login_error = "Введенное вами имя пользователя и пароль недействительны";
		
	}
	
}

// Страницы предоставляете логин
include_from_template('header.php');

?>

<?php
if($login_error !== FALSE) {
	echo "<p class=\"errorMessage\">" . htmlspecialchars_default($login_error) . "</p>";
}
?>

<form method="post" action="login.php">
<fieldset>
<legend>Login</legend>

<p>
<label for="username">Username:</label>
<input type="text" name="username" id="username" class="inputText" />
<br />
<label for="password">Password:</label>
<input type="password" name="password" id="password" class="inputText" />
</p>
<input type="submit" value="Login" class="submit" />
</fieldset>  
</form>


