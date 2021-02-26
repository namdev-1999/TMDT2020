<?php

$shortcodes = array(
	'xoo_el_action' => array(
		'shortcode' => '[xoo_el_action]',
		'desc' 		=> 'Creates a link/button to open popup',
		'example' 	=> '[xoo_el_action type="login" display="button" text="Login" change_to="logout" redirect_to="same"]',
		'atts' 		=> array(
			array(
				'type',
				'login, register, lost-password',
				'login',
				'Which form to open on click'
			),
			array(
				'display',
				'button, link',
				'link',
				'Display as a link or button'
			),
			array(
				'text',
				'Custom Text',
				'Login/Register',
				'Button/Link text'
			),
			array(
				'change_to',
				'hide, logout, myaccount, www.customURL.com',
				'logout',
				'After signing in, the link should change into'
			),
			array(
				'change_to_text',
				'Custom Text',
				'Logout',
				'After signing in, the link text should change into'
			),
			array(
				'redirect_to',
				'same (for same page), www.customURL.com',
				'',
				'Redirect link after user submits a form. If value is set, it will override the redirection on settings page.'
			)
		)
	),
	'xoo_el_inline_form' => array(
		'shortcode' => '[xoo_el_inline_form]',
		'desc' 		=> 'Generates an inline login/signup form',
		'example' 	=> '[xoo_el_inline_form active="login"]',
		'atts' 		=> array(
			array(
				'active',
				'login, register',
				'login',
				'Which form to open on click'
			)
		)
	)
);

return apply_filters( 'xoo_el_shortcode_info_tab', $shortcodes );

?>