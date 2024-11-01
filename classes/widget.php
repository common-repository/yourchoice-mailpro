<?php
/*
Plugin Name: YourChoice MailPro Widget
Plugin URI: https://www.yourchoice.ch
Description: A Widget to add an email address over the YourChoice MailPro Plugin into a MailPro address book
Version: 2.0.0
Author: YourChoice Informatik GmbH
Author URI: https://www.yourchoice.ch
*/
 
class YourChoiceMailProWidget extends WP_Widget {
  
	/* Constructor */
	function __construct() {
		$widget_ops = array('classname' => 'YourChoiceMailProWidget', __('Displays the YourChoice MailPro Subscription Form','yourchoice-mailpro') );
		parent::__construct('YourChoiceMailProWidget', __('YourChoice MailPro', 'yourchoice-mailpro'), $widget_ops);
		$this->alt_option_name = 'YourChoiceMailProWidget';
	}
 
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:', 'yourchoice-mailpro');?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<?php
	}
 
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		return $instance;
	}
 
	/** @see WP_Widget::widget */
	function widget($args, $instance) {
        $options = get_option('ycmp_options');
		extract($args, EXTR_SKIP);
		wp_enqueue_script('jquery');
		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
		if (!empty($title))
			echo $before_title . $title . $after_title;;
		wp_enqueue_style('yourchoice-mailpro');
        wp_enqueue_script( 'jquery-validate' );
        wp_enqueue_script( 'yourchoice-validator' );
        if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
            wp_enqueue_script( 'yourchoice-recaptcha-callback' );
        }
		if(isset($_POST['mailpro_widget_email'])) {
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
                    if(isset($_POST['mailpro_widget_variable_' . $i])) {
                        $thisvariable = $_POST['mailpro_widget_variable_' . $i];
                    }
                    array_push($allvars,$thisvariable);
                }
                $mailpro = new mailpro();
                $res = $mailpro->addEmailAddress($_POST['mailpro_widget_email'], $options, $allvars);
                $json_res = json_decode($res);
                if ($json_res->Code > 0) {
                    echo '<div id="yourchoice_mailpro">';
                    echo '<div class="error">';
                    echo '<p>';
                    if (isset($options['error']) && strlen($options['error']) >0) {
                        echo $options['error'];
                    } else {
                        echo __('Registration failed!', 'yourchoice-mailpro');
                    }
                    echo '<br />' . __($json_res->Error, 'yourchoice-mailpro');
                    echo '</p>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div id="yourchoice_mailpro">';
                    echo '<div class="success">';
                    echo '<p>';
                    if (isset($options['success']) && strlen($options['success']) >0) {
                        echo $options['success'];
                    } else {
                        echo __('You have successfully registered!', 'yourchoice-mailpro');
                    }
                    echo '</p>';
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo '<div id="yourchoice_mailpro">';
                echo '<div class="error">';
                echo '<p>';
                echo __('An error occurred while checking the reCAPTCHA', 'yourchoice-mailpro');
                echo '</p>';
                echo '</div>';
                echo '</div>';
            }
		} else {
			$options = get_option('ycmp_options');
			echo '<div id="yourchoice_mailpro">';
            if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
                echo '<script src="https://www.google.com/recaptcha/api.js" async defer ></script>';
            }
			echo '<form class="yourchoice_mailpro_float" id="mailpro_widget_subscription" name="mailpro_widget_subscription" method="post" action="">';
			echo '<div class="clearBoth">&nbsp;</div><div class="mailpro_widget_email"><div class="label"><label for="mailpro_widget_email">' . __('Email', 'yourchoice-mailpro') . '*</label></div><div class="input"><input name="mailpro_widget_email" id="mailpro_widget_email" type="email" /></div></div>';
			for($i=1;$i<=25;$i++) {
				if($options['variables_field' . $i] == 1) {
					$fyes = 'checked="checked"';
				?>
				<div class="clearBoth">&nbsp;</div><div class="mailpro_widget_variable_<?php echo $i; ?>">
					<div class="label"><label for="mailpro_widget_variable_<?php echo $i; ?>"><?php echo $options['variables_tags_field' . $i]; ?></label></div>
					<div class="input"><input name="mailpro_widget_variable_<?php echo $i; ?>" type="text" /></div>
				</div>
				<?php
				}
			}
            if (isset($options['submitmsg']) && strlen($options['submitmsg']) >0) {
                $submitmsg = $options['submitmsg'];
            } else {
                $submitmsg = __('Subscribe', 'yourchoice-mailpro');
            }
			echo '<div class="clearBoth">&nbsp;</div><div class="mailpro_widget_subscribe"><div class="label">&nbsp;</div><div class="input">';
    		if (isset($options['SiteKey']) && strlen($options['SiteKey']) >0 && isset($options['SecretKey']) && strlen($options['SecretKey']) >0) {
                echo '<div id="recaptchaContainer" style="transform:scale(' . $options['WidgetScale'] . ');transform-origin:0 0">';
    		    echo '<div class="g-recaptcha" data-theme="' . $options['Theme'] . '" data-size="' . $options['WidgetSize'] . '" data-sitekey="' . $options['SiteKey'] . '" data-callback="ycmp_recaptchaCallback_widget"></div><br />';
    		    echo '</div>';
                echo '<input name="mailpro_widget_subscribe" id="mailpro_widget_subscribe" type="submit" value="' . $submitmsg . '" title="' . __('Please confirm first that you are human', 'yourchoice-mailpro') . '" disabled />';
            } else {
    		    echo '<input name="mailpro_widget_subscribe" id="mailpro_widget_subscribe" type="submit" value="' . $submitmsg . '" />';
            }
            echo '</div></div><div class="clearBoth">&nbsp;</div>';
			echo '</form>';
			echo '</div>';
			echo $after_widget;
		}
	}
}

function yourchoice_mailpro_widget() {
	register_widget('YourChoiceMailProWidget');
}

add_action('widgets_init', 'yourchoice_mailpro_widget' );

?>
