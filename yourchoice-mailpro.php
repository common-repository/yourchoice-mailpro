<?php
/*
Plugin Name: YourChoice MailPro
Plugin URI: https://www.yourchoice.ch
Description: A MailPro API interface to add email addresses into a MailPro address book
Version: 2.0.3
Author: YourChoice Informatik GmbH
Author URI: https://www.yourchoice.ch
License: GPLv2 or later
Text Domain: yourchoice-mailpro
*/

/*  Copyright 2023 YourChoice Informatik GmbH (email: wordpress@yourchoice.ch)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Plugin Version
define('YOURCHOICE_MAILPRO_VER', '2.0.3');

/*
 * Definitions
 */
define('YOURCHOICE_MAILPRO_FOLDER', plugin_basename(dirname(__FILE__)));
define('YOURCHOICE_MAILPRO_DIR', WP_PLUGIN_DIR . '/' . YOURCHOICE_MAILPRO_FOLDER);

// Classes
include_once( YOURCHOICE_MAILPRO_DIR . '/classes/mailpro.php' );

// Widget
include_once( YOURCHOICE_MAILPRO_DIR . '/classes/widget.php' );

// Dashboard Widget
include_once( YOURCHOICE_MAILPRO_DIR . '/classes/dashboard-widget.php' );

// Shortcode
function yourchoice_mailpro_shortcode() {

	$content = yourchoice_mailpro_shortcode_form();
	return $content;
}

add_shortcode( 'yourchoice_mailpro', 'yourchoice_mailpro_shortcode' );

$api_is_valid = false;

add_action('wp_enqueue_scripts', 'yourchoice_mailpro_enqueue_script');
add_action( 'plugins_loaded', 'yourchoice_mailpro_load_textdomain' );

if ( is_admin() ) {
  add_action( 'admin_menu', 'yourchoice_mailpro_menu' );
  add_action( 'admin_init', 'yourchoice_mailpro_register_settings' );
}

function yourchoice_mailpro_enqueue_script() {

	wp_register_style('yourchoice-mailpro', plugins_url('css/style.css', __FILE__), false, YOURCHOICE_MAILPRO_VER,  'screen');

    // Validation
    wp_register_script( 'jquery-validate', plugins_url('js/jquery.validate.min.js', __FILE__), array(), '1.19.2', false );

    // YourChoice
    wp_register_script( 'yourchoice-validator', plugins_url('js/validator.js', __FILE__), array(), YOURCHOICE_MAILPRO_VER, false );
	wp_register_script( 'yourchoice-recaptcha-callback', plugins_url('js/recaptcha-callback.js', __FILE__), array(), YOURCHOICE_MAILPRO_VER, false );

    $yourchoice_validator_translation_array = array(
        'please_enter_your_email_address' => __('Please enter your email address', 'yourchoice-mailpro'),
        'please_enter_a_valid_email_address' => __('Please enter a valid email address', 'yourchoice-mailpro')
    );
    wp_localize_script( 'yourchoice-validator', 'yourchoice_validator_texts', $yourchoice_validator_translation_array );

}

function yourchoice_mailpro_load_textdomain() {
	
	load_plugin_textdomain( 'yourchoice-mailpro', false, YOURCHOICE_MAILPRO_FOLDER . '/lang' );
}

