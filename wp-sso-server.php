<?php
/*
Plugin Name: WP SSO server
Description: Mi servidor de sso para wordpress, este plugin <strong>convierte a tu wordpress en el servidor de login de otro wordpress</strong>, sin DBs, sin cookies.
Version: 1.0
Author: ferro.mariano
Author URI: https://gitlab.com/ferromariano
Text Domain: wp-sso-server
*/

if ( !defined( 'ABSPATH' ) ) { exit; }

class wp_sso_server
{
	
	static $instance = null;

	private $sites = array();

	function __construct() {
		$this->sites = get_option('wp_sso_server_sites', array());
	}

	function genNonceCode($v) { return AUTH_KEY.NONCE_KEY.$v; }

	function createdNonce($v) { return time(); }
	function  verifyNonce($v, $a) { return true; }

	function dataUserPwd_encode($p) { return md5(NONCE_KEY.$p.AUTH_KEY); }
	function dataUser_encode($user) {
		return $this->dataUserPwd_encode($user->user_pass)
						.'_'.
						$user->ID
						.'_'.
						time();

	}

	function dataUser_decode($d) {
		$tmp = explode('_', $d);
		return array(
      'pws_sum' => isset($tmp[0]) ? $tmp[0] : null,
      'user_id' => isset($tmp[1]) ? $tmp[1] : null,
      'time'    => isset($tmp[2]) ? $tmp[2] : null,
		);
		return explode('_', $d);
	}

	function dataUser_valid($d) {
		return is_array($d) ? $d == 3 ? true : false : false;
	}


	function api_is_login() {

		$current_user = wp_get_current_user();
		$data = array();
		$data['is_login']       = $current_user->exists();
		$data = apply_filters( 'wp_sso_server_response_is_login', $data, $current_user );


		if (is_array($data) && count($data) == 1) {
			if ($data['is_login']) {
				$data['data_user'] = array(
					'display_name' => $current_user->display_name,
				);
			}
		}

		if ($data['is_login']) {
			$token = base64_encode( $this->dataUser_encode($current_user) );
			$data['user_token'] = array(
				'token' => $token,
				'nonce' => $this->createdNonce( $this->genNonceCode($token) )
			);
		}

		$this->__responce_json($data);
	}

	function api_get_data($site_id) {
		$rs = $_REQUEST;

		if (!$this->checkSiteHashRequest($site_id)) { $this->__responce_json(array('error'=>1011, 'error_text' => 'invalida data')); exit(); }

		if (!isset($rs['data'])) { $this->__responce_json(array('error'=>1000, 'error_text' => 'invalida data')); exit(); }

		$rs = base64_decode($rs['data']);
		if (!isset($rs)) { $this->__responce_json(array('error'=>1001, 'error_text' => 'invalida data')); exit(); }

		$rs = json_decode($rs, true);

		if (!is_array($rs)) { $this->__responce_json(array('error'=>1002, 'error_text' => 'invalida data')); exit(); }

		if (!$this->verifyNonce( $rs['nonce'], $this->genNonceCode($rs['token']) )) { $this->__responce_json(array('error'=>1004, 'error_text' => 'invalida data')); exit(); }


		$data_user = base64_decode($rs['token']);
		if (!$data_user) { $this->__responce_json(array('error'=>1005, 'error_text' => 'invalida data')); exit(); }

		$data_user = $this->dataUser_decode($data_user);

		if ($this->dataUser_valid($data_user)) { $this->__responce_json(array('error'=>1006, 'error_text' => 'invalida data')); exit(); }

		if ($data_user['time'] < 1) { $this->__responce_json(array('error'=>1007, 'error_text' => 'invalida data')); exit(); }

		if ( (time()-$data_user['time']) > 360) { $this->__responce_json(array('error'=>1008, 'error_text' => 'invalida data')); exit(); }

		$user = get_user_by('ID', $data_user['user_id']);
		if ( !( $user instanceof WP_User ) ) { $this->__responce_json(array('error'=>1009, 'error_text' => 'invalida data')); exit(); }

		if ( $this->dataUserPwd_encode($user->user_pass) != $data_user['pws_sum'] ) {
			$this->__responce_json(array('error'=>1010, 'error_text' => 'invalida data')); exit();
		}

		$ar_user_data = json_decode( json_encode($user->data) , true);

		unset($ar_user_data['user_pass']);
		unset($ar_user_data['ID']);
		unset($ar_user_data['user_activation_key']);
		unset($ar_user_data['user_status']);

		$ar_user_data = apply_filters( 'wp_sso_server_response_get_data', $ar_user_data, $user );

		$this->__responce_json($ar_user_data);
	}

	function checkSiteRequest() {
		if (!isset($_REQUEST['public'])) { return false; }
		$site_id = $_REQUEST['public'];
		if (!isset($this->sites[$site_id])) { return false; }
		return $site_id;
	}

	function checkSiteHashRequest($site_id) {
		$requst = $_REQUEST;
		$hash = $requst['hash'];
		if (!isset($hash)) { return false; }
		unset($requst['hash']);

		$genHash = md5(http_build_query( $requst ) . $this->sites[$site_id]['token'] );

		if ($genHash != $hash) {
			return false;
		}

		return true;
	}

	function initAction($action=null) {
		
		if (!$site_id = $this->checkSiteRequest()) { return; }

	

		$data = apply_filters( 'wp_sso_server_response', null, $site_id, $this );
		if ($data != null) {
			$this->__responce_json($data);
			exit();
		}
		
		switch ($action) {
			case 'is_login':  $this->api_is_login(); break;
			case 'get_data':  $this->api_get_data($site_id); break;
			default:
				$data = apply_filters( 'wp_sso_server_response_'.$action, null, $site_id, $this );
				if ($data != null) {
					$this->__responce_json($data);
				} else {
					return;
				}
			break;
		}
		exit();
	}


	function __responce_json($data) {
		if (isset($_GET['jsonp'])) {
			echo  '+(function(){'.(isset($_GET['callback']) ? $_GET['callback'] : '_callback'). '('.$this->__responce_json_string($data).');})();';
		} else {
			echo $this->__responce_json_string($data);
		}
	}
	function __responce_json_string($data) { return json_encode(array( 'data' => $data, '_' => time(), )); }

	static function getInstance() {
		self::genInstance();
		return self::$instance;
	}

	static function genInstance() {
		if (self::$instance) { return; }
		self::$instance = new wp_sso_server();
	}

	static function wrap() {
		$parse_url = parse_url(get_home_url());
		if (!isset($parse_url['path'])) { return; }
		$parse_url = str_replace(array($parse_url['path'], '/', $_SERVER['QUERY_STRING'], '?'), '', $_SERVER['REQUEST_URI']);
		if (substr($parse_url, 0, 3) != 'sso') { return; }

		$action = substr($parse_url, 3);


		wp_sso_server::getInstance()->initAction($action);
	}
}

if (is_admin()) {
	require realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'admin.php';
	new wp_sso_server_admin();
} else {
	add_action( 'parse_request', array('wp_sso_server', 'wrap'), 20 );
}