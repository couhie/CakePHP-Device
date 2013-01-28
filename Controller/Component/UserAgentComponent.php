<?php
/**
 * UserAgentComponent.php
 * @author kohei hieda
 *
 */
class UserAgentComponent extends Component {

	const DOCOMO = 'docomo';
	const AU = 'au';
	const SOFTBANK = 'softbank';
	const GOOGLE = 'google';
	const YAHOO = 'yahoo';
	const WILLCOM = 'willcom';
	const IPOD = 'ipod';
	const IPAD = 'ipad';
	const IPHONE = 'iphone';
	const ANDROID = 'android';
	const BLACKBERRY = 'blackberry';
	const SMART = 'smart';
	const PC = 'pc';

	var $_controller = null;
	var $device = null;

	function initialize(&$controller) {
		$this->_controller = $controller;
	}

	function startup(&$controller) {
	}

	function beforeRender(&$controller) {
	}

	function beforeRedirect(&$controller) {
	}

	function shutdown(&$controller) {
	}

	function getAllDevices() {
		return array(
			self::DOCOMO,
			self::AU,
			self::SOFTBANK,
			self::GOOGLE,
			self::YAHOO,
			self::WILLCOM,
			self::IPOD,
			self::IPAD,
			self::IPHONE,
			self::ANDROID,
			self::BLACKBERRY,
			self::SMART,
			self::PC);
	}

	function getDevice() {
		if (!empty($this->device)) {
			return $this->device;
		}
		$userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		if (strpos($userAgent, 'DoCoMo/') !== false) {
			$this->device = self::DOCOMO;
		} else if (strpos($userAgent, 'KDDI-') !== false) {
			$this->device = self::AU;
		} else if (strpos($userAgent, 'SoftBank/') !== false || strpos($userAgent, 'Vodafone/') !== false || strpos($userAgent, 'J-PHONE/') !== false) {
			$this->device = self::SOFTBANK;
		} else if (strpos($userAgent, 'Googlebot-Mobile') !== false) {
			$this->device = self::GOOGLE;
		} else if (strpos($userAgent, 'Y!J-SRD/1.0') !== false || strpos($userAgent, 'Y!J-MBS/1.0') !== false) {
			$this->device = self::YAHOO;
		} else if (strpos($userAgent, 'WILLCOM') !== false || strpos($userAgent, 'DDIPOCKET') !== false) {
			$this->device = self::WILLCOM;
		} else if (strpos($userAgent, 'iPod') !== false) {
			$this->device = self::IPOD;
		} else if (strpos($userAgent, 'iPad') !== false) {
			$this->device = self::IPAD;
		} else if (strpos($userAgent, 'iPhone') !== false) {
			$this->device = self::IPHONE;
		} else if (strpos($userAgent, 'Android') !== false) {
			$this->device = self::ANDROID;
		} else if (strpos($userAgent, 'blackberry') !== false) {
			$this->device = self::BLACKBERRY;
		} else {
			//その他スマートフォンの判定
			$smartArray = array(
				'dream',		// Pre 1.5 Android
				'CUPCAKE',		// 1.5+ Android
				'webOS',		// Palm Pre Experimental
				'incognito',	// Other iPhone browser
				'webmate',		// Other iPhone browser
			);
			foreach ($smartArray as $smart) {
				if (strpos($userAgent, $smart) !== false) {
					$this->device = self::SMART;
				}
			}
			if (empty($this->device)) {
				$this->device = self::PC;
			}
		}
		return $this->device;
	}

	function isTouch() {
		if (empty($this->device)) {
			$this->getDevice();
		}
		if (in_array($this->device, array(self::IPOD, self::IPAD, self::IPHONE, self::ANDROID, self::BLACKBERRY))) {
			return true;
		}
		return false;
	}

	function isIos() {
		if (empty($this->device)) {
			$this->getDevice();
		}
		if (in_array($this->device, array(self::IPOD, self::IPAD, self::IPHONE))) {
			return true;
		}
		return false;
	}

	function isGalap() {
		if (empty($this->device)) {
			$this->getDevice();
		}
		if (in_array($this->device, array(self::DOCOMO, self::AU, self::SOFTBANK, self::WILLCOM))) {
			return true;
		}
		return false;
	}

	function appendSidForHtml() {
		$body = $this->_controller->response->body();
		if ($this->_controller->UserAgent->isGalap()) {
			$sessionName = session_name();
			$sessionId = session_id();
			$body = preg_replace(
				'#href="(?:(?!\#|http:|ftp:|mailto:|tel:|sms:)|(?=http://'.str_replace('.', '\.', $_SERVER['SERVER_NAME']).'))([^"]+)"#',
				'href="$1?guid=on&'.$sessionName.'='.$sessionId.'"',
				$body);
			$body = preg_replace(
				'#action="(?:(?!\#|http:|ftp:|mailto:|tel:|sms:)|(?=http://'.str_replace('.', '\.', $_SERVER['SERVER_NAME']).'))([^"]+)"#',
				'action="$1?guid=on&'.$sessionName.'='.$sessionId.'"',
				$body);
		}
		$this->_controller->response->body($body);
	}

	function appendSidForUrl($str) {
		if ($this->_controller->UserAgent->isGalap()) {
			$sessionName = session_name();
			$sessionId = session_id();

			if (is_array($str)) {
				$str['?'] = array(
					'guid'=>'on',
					$sessionName=>$sessionId);
			} else if (is_string($str)) {
				if (!preg_match('#^http[s]?://#', $str)) {
					$str = sprintf('%s?guid=on&%s=%s', $str, $sessionName, $sessionId);
				}
			}
		}
		return $str;
	}

}