function yourchoice_mailpro_register_settings() {
	
	register_setting( 'ycmp_options', 'ycmp_options', 'yourchoice_mailpro_options_validate' );
	
	add_settings_section('ycmp_main', __('Main Settings', 'yourchoice-mailpro'), 'ycmp_section_text', 'ycmp');
	add_settings_field('ycmp_AddressBookID', __('Address Book', 'yourchoice-mailpro'), 'selectAddressBook', 'ycmp', 'ycmp_main');
	add_settings_field('ycmp_submitmsg', __('Submit Button Text', 'yourchoice-mailpro'), 'setSubmitButtonText', 'ycmp', 'ycmp_main');
	add_settings_field('ycmp_success', __('Success Message', 'yourchoice-mailpro'), 'setSuccessMessage', 'ycmp', 'ycmp_main');
	add_settings_field('ycmp_error', __('Error Message', 'yourchoice-mailpro'), 'setErrorMessage', 'ycmp', 'ycmp_main');
	add_settings_field('ycmp_variables', __('Fields', 'yourchoice-mailpro'), 'setFields', 'ycmp', 'ycmp_main');

	add_settings_section('ycmp_main', __('Basic Settings', 'yourchoice-mailpro'), 'ycmp_section_text', 'ycmpbasic');
	add_settings_field('ycmp_APIKey', __('MailPro APIKey', 'yourchoice-mailpro'), 'setAPIKey', 'ycmpbasic', 'ycmp_main');
	add_settings_field('ycmp_IDClient', __('MailPro IDClient', 'yourchoice-mailpro'), 'setIDClient', 'ycmpbasic', 'ycmp_main');

	add_settings_section('ycmp_main', __('Google reCAPTCHA v2 Settings', 'yourchoice-mailpro'), 'ycmp_section_text', 'ycmprecaptcha');
	add_settings_field('ycmp_SiteKey', __('Site Key', 'yourchoice-mailpro'), 'setSiteKey', 'ycmprecaptcha', 'ycmp_main');
	add_settings_field('ycmp_SecretKey', __('Secret Key', 'yourchoice-mailpro'), 'setSecretKey', 'ycmprecaptcha', 'ycmp_main');
	add_settings_field('ycmp_Theme', __('Theme', 'yourchoice-mailpro'), 'setTheme', 'ycmprecaptcha', 'ycmp_main');
	add_settings_field('ycmp_WidgetSize', __('Widget Size', 'yourchoice-mailpro'), 'setWidgetSize', 'ycmprecaptcha', 'ycmp_main');
	add_settings_field('ycmp_WidgetScale', __('Widget Scale', 'yourchoice-mailpro'), 'setWidgetScale', 'ycmprecaptcha', 'ycmp_main');
}

function yourchoice_mailpro_menu() {
	
	add_options_page(__('YourChoice MailPro', 'yourchoice-mailpro'), __('YourChoice MailPro', 'yourchoice-mailpro'), 'manage_options', 'mailpro', 'yourchoice_mailpro_options_page');
}


function yourchoice_mailpro_options_validate($input) {
	$old = get_option('ycmp_options');
	if(isset($input['IDClient'])) {
		$newinput['IDClient'] = trim($input['IDClient']);
		$newinput['APIKey'] = trim($input['APIKey']);
		$newinput['SiteKey'] = $old['SiteKey'];
		$newinput['SecretKey'] = $old['SecretKey'];
		$newinput['Theme'] = $old['Theme'];
		$newinput['WidgetSize'] = $old['WidgetSize'];
		$newinput['WidgetScale'] = $old['WidgetScale'];
		$newinput['submitmsg'] = $old['submitmsg'];
		$newinput['success'] = $old['success'];
		$newinput['error'] = $old['error'];
		$newinput['AddressBookID'] = $old['AddressBookID'];
		$newinput['variables'] = $old['variables'];
		for($i=1;$i<=25;$i++) {
			$newinput['variables_tags_field'.$i] = trim($old['variables_tags_field' . $i]);
			if($old['variables_field' . $i] == 1){
				$newinput['variables_field' . $i] = 1;
			}
		}

	} elseif(isset($input['SiteKey'])) {
		$newinput['IDClient'] = $old['IDClient'];
		$newinput['APIKey'] = $old['APIKey'];
		$newinput['SiteKey'] = trim($input['SiteKey']);
		$newinput['SecretKey'] = trim($input['SecretKey']);
		$newinput['Theme'] = $input['Theme'];
		$newinput['WidgetSize'] = $input['WidgetSize'];
		$newinput['WidgetScale'] = $input['WidgetScale'];
		$newinput['submitmsg'] = $old['submitmsg'];
		$newinput['success'] = $old['success'];
		$newinput['error'] = $old['error'];
		$newinput['AddressBookID'] = $old['AddressBookID'];
		$newinput['variables'] = $old['variables'];
		for($i=1;$i<=25;$i++) {
			$newinput['variables_tags_field'.$i] = trim($old['variables_tags_field' . $i]);
			if($old['variables_field' . $i] == 1){
				$newinput['variables_field' . $i] = 1;
			}
		}

	} else {
		$newinput['IDClient'] = $old['IDClient'];
		$newinput['APIKey'] = $old['APIKey'];
		$newinput['SiteKey'] = $old['SiteKey'];
		$newinput['SecretKey'] = $old['SecretKey'];
		$newinput['Theme'] = $old['Theme'];
		$newinput['WidgetSize'] = $old['WidgetSize'];
		$newinput['WidgetScale'] = $old['WidgetScale'];
		$newinput['submitmsg'] = trim($input['submitmsg']);
		$newinput['success'] = trim($input['success']);
		$newinput['error'] = trim($input['error']);
		if(isset($input['AddressBookID'])){
			$newinput['AddressBookID'] = trim($input['AddressBookID']);
		}
		$vars = array();
		for($i=1;$i<=25;$i++) {
			$newinput['variables_tags_field' . $i] = trim($input['variables_tags_field' . $i]);
			if($input['variables_field' . $i] == 1){
				$newinput['variables_field' . $i] = 1;
			}
		}
	}
	return $newinput;
}

