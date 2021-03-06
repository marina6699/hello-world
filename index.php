<?php

$DEMO_MODE = FALSE;
$READ_ONLY_MODE = FALSE;
$MODERATION_ENABLED = FALSE;

$base_url = "./../";
require_once(dirname(__FILE__) . '/../includes/utils.php');
require_once(dirname(__FILE__) . '/../includes/guestbook.php');
require_once(dirname(__FILE__) . '/../includes/views.php');
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../strings.php');

// Убедитесь, что приложение полностью устанавливается
confirm_install();

// Начало сеанса
session_start();

// Проверяем вход
if(!isset($_SESSION['username']) || !isset($_SESSION['admin'])) {
	
	// Перенаправление на страницу входа в систему
	relative_location('../login.php');
	exit();
	
}

// Проверяем, является ли администратор
if($_SESSION['admin'] !== "TRUE") {
	
	// Перенаправление на главную страницу
	relative_location('..');
	exit();

}

// Обработка действий
if(isset($_REQUEST['action'])) {
	global $READ_ONLY_MODE;
	global $DEMO_MODE;
	global $MODERATION_ENABLED;
	
	$action = $_REQUEST['action'];
	switch($action) {
		
		case "delete":
			if($READ_ONLY_MODE === FALSE && $DEMO_MODE === FALSE && isset($_REQUEST['id'])) {
				guestbook_entries_action($_REQUEST['id'], (isset($_REQUEST['banip']) && $_REQUEST['banip'] == "true"), 'delete');
			}
			show_entries_admin();
			break;
			
		case "approve":
			if($READ_ONLY_MODE === FALSE && $DEMO_MODE === FALSE && $MODERATION_ENABLED === TRUE && isset($_REQUEST['id'])) {
				guestbook_entries_action($_REQUEST['id'], false, 'approve');
			}
			show_entries_admin();
			break;
			
		case "showbans":
			show_bans_admin();
			break;
			
		case "unban";
			if($READ_ONLY_MODE === FALSE && $DEMO_MODE === FALSE && isset($_POST['id'])) {
				unban($_POST['id']);
			}
			show_bans_admin();
			break;
			
		case "showbadwords":
			show_bad_words_admin();
			break;
			
		case "removebadword";
			if($READ_ONLY_MODE === FALSE && $DEMO_MODE === FALSE && isset($_POST['id'])) {
				remove_bad_word($_POST['id']);
			}
			show_bad_words_admin();
			break;
			
		case "addbadword";
			if($READ_ONLY_MODE === FALSE && $DEMO_MODE === FALSE && isset($_POST['word'])) {
				bad_word_add($_POST['word']);
			}
			show_bad_words_admin();
			break;
			
		case "logout":
		
			// Убейте сеанс и истечет срок действия cookie сеанса на клиенте
			$_SESSION = array();
			if(isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-7200, '/');
			session_destroy();

			// Перенаправление на страницу входа в систему		
			relative_location('../login.php');
			exit();
			
		default:
			die('Invalid action.');
			
	}
	

} else {
	
	show_entries_admin();
	
}

function show_logout_button() {
	
?>
<div>
<form method="post" action="index.php">
<p>
<input type="hidden" name="action" value="logout" />
<input type="submit" value="Logout" class="submit" />
</p>
</form>
</div>
<?php

}

function show_checkboxes_js($formName) {
	
?>
<script type="text/javascript">
function changeAllCheckboxes(checked) {
	var entriesForm = document.getElementById("<?php echo $formName; ?>");
	for (i = 0; i < entriesForm.length; i++) {
		if(entriesForm.elements[i].name == "id[]") {
			entriesForm.elements[i].checked = checked;
		}
	}
}
</script>
<?php

}

function show_bans_admin() {
	
	include_from_template('header.php');
	show_logout_button();
	show_checkboxes_js("bansForm");
	
?>

<p>
<a href="index.php">Guestbook Entries Maintenance</a> | 
<a href="index.php?action=showbadwords">Bad Words Maintenance</a>
</p>

<form method="post" action="index.php" id="bansForm">
<fieldset>
<input type="hidden" name="action" value="unban" />
<input type="submit" value="Unban Selected" class="submit" />
<p>
<a href="#" onclick="changeAllCheckboxes(true); return false;">Check All</a> - 
<a href="#" onclick="changeAllCheckboxes(false); return false;">Uncheck All</a>
</p>
<?php

	// Показать запреты
	echo("<h2>Current Bans</h2>");
	show_bans();

?>
</fieldset>
</form>
<?php
	the_credits();
	include_from_template('footer.php');
}

function show_bad_word_add_form() {
 	global $MAX_BAD_WORD_LENGTH;
 	
?>
<form method="post" action="index.php">
<fieldset>
<legend><?php echo htmlspecialchars_default("Add Bad Word"); ?></legend>
<p>

<label for="word">Word:</label>
<input type="text" name="word" id="word" maxlength="<?php echo htmlspecialchars_default($MAX_BAD_WORD_LENGTH); ?>" class="inputText" />

</p>
<input type="hidden" name="action" value="addbadword" class="submit" />
<input type="submit" value="Add" class="submit" />
</fieldset>  
</form>
<?php
 	
}

function show_bad_words_admin() {
	
	include_from_template('header.php');
	show_logout_button();
	show_checkboxes_js("badWordsForm");
	
?>

<p>
<a href="index.php">Guestbook Entries Maintenance</a> | <a href="index.php?action=showbans">Banned IP Address Maintenance</a>
</p>

<?php show_bad_word_add_form(); ?>

<form method="post" action="index.php" id="badWordsForm">
<fieldset>
<input type="hidden" name="action" value="removebadword" />
<input type="submit" value="Remove Selected" class="submit" />
<p>
<a href="#" onclick="changeAllCheckboxes(true); return false;">Check All</a> - 
<a href="#" onclick="changeAllCheckboxes(false); return false;">Uncheck All</a>
</p>
<?php

	// Показать плохие слова
	echo("<h2>Current Bad Words</h2>");
	show_bad_words();

?>
</fieldset>
</form>
<?php
	the_credits();
	include_from_template('footer.php');
}

function show_entries_admin() {

	include_from_template('header.php');
	show_logout_button();
	show_checkboxes_js("entriesForm");

?>

<form method="post" action="index.php" id="entriesForm">
<fieldset>

<p>
<?php
global $MODERATION_ENABLED;
if($MODERATION_ENABLED === TRUE) { 
?>
<strong>Select an action to perform: </strong>
<br />
<select name="action">
	<option value="approve">Approve Checked Entries</option>
	<option value="delete">Delete Checked Entries</option>
</select>
<br /> 
<?php } else { ?>
<input type="hidden" name="action" value="delete" />
<?php } ?>
<input type="checkbox" name="banip" value="true" /> Ban IP addresses of deleted entries
<br /><br />
<?php if($MODERATION_ENABLED === TRUE) { ?>
<input type="submit" value="Perform Action" class="submit" />
<?php } else { ?>
<input type="submit" value="Delete Selected" class="submit" />
<?php } ?>
</p>
<p>
<a href="#" onclick="changeAllCheckboxes(true); return false;">Select All</a> - 
<a href="#" onclick="changeAllCheckboxes(false); return false;">Select None</a>
</p>

<?php

// Show entries
echo("<h2>Current Entries</h2>");
show_entries(NULL, NULL, TRUE, TRUE, TRUE);

?>
</fieldset>
</form>
<?php

include_from_template('footer.php');

}

?>
