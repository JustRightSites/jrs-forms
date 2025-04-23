<?php
/*
Plugin Name: JRS Forms
Plugin URI: JustRightSites.com
Description: Custom forms and reports
Version: 1.0.0
Author: Pat L.
Author URI: JustRightSites.com
Text Domain: jrs-forms
*/

global $wpdb;
define( 'JRSF_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
define( 'JRSF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'JRSF_PREFIX', $wpdb->prefix);

################# CUSTOM #################
## Change these values to display custom titles and page slugs
define( 'PLUGIN_FAUX_NAME', 'JRS Forms' );
define( 'PLUGIN_FAUX_SLUG', 'jrs-forms' );
define( 'REQUEST_FORM_RECIPIENT', get_bloginfo('admin_email') );

## Sample form
add_shortcode( 'request_form', function($args = array()) {
	ob_start();
	require_once(JRSF_PLUGIN_PATH . "/forms/request_form.php");
	return ob_get_clean();
});

## Sample form
add_shortcode( 'contact_form', function($args = array()) {
	ob_start();
	require_once(JRSF_PLUGIN_PATH . "/forms/contact_form.php");
	return ob_get_clean();
});
################# CUSTOM #################

add_action( 'admin_menu', function() {
    add_menu_page( PLUGIN_FAUX_NAME, PLUGIN_FAUX_NAME, 'manage_options', 'jrs-forms', 'display_reports_dashboard', 'dashicons-media-text', 150 );
});

add_action( 'wp_enqueue_scripts', function() {
	global $post;
	$slug = (isset($post->post_name)) ? $post->post_name : ""; 
	wp_enqueue_script( 'jrs', JRSF_PLUGIN_URI . "assets/js/jrs-forms.js", array ( 'jquery' ), 1.1, true);
	@wp_localize_script( 'jrs', 'ajaxurl', admin_url( 'admin-ajax.php' ));
	wp_enqueue_style( 'jrs', JRSF_PLUGIN_URI . "assets/css/jrs-forms.css?t=" . time(), null );

});

add_action( 'admin_enqueue_scripts', function() {
	$page = (isset($_GET['page'])) ? strtolower($_GET['page']) : "";
	if (strpos($page, "jrs-forms") !== false) {
		wp_enqueue_script( 'jrs-forms_admin', JRSF_PLUGIN_URI . "assets/js/jrs-forms_admin.js", array ( 'jquery' ), 1.1, true);
		@wp_localize_script( 'jrs-forms_admin', 'ajaxurl', admin_url( 'admin-ajax.php' ));
		wp_enqueue_style( 'jrs-forms_admin', JRSF_PLUGIN_URI . "assets/css/jrs-forms_admin.css?t=" . time(), null );
	}

});

add_action( 'activate_plugin', function ( $plugin, $network_wide ) {
	if ($plugin === 'jrs-forms/jrs-forms.php') {
		create_jrs_forms_tables();
	}
}, 10, 2 );

function display_reports_dashboard() {
	global $wpdb;
	$form_name_options = get_form_options();
    require_once(JRSF_PLUGIN_PATH . "/jrs-forms-dashboard.php");
}

function get_form_options() {
	global $wpdb;
	$return = "";
	
	$sql = "SELECT DISTINCT form_name FROM " . JRSF_PREFIX . "jrs_forms ORDER BY form_name";
	$rs = $wpdb->get_results($sql);

	if (is_null($rs) || !is_array($rs) || count($rs) === 0) {
		return $return;
	}

	foreach ($rs as $record) {
		$return .= "<option value='" . $record->form_name . "'>" . $record->form_name . "</option>\n";
	}
	
	return $return;
	
}

function create_jrs_forms_tables() {
	
	global $wpdb;

	$sql = "SHOW TABLES LIKE '" . JRSF_PREFIX . "jrs_forms'";
	$rs = $wpdb->get_results($sql);

	if (empty($rs)) {
		$sql = "CREATE TABLE " . JRSF_PREFIX . "jrs_forms (
				ID int(11) NOT NULL,
				form_name varchar(25) NOT NULL,
				content text NOT NULL,
				date_created datetime NOT NULL DEFAULT current_timestamp()
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
		$wpdb->query($sql);

		$sql = "ALTER TABLE " . JRSF_PREFIX . "jrs_forms ADD PRIMARY KEY (ID)";
		$wpdb->query($sql);

		$sql = "ALTER TABLE " . JRSF_PREFIX . "jrs_forms MODIFY ID int(11) NOT NULL AUTO_INCREMENT";
		$wpdb->query($sql);

	}
	
}
##################### AJAX ####################
function jrs_forms_run_report() {
	
	global $wpdb;
	$response = array();
	$response['stat'] = "ok";
	$response['data'] = "";
	$post = $_POST;
	
	$sub_start = (isset($post['sub_start'])) ? $post['sub_start'] : "";
	$sub_end = (isset($post['sub_end'])) ? $post['sub_end'] : "";
	$form_name = (isset($post['form_name'])) ? $post['form_name'] : "";
	if ($sub_end === "") { $sub_end = "3000-01-01"; }

	$sub_start = $sub_start . " 00:00:00";
	$sub_end = $sub_end . " 23:59:59";

	if ($form_name === "") {
		$sql = "SELECT * FROM " . JRSF_PREFIX . "jrs_forms WHERE content != '' AND content != '[]' AND date_created >= '$sub_start' AND date_created <= '$sub_end' ORDER BY form_name, date_created DESC";
	}else{
		$sql = "SELECT * FROM " . JRSF_PREFIX . "jrs_forms WHERE content != '' AND content != '[]' AND date_created >= '$sub_start' AND date_created <= '$sub_end' AND form_name = '$form_name' ORDER BY form_name, date_created DESC";
	}

	$rs = $wpdb->get_results($sql);
	
	$content = $rec = $tbl_header = $hdr = "";
	$count = 0;
	if (!empty($rs)) {
		foreach ($rs as $record) {

			$this_form_name = $record->form_name;
			if ($this_form_name !== $form_name) {
				$form_name = $this_form_name;
				$tbl_header = $hdr = "";
				$count+=1;
			}

			$rec = "<tr>";
			
			if ($tbl_header === "") $hdr .= "<th>ID</th>";
			$rec .= "<td>" . $record->ID . "</td>";
			if ($tbl_header === "") $hdr .= "<th>Form Name</th>";
			$rec .= "<td>" . $record->form_name . "</td>";
			
			$cont = json_decode($record->content);
			foreach ($cont as $key => $value) {
				if ($tbl_header === "") $hdr .= "<th>" . ucfirst(str_replace("_", " ", $key)) . "</th>";
				$rec .= "<td>" . $value . "</td>";
			}
			
			if ($tbl_header === "") $hdr .= "<th>Date</th>";
			$rec .= "<td>" . $record->date_created . "</td>";
			$rec .= "</tr>";

			if ($tbl_header === "" && $rec !== "" && $hdr !== "") {
				$tbl_header = "<table class='jrs-forms_report'>\n<tr>" . $hdr . "</tr>";
				if ($count === 1) { // first table
					$content = $tbl_header . $rec;
				}else{ // rest of tables
					$content .= "</table>\n" . $tbl_header . $rec;
				}
			}else{
				$content .= $rec;
			}

		}
	}
	
	$content .= "</table>\n";

	$content = "<div id='jrs-forms_controls'><button class='print'>Print</button><button class='export'>Export</button></div>\n" . $content;

	if (is_null($rs) || !is_array($rs) || count($rs) === 0) {
		$response['stat'] = "no";
		$response['msg'] = "No data returned for the requested criteria.";
		echo json_encode($response);
		wp_die();
	}

	$response['data'] = $content;
	$response['msg'] = count($rs) . " records found";	
	echo json_encode($response);
	wp_die();
	
}
add_action( 'wp_ajax_jrs_forms_run_report', 'jrs_forms_run_report');
add_action( 'wp_ajax_nopriv_jrs_forms_run_report', 'jrs_forms_run_report' );

function send_request() {

	global $wpdb;
	$response = array();
	$response['stat'] = "ok";
	$response['data'] = "";
	
	$con = array();
	$msg = $content = "";
	$pst = $_POST;

	$form = (isset($pst['form_id'])) ? $pst['form_id'] : '';
	$flds = (isset($pst['flds'])) ? json_decode(stripslashes($pst['flds'])) : '';
	$vals = (isset($pst['vals'])) ? json_decode(stripslashes($pst['vals'])) : '';
	
	if ($form !== "") {
		$form = ucwords(str_replace("_", " ", $form));
	}
	
	if (is_array($flds)) {
		foreach ($flds as $key => $fld) {
			if ($fld === 'email') { $from_email = $vals[$key]; }
			$con[$fld] = htmlentities($vals[$key], ENT_QUOTES);
			$msg .= ucfirst($fld) . ": " . $vals[$key] . PHP_EOL;
		}
	}

	$content = json_encode($con);
	$sql = "INSERT INTO " . JRSF_PREFIX . "jrs_forms (form_name, content) VALUES ('$form', '$content')";
	$wpdb->query($sql);

	## using PHP_EOL in the header makes it fail
	$headers = 'From: ' . $from_email . "\r\n" .
    'Reply-To: ' . $from_email . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	$form_name = ucwords(str_ireplace("_", " ", $form));
	$subject = "$form_name Form Submission from the " . site_url() . " website.";
	$message = "The following was submitted via the $form form:" . PHP_EOL . PHP_EOL;
	$message .= $msg;

	if (!mail(REQUEST_FORM_RECIPIENT, $subject, $message, $headers)) {
		$response['stat'] = "no";
		$response['data'] = "Message could not be sent!";
	}else{
		$response['data'] = "Message sent!";
	}

	echo json_encode($response);
	wp_die();
	
}
add_action( 'wp_ajax_send_request', 'send_request');
add_action( 'wp_ajax_nopriv_send_request', 'send_request' );