$api_error = false;

function yourchoice_mailpro_options_page() {
	global $api_is_valid;
	global $api_error;
	$mailpro = new mailpro();
	$options = get_option('ycmp_options');
	if(isset($options['APIKey']) && isset($options['IDClient']) && $options['IDClient'] != '' && $options['APIKey'] != '') {
		$adbooks = json_decode($mailpro->getAddressBooks($options));
		if(!isset($adbooks->Error)) {
			$api_is_valid = true;
		} else {
			$api_error = true;
		}
	}
	ob_start(); 
	if(isset($_GET['basic']) || !isset($options['APIKey']) || !isset($options['IDClient']) || !$api_is_valid) {
		include_once( YOURCHOICE_MAILPRO_DIR . '/views/admin_basic.tpl.php' );
	} else if (isset($_GET['recaptcha'])) {
		include_once( YOURCHOICE_MAILPRO_DIR . '/views/admin_recaptcha.tpl.php' );
	} else {
		include_once( YOURCHOICE_MAILPRO_DIR . '/views/admin.tpl.php' );
	}
	$contents = ob_get_contents();
	ob_end_clean();
	echo $contents;
}

function selectAddressBook() {
	$mailpro = new mailpro();
	$options = get_option('ycmp_options');
	if(isset($options['APIKey']) && isset($options['IDClient']) && $options['IDClient'] != '' && $options['APIKey'] != '') {
	$adbooks = json_decode($mailpro->getAddressBooks($options));
	echo '<select name="ycmp_options[AddressBookID]" id="ycmp_AddressBookID">';
	foreach($adbooks->AddressBookList as $book){
		$selected = "";
		if($book->AddressBookId == $options['AddressBookID']) {
			$selected = ' selected="selected" ';
		}
		echo'<option value="' . $book->AddressBookId . '" ' . $selected . '>' . $book->Title . '</option>';
	}
	
	echo '</select>';
	} else {
		echo __('Please set API key and IDClient first', 'yourchoice-mailpro');
	}
}

function setIDClient() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_IDClient" name="ycmp_options[IDClient]"  size="60" value="' . $options['IDClient'] . '">';
}

function setFields() {
	$options = get_option('ycmp_options');
	
	echo '<table width="500" border="0">
  <tr>
    <td nowrap="nowrap">' . __('Name', 'yourchoice-mailpro') . '</td>
    <td>' . __('Tag', 'yourchoice-mailpro') . '</td>
    <td>' . __('Include', 'yourchoice-mailpro') . '</td>
  </tr>
  <tr>
    <td nowrap="nowrap">' . __('Email', 'yourchoice-mailpro') . '</td>
    <td>' . __('Email', 'yourchoice-mailpro') . '</td>
	<td align="left"><input type="checkbox" name="notused" checked="checked" disabled /></td>
  </tr>';
  for($i=1; $i<=25; $i++) {
	  $fyes = '';
	  if($options['variables_field' . $i] == 1){
		  $fyes = 'checked="checked"';
	  }
	  echo '<tr>
		<td nowrap="nowrap">' . __('Field', 'yourchoice-mailpro') . ' ' . $i . '</td>
		<td>
		<input name="ycmp_options[variables_tags_field' . $i . ']" value="' . $options['variables_tags_field' . $i] . '" type="text" size="40" /></td>
		<td align="left"><input type="checkbox" name="ycmp_options[variables_field' . $i . ']" value="1" ' . $fyes . ' />
	  </td>
	  </tr>';
  }
 echo '</table>';
}

