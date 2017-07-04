<?php

// Это значения по умолчанию; Для настройки, добавления / изменения в config.php
$CHALLENGE_STRING_LENGTH = 5;
$CHALLENGE_STRING_LETTERS = 'ABCDEFGHJKLMNPQRTUVWXY3678@#$&*+?';
$CHALLENGE_STRING_SESSION_VAR_NAME = 'drbg_challenge_string';
$CHALLENGE_STRING_FONT_SIZE = 5;
$CHALLENGE_BACKGROUND_PATTERN_ENABLED = TRUE;
$CHALLENGE_BACKGROUND_STRING_FONT_SIZE = 1;
$CHALLENGE_ALTERNATE_COLORS = TRUE;
$CHALLENGE_STRING_PADDING = 4;	// В пикселях
$CHALLENGE_CONVERT_TO_UPPER = TRUE;
$CHALLENGE_FIELD_PARAM_NAME = "electricsheep"; 
$CHALLENGE_ENABLED = TRUE;

// Это значения по умолчанию; Для настройки, добавления / изменения в strings.php
$CHALLENGE_FIELD_NAME = "Введите код";
$ERROR_MSG_BAD_CHALLENGE_STRING = "Код не корректен";
   
function createChallengeString() {
	global $CHALLENGE_STRING_LENGTH;
 	
 	$challenge_string = "";
 	
 	// Создаем строку из случайных символов в списке допустимых символов
 	for($i = 0; $i < $CHALLENGE_STRING_LENGTH; $i++) {
 		$challenge_string .= pickNextChar(); 
 	}
 	
 	// Хранить цепочку вызовов в сеансе
 	@session_start();
 	global $CHALLENGE_STRING_SESSION_VAR_NAME;
 	$_SESSION[$CHALLENGE_STRING_SESSION_VAR_NAME] = $challenge_string;
 	
 	return $challenge_string;
 }
 
 function pickNextChar() {
 	global $CHALLENGE_STRING_LETTERS;
 	return substr($CHALLENGE_STRING_LETTERS, (rand() % strlen($CHALLENGE_STRING_LETTERS)), 1);
 }
 
 function getChallengeString() {
 	global $CHALLENGE_STRING_SESSION_VAR_NAME;
 	
 	// Проверяем, нет ли допустимой строки запроса
 	@session_start(); 
 	if(!isset($_SESSION[$CHALLENGE_STRING_SESSION_VAR_NAME])) {
 		return FALSE;
 	}
 	
 	return $_SESSION[$CHALLENGE_STRING_SESSION_VAR_NAME];
 }
 
 function isChallengeAccepted($entered_value) {
 	global $CHALLENGE_STRING_SESSION_VAR_NAME;
 	
 	// Получить строку запроса
 	$challenge_string = getChallengeString();
 	if($challenge_string === FALSE) { return FALSE; }
 
 	// Преобразование введенного значения в верхний регистр, если включено
 	global $CHALLENGE_CONVERT_TO_UPPER;
 	if($CHALLENGE_CONVERT_TO_UPPER === TRUE) {
 		$entered_value = strtoupper($entered_value);
 	}
 	
 	// Удалить из сеанса, чтобы его нельзя было повторно использовать
 	unset($_SESSION[$CHALLENGE_STRING_SESSION_VAR_NAME]);
 	
 	// Сравнение введенного значения для вызова строки в сеансе
    return ($challenge_string === $entered_value);
}
 
 function outputChallengeImage() {
 	
 	// Создаем строку запроса
 	$challenge_string = getChallengeString();
 	if($challenge_string === FALSE) { return FALSE; }
 	
 	// Устанавливаем тип содержимого
 	header("Content-type: image/png");

 	// Получать размеры символов и размеры строк
 	global $CHALLENGE_STRING_FONT_SIZE;
 	global $CHALLENGE_STRING_LENGTH;
	$char_width = imagefontwidth($CHALLENGE_STRING_FONT_SIZE) - 1;
    $char_height = imagefontheight($CHALLENGE_STRING_FONT_SIZE);
    $string_width = $CHALLENGE_STRING_LENGTH * $char_width;
    $string_height = 1 * $char_height;
     	
    // Создаем изображение и получим цвет
    global $CHALLENGE_STRING_PADDING;
    $img_width = $string_width + $CHALLENGE_STRING_PADDING * 2;
    $img_height = $string_height + $CHALLENGE_STRING_PADDING * 2; 	
 	$img = @imagecreatetruecolor($img_width, $img_height)
 	  or die("imagecreatetruecolor failed");

    // Выбор цветов
    global $CHALLENGE_ALTERNATE_COLORS;
    if($CHALLENGE_ALTERNATE_COLORS === FALSE || rand(0, 1) == 0) {
	 	$background_color = imagecolorallocate($img, 15, 15, 15);
	 	$text_color = imagecolorallocate($img, 238, 238, 238);
	 	$bg_text_color = imagecolorallocate($img, 95, 95, 95);
    } else {
	 	$background_color = imagecolorallocate($img, 238, 238, 238);
	 	$text_color = imagecolorallocate($img, 15, 15, 15);
	 	$bg_text_color = imagecolorallocate($img, 191, 191, 191);
    }

 	// Заполнить фон
 	imagefill($img ,0, 0, $background_color);
 	
 	// Нарисовать шаблон фонового текста
 	global $CHALLENGE_BACKGROUND_PATTERN_ENABLED;
 	if($CHALLENGE_BACKGROUND_PATTERN_ENABLED === TRUE) {
	 	global $CHALLENGE_BACKGROUND_STRING_FONT_SIZE;
		$bg_char_width = imagefontwidth($CHALLENGE_BACKGROUND_STRING_FONT_SIZE);
	    $bg_char_height = imagefontheight($CHALLENGE_BACKGROUND_STRING_FONT_SIZE);
	 	for($x = rand(-2, 2); $x < $img_width; $x += $bg_char_width + 1) {
	 		for($y = rand(-2, 2); $y <  $img_height; $y += $bg_char_height + 1) {
	 			imagestring($img, $CHALLENGE_BACKGROUND_STRING_FONT_SIZE, $x, 
	        		$y, pickNextChar(), $bg_text_color);
	 		}
	 	}
 	}

 	// Рисование текста
 	$x = $CHALLENGE_STRING_PADDING + rand(-2, 2);
 	$y = $CHALLENGE_STRING_PADDING + rand(-2, 2);
 	for($i = 0; $i < strlen($challenge_string); $i++) {
	    imagestring($img, $CHALLENGE_STRING_FONT_SIZE, $x, 
	        $y  + rand(-2, 2), substr($challenge_string, $i, 1), $text_color);
	    $x += $char_width;
 	}
 	
    // Выходное изображение
	imagepng($img);
	
	// Освобождение ресурсов изображения
	imagedestroy($img);
 	
 }
 
?>