function setSubmitButtonText() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_submitmsg" name="ycmp_options[submitmsg]"  size="60" value="' . $options['submitmsg'] . '">';
}

function setSuccessMessage() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_success" name="ycmp_options[success]"  size="60" value="' . $options['success'] . '">';
}

function setErrorMessage() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_error" name="ycmp_options[error]"  size="60" value="' . $options['error'] . '">';
}

function setAPIKey() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_APIKey" name="ycmp_options[APIKey]"  size="60" value="' . $options['APIKey'] . '">';
}

function setSiteKey() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_SiteKey" name="ycmp_options[SiteKey]"  size="60" value="' . $options['SiteKey'] . '">';
}

function setSecretKey() {
	$options = get_option('ycmp_options');
	echo '<input type="text" id="ycmp_SecretKey" name="ycmp_options[SecretKey]"  size="60" value="' . $options['SecretKey'] . '">';
}

function setTheme() {
	$options = get_option('ycmp_options');
		echo '<select name="ycmp_options[Theme]" id="ycmp_Theme">';
		$darkSelected = '';
		$lightSelected = ' selected="selected" ';
		if('dark' == $options['Theme']) {
			$darkSelected = ' selected="selected" ';
			$lightSelected = '';
		}
		echo'<option value="light" ' . $lightSelected . '>light</option>';
		echo'<option value="dark" ' . $darkSelected . '>dark</option>';
		echo '</select>';
}

function setWidgetSize() {
	$options = get_option('ycmp_options');
	echo '<select name="ycmp_options[WidgetSize]" id="ycmp_WidgetSize">';
	$compactSelected = '';
	$normalSelected = ' selected="selected" ';
	if('compact' == $options['WidgetSize']) {
		$compactSelected = ' selected="selected" ';
		$normalSelected = '';
	}
	echo'<option value="normal" ' . $normalSelected . '>normal</option>';
	echo'<option value="compact" ' . $compactSelected . '>compact</option>';
	echo '</select>';
}

function setWidgetScale() {
	$options = get_option('ycmp_options');
	$value = 1;
	if (isset($options['WidgetScale']) && $options['WidgetScale'] !== '') {
		$value = $options['WidgetScale'];
	}
	echo '<input type="number" id="ycmp_WidgetScale" name="ycmp_options[WidgetScale]" min="0.4" max="1.0" step=".1" value="' . $value . '">';
}

function plugin_section_text() {
	echo "";
}
function ycmp_section_text() {
	echo "";
}

function yourchoice_mailpro_shortcode_form() {
	$options = get_option('ycmp_options');
	$content = '';
	wp_enqueue_style('yourchoice-mailpro');
    wp_enqueue_script( 'jquery-validate' );
    wp_enqueue_script( 'yourchoice-validator' );
	if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
		wp_enqueue_script( 'yourchoice-recaptcha-callback' );
	}
	if(isset($_POST['mailpro_shortcode_email'])) {
		$reCaptchaOkOrNotNeeded = false;
		if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
			if(!empty($_POST['g-recaptcha-response'])) {
				$secret = $options['SecretKey'];
				$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
				$responseData = json_decode($verifyResponse);
				if($responseData->success) {
					$reCaptchaOkOrNotNeeded = true;
				}
			}
		} else {
			$reCaptchaOkOrNotNeeded = true;
		}
		if ($reCaptchaOkOrNotNeeded) {
			$allvars = array();
			for($i=1; $i<=25; $i++) {
				$thisvariable = '';
				if(isset($_POST['mailpro_shortcode_variable_' . $i])) {
					$thisvariable = $_POST['mailpro_shortcode_variable_' . $i];
				}
				array_push($allvars,$thisvariable);
			}
			$mailpro = new mailpro();
			$res = $mailpro->addEmailAddress($_POST['mailpro_shortcode_email'], $options, $allvars);
			$json_res = json_decode($res);
			if ($json_res->Code > 0) {
				$content .= '<div id="yourchoice_mailpro">';
				$content .= '<div class="error">';
				$content .= '<p>';
				if (isset($options['error']) && strlen($options['error']) >0) {
					$content .= $options['error'];
				} else {
					$content .= __('Registration failed!', 'yourchoice-mailpro');
				}
				$content .= '<br />' . __($json_res->Error, 'yourchoice-mailpro');
				$content .= '</p>';
				$content .= '</div>';
				$content .= '</div>';
			} else {
				$content .= '<div id="yourchoice_mailpro">';
				$content .= '<div class="success">';
				$content .= '<p>';
				if (isset($options['success']) && strlen($options['success']) >0) {
					$content .= $options['success'];
				} else {
					$content .= __('You have successfully registered!', 'yourchoice-mailpro');
				}
				$content .= '</p>';
				$content .= '</div>';
				$content .= '</div>';
			}
		} else {
			$content .= '<div id="yourchoice_mailpro">';
			$content .= '<div class="error">';
			$content .= '<p>';
			$content .= __('An error occurred while checking the reCAPTCHA', 'yourchoice-mailpro');
			$content .= '</p>';
			$content .= '</div>';
			$content .= '</div>';
		}
	} else {
		$options = get_option('ycmp_options');
		$content .= '<div id="yourchoice_mailpro">';
		if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
			$content .= '<script src="https://www.google.com/recaptcha/api.js" async defer ></script>';
		}
		$content .= '<form class="yourchoice_mailpro_float" id="mailpro_shortcode_subscription" name="mailpro_shortcode_subscription" id="mailpro_shortcode_subscription" method="post" action="">';
		$content .= '<div class="clearBoth">&nbsp;</div><div class="mailpro_shortcode_email"><div class="label"><label for="mailpro_shortcode_email">' . __('Email', 'yourchoice-mailpro') . '*</label></div><div class="input"><input name="mailpro_shortcode_email" id="mailpro_shortcode_email" type="email" /></div></div>';
		for($i=1;$i<=25;$i++) {
			if($options['variables_field' . $i] == 1) {
				$fyes = 'checked="checked"';
				$content .= '<div class="clearBoth">&nbsp;</div><div class="mailpro_shortcode_variable_' . $i . '">';
				$content .= '<div class="label"><label for="mailpro_shortcode_variable_' . $i . '">' . $options['variables_tags_field' . $i] . '</label></div>';
				$content .= '<div class="input"><input name="mailpro_shortcode_variable_' . $i . '" type="text" /></div>';
				$content .= '</div>';
			}
		}
        if (isset($options['submitmsg']) && strlen($options['submitmsg']) >0) {
            $submitmsg = $options['submitmsg'];
        } else {
            $submitmsg = __('Subscribe', 'yourchoice-mailpro');
		}
		$content .= '<div class="clearBoth">&nbsp;</div><div class="mailpro_shortcode_subscribe"><div class="label">&nbsp;</div><div class="input">';
		if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
			$content .= '<div class="g-recaptcha" data-theme="' . $options['Theme'] . '" data-sitekey="' . $options['SiteKey'] . '" data-callback="ycmp_recaptchaCallback_shortcode"></div><br />';
			$content .= '<input name="mailpro_shortcode_subscribe" id="mailpro_shortcode_subscribe" type="submit" value="' . $submitmsg . '" title="' . __('Please confirm first that you are human', 'yourchoice-mailpro') . '" disabled />';
		} else {
			$content .= '<input name="mailpro_shortcode_subscribe" id="mailpro_shortcode_subscribe" type="submit" value="' . $submitmsg . '" />';
		}
		$content .= '</div></div><div class="clearBoth">&nbsp;</div>';
		$content .= '</form>';
		$content .= '</div>';
	}
	return $content;
}

/* Translate Description */
function yourchoice_mailpro_description() {
	$var = __( "A MailPro API interface to add email addresses into a MailPro address book", 'yourchoice-mailpro' );
}
?>
