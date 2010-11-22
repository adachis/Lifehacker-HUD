<?php
/**
 * Include (require) this page if your application wish to use the class Gmailer.
 * This page (libgmailer.php) is the definition of 3 classes: GMailer, GMailSnapshot
 * and Debugger.
 *
 * @package libgmailer.php
 */
 
/**
 * Constant defined by application author. Set it to true if the class is used as
 * a module of an online office app or other situation where PHP Session should NOT
 * by destoryed after signing out from Gmail.
 *
 * @var bool
 */
define("GM_USE_LIB_AS_MODULE",		false);	// Normal operation

/**#@+ 
 * URL's of Gmail.
 * @var string 
 */
define("GM_LNK_GMAIL",        		"https://mail.google.com/mail/");
define("GM_LNK_GMAIL_HTTP",        	"http://mail.google.com/mail/");
define("GM_LNK_LOGIN",				"https://www.google.com/accounts/ServiceLoginAuth");
/** 
 * @deprecated
 */
define("GM_LNK_LOGOUT",				"https://mail.google.com/mail/?logout");
define("GM_LNK_REFER",				"https://www.google.com/accounts/ServiceLoginBox?service=mail&continue=https%3A%2F%2Fmail.google.com%2Fmail");
define("GM_LNK_CONTACT",			"https://mail.google.com/mail/?view=cl&search=contacts&pnl=a");
define("GM_LNK_ATTACHMENT",			"https://mail.google.com/mail/?view=att&disp=att");
define("GM_LNK_ATTACHMENT_ZIPPED",	"https://mail.google.com/mail/?view=att&disp=zip");
// Added by Neerav; 5 June 2005
define("GM_LNK_INVITE_REFER",	 	"https://www.google.com/accounts/ServiceLoginBox?service=mail&continue=https%3A%2F%2Fmail.google.com%2Fmail");
define("GMAIL_FILTER_REFERRER_URL",	"https://mail.google.com/mail/?&pnl=f");
define("GMAIL_CONTACT_REFERRER_URL","https://mail.google.com/mail/?&search=contacts&ct_id=1&cvm=2&view=ct");
define("GM_USER_AGENT", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.8) Gecko/20050516 Firefox/1.0.4");
/**#@-*/

/**#@+ 
 * Constants defining Gmail content's type.
 * @var int 
*/
define("GM_STANDARD",			0x001);
define("GM_LABEL",				0x002);
define("GM_CONVERSATION",		0x004);
define("GM_QUERY",				0x008);
define("GM_CONTACT",			0x010);
define("GM_PREFERENCE",			0x020);
/**#@-*/

/**#@+ 
 * Constants defining Gmail action.
 * @var int 
*/
/**
 * Apply label to conversation
*/
define("GM_ACT_APPLYLABEL",		1);
/**
 * Remove label from conversation
*/
define("GM_ACT_REMOVELABEL",	2);
/**
 * Star a conversation
*/
define("GM_ACT_STAR",			3);
/**
 * Remove a star from (unstar) a conversation
*/
define("GM_ACT_UNSTAR",			4);
/**
 * Mark a conversation as spam
*/
define("GM_ACT_SPAM",			5);
/**
 * Unmark a conversation from spam
*/
define("GM_ACT_UNSPAM",			6);
/**
 * Mark conversation as read
*/
define("GM_ACT_READ",			7);
/**
 * Mark conversation as unread
*/
define("GM_ACT_UNREAD",			8);
/**
 * Trash a conversation
*/
define("GM_ACT_TRASH",			9);
/**
 * Directly delete a conversation
*/
define("GM_ACT_DELFOREVER",		10);
/**
 * Archive a conversation
*/
define("GM_ACT_ARCHIVE",		11);
/**
 * Move conversation to Inbox
*/
define("GM_ACT_INBOX",			12);
/**
 * Move conversation out of Trash
*/
define("GM_ACT_UNTRASH",		13);
/**
 * Discard a draft
*/
define("GM_ACT_UNDRAFT",		14);
/**
 * Trash individual message.
*/ 
define("GM_ACT_TRASHMSG",		15);		
/**
 * Delete spam, forever.
*/ 
define("GM_ACT_DELSPAM",		16);
/**
 * Delete trash message, forever.
*/ 
define("GM_ACT_DELTRASHED",		17);
/**#@-*/

/**#@+ 
 * Other constants.
*/
define("GM_VER", "0.8.0");
define("GM_COOKIE_KEY",			"LIBGMAILER");
define("GM_COOKIE_IK_KEY",		"LIBGMAILER_IdKey");	// Added by Neerav; 6 July 2005
define("GM_USE_COOKIE",			0x001);
define("GM_USE_PHPSESSION",   0x002);
/**#@-*/


/**
 * Class GMailer is the main class/library for interacting with Gmail (Google's
 * free webmail service) with ease.
 * 
 * <b>Acknowledgement</b><br/>It is not completely built from scratch. It is based on: "Gmail RSS feed in PHP"
 * by thimal, "Gmail as an online backup system" by Ilia Alshanetsky, and "Gmail
 * Agent API" by Johnvey Hwang and Eric Larson. 
 *
 * Special thanks to Eric Larson and all other users, testers, and forum posters
 * for their bug reports, comments and advices.
 *
 * @package GMailer
 * @author Gan Ying Hung <ganyinghung|no@spam|users.sourceforge.net>
 * @author Neerav Modi <neeravmodi|no@spam|users.sourceforge.net>
 * @link http://gmail-lite.sourceforge.net Project homepage
 * @link http://sourceforge.net/projects/gmail-lite Sourceforge project page
 * @version 0.8.0-rc
*/
class GMailer {
   /**#@+
    * @access private
    * @var string
   */
	var $cookie_str;
	var $login;
	var $pwd;
	/**
	 * @author Neerav
	 * @since 13 Aug 2005
	*/
	var $gmail_data;
	/**
	 * Raw packet
	*/
	var $raw;
	/**
	 * Raw packet for contact list
	*/
	var $contact_raw;
	var $timezone;
	var $use_session;
	var $proxy_host;
	var $proxy_auth;	
	/**#@-*/	 
	
	/**
	 * @access public
	 * @var bool
	*/
   var $created;
   /**
	 * Status of GMailer
	 *
	 * If something is wrong, check this class property to see what is
	 * going wrong.
	 *
	 * @author Neerav
	 * @since 8 July 2005
	 * @var mixed[]
	 * @access public
	*/
	var $return_status = array();

	
	/**
	 * Constructor of GMailer
	 *
	 * During the creation of GMailer object, it will perform several tests to see
	 * if the cURL extension is available or not. However, 
	 * note that the constructor will NOT return false or null even if these tests
	 * are failed. You will have to check the class property {@link GMailer::$created} to see if
	 * the object "created" is really, uh, created (i.e. working), and property
	 * {@link GMailer::$return_status} or method {@link GMailer::lastActionStatus()} to see what was going wrong.
	 *
	 * Example:
	 * <code>
	 * <?php
	 *    $gmailer = new GMailer();
	 *    if (!$gmailer->created) {
	 *       echo "Error message: ".$gmailer->lastActionStatus("message");
	 *    } else {
	 *       // Do something with $gmailer
	 *    }
	 * ?>
	 * </code>
	 *
    * A typical usage og GMailer object would be like this:
    * <code>
    * <?php
    *    require_once("libgmailer.php");
    *
    *    $gmailer = new GMailer();
    *    if ($gmailer->created) {
    *       $gmailer->setLoginInfo($gmail_acc, $gmail_pwd, $my_timezone);
    *       $gmailer->setProxy("proxy.company.com");
    *       if ($gmailer->connect()) {
    *          // GMailer connected to Gmail successfully.
    *          // Do something with it.
    *       } else {
    *          die("Fail to connect because: ".$gmailer->lastActionStatus());
    *       }
    *    } else {
    *       die("Failed to create GMailer because: ".$gmailer->lastActionStatus());
    *    }
    * ?>
    * </code>	 
	 *
	 * @see GMailer::$created, GMailer::$return_status, GMailer::lastActionStatus()
	 * @return GMailer
	*/
	function GMailer() {
		// GMailer needs "curl" extension to work
		Debugger::say(date("l dS of F Y h:i:s A",time()));
		//Debugger::say("Constructing GMailer...");
		$this->created = true;
		if (!extension_loaded('curl')) {
			// Added to gracefully handle multithreaded servers; by Neerav; 8 July 2005
			if (isset($_ENV["NUMBER_OF_PROCESSORS"]) and ($_ENV["NUMBER_OF_PROCESSORS"] > 1)) {
				Debugger::say("Constructing FAILED: Using a multithread server. Ensure php_curl.dll has been enabled (uncommented) in your php.ini.");
				$this->created = false;
				$a = array(
					"action" 		=> "constructing GMailer object",
					"status" 		=> "failed",
					"message" 		=> "libgmailer: Using a multithread server. Ensure php_curl.dll has been enabled (uncommented) in your php.ini.",
				);
				array_unshift($this->return_status, $a);

			} else {
				if (!dl('php_curl.dll') && !dl('curl.so')) {
					Debugger::say("Constructing FAILED: unable to load curl extension.");
					$this->created = false;
					$a = array(
						"action" 		=> "constructing GMailer object",
						"status" 		=> "failed",
						"message" 		=> "libgmailer: unable to load curl extension.",
					);
					array_unshift($this->return_status, $a);
				}
			}
		}
		if (!function_exists("curl_setopt")) {			  
			Debugger::say("Constructing FAILED: No curl.");
			$this->created = false;
			$a = array(
				"action" 		=> "constructing GMailer object",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: No curl.",
			);
			array_unshift($this->return_status, $a);
		}
		$this->login = 0;
		$this->pwd = 0;
		$this->proxy_host = "";
		$this->proxy_auth = "";
		$this->use_session = 2;
		Debugger::say("Constructing completed.");
		if ($this->created == true) {
			$a = array(
				"action" 		=> "constructing GMailer object",
				"status" 		=> "success",
				"message" 		=> "libgmailer: Constructing completed.",
			);
			array_unshift($this->return_status, $a);
		}
	}
	
	/**
	* Set Gmail's login information.
	*
	* @return void
	* @param string $my_login Gmail's login name (without @gmail.com)
	* @param string $my_pwd Password
	* @param float $my_tz Timezone with respect to GMT, but in decimal. For example, -2.5 for -02:30GMT
	*/
	function setLoginInfo($my_login, $my_pwd, $my_tz) {
		$this->login = $my_login;
		$this->pwd = $my_pwd;
		$this->timezone = strval($my_tz*-60);
		Debugger::say("LoginInfo set.");
		// Added return_status; by Neerav; 16 July 2005
		$a = array(
			"action" 		=> "set login info",
			"status" 		=> "success",
			"message" 		=> "libgmailer: LoginInfo set.",
		);
		array_unshift($this->return_status, $a);
	}
	
	/**
	* Setting proxy server.
	*
	* Example:
	* <code>
	* <?php
	*    // proxy server requiring login
	*    $gmailer->setProxy("proxy.company.com", "my_name", "my_pwd");
	* 
	*    // proxy server without login
	*    $gmailer->setProxy("proxy2.company.com", "", "");
	* ?>
	* </code>
	*	
	* @return void
	* @param string $host Proxy server's hostname
	* @param string $username User name if login is required
	* @param string $pwd Password if required
	*/
	function setProxy($host, $username, $pwd) {
		if (strlen($this->proxy_host) > 0) {
			$this->proxy_host = $host;
			if (strlen($username) > 0 || strlen($pwd) > 0) {
				$this->proxy_auth = $username.":".$pwd;
			}
			$a = array(
				"action" 		=> "set proxy",
				"status" 		=> "success",
				"message" 		=> "libgmailer: Proxy set.",
			);
			array_unshift($this->return_status, $a);
		} else {
			$a = array(
				"action" 		=> "set proxy",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: no hostname supplied.",
			);
			array_unshift($this->return_status, $a);
		}
	}
	
	/**
	* Setting session management method.
	* 
	* You have to select a session management method so that GMailer would "remember"
	* your identity. Method has to be one of the following values:
	* 1. {@link GM_USE_COOKIE} | !{@link GM_USE_PHPSESSION} (if your server does not have PHP Session installed)
	* 2. !{@link GM_USE_COOKIE} | {@link GM_USE_PHPSESSION} (if your server have PHP Session installed, and don't want to set browser's cookie)
	* 3. {@link GM_USE_COOKIE} | {@link GM_USE_PHPSESSION} (if your server have PHP Session installed, and would like to use cookie to store session)
	*
	* @return void
	* @param int $method	
	*/
	function setSessionMethod($method) {
		if ($method & GM_USE_PHPSESSION) {
			if (!extension_loaded('session')) {
				// Added to gracefully handle multithreaded servers; by Neerav; 8 July 2005
				if (isset($_ENV["NUMBER_OF_PROCESSORS"]) and ($_ENV["NUMBER_OF_PROCESSORS"] > 1)) {
					Debugger::say("Constructing FAILED: Using a multithread server. Ensure php_session.dll has been enabled (uncommented) in your php.ini.");
					$this->setSessionMethod(GM_USE_COOKIE | !GM_USE_PHPSESSION);  // forced to use custom cookie
					return;
				} else {
					// Changed extension loading; by Neerav; 18 Aug 2005
					//if (!dl('php_session.dll') && !dl('session.so')) {
					if (dl(((PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '') . 'session.' . PHP_SHLIB_SUFFIX)) {
						Debugger::say("Session: unable to load PHP session extension.");
						$this->setSessionMethod(GM_USE_COOKIE | !GM_USE_PHPSESSION);  // forced to use custom cookie
						return;
					}
				}
			}
			if (!($method & GM_USE_COOKIE)) {
				@ini_set("session.use_cookies",	 0);
				@ini_set("session.use_trans_sid", 1);					 
				Debugger::say("Session: not using cookie.");
			} else {
				@ini_set("session.use_cookies",	 1);
				@ini_set("session.use_trans_sid", 0);
				Debugger::say("Session: using cookie.");					 
			}
			@ini_set("arg_separator.output", '&amp;');
			session_start();
			Debugger::say("Session: using PHP session.");
			$this->use_session = true;			  
		} else {
			//@ini_set("session.use_only_cookies", 1);
			@ini_set("session.use_cookies",	 1);
			@ini_set("session.use_trans_sid", 0);
			Debugger::say("Session: using cookie.");
			$this->use_session = false;
		}
	}			


	/**
	* Connect to Gmail without setting any session/cookie
	*
	* @return bool Connect to Gmail successfully or not
	*/
	function connectNoCookie() {
		Debugger::say("Start connecting without cookie...");
		
		$postdata  = "service=mail";
		$postdata .= "&Email=".urlencode($this->login);
		$postdata .= "&Passwd=".urlencode($this->pwd);
		$postdata .= "&null=Sign%20in";
		$postdata .= "&continue=https%3A%2F%2Fmail.google.com%2Fmail%3F";
		// Added by Neerav; 28 June 2005
		$postdata .= "&rm=false"; 	// not required but appears
		$postdata .= "&hl=en";
		
		// Added by Neerav; 8 July 2005
		// login challenge
		//id="logintoken" value="cpVIYkaTDTkVZ9ZHNM_384GVV79tjExj-ac2NFVgS3AVbm7lEn7Q967JHKe_sDzMP7plluysBDJRyUwkjuHQFw:D0cwussDwRyIgJGSdeMMnA" name="logintoken"> 
		if (isset($this->logintoken)   and $this->logintoken != "")   $postdata .= "&logintoken=".$logintoken;
		if (isset($this->logincaptcha) and $this->logincaptcha != "") $postdata .= "&logincaptcha=".$logincaptcha;

		
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_URL, GM_LNK_LOGIN);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
		$this->CURL_PROXY($c);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_HEADER, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
		$this->gmail_data = curl_exec($c);
		curl_close($c);
		
		//Debugger::say("login result: ".$result);
		/** from here we have to perform "cookie-handshaking"... **/
		Debugger::say("Start cookie-handshaking...");
		
		$cookies = GMailer::get_cookies($this->gmail_data);
		Debugger::say("1st phase cookie obtained: ".$cookies);

		$this->logintoken	= "";
		$this->logincaptcha	= "";

		if (strpos($this->gmail_data, "errormsg_0_Passwd") > 0) {
			$this->cookie_str = "";
			$this->cookie_ik_str = "";
			// Added appropriate error message; by Neerav; 8 July 2005
			$a = array(
				"action" 		=> "sign in",
				"status" 		=> "failed",
				"message" 		=> "Username and password do not match. (You provided ".$this->login.")",
				"login_error" 	=> "userpass"
			);
			array_unshift($this->return_status, $a);
			//Debugger::say("login incorrect: ".print_r($a,true));
			Debugger::say("Connect FAILED: user/pass incorrect");
			return false;

		// Added to support login challenge; by Neerav; 8 July 2005
		} elseif (strpos($this->gmail_data, "errormsg_0_logincaptcha") > 0) {
			$this->cookie_str = "";
			$this->cookie_ik_str = "";
			ereg("id=\"logintoken\" value=\"([^\"]*)\" name=\"logintoken\"", $this->gmail_data, $matches);
			
			Debugger::say("Connect FAILED: login challenge");
			//Debugger::say("Connect FAILED: login challenge: ".$this->gmail_data);
			//Debugger::say("ErrorLogin: ".$this->login);
			//Debugger::say("ErrorToken: ".$matches[1]);
			//Debugger::say("logintoken: ".print_r($matches,true));
			// Added appropriate error message; by Neerav; 8 July 2005
			$a = array(
				"action" 		=> "sign in",
				"status" 		=> "failed",
				"message" 		=> "login challenge",
				"login_token"	=> $matches[1],
				//"login_token_img" => urlencode("Captcha?ctoken=".$matches[1]."&amp;email=".$this->login."%40gmail.com"),
				//"login_token_img" => $login_img,
				//"login_cookie" 	=> $login_cookie,
				"login_error" 	=> "challenge"
				
			);
			array_unshift($this->return_status, $a);
			//Debugger::say("login challenge: ".print_r($a,true));
			return false;
		}
		
		
		Debugger::say("Received: ".$this->gmail_data);
		
		/*** js forward path (Gan:  no longer used? 10 Sept 2005)
		$a = strpos($this->gmail_data, "top.location = \"");
		$b = strpos($this->gmail_data, ";", $a);
		$forward = substr($this->gmail_data, $a+16, $b-($a+16)-1);

		// forces relative url into absolute if not already; Added by Neerav; 31 July 2005 
		if (substr($forward,0,8) != "https://") {
			$forward = "https://mail.google.com/accounts/".$forward;
		}
		**/
		$a = strpos($this->gmail_data, "Location: ");
		$b = strpos($this->gmail_data, "\n", $a);
		$forward = substr($this->gmail_data, $a+10, $b-($a+10));
		Debugger::say("Redirecting: ".$forward);
			
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		// Forward url is now absolute instead of relative; Fixed by Gan; 27 July 2005
		//curl_setopt($c, CURLOPT_URL, "https://mail.google.com/accounts/".$forward);
		curl_setopt($c, CURLOPT_URL, $forward);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
		curl_setopt($c, CURLOPT_HEADER, 1);
		$this->CURL_PROXY($c);
		curl_setopt($c, CURLOPT_COOKIE, $cookies);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
		$ret = curl_exec($c);
		curl_close($c); 
		
		$data = GMailer::get_cookies($ret);
		$d = ($data) ? $data : $cookies;
		$d = $d.";TZ=".$this->timezone;
		
		Debugger::say("2nd phase cookie obtained: ".$d);
				 
		$this->cookie_str = $cookies.";".$d;  // the cookie string obtained from gmail
		
		Debugger::say("Finished connecting without cookie.");
		return true;
		
	}		
	
	/**
	* Connect to GMail with default session management settings.
	*
	* @return bool Connect to Gmail successfully or not
	*/
	function connect() {
		if ($this->use_session === 2)
			$this->setSessionMethod(GM_USE_COOKIE | GM_USE_PHPSESSION);	  // by default
		
		// already logged in
		if ($this->login == 0 && $this->pwd == 0) {
			if (!$this->getSessionFromBrowser()) {			  
				return $this->connectNoCookie() && $this->saveSessionToBrowser();
			} else {
				Debugger::say("Connect completed by getting cookie/session from browser/server.");
				return true;
			}

		// log in
		} else {
			// Changed to support login challenge; by Neerav; 8 July 2005 
			//return $this->connectNoCookie() && $this->saveSessionToBrowser();
			if ($this->connectNoCookie()) {
				return $this->saveSessionToBrowser();
			} else {
				return false;
			}
		}
	}
	
	/**
	* See if it is connected to GMail.
	*
	* @return bool
	*/
	function isConnected() {
		return (strlen($this->cookie_str) > 0);
	}

	/**
	* Last action's action, status, message, and other info
	*
	* @param string $request What information you would like to request. Default is "message".
	* @return string
	*/
	function lastActionStatus($request = "message") {
		return $this->return_status[0]["$request"];
	}

	/**
	* Append a random string to url to fool proxy
	*
	* @param string $type Set to "nodash" if you do not want a dash ("-") in random string. Otherwise just leave it blank.
	* @access private
	* @return string Complete URL
	* @author Neerav
	* @since June 2005
	*/
	function proxy_defeat($type = "") {
		$length = 12;
		$seeds = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$string = '';
		$seeds_count = strlen($seeds);
	 	 
		// Generate
		// Changed to also use without dash; by Neerav; 11 Aug 2005
		if ($type == "nodash") {
			for ($i = 0; $length > $i; $i++) {
				$string .= $seeds{mt_rand(0, $seeds_count - 1)};
			}
		} else {
			for ($i = 0; $length > $i; $i++) {
				$string .= $seeds{mt_rand(0, $seeds_count - 1)};
				if ($i == 5) $string .= "-";	// Added by Neerav; 28 June 2005
			}
		}
	 
		return "&zx=".$string;
	}

	/**
	* Fetch contents by URL query. 
	*
	* This is a "low-level" method. Please use {@link GMailer::fetchBox()} for fetching standard contents.
	*
	* @param string $query URL query string
	* @return bool Success or not
	*/
	function fetch($query) {
		if ($this->isConnected() == true) {
			Debugger::say("Start fetching query: ".$query);
			$query .= $this->proxy_defeat();	 // to fool proxy
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);	 
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
			$this->gmail_data = curl_exec($c);
			curl_close($c);
			
			$inbox = str_replace("\n", "", $this->gmail_data);
			$inbox = str_replace("D([", "\nD([", $inbox);
			$inbox = str_replace("]);", "]);\n", $inbox);
			
			//Debugger::say("raw result of fetch: ". print_r($inbox,true));
			
			$regexp = "|D\(\[(.*)\]\);|U"; 
			$matches = "";	 
			preg_match_all($regexp, $inbox, $matches, PREG_SET_ORDER); 
			$packets = array();
			for ($i = 0; $i < count($matches); $i++) {
				$off = 0;
				$tmp = GMailer::parse_data_packet("[".$matches[$i][1]."]", $off);
				if (array_key_exists($tmp[0], $packets) || ($tmp[0]=="mi"||$tmp[0]=="mb"||$tmp[0]=="di")) {
					// Added cl as alternate contact datapack; by Neerav; 15 June 2005
					if ($tmp[0]=="t" || $tmp[0]=="ts" || $tmp[0]=="a" || $tmp[0]=="cl") 
						$packets[$tmp[0]] = array_merge($packets[$tmp[0]], array_slice($tmp, 1));
					if ($tmp[0]=="mi" || $tmp[0]=="mb" || $tmp[0]=="di") {
						if (array_key_exists("mg", $packets))
							array_push($packets["mg"],$tmp);
						else
							$packets["mg"] = array($tmp);
					}									  
				} else {
					$packets[$tmp[0]] = $tmp;
				}
			}
			$this->raw = $packets;
			Debugger::say("Fetch completed.");
			return 1;
		
		} else {	  // not logged in yet				 
			Debugger::say("Fetch FAILED: not connected.");
			return 0;
			
		}
	}
	
	/**
	* Fetch contents from Gmail by type.
	*
	* Content can be one of the following categories:
	* 1. {@link GM_STANDARD}: For standard mail-boxes like Inbox, Sent Mail, All, etc. In such case, $box should be the name of the mail-box: "inbox", "all", "sent", "draft", "spam", or "trash". $paramter would be used for paged results.
	* 2. {@link GM_LABEL}: For user-defined label. In such case, $box should be the name of the label.
	* 3. {@link GM_CONVERSATION}: For conversation. In such case, $box should be the conversation ID.
	* 4. {@link GM_QUERY}: For search query. In such case, $box should be the query string.
	* 5. {@link GM_PREFERENCE}: For Gmail preference. In such case, $box = "".
	* 6. {@link GM_CONTACT}: For contact list. In such case, $box can be either "all", "search" or "detail". When $box = "detail", $parameter is the Contact ID. When $box = "search", $parameter is the search query string.
	*
	* @return bool Success or not
	* @param constant $type Content category 
	* @param mixed $box Content type
	* @param int $parameter Extra parameter. See above.
	* @see GM_STANDARD, GM_LABEL, GM_CONVERSATION, GM_QUERY, GM_PREFERENCE, GM_CONTACT
	*/
	function fetchBox($type, $box, $parameter) {
		if ($this->isConnected() == true) {
			switch ($type) {
				case GM_STANDARD:
					$q = "search=".strtolower($box)."&view=tl&start=".$parameter;
					break;
				case GM_LABEL:
					$q = "search=cat&cat=".$box."&view=tl&start=".$parameter;
					break;
				case GM_CONVERSATION: 
					$q = "search=inbox&ser=1&view=cv";
					if (is_array($box)) {
						$q .= "&th=".$box[0];
						for ($i = 1; $i < count($box); $i++)
							$q .= "&msgs=".$box[$i];
					} else {
						$q .= "&th=".$box;
					}
					break;
				case GM_QUERY:
					$q = "search=query&q=".urlencode($box)."&view=tl&start=".$parameter;
					break;
				case GM_PREFERENCE:
					$q = "view=pr&pnl=g";
					break;
				case GM_CONTACT:
					if (strtolower($box) == "all")
						$q = "view=cl&search=contacts&pnl=a";
					// Added by Neerav; 15 June 2005
					elseif (strtolower($box) == "search")
						$q = "view=cl&search=contacts&pnl=s&q=".$parameter;
					// Added by Neerav; 1 July 2005
					elseif (strtolower($box) == "detail")
						$q = "search=contacts&ct_id=".$parameter."&cvm=2&view=ct";
					else // frequently mailed
						$q = "view=cl&search=contacts&pnl=p";
					break;						
				default:
					$q = "search=inbox&view=tl&start=0&init=1";
					break;
			}
			$this->fetch($q);
			return true;
		} else {
			return false;
		}
	}		 
	
	/**
	* Save all attaching files of conversations to a path.
	*
	* Random number will be appended to the new filename if the file already exists.
	*
	* @return string[] Name of the files saved. False if failed.
	* @param string[] $convs Conversations.
	* @param string $path Local path.
	*/
	function getAttachmentsOf($convs, $path) {
		if ($this->isConnected() == true) {
			if (!is_array($convs)) {
				$convs = array($convs);	 // array wrapper
			}
			$final = array();
			foreach ($convs as $v) {
				if (count($v["attachment"]) > 0) {
					foreach ($v["attachment"] as $vv) {
						$f = $path."/".$vv["filename"];
						while (file_exists($f)) {
							$f = $path."/".$vv["filename"].".".round(rand(0,1999));
						}
						if ($this->getAttachment($vv["id"],$v["id"],$f,false)) {
							array_push($final, $f);
						}
					}
				}
			}
			return $final;
		} else {
			return false;
		}
	}								
	
	/**
	* Save attachment with attachment ID $attid and message ID $msgid to file with name $filename.
	*
	* @return bool Success or not.
	* @param string $attid Attachment ID.
	* @param string $msgid Message ID.
	* @param string $filename File name.
	* @param bool $zipped Save all attachment of message ID $msgid into a zip file.
	*/
	function getAttachment($attid, $msgid, $filename, $zipped=false) {
		if ($this->isConnected() == true) {
			Debugger::say("Start getting attachment...");
			
			if (!$zipped)
				$query = GM_LNK_ATTACHMENT."&attid=".urlencode($attid)."&th=".urlencode($msgid);					  
			else 
				$query = GM_LNK_ATTACHMENT_ZIPPED."&th=".urlencode($msgid);
					
			$fp = fopen($filename, "wb");
			if ($fp) {
				$c = curl_init();
				curl_setopt($c, CURLOPT_FILE, $fp);
				curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
				curl_setopt($c, CURLOPT_URL, $query);
				curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
				$this->CURL_PROXY($c);
				curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);	 
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
				curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
				curl_exec($c);
				curl_close($c);
				fclose($fp);
			} else {
				Debugger::say("FAILED to get attachment: cannot fopen the file.");
				return false;
			}
			Debugger::say("Completed getting attachment.");
			return true;
		} else {
			Debugger::say("FAILED to get attachment: not connected.");
			return false;
		}
	}			
			
	/**
	* Dump everything to output.
	*
	* This is a "low-level" method. Use the method {@link GMailer::fetchBox()} to fetch standard contents from Gmail.
	*
	* @return string Everything received from Gmail.
	* @param string $query URL query string.
	*/
	function dump($query) {
		if ($this->isConnected() == true) {
			Debugger::say("Dumping...");
			$query .= $this->proxy_defeat();	 // to fool proxy
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);	 
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
			$page = curl_exec($c);
			curl_close($c);				
			Debugger::say("Finished dumping ".strlen($page)." bytes.");			  
			return $page;
		} else {	  // not logged in yet				 
			Debugger::say("FAILED to dump: not connected.");
			return "";
			
		}
	}		 
	
	/**
	* Send Gmail. Or save a draft email.
	*
	* Examples:
	* <code>
	* <?php
	*    // Simplest usage: send a new mail to one person:
	*    $gmailer->send("who@what.com", "Hello World", "Cool!\r\nFirst mail!");
	*
	*    // More than one recipients. And with CC:
	*    $gmailer->send("who@what.com, boss@company.com",
	*                   "Hello World",
	*                   "This is second mail.",
	*                   "carbon-copy@company.com");
	*
	*    // With file attachment
	*    $gmailer->send("who@what.com", 
	*                   "Your file", 
	*                   "Here you are!", 
	*                   "", "", "", "", 
	*                   array("path/to/file.zip", "path/to/another/file.tar.gz"));
	*
	*    // more examples...
	* ?>
	* </code>
	*
	* @since 9 September 2005
	* @return bool Success or not. If returned false, please check {@link GMailer::$return_status} or {@link GMailer::lastActionStatus()} for error message.
	* @param string $to Recipient's address. Separated by comma for multiple recipients.
	* @param string $subj Subject line of email.
	* @param string $body Message body of email.
	* @param string $cc Address for carbon-copy (CC). Separated by comma for multiple recipients. $cc = "" for none.
	* @param string $bcc Address for blind-carbon-copy (BCC). Separated by comma for multiple recipients. $bcc = "" for none.
	* @param string $mid Message ID of the replying email. $mid = "" if this is a newly composed email.
	* @param string $tid Conversation (thread) ID of the replying email. $tid = "" if this is a newly composed email.	
	* @param string[] $files File names of files to be attached.
	* @param bool $draft Indicate this email is saved as draft, or not.
	* @param string $orig_df If this email is saved as a <i>modified</i> draft, then set $orig_df as the draft ID of the original draft.
	* @param bool $is_html HTML-formatted mail, or not.
	* @param string $from Send mail as this email address (personality). $from = "" to use your Gmail address (NOT the default one in your settings). Note: you will NOT send your mail successfully if you do not register this address in your Gmail settings panel.
	*/
	function send($to, $subj, $body, $cc="", $bcc="", $mid="", $tid="", $files=0, $draft=false, $orig_df="", $is_html=0, $from="") {
		if ($this->isConnected()) {			
			Debugger::say("Starting to send mail...");
			
			$postdata = array();
			if ($draft == true) {
				$postdata["view"] 	= "sd";
				$postdata["draft"] 	= $orig_df;
				$postdata["rm"] 	= $mid;
				$postdata["th"] 	= $tid;			
			} else {
				$postdata["view"] 	= "sm";
				$postdata["draft"] 	= $orig_df;
				$postdata["rm"] 	= $mid;
				$postdata["th"] 	= $tid;								  
			}
			if (strlen($from) > 0) {
			   $postdata["from"] = $from;
			}
			$postdata["msgbody"] 	= stripslashes($body);
			$postdata["to"] 		= stripslashes($to);
			$postdata["subject"] 	= stripslashes($subj);
			$postdata["cc"] 		= stripslashes($cc);
			$postdata["bcc"] 		= stripslashes($bcc);
			$postdata["cmid"] 		= 1;		  
			$postdata["ishtml"] 	= ($is_html) ? 1 : 0;	  
			
			//echo $this->cookie_str;				 
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				//echo "***".$cc_part."****<br>";				
				$cc_parts = split("=", $cc_part);
				//echo "***".$cc_parts[0]."******".$cc_parts[1]."****<br>";
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$postdata["at"] = $cc_parts[1];
					break;
				}
			}

			if (is_array($files)) {
				for ($i = 0; $i < count($files); $i++) {
					$postdata["file".$i] = "@".realpath($files[$i]);
				}
			}
			//echo $postdata;
			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			curl_setopt($c, CURLOPT_POST, 1);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished sending email.");

			// Added by Neerav; 12 July 2005
			$status = (isset($this->raw["sr"][2])) ? $this->raw["sr"][2] : false;
			$a = array(
				"action" 	=> "send email",
				// $this->raw["sr"][1] // what is this?? 
				"status" 	=> ($status ? "success" : "failed"),
				"message" 	=> (isset($this->raw["sr"][3]) ? $this->raw["sr"][3] : ""),
				"thread_id"	=> (isset($this->raw["sr"][4]) ? $this->raw["sr"][4] : ""),
				// $this->raw["sr"][5] // what is this?? 
				// $this->raw["sr"][6] // what is this?? 
				// $this->raw["sr"][7] // what is this?? 
				// $this->raw["sr"][8] // what is this?? 
				// $this->raw["sr"][9] // what is this?? 
				"sent_num"  => ((isset($this->raw["aa"][1])) ? count($this->raw["aa"][1]) : 0)
			);
			array_unshift($this->return_status, $a);
			//Debugger::say("temp Return status array: ".print_r($a,true));
			//Debugger::say("Return status array: ".print_r($this->return_status,true));
			
			// Changed by Neerav; 12 July 2005
			return $status;
		} else {
			Debugger::say("FAILED to send email: not connected.");
			// Added by Neerav; 12 July 2005
			$a = array(
				"action" 		=> "send email",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected.",
				"thread_id" 	=> $tid,
				"sent_num"  	=> 0
			);
			array_unshift($this->return_status, $a);

			return false;
		}
	}
			
	/**
	* Perform action on messages.
	*
	* Examples:
	* <code>
	* <?php
	*    // Apply label to $message_id
	*    $gmailer->performAction(GM_ACT_APPLYLABEL, $message_id, "my_label");
	*
	*    // Star $message_id
	*    $gmailer->performAction(GM_ACT_STAR, $message_id);
	*
	*    // more examples...
	* ?>
	* </code>
	*
	* @return bool Success or not. If returned false, please check {@link GMailer::$return_status} or {@link GMailer::lastActionStatus()} for error message.
	  Additional return: Gmail returns a full datapack in response
	* @param constant $act Action to be performed.
	* @param string[] $id Message ID.
	* @param string $para Action's parameter:
	* 1. {@link GM_ACT_APPLYLABEL}, {@link GM_ACT_REMOVELABEL}: Name of the label.
	*/
	function performAction($act, $id, $para="") {
		/** quick references:
			define("GM_ACT_APPLYLABEL",		1);
			define("GM_ACT_REMOVELABEL",	2);
			define("GM_ACT_STAR",			3);
			define("GM_ACT_UNSTAR",			4);
			define("GM_ACT_SPAM",			5);
			define("GM_ACT_UNSPAM",			6);
			define("GM_ACT_READ",			7);
			define("GM_ACT_UNREAD",			8);
			define("GM_ACT_TRASH",			9);
			define("GM_ACT_DELFOREVER",		10);
			define("GM_ACT_ARCHIVE",		11);
			define("GM_ACT_INBOX",			12);
			define("GM_ACT_UNTRASH",		13);
			define("GM_ACT_UNDRAFT",		14);
			define("GM_ACT_TRASHMSG",		15);
			define("GM_ACT_DELSPAM",		16);
			define("GM_ACT_DELTRASHED",		17);
		**/
		
		if ($this->isConnected()) {
			Debugger::say("Start performing action...");
			
			if ($act == GM_ACT_DELFOREVER)
				$this->performAction(GM_ACT_TRASH, $id, 0);	// trash it before
			
			$postdata = "act=";
			
			$action_codes = array("ib", "ac_", "rc_", "st", "xst", "sp", "us", "rd", "ur", "tr", "dl", "rc_^i", "ib", "ib", "dd", "dm", "dl", "dl");
			$postdata .= (isset($action_codes[$act])) ? $action_codes[$act] : $action_codes[GM_ACT_INBOX];
			if ($act == GM_ACT_APPLYLABEL || $act == GM_ACT_REMOVELABEL) {
				$postdata .= $para;
			}
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$postdata .= "&at=".$cc_parts[1];
					break;
				}
			}
			
			if ($act == GM_ACT_TRASHMSG) {
				$postdata .= "&m=".$id;
			} else {
				if (is_array($id)) {
					foreach ($id as $t) {
						$postdata .= "&t=".$t;
					}
				} else {
					$postdata .= "&t=".$id;
				}
			}
			$postdata .= "&vp=";
			
			//Debugger::say("message action postdata: ".print_r($postdata,true));

			if ($act == GM_ACT_UNTRASH || $act == GM_ACT_DELFOREVER || $act == GM_ACT_DELTRASHED)
				$link = GM_LNK_GMAIL."?search=trash&view=tl&start=0";
			elseif ($act == GM_ACT_DELSPAM)
				$link = GM_LNK_GMAIL."?search=spam&view=tl&start=0";
			else
				$link = GM_LNK_GMAIL."?search=query&q=&view=tl&start=0";

			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_URL, $link);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);		// Changed by Neerav; 13 July 2005
			curl_close($c);
			
			//Debugger::say("Action result: ".print_r($this->raw,true));
			Debugger::say("Finished performing action.");

			// Added additional return info; by Neerav; 13 July 2005
			$status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "message action",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);
			//Debugger::say("Action result: ".print_r($a,true));
			//Debugger::say("All action results: ".print_r($this->return_status,true));
			return $status;
		} else {
			Debugger::say("FAILED to $action label: not connected.");
			// Added by Neerav; 12 July 2005
			$a = array(
				"action" 		=> "message action",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			return false;
		}
	}			
	
	/**
	* @return bool Success or not.
	* @desc Recover session information.
	*/
	function getSessionFromBrowser() {
		Debugger::say("Start getting session from browser...");
		
		if (!$this->use_session) {
			return $this->getCookieFromBrowser();
		}
		// Changed to support IK; by Neerav; 13 July 2005
		// Last modified by Neerav; 14 Aug 2005
		if (isset($_SESSION[GM_COOKIE_KEY])) {
			$this->cookie_str = base64_decode($_SESSION[GM_COOKIE_KEY]);
			Debugger::say("Completed getting session from server: ".$this->cookie_str);

			if (isset($_SESSION['id_key'])) {
				$this->cookie_ik_str = $_SESSION['id_key'];
				Debugger::say("Completed getting ik from server: ".$this->cookie_ik_str);
			} else {
				Debugger::say("FAILED to read id_key from server.");
			}
			return true;
		} else {
			Debugger::say("FAILED to read ".GM_COOKIE_KEY." or ".'id_key'." from server.");
/* 			Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." from browser."); */
			return false;
		}
	}
	
	/**
	* @return bool Success or not.
	* @desc Get cookies from browser.
	*/
	function getCookieFromBrowser() {
		Debugger::say("Start getting cookie from browser...");
		
		if (!isset($_COOKIE)) {
			Debugger::say("FAILED to get any cookie from browser.");
			return false;
		}
		if (count($_COOKIE) == 0) {
			Debugger::say("FAILED to get non-empty cookie array from browser.");
			return false;
		}
		// Changed to support IK cookie; by Neerav; 8 July 2005
		// Disabled IK cookie requirement
		//if (isset($_COOKIE[GM_COOKIE_KEY]) and isset($_COOKIE[GM_COOKIE_IK_KEY])) {
 		if (isset($_COOKIE[GM_COOKIE_KEY])) {
			$this->cookie_str = base64_decode($_COOKIE[GM_COOKIE_KEY]);
			Debugger::say("Completed getting cookie from browser: ".$this->cookie_str);

			if (isset($_COOKIE[GM_COOKIE_IK_KEY])) {
				$this->cookie_ik_str = base64_decode($_COOKIE[GM_COOKIE_IK_KEY]);
				Debugger::say("Completed getting ik cookie from browser: ".$this->cookie_ik_str);
			}
			return true;
		} else {
			//Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." or ".GM_COOKIE_IK_KEY." from browser.");
			Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." from browser.");
			return false;
		}
	}		 
	
	/**
	* @return bool Success or not.
	* @desc Save session data.
	*/
	function saveSessionToBrowser() {
		Debugger::say("Start saving session to server/browser...");
		
		if ($this->isConnected()) {
			if (!$this->use_session)
				return $this->saveCookieToBrowser();				
			
			$_SESSION[GM_COOKIE_KEY] = base64_encode($this->cookie_str);
			Debugger::say("Just saved session: ".GM_COOKIE_KEY."=".base64_encode($this->cookie_str));
			Debugger::say("Completed saving session to server.");
			return true;
		}
		Debugger::say("FAILED to save session to server/browser: not connected.");
		return false;
	}
	
	/**
	* @return bool Success or not.
	* @desc Save (send) cookies to browser.
	*/
	function saveCookieToBrowser() {			  
		Debugger::say("Start saving cookie to browser...");			
		if ($this->isConnected()) {
			
			if (strpos($_SERVER["HTTP_HOST"],":"))
				$domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
			else
				$domain = $_SERVER["HTTP_HOST"];
			Debugger::say("Saving cookies with domain=".$domain);
				
			header("Set-Cookie: ".GM_COOKIE_KEY."=".base64_encode($this->cookie_str)."; Domain=".$domain.";");
			//setcookie(GM_COOKIE_KEY, base64_encode($this->cookie_str), 1209600, "/" , $domain);
			Debugger::say("Just saved cookie: ".GM_COOKIE_KEY."=".base64_encode($this->cookie_str));
			Debugger::say("Completed saving cookie to browser.");
			return true;
		}
		Debugger::say("FAILED to save cookie to browser: not connected.");
		return false;
	}
	
	/**
	* @return bool Success or not.
	* @desc Remove all session information related to Gmailer.
	*/
	function removeSessionFromBrowser() {
		Debugger::say("Start removing session from browser...");
		
		if (!$this->use_session)
			return $this->removeCookieFromBrowser();
		
		// Changed/Added by Neerav; 6 July 2005
		// determines whether session should be preserved or normally destroyed
		if (GM_USE_LIB_AS_MODULE) {
			// if this lib is used as a Gmail module in some other app (e.g. 
			//     "online office"), don't destroy session

			// Let's unset session variables
			if (isset($_SESSION[GM_COOKIE_KEY])) unset($_SESSION[GM_COOKIE_KEY]);
			if (isset($_SESSION['id_key'])) unset($_SESSION['id_key']);
			Debugger::say("Cleared libgmailer related session info.");
			Debugger::say("Session preserved for other use.");
		} else {
			// otherwise (normal) unset and destroy session
			@session_unset();
			@session_destroy();
			Debugger::say("Just removed session: ".GM_COOKIE_KEY);
			Debugger::say("Finished removing session from browser.");
		}
		return true;
	}
	
	/**
	* @return bool
	* @desc Remove all related cookies stored in browser.
	*/
	function removeCookieFromBrowser() {
		Debugger::say("Start removing cookie from browser...");
		if (isset($_COOKIE)) {
			// Changed to include IK cookie; by Neerav; 8 July 2005
			if (isset($_COOKIE[GM_COOKIE_KEY]) or isset($_COOKIE[GM_COOKIE_IK_KEY])) {
				// libgmailer cookies exist
				if (strpos($_SERVER["HTTP_HOST"],":"))
					$domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
				else
					$domain = $_SERVER["HTTP_HOST"];
				Debugger::say("Removing cookies with domain=".$domain);					 
				
				header("Set-Cookie: ".GM_COOKIE_KEY."=1; Discard; Max-Age=0; Domain=".$domain.";");
				header("Set-Cookie: ".GM_COOKIE_IK_KEY."=0; Discard; Max-Age=0; Domain=".$domain.";");
				Debugger::say("Just removed cookies: ".GM_COOKIE_KEY." and ".GM_COOKIE_IK_KEY);
				return true;
			} else {
				Debugger::say("Cannot find libgmailer cookies: ".GM_COOKIE_KEY." or ".GM_COOKIE_IK_KEY);
				return false;
			}
		} else {
			Debugger::say("Cannot find any cookie from browser.");
			return false;
		}
	}					 
	
	/**
	* @return void
	* @desc Disconnect from Gmail.
	*/
	function disconnect() {
		Debugger::say("Start disconnecting...");
		
		/** logout from mail.google.com too **/
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);

		// Updated by Neerav; 28 June 2005
		//curl_setopt($c, CURLOPT_URL, GM_LNK_LOGOUT);
		//curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?logout&hl=en&zx=".$this->proxy_defeat());
		curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?logout".$this->proxy_defeat());
		// "&ik=&" + this.Threads.LastSearch + "&view=tl&start=0&init=1&zx=" + this.MakeUniqueUrl();
		curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."&ik=&view=tl&start=0&init=1".$this->proxy_defeat());

		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		$this->CURL_PROXY($c);
		curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
		curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);

		curl_exec($c);
		curl_close($c);
		Debugger::say("Logged-out from GMail.");
		
		$this->removeSessionFromBrowser();
		$this->cookie_str = "";
		$this->cookie_ik_str = "";	// Added to support IK; by Neerav; 13 July 2005
		
		Debugger::say("Completed disconnecting.");
	}
	
	/**
	* Get {@link GMailSnapshot} by type.
	*
	* Examples:
	* <code>
	* <?php
	*    // For "Inbox"
	*    $gmailer->fetchBox(GM_STANDARD, "inbox", 0);
	*    $snapshot = $gmailer->getSnapshot(GM_STANDARD);
	*
	*    // For conversation
	*    $gmailer->fetchBox(GM_CONVERSATION, $thread_id, 0);
	*    $snapshot = $gmailer->getSnapshot(GM_CONVERSATION);
	* ?>
	* </code>
	*
	* @return GMailSnapshot
	* @param constant $type
	* @see GMailSnapshot
	* @see GM_STANDARD, GM_LABEL, GM_CONVERSATION, GM_QUERY, GM_PREFERENCE, GM_CONTACT	
	*/
	function getSnapshot($type) {
		// Comment by Neerav; 9 July 2005
		// $type slowly will be made unnecessary as we move towards included all response
		//     fields in the snapshot
		
		if ($type & (GM_STANDARD|GM_LABEL|GM_CONVERSATION|GM_QUERY|GM_PREFERENCE|GM_CONTACT)) {
			// Changed by Neerav; Fix by Dave DeLong <daveATdavedelongDOTcom>; 9 July 2005
			//return new GMailSnapshot($type, $this->raw);
			//return new GMailSnapshot($type, $this);
			return new GMailSnapshot($type, $this->raw, $this->use_session);
		} else {
			// assuming normal by default
			// Changed by Neerav; Fix by Dave DeLong <daveATdavedelongDOTcom>; 9 July 2005
			//return new GMailSnapshot(GM_STANDARD, $this->raw);
			//return new GMailSnapshot(GM_STANDARD, $this);
			return new GMailSnapshot(GM_STANDARD, $this->raw, $this->use_session);
		}
	}
	
	/**
	* @return bool Success or not. Note that it will still be true even if $email is an illegal address.
	* @param string $email
	* @desc Send Gmail invite to $email
	*/
	function invite($email) {
		if ($this->isConnected()) {
			Debugger::say("Start sending invite...");
			
			$postdata = "act=ii&em=".urlencode($email);
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$postdata .= "&at=".$cc_parts[1];
					break;
				}
			}
			$link = GM_LNK_GMAIL."?view=ii";
			
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_URL, $link);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_INVITE_REFER);	// Changed by Neerav; 7 June 2005

			// Added status message parsing and return; by Neerav; 6 Aug 2005
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			Debugger::say("Finished sending invite.");

			// Added by Neerav; 6 Aug 2005
			$status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "invite",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);

			return $status;
		} else {
			Debugger::say("FAILED to send invite: not connected.");

			// Added by Neerav; 6 Aug 2005
			$a = array(
				"action" 		=> "$action label",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			return false;
		}
	}
	
	/**
	* Get names of standard boxes.
	*
	* @static
	* @return string[]
	* @deprecated
	*/
	function getStandardBox() {
		return array("Inbox","Starred","Sent","Drafts","All","Spam","Trash");
	}		 
	
	/**
	* Get raw packet Gmailer::$raw
	*
	* @access private
	* @return mixed
	*/
	function dump_raw() {
		return $this->raw;
	}		 

	/**
	* Get full contents of $gmail_data (complete response from Gmail)
	*
	* @access private
	* @return mixed
	* @author Neerav
	* @since 13 Aug 2005
	*/
	function debug_gmail_response() {
		return $this->gmail_data;
	}
		 
	/**
	* cURL "helper" for proxy.
	*
	* @access private
	* @return void
	* @param curl_descriptor $cc
	*/
	function CURL_PROXY($cc) {
		if (strlen($this->proxy_host) > 0) {
			curl_setopt($cc, CURLOPT_PROXY, $this->proxy_host);
			if (strlen($this->proxy_auth) > 0)
				curl_setopt($cc, CURLOPT_PROXYUSERPWD, $this->proxy_auth);
		}
	}
	
	/**
	* Extract cookies from HTTP header.
	*
	* @return string Cookies string
	* @param string $header HTTP header
	* @access private
	* @static
	*/
	function get_cookies($header) {
		$match = "";
		preg_match_all('!Set-Cookie: ([^;\s]+)($|;)!', $header, $match);	 
		$cookie = "";
		foreach ($match[1] as $val) {
			if ($val{0} == '=') {
				continue;
			}
			$cookie .= $val . ";";
		}
		return substr($cookie, 0, -1);
	}

	/**
	* Process Gmail data packets.
	*
	* @access private
	* @static
	* @return mixed[]
	* @param string $input
	* @param int& $offset
	*/
	function parse_data_packet($input, &$offset) {
		$output = array();
		
		// state variables
		$isQuoted = false;		// track when we are inside quotes
		$dataHold = "";			// temporary data container
		$lastCharacter = " ";

		// walk through the entire string
		for($i=1; $i < strlen($input); $i++) {
			switch($input[$i]) {
				case "[":	// handle start of array marker
					if(!$isQuoted) {
						// recurse any nested arrays
						array_push($output, GMailer::parse_data_packet(substr($input,$i), $offset));
						
						// the returning recursive function write out the total length of the characters consumed
						$i += $offset;
						
						// assume that the last character is a closing bracket
						$lastCharacter = "]";
					} else {
						$dataHold .= "[";
					}
					break;

				case "]":	// handle end of array marker
					if(!$isQuoted) {
						if($dataHold != "") {
							array_push($output, $dataHold);
						}
						
						// determine total number of characters consumed (write to reference)
						$offset = $i;
						return $output;
					} else {
						$dataHold .= "]";
						break;
					}

				case '"':	// toggle quoted state
					if($isQuoted) {
						$isQuoted = false;
					} else {
						$isQuoted = true;
						$lastCharacter = '"';
					}
					break;

				case ',':	// find end of element marker and add to array
					if(!$isQuoted) {
						if($dataHold != "") {	// this is to filter out adding extra elements after an empty array
							array_push($output, $dataHold);
							$dataHold = "";
						} else if($lastCharacter == '"') {	 // this is to catch empty strings
							array_push($output, "");
						}
					} else {
						$dataHold .= ",";
					}
					break;
					
				case '\\':
					if ($i < strlen($input) - 1) { 
						switch($input[$i+1]) {
							case "\\":							/* for the case \\ */
								// Added by Neerav; June 2005
								// strings that END in \ are now handled properly
								if ($i < strlen($input) - 2) { 
									switch($input[$i+2]) {
										case '"':							/* for the case \\" */
											$dataHold .= '\\';
											$lastCharacter = '\\"';
											$i += 1;
											break;
										case "'":							/* for the case \\' */
											$dataHold .= "\\";
											$lastCharacter = "\\'";
											$i += 1;
											break;
										default:
									}							 
								} else {
									$dataHold .= '\\';
									$lastCharacter = '\\';
								}
								break;
							case '"':							/* for the case \" */
								$dataHold .= '"';
								$lastCharacter = '\"';
								$i += 1;
								break;
							case "'":							/* for the case \' */
								$dataHold .= "'";
								$lastCharacter = "\'";
								$i += 1;
								break;
							case "n":							/* for the case \n */
								$dataHold .= "\n";
								$lastCharacter = "\n";
								$i += 1;
								break;
							case "r":							/* for the case \r */								
								$dataHold .= "\r";
								$lastCharacter = "\r";
								$i += 1;
								break;
							case "t":							/* for the case \t */
							  $dataHold .= "\t";
							  $lastCharacter = "\t";
							  $i += 1;
							  break;
							default:
						}							 
					}
					break;

				default:	  // regular characters are added to the data container
					$dataHold .= $input[$i];
					break;
			}
		}	 
		return $output;
	}

	/**
	* Create/edit contact.
	*
	* Examples:
	* <code>
	* <?php
	*    // Add a new one
	*    $gmailer->editContact(-1, 
	*                          "John", 
	*                          "john@company.com", 
	*                          "Supervisor of project X", 
	*                          "");
	*
	*    // Add a new one with lots of details
	*    $gmailer->editContact(-1, "Mike", "mike@company.com", 
	*       "Mike the driver",
	*       array(array("phone" => "123-45678",
	*             "mobile" => "987-65432",
	*             "fax" => "111-11111",
	*             "pager" => "222-22222",
	*             "im" => "34343434",
	*             "company" => "22th Century Fox",
	*             "position" => "CEO",
	*             "other" => "Great football player!",
	*             "address" => "1 Fox Rd",
	*             "detail_name" => "Mike G. Stone"));
	*
	*    // Modified an existing one
	*    $gmailer->editContact($contact_id, 
	*                          "Old Name", 
	*                          "new_mail@company.com", 
	*                          "Old notes");
	* ?>
	* </code>
	*
	* Note: You must supply the old name even if you are not going to modify it, or it will
	* be changed to empty!
	*
	* @return bool Success or not.
	  Extended return: array(bool success/fail, string message, string contact_id)
	* @param string $contact_id  Contact ID for editing an existing one, or -1 for creating a new one
	* @param string $name Name
	* @param string $email Email address
	* @param string $notes Notes
	* @param mixed[][] $details Detailed information
	* @author Neerav
	* @since 15 Jun 2005
	*/
	function editContact($contact_id, $name, $email, $notes, $details=array()) {
		if ($this->isConnected()) {			
			Debugger::say("Starting to add/edit contact...");
			//Debugger::say(print_r($details,true));

			$postdata = array();
 			$postdata["act"] 	= "ec";
 	 		$postdata["ct_id"] 	= "$contact_id";
 			$postdata["ct_nm"] 	= $name;
 			$postdata["ct_em"] 	= $email;
 			$postdata["ctf_n"] 	= $notes;

			// Added by Neerav; 1 July 2005
			// contact details
			if (count($details) > 0) {
				$i = 0;				// the detail number
				$det_num = '00';	// detail number padded to 2 numbers for gmail
				foreach ($details as $detail1) {
					//Debugger::say(print_r($detail1,true));
					$postdata["ctsn_"."$det_num"] = "Unnamed";	// default name if none defined later
					$address = "";								// default address if none defined later
					$k = 0;										// the field number supplied to Gmail
					$field_num = '00';							// must be padded to 2 numbers for gmail
					foreach ($detail1 as $detail) {
						$field_type = "";
						switch (strtolower($detail["type"])) {
							case "phone":		$field_type = "p";	break;
							case "email":		$field_type = "e";	break;
							case "mobile":		$field_type = "m";	break;
							case "fax":			$field_type = "f";	break;
							case "pager":		$field_type = "b";	break;
							case "im":			$field_type = "i";	break;
							case "company":		$field_type = "d";	break;
							case "position":	$field_type = "t";	break;	// t = title
							case "other":		$field_type = "o";	break;
							case "address":		$field_type = "a";	break;
							case "detail_name": $field_type = "xyz";	break;
							default:			$field_type = "o";	break;	// default to other
							//default:			$field_type = $detail["type"];	break;	// default to the unknown detail
						}
						if ($field_type == "xyz") {
							$postdata["ctsn_"."$det_num"] = $detail["info"];
						} elseif ($field_type == "a") {
							$address = $detail["info"];
						} else {
							// e.g. ctsf_00_00_p for phone
							$postdata["ctsf_"."$det_num"."_"."$field_num"."_"."$field_type"] = $detail["info"];
							// increments the field number and pads it
							$k++;
							$field_num = str_pad($k, 2, '0', STR_PAD_LEFT);
						}
					}				
					// Address field needs to be last
					// if more than one address was given, the last one found will be used
					if ($address != "") $postdata["ctsf_"."$det_num"."_"."$field_num"."_a"] = $address;

					// increment detail number
					$i++;
					$det_num = str_pad($i, 2, '0', STR_PAD_LEFT);
				}
			}

			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$postdata["at"] = $cc_parts[1];
					break;
				}
			}
			
			//Debugger::say(print_r($postdata,true));
			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?view=up");
			curl_setopt($c, CURLOPT_REFERER, GMAIL_CONTACT_REFERRER_URL);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished add/edit contact.");
			
			$orig_contact_id = $contact_id;
			if ($orig_contact_id == -1 and $this->raw["ar"][1]) {
				if (isset($this->raw["cov"][1][1])) $contact_id = $this->raw["cov"][1][1];
				elseif (isset($this->raw["a"][1][1])) $contact_id = $this->raw["a"][1][1];
				elseif (isset($this->raw["cl"][1][1])) $contact_id = $this->raw["cl"][1][1];
			}

			$status = $this->raw["ar"][1];
			$a = array(
				"action" 		=> (($orig_contact_id == -1) ? "add contact": "edit contact"),
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $this->raw["ar"][2],
				"contact_id" 	=> "$contact_id"
			);
			array_unshift($this->return_status, $a);

			return $status;

		} else {
			$a = array(
				"action" 		=> (($orig_contact_id == -1) ? "add contact": "edit contact"),
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected",
				"contact_id" 	=> "$contact_id"
			);
			array_unshift($this->return_status, $a);

			Debugger::say("FAILED to add/edit contact: not connected.");
			return false;
		}
	}

	/**
	* Add message's senders to contact list.
	*
	* @return bool
	* @param string $message_id Message ID
	* @author Neerav
	* @since 14 Aug 2005
	*/
	function addSenderToContact($message_id) {
		if ($this->isConnected()) {			
			Debugger::say("Starting to add sender to contact list...");

			$query  = "";
			$query .= "&ik=".$this->cookie_ik_str;
			$query .= "&search=inbox";
			$query .= "&view=up";
			$query .= "&act=astc";
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query	.= "&at=".$cc_parts[1];
					break;
				}
			}
			$query .= "&m=".$message_id;
			$query .= $this->proxy_defeat();	 // to fool proxy

			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			// NOTE: DO NOT SEND REFERRER
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//Debugger::say(print_r($this->gmail_data,true));
			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished adding sender to contact list.");
			
			$a = array(
				"action" 		=> "add sender to contact list",
				"status" 		=> "success",
				"message" 		=> ""
			);
			array_unshift($this->return_status, $a);
			return true;
		} else {
			$a = array(
				"action" 		=> "add sender to contact list",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			Debugger::say("FAILED to adding sender to contact list: not connected.");
			return false;
		}
	}

	/**
	* Star/unstar a message quickly.
	*
	* @return bool Success or not.
	  Extended return: array(bool success/fail, string message, string contact_id)
	* @param string $message_id
	* @param string $action Either "star" or "unstar".
	* @author Neerav
	8 @since 18 Aug 2005
	*/
	function starMessageQuick($message_id, $action) {
		if ($this->isConnected()) {			
			Debugger::say("Starting to quick $action message...");
			$query  = "";
			$query .= "&ik=".$this->cookie_ik_str;
			$query .= "&search=inbox";
			$query .= "&view=up";
			if ($action == "star") {
				$query .= "&act=st";
			} else {
				$query .= "&act=xst";
			}
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query	.= "&at=".$cc_parts[1];
					break;
				}
			}
			$query .= "&m=".$message_id;
			$query .= $this->proxy_defeat();	 // to fool proxy

			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			// NOTE: DO NOT SEND REFERRER
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//Debugger::say(print_r($this->gmail_data,true));
			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished $action.");
			
			$a = array(
				"action" 		=> "$action message",
				"status" 		=> "success",
				"message" 		=> ""
			);
			array_unshift($this->return_status, $a);
			return true;
		} else {
			$a = array(
				"action" 		=> "$action message",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			Debugger::say("FAILED to quick $action message: not connected.");
			return false;
		}
	}

	/**
	* Delete contacts.
	*
	* @return bool Success or not.
	  Extended return: array(bool success/fail, string message)
	* @param string[] $id Contact ID to be deleted
	* @author Neerav
	* @since 15 Jun 2005
	*/
	function deleteContact($id) {
		if ($this->isConnected()) {			
			Debugger::say("Starting to delete contact...");
			
			$query 	 = "";

			if (is_array($id)) {
				//Post: act=dc&at=bb8085dc44e05fc4-1047dc471e4&cl_nw=&cl_id=&cl_nm=&c=0&c=3d
				$query .= "&act=dc&cl_nw=&cl_id=&cl_nm=";
				foreach ($id as $indexval => $contact_id) {
					$query .= "&c=".$contact_id;
				}
			} else {
				$query 	.= "search=contacts";
				$query 	.= "&ct_id=".$id;
				$query 	.= "&cvm=2";
				$query 	.= "&view=up";
				$query 	.= "&act=dc";
			}

			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query 	.= "&at=".$cc_parts[1];
					break;
				}
			}
			if (!is_array($id)) {
				$query 	.= "&c=".$id;
				$query .= $this->proxy_defeat();	 // to fool proxy
			}

			set_time_limit(150);
			$c = curl_init();
			if (is_array($id)) {
				//URL: POST /gmail/?&ik=&view=up HTTP/1.1
				//Referer: http://mail.google.com/mail/?&ik=136a6cefc0&view=cl&search=contacts&pnl=a&zx=zfowhxlm2nrh
				curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?view=up");
				curl_setopt($c, CURLOPT_POST, 1);
				curl_setopt($c, CURLOPT_POSTFIELDS, $query);
			} else {
				curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			}
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."?view=cl&search=contacts&pnl=a");
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			$status = $this->raw["ar"][1];
			$a = array(
				"action" 		=> "delete contact",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $this->raw["ar"][2]
			);
			array_unshift($this->return_status, $a);

			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished deleting contact.");
			return $this->raw["ar"][1];
		} else {
			$a = array(
				"action" 		=> "delete contact",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			Debugger::say("FAILED to delete contact: not connected.");
			return false;
		}
	}

	/**
	* Create, edit or remove label.
	* 
	* @return bool Success or not.
	  Extended return: array (boolean success/fail, string message)
	* @param string $label
	* @param string $action Either "create", "delete" or "rename"
	* @param string $renamelabel New name if renaming label
	* @author Neerav
	* @since 7 Jun 2005
	*/
	function editLabel($label, $action, $renamelabel) {
		//Debugger::say("$label");
		if ($this->isConnected()) {			
			Debugger::say("Starting to $action label...");
			//Debugger::say("ik value: ".$this->cookie_ik_str);
				
			$postdata = array();
			if ($action == "create") {
				$postdata["act"] = "cc_".$label;
			} elseif ($action == "rename") {
				$postdata["act"] = "nc_".$label."^".$renamelabel;
			} elseif ($action == "remove") {
				$postdata["act"] = "dc_".$label;
			} else {
				// Changed by Neerav; 28 June 2005
				// was boolean, now array(boolean,string)
				$a = array(
					"action" 		=> "$action label",
					"status" 		=> (($status) ? "success" : "failed"),
					"message" 		=> "libgmailer error: unknown action in editLabel()"
				);
				array_unshift($this->return_status, $a);
				return false;
			}
			
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $indexval => $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$postdata["at"] = $cc_parts[1];
					break;
				}
			}
			/***
			$postdata["sx_dl"] = "en";
			$postdata["ix_nt"] = 25;
			$postdata["bx_hs"] = 0;
			$postdata["bx_sc"] = 1;
			$postdata["bx_ns"] = 0;
			$postdata["sx_sg"] = 0;
			$postdata["sx_sg"] = "";
			$postdata["bx_en"] = 0;
			***/

			//Debugger::say(print_r($postdata,true));
			//echo GM_LNK_GMAIL."?&ik=".$_SESSION['id_key']."&view=pr&pnl=l".$this->proxy_defeat();
			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?&ik=&view=up");
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_HEADER, 1);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."?&ik=".$_SESSION['id_key']."&view=pr&pnl=l".$this->proxy_defeat());
			$this->gmail_data = curl_exec($c);
			curl_close($c);
			//Debugger::say(print_r($this->gmail_data,true));
			GMailer::status_message($this->gmail_data);

			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished $action label.");

			// Changed by Neerav; 28 June 2005
			$status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "$action label",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);

			return $status;
		} else {
			Debugger::say("FAILED to $action label: not connected.");
			// Added by Neerav; 12 July 2005
			$a = array(
				"action" 		=> "$action label",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);

			return false;
		}
	}
	
	/**
	* Create/edit a filter.
	*
	* @return bool Success or not.
	  Extended return: array(bool,string message)
	* @param integer $filter_id Filter ID to be edited, or "0" for creating a new one
	* @param string $from
	* @param string $to
	* @param string $subject
	* @param string $has
	* @param string $hasnot
	* @param bool 	$hasAttach
	* @param bool	$archive
	* @param bool 	$star
	* @param bool 	$label
	* @param string $label_name
	* @param bool	$forward
	* @param string $forwardto
	* @param bool	$trash
	* @author Neerav
	* @since 25 Jun 2005
	*/
	function editFilter($filter_id, $from, $to, $subject, $has, $hasnot, $hasAttach,
				$archive, $star, $label, $label_name, $forward, $forwardto, $trash) {

		$action = ($filter_id == 0) ? "create" : "edit";		
		if ($this->isConnected()) {
			Debugger::say("Starting to $action filter...");
			
			$query = "";

			$query .= "view=pr";
			$query .= "&pnl=f";
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query .= "&at=".$cc_parts[1];
					break;
				}
			}
			if ($action == "create") {
				// create new filter
				$query .= "&act=cf";
				$query .= "&cf_t=cf";
			} else {
				// edit existing filter
				$query .= "&act=rf";
				$query .= "&cf_t=rf";
			}
			
			$query .= "&cf1_from="	. urlencode($from);
			$query .= "&cf1_to="	. urlencode($to);
			$query .= "&cf1_subj="	. urlencode($subject);
			$query .= "&cf1_has="	. urlencode($has);
			$query .= "&cf1_hasnot=". urlencode($hasnot);
			$query .= "&cf1_attach="; $query .= ($hasAttach == true) ? "true" : "false" ;
			$query .= "&cf2_ar="	; $query .= ($archive == true) 	? "true" : "false" ;
			$query .= "&cf2_st="	; $query .= ($star == true) 	? "true" : "false" ;
			$query .= "&cf2_cat="	; $query .= ($label == true) 	? "true" : "false" ;
			$query .= "&cf2_sel="	. urlencode($label_name);
			$query .= "&cf2_emc="	; $query .= ($forward == true) 	? "true" : "false" ;
			$query .= "&cf2_email="	. urlencode($forwardto);
			$query .= "&cf2_tr="	; $query .= ($trash == true) 	? "true" : "false" ;
			if ($action == "edit") {
				$query .= "&ofid=".$filter_id;
			}
			$query .= $this->proxy_defeat();	 // to fool proxy

			$refer = "";
			$refer .= "&pnl=f";
			$refer .= "&search=cf";
			$refer .= "&view=tl";
			$refer .= "&start=0";
			$refer .= "&cf_f=cf1";
			$refer .= "&cf_t=cf2";
			$refer .= "&cf1_from="	. urlencode($from);
			$refer .= "&cf1_to="	. urlencode($to);
			$refer .= "&cf1_subj="	. urlencode($subject);
			$refer .= "&cf1_has="	. urlencode($has);
			$refer .= "&cf1_hasnot=". urlencode($hasnot);
			$refer .= "&cf1_attach="; $query .= ($hasAttach == true) 	? "true" : "false" ;
			if ($action == "edit") {
				$refer .= "&cf2_ar="	; $query .= ($archive == true) 	? "true" : "false" ;
				$refer .= "&cf2_st="	; $query .= ($star == true) 	? "true" : "false" ;
				$refer .= "&cf2_cat="	; $query .= ($label == true) 	? "true" : "false" ;
				$refer .= "&cf2_sel="	. urlencode($label_name);
				$refer .= "&cf2_emc="	; $query .= ($forward == true) 	? "true" : "false" ;
				$refer .= "&cf2_email="	. urlencode($forwardto);
				$refer .= "&cf2_tr="	; $query .= ($trash == true) 	? "true" : "false" ;
				$refer .= "&ofid="		. urlencode($filter_id);
			}
			$refer .= $this->proxy_defeat();	 // to fool proxy

			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."?".$refer);
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//$updated_snapshot = new GMailSnapshot(GM_PREFERENCE, $this->raw, $this->use_session);
			//Debugger::say(print_r($updated_snapshot,true));
			Debugger::say("Finished $action filter.");
			$status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "$action filter",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);

			return $status;
		} else {
			$a = array(
				"action" 		=> "$action filter",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);
			Debugger::say("FAILED to $action filter: not connected.");
			return false;
		}
	}


	/**
	* Delete a filter.
	*
	* @return bool Success or not.
	  Extended return: array(bool success/fail, string message)
	* @param string $id Filter ID to be deleted
	* @author Neerav
	* @since 25 Jun 2005
	*/
	function deleteFilter($id) {
		if ($this->isConnected()) {			
			Debugger::say("Starting to delete filter...");

			$query 	 = "";

			//PostData = "act=df_" + this.id.ToString() +
				//"&at=" + this.Parent.Cookies["GMAIL_AT"].Value;
			$query 	.= "act=df_".$id;

			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query 	.= "&at=".$cc_parts[1];
					break;
				}
			}

			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?ik=&view=up");
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $query);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."?pnl=f&view=pr".$this->proxy_defeat());
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//$updated_snapshot = new GMailSnapshot(GM_PREFERENCE, $this->raw, $this->use_session);
			//Debugger::say(print_r($updated_snapshot,true));
			//Debugger::say(print_r($this->raw,true));
			Debugger::say("Finished deleting filter.");
			$status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "delete filter",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);
			return $status;
		} else {
			$a = array(
				"action" 		=> "delete filter",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);
			Debugger::say("FAILED to delete filter: not connected.");
			return false;
		}
	}


	/**
	* Set general settings of Gmail account.
	*
	* @return bool Success or not.
	  Extended return: array(bool status, string message)
	* @param bool $use_outgoing_name Use outgoing name (instead of the default)?
	* @param string $outgoing_name Outgoing name
	* @param bool $use_reply_email Use replying email address (instead of the default)?
	* @param string $reply_to Replying email address
	* @param string $language Language
	* @param int $page_length Page length. Must be either 25, 50 or 100
	* @param bool $shortcut Enable keyboard shortcut?
	* @param bool $indicator Enable personal level indicator?
	* @param bool $snippet Enable snippet?
	* @param bool $custom_signature Enable custom signature?
	* @param string $signature Custom signature
	* @param string $forward_to Address to auto-forward to. $forward_to = "" to disable auto-forward
	* @param string $forward_action Action on Gmail's copy of auto-forwarded emails. $forward_action = "" to keep them, "archive" to archive them, or "trash" to trash them
	* @param int $pop_setting POP settings. $pop_setting = 0 to disable POP access, 1 to remain unchanged, 2 to enable POP for new mails, or 3 to enable POP for all mails
	* @param int $pop_action Action on Gmail's copy of POP-accessed emails. $pop_action = 0 to keep them, 1 to archive them, or 2 to trash them
	* @param bool $expand_label_box Expand label box?
	* @param bool $expand_invite_box Expand invite box?
	* @author Neerav
	* @since 29 Jun 2005
	*/
	function setGeneralSetting($use_outgoing_name, $outgoing_name, $use_reply_email, $reply_to,
				$language, $page_length, $shortcut, $indicator, $snippet, $custom_signature, 
				$signature, 
				$forward_to, $forward_action, $pop_setting, $pop_action,
				$expand_label_box = 1, $expand_invite_box = 1
		) {

		/* 			
		"bx_hs"		// (boolean) keyboard shortcuts {0 = off, 1 = on}
		"bx_show0"	// (boolean) labels box {0 = collapsed, 1 = expanded}
		"ix_nt"		// (integer) msgs per page (maximum page size)
		"sx_dl"		// (string) display language (en = English, en-GB = British-english, etc)
		"bx_sc"		// (boolean) personal level indicators {0 = no indicators, 1 = show indicators}
		"bx_show1"	// (boolean) invite box {0 = collapsed, 1 = expanded}
		"sx_sg"		// (string) signature
		"sx_dn" 	// (string) display name 
		"sx_rt" 	// (string) reply to email address
		"bx_ns" 	// (boolean) no snippets {0 = show snippets, 1 = no snippets}
		"bx_cm" 	// (boolean) rich text composition {0 = plain text, 1 = rich text}
		"sx_em"  // (string) email address forwarding to {"" to disable forwarding}
		"sx_at"  // (string) action on Gmail's copy {"trash", "archive" or ""}
		"bx_pe"  // (int) POP settings {3 = switch on POP for all mail, 2 = POP for new mail, 0 = disable}
		"ix_pd"  // (int) action on Gmail's copy {0 = keep, 1 = archive, 2 = trash}		
		*/

		if ($this->isConnected()) {			
			Debugger::say("Starting to set general settings...");
			$query = "";

			//$query .= "&ik=".IKVALUE;
			$query .= "&search=inbox";
			$query .= "&view=tl";
			$query .= "&start=0";
			$query .= "&act=prefs";
			$cc = split(";", $this->cookie_str);
			foreach ($cc as $cc_part) {
				$cc_parts = split("=", $cc_part);
				if (trim($cc_parts[0]) == "GMAIL_AT") {
					$query .= "&at=".$cc_parts[1];
					break;
				}
			}

			$query .= "&p_bx_hs=";		$query .= ($shortcut) ? "1" : "0" ;
			$query .= "&p_bx_show0=";	$query .= ($expand_label_box) ? "1" : "0" ;
			$query .= "&p_ix_nt="		. $page_length;
			$query .= "&p_sx_dl="		. $language;
			$query .= "&p_bx_sc=";		$query .= ($indicator) ? "1" : "0" ;
			$query .= "&p_bx_show1=";	$query .= ($expand_invite_box) ? "1" : "0" ;
			$query .= "&p_sx_sg=";		$query .= ($custom_signature) 	? urlencode("$signature") 		: "%0A%0D" ;
			$query .= "&p_sx_dn=";		$query .= ($use_outgoing_name) 	? urlencode("$outgoing_name") 	: "%0A%0D" ;
			$query .= "&p_sx_rt=";		$query .= ($use_reply_email) 	? urlencode("$reply_to") 		: "%0A%0D" ;
			$query .= "&p_bx_ns=";		$query .= ($snippet) ? "0" : "1" ; // REVERSED because we originally reversed it for convenience
         $query .= "&p_sx_em="      . $forward_to;
         $query .= "&p_sx_at="      . $forward_action;
         $query .= "&p_bx_pe="      . $pop_setting;
         $query .= "&p_ix_pd="      . $pop_action;
			$query .= $this->proxy_defeat();	 // to fool proxy

			set_time_limit(150);
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GM_LNK_GMAIL."?".$query);
			curl_setopt($c, CURLOPT_REFERER, GM_LNK_GMAIL."?&view=pr&pnl=g".$this->proxy_defeat());//&ik=921eb6d481&view=pr&pnl=g&zx=zeurrt-akcb6h
			$this->CURL_PROXY($c);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				
			$this->gmail_data = curl_exec($c);
			GMailer::status_message($this->gmail_data);
			curl_close($c);
			
			//$updated_snapshot = new GMailSnapshot(GM_PREFERENCE, $this->raw, $this->use_session);
			//Debugger::say(print_r($updated_snapshot,true));
			Debugger::say("Finished set general settings.");
			$status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
			$a = array(
				"action" 		=> "set general settings",
				"status" 		=> (($status) ? "success" : "failed"),
				"message" 		=> $message
			);
			array_unshift($this->return_status, $a);
			return $status;
		} else {
			$a = array(
				"action" 		=> "set general settings",
				"status" 		=> "failed",
				"message" 		=> "libgmailer: not connected"
			);
			array_unshift($this->return_status, $a);
			Debugger::say("FAILED to set general settings.");
			return false;
		}
	}

	/*
	* @return array(bool,string message, contact id)
	* @param string $label
	* @desc Set Fowarding and POP Settings.
	*/
	// Added by Neerav; 29 June 2005
/* 	function set_fpop_setting() { */
/*  */
/* 		# "bx_show0"	// (boolean) labels box {0 = collapsed, 1 = expanded} */
/* 		# "bx_show1"	// (boolean) invite box {0 = collapsed, 1 = expanded} */
/* 		# "sx_em" 		// (string) forward to email */
/* 		# "sx_at" 		// (string) forwarding action {selected, archive, trash} */
/* 		# "ix_pd" 		// (integer) action after pop access {0 = keep, 1 = archive, 2 = trash} */
/* 		# "bx_pe" 		// (boolean) pop enabled {0 = disabled, 1 = enabled} */
/* 		# "bx_cm" 		// (boolean) rich text composition {0 = plain text, 1 = rich text} */
/* 		 */
/* 		if ($this->isConnected()) {			 */
/* 			Debugger::say("Starting to set forward and POP settings..."); */
/*  */
/*  */
/*  */
/* 			$status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0; */
/* 			$message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : ""; */
/* 			$a = array( */
/* 				"action" 		=> "set forward and POP settings", */
/* 				"status" 		=> (($status) ? "success" : "failed"), */
/* 				"message" 		=> $message */
/* 			); */
/* 			array_unshift($this->return_status, $a); */
/* 			return $status; */
/*  */
/* 		} else { */
/* 			$a = array( */
/* 				"action" 		=> "set forward and POP settings", */
/* 				"status" 		=> "failed", */
/* 				"message" 		=> "libgmailer: not connected" */
/* 			); */
/* 			array_unshift($this->return_status, $a); */
/* 			Debugger::say("FAILED to set forward and POP settings."); */
/* 			return false; */
/* 		} */
/* 	} */

	/**
	* Parse status replies.
	*
	* @access private
	* @static
	* @return bool 
	* @param string $raw_html
	* @author Neerav
	* @since 7 Jun 2005
	*/
	function status_message($raw_html) {

		Debugger::say("Begin parsing status...");
		$raw_html = str_replace("\n", "", $raw_html);
		$raw_html = str_replace("D([", "\nD([", $raw_html);
		$raw_html = str_replace("]);", "]);\n", $raw_html);
		
		$regexp = "|D\(\[(.*)\]\);|U"; 
		$matches = "";	 
		preg_match_all($regexp, $raw_html, $matches, PREG_SET_ORDER); 
		$packets = array();
		for ($i = 0; $i < count($matches); $i++) {
			$off = 0;
			$tmp = GMailer::parse_data_packet("[".$matches[$i][1]."]", $off);
			if (array_key_exists($tmp[0], $packets) || ($tmp[0]=="mi"||$tmp[0]=="mb"||$tmp[0]=="di")) {
				if ($tmp[0]=="t"||$tmp[0]=="ts"||$tmp[0]=="a"||$tmp[0]=="cl")
					$packets[$tmp[0]] = array_merge($packets[$tmp[0]], array_slice($tmp, 1));
				if ($tmp[0]=="mi"||$tmp[0]=="mb"||$tmp[0]=="di") {
					if (array_key_exists("mg", $packets))
						array_push($packets["mg"],$tmp);
					else
						$packets["mg"] = array($tmp);
				}									  
			} else {
				$packets[$tmp[0]] = $tmp;
			}
		}
		$this->raw = $packets;
		Debugger::say("Status parsing completed.");
		return 1;
	}
}

/**
 * Class GMailSnapshot allows you to read information about Gmail in a structured way.
 * 
 * There is no creator for this class. You must use {@link GMailer::getSnapshot()} to obtain
 * a snapshot.
 *
 * @package GMailer
*/
class GMailSnapshot {
	var $created;

	/**
	* Constructor.
	*
	* Note: you are not supposed to create a GMailSnapshot object yourself. You should
	* use {@link GMailer::getSnapshot()} instead.
	*
	* @return GMailSnapshot
	* @param constant $type
	* @param array $raw
	*/
	function GMailSnapshot($type, $raw, $use_session) {
		// input: raw packet generated by GMailer
		if (!is_array($raw)) {
			$this->created = 0;
			$this->snapshot_error = "libgmailer: invalid datapack -- not an array";  // Added by Neerav; 3 Aug 2005
			return null;
		}
		if (count($raw) == 0) {
			$this->created = 0;
			$this->snapshot_error = "libgmailer: invalid datapack -- no entries";  // Added by Neerav; 3 Aug 2005
			return null;
		}
	
		//Debugger::say("raw packet as snapshot input: ".print_r($raw,true));

		// Gmail version
		if (isset($raw["v"][1])) $this->gmail_ver = $raw["v"][1];
		//$raw["v"][2]	// What is this?
		//$raw["v"][3]	// What is this?

		// IdentificationKey (ik)
		// Added by Neerav; 6 July 2005
		if ($use_session) {
			if (!isset($_SESSION['id_key']) or ($_SESSION['id_key'] == "")) {
				Debugger::say("Snapshot: Using Sessions, saving id_key(ik)...");
				if (isset($raw["ud"][3])) {
					$_SESSION['id_key'] = $raw["ud"][3];
					Debugger::say("Snapshot: Session id_key saved: " . $_SESSION['id_key']);
				} else {
					Debugger::say('Snapshot: Session id_key NOT saved.  $raw["ud"][3] not found.');
				}
			}
		} else {
			if (!isset($_COOKIE[GM_COOKIE_IK_KEY]) or ($_COOKIE[GM_COOKIE_IK_KEY] == 0)) {
				Debugger::say("Snapshot: Using Cookies, saving id_key(ik)...");
				if (isset($raw["ud"][3])) {
					if (strpos($_SERVER["HTTP_HOST"],":"))
						$domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
					else
						$domain = $_SERVER["HTTP_HOST"];
					Debugger::say("Saving id_key as cookie ".GM_COOKIE_IK_KEY." with domain=".$domain);
						
					header("Set-Cookie: ".GM_COOKIE_IK_KEY."=".base64_encode($raw["ud"][3])."; Domain=".$domain.";");
					Debugger::say("Snapshot: Cookie id_key saved: ".GM_COOKIE_IK_KEY."=".base64_encode($raw["ud"][3]));
				} else {
					Debugger::say('Snapshot: Cookie id_key NOT saved.  $raw["ud"][3] not found.');
				}
			}
		}
		
		// other "UD"
		// Added by Neerav; 6 July 2005
		if (isset($raw["ud"])) {
			// account email address
			// your app SHOULD cache this in session or cookie for use across pages
			// Added by Neerav; 6 May 2005
			$this->gmail_email = $raw["ud"][1];
			//$raw["ud"][2]		// keyboard shortcuts
			//$raw["ud"][3]		// Identification Key, set above	
			//$raw["ud"][4]		// What is this?
		}
		
		// su
		//$raw["su"][1]		// What is this?
		//$raw["su"][2]		// What is this? (?? array of text strings for invites)
		

		// Google Accounts' name
		// your app SHOULD cache this in session or cookie for use across pages
		//     it's bandwidth expensive to retrieve preferences just for this
		// Added by Neerav; 2 July 2005
		if (isset($raw["gn"][1])) $this->google_name = $raw["gn"][1];

		// your app SHOULD cache this in session or cookie for use across pages
		//     it's bandwidth expensive to retrieve preferences just for this
		// Added by Neerav; 6 July 2005
		if (isset($raw["p"])) {
			for ($i = 0; $i < count($raw["p"]); $i++) {
				if ($raw["p"][$i][0] == "sx_sg") {
					// can be undefined ?!?!
					$this->signature = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "" ;
					break;	
				}
			}
		}

		
		// Invites
		if (isset($raw["i"][1])) {
			$this->have_invit = $raw["i"][1];
		} else {
			$this->have_invit = 0;
		}

		// QUota information
		if (isset($raw["qu"])) {
			// Space used as xx MB
			$this->quota_mb  = $raw["qu"][1];
			// Total space allotted as xxxx MB
			$this->quota_tot = $raw["qu"][2];	// Added by Neerav; 6 May 2005
			// Space used as xx%
			$this->quota_per = $raw["qu"][3];	// Added by Neerav; 6 May 2005
			// html color as #aabbcc (normally a green color, but red when nearly full)
			$this->quota_col = $raw["qu"][4];	// Added by Neerav; 6 July 2005
		}

		// Footer Tips
		// Added by Neerav; 6 July 2005
		if (isset($raw["ft"][1])) $this->gmail_tip = $raw["ft"][1];
		
		// cfs; Compose from source
		// Added by Neerav: 30 Aug 2005; Modified: 9 Sep 2005
		$this->personality = array();
		$this->personality_unverify = array();
		if (isset($raw["cfs"])) {
			if (isset($raw["cfs"][1])) {
				$person_verified = count($raw["cfs"][1]);
				for($i = 0; $i < $person_verified; $i++) {
					$this->personality[] = array(
						"name"		=> $raw["cfs"][1][$i][0],
						"email"		=> $raw["cfs"][1][$i][1],
						"default"   => ($raw["cfs"][1][$i][2]==0)?false:true,
						/** "reply-to"  => $raw["cfs"][1][$i][3], [not available to everyone yet (Gan: 9 Sept)] **/
						"verified" 	=> true
					);
				}
				$person_unverified = count($raw["cfs"][2]);
				for($i = 0; $i < $person_unverified; $i++) {
					$this->personality_unverify[] = array(
						"name"		=> $raw["cfs"][2][$i][0],
						"email"		=> $raw["cfs"][2][$i][1],
						"default"   => ($raw["cfs"][2][$i][2]==0)?false:true,
						/** "reply-to"  => $raw["cfs"][2][$i][3], [not available to everyone yet (Gan: 9 Sept)] **/
						"verified" 	=> false
					);
				}
			}
		}

		// What is this?
		// $raw["df"][1]  // shows ?false?
		
		// What is this?
		// $raw["ms"]

		// What is this?
		// $raw["e"]

		// What is this?
		// $raw["pod"]

		if ($type & (GM_STANDARD|GM_LABEL|GM_CONVERSATION|GM_QUERY)) {
			//Debugger::say(print_r($raw,true));

			// Added by Neerav; 6 May 2005
			if (isset($raw["p"]) and !isset($this->signature)) {
				for ($i = 1; $i < count($raw["p"]); $i++) {
					if ($raw["p"][$i][0] == "sx_sg") {
						// can be undefined ?!?!
						$this->signature = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "" ;
						break;	
					}
				}
			}
			if (!isset($this->signature)) $this->signature = "";

			// when a conversation does not exist, neither does ds; Fix by Neerav; 1 Aug 2005
			if (isset($raw["ds"])) {
				if (!is_array($raw["ds"])) {
					$this->created = 0;
					$this->snapshot_error = "libgmailer: invalid datapack";
					return null;
				}
				$this->std_box_new = array_slice($raw["ds"],1);
			} else {
				$this->created = 0;
				if (isset($raw["tf"])) {
					$this->snapshot_error = $raw["tf"][1];
				} else {
					$this->snapshot_error = "libgmailer: unknown but fatal datapack error";
				}
				return null;
			}

			$this->label_list = array();
			$this->label_new = array();

			// Last changed by Neerav; 12 July 2005
			if ((isset($raw["ct"][1])) and (count($raw["ct"][1]) > 0)) {
				foreach ($raw["ct"][1] as $v) {
					array_push($this->label_list, $v[0]);
					array_push($this->label_new, $v[1]);
				}			 
			}
						
			// Thread Summary
			if (isset($raw["ts"])) {
				$this->view 	 = (GM_STANDARD|GM_LABEL|GM_QUERY);
				$this->box_name  = $raw["ts"][5];		// name of box/label/query
				$this->box_total = $raw["ts"][3];		// total messages found
				$this->box_pos 	 = $raw["ts"][1];		// starting message number

				// Added by Neerav; 6 July 2005
				$this->box_display 		= $raw["ts"][2];	// max number of messages to display on the page
				$this->box_query 		= $raw["ts"][6];	// gmail query for box
				$this->queried_results 	= $raw["ts"][4];	// was this a search query (bool)
				//$this->?? 		= $raw["ts"][7];		// what is this?? some id number?
				//$this->?? 		= $raw["ts"][8];		// what is this?? total number of messages in account?
				//$this->?? 		= $raw["ts"][9];		// what is this??
			}

			$this->box = array();
			if (isset($raw["t"])) {					  
				foreach ($raw["t"] as $t) {
					if ($t == "t") continue;
					
					// Fix for 12 OR 13 fields!!; by Neerav; 23 July 2005
					$less  = (count($t) == 12) ? 1 : 0 ;

					// Added by Neerav; 6 July 2005
					$long_date = "";
					$long_time = "";
					$date_time = explode("_",$t[12-$less]);
					if (isset($date_time[0])) $long_date = $date_time[0];
					if (isset($date_time[1])) $long_time = $date_time[1]; 
											
					// Added labels for use in multiple languages; by Neerav; 7 Aug 2005
					//$label_array_lang = $t[8-$less];	

					// Added by Neerav; 6 July 2005
					// Gives an array of labels and substitutes the standard names
					// Changed to be language compatible; by Neerav; 8 Aug 2005
					$label_array = array();
					foreach($t[8-$less] as $label_entry) {
						switch ($label_entry) {
							//case "^i": 	$label_array[] = "Inbox";		break;
							//case "^s": 	$label_array[] = "Spam";		break;
							//case "^k": 	$label_array[] = "Trash";		break;
							case "^t": 	/* Starred */					break;
							//case "^r": 	$label_array[] = "Draft";		break;
							default:	$label_array[] = $label_entry; 	break;
						}
					}

					$b = array();
					$b["id"]		= $t[0];
					$b["is_read"]	= (($t[1] == 1) ? 1 : 0);
					$b["is_starred"]= (($t[2] == 1) ? 1 : 0);
					$b["date"]		= strip_tags($t[3]);
					$b["sender"]	= strip_tags($t[4],"<b>");
					$b["flag"]		= $t[5];
					$b["subj"]		= strip_tags($t[6],"<b>");
					$b["snippet"]	= ((count($t) == 12) ? "" : $t[7] );
					$b["msgid"]		= $t[10-$less];

					// Added by Neerav; 7 Aug 2005
					//$b["labels_lang"]= $label_array_lang;	// for use with languages
					// Added/Changed by Neerav; 6 July 2005
					$b["labels"]	= $label_array;	// gives an array even if 0 labels
					$b["attachment"]= ((strlen($t[9-$less]) == 0) ? array() : explode(",",$t[9-$less]));// Changed to give an array even if 0 attachments
					//$b["??"]		= $t[11-$less];			// what is this??
					$b["long_date"]	= $long_date;		// added
					$b["long_time"]	= $long_time;		// added

					array_push($this->box, $b);
				}
			}
			if (isset($raw["cs"])) {
				// Fix for 14 OR 12 fields!!; by Neerav; 25 July 2005
				$less  = (count($raw["cs"]) == 12) ? 2 : 0 ;

				$this->view = GM_CONVERSATION;				
				$this->conv_id = $raw["cs"][1];
				$this->conv_title = $raw["cs"][2];
				// $raw["cs"][3]		// what is this??  escape/html version of 2?
				// $raw["cs"][4]		// what is this?? empty
				// $raw["cs"][5]		// (array) conversation labels, below
				// $raw["cs"][6]		// what is this?? array
				// $raw["cs"][7]		// what is this?? integer/bool?
				$this->conv_total = $raw["cs"][8];
				// (count($t) == 14) $raw["cs"][9] 	// may be missing! what is this?? long id number?
				// (count($t) == 14) $raw["cs"][10]	// may be missing! what is this?? empty
				// $raw["cs"][11-$less]		// may be 9 what is this?? repeat of id 1?
				// $raw["cs"][12-$less]		// may be 10 what is this?? array
				// $raw["cs"][13-$less]		// may be 10 what is this?? integer/bool?

				$this->conv_labels = array ();
				$this->conv_starred = false;

				// Added labels for use in multiple languages; by Neerav; 7 Aug 2005
				//$this->conv_labels_lang = $raw["cs"][5];	// for use with languages

				// Changed to give translated label names; by Neerav; 6 July 2005
				// Changed back to be language compatible; by Neerav; 8 Aug 2005
				//$this->conv_labels_temp = (count($raw["cs"][5])==0) ? array() : $raw["cs"][5];	
				$temp_array = $raw["cs"][5];
				foreach($raw["cs"][5] as $label_entry) {
					switch ($label_entry) {
						//case "^i": 	$this->conv_labels[] = "Inbox";		break;
						//case "^s": 	$this->conv_labels[] = "Spam";		break;
						//case "^k": 	$this->conv_labels[] = "Trash";		break;
						case "^t": 	$this->conv_starred  = true;		break;
						//case "^r": 	$this->conv_labels[] = "Draft";		break;
						default:	$this->conv_labels[] = $label_entry; break;
					}
				}
				
				$this->conv = array();
							 
				//Debugger::say(print_r($raw["mg"],true));
				$mg_count = count($raw["mg"]);
				for ($i = 0; $i < $mg_count; $i++) {
					if ($raw["mg"][$i][0] == "mb" && $i > 0) {
						$b["body"] .= $raw["mg"][$i][1];
						if ($raw["mg"][$i][2] == 0) {
							array_push($this->conv, $b);
							unset($b);
						}
					} elseif (($raw["mg"][$i][0] == "mi") or ($raw["mg"][$i][0] == "di")) {
						// Changed to merge "di" and "mi" routines; by Neerav; 11 July 2005
						if (isset($b)) {
							array_push($this->conv, $b);
							unset($b);
						}
						$b = array();
						// $raw["mg"][$i][0] is mi or di
						$b["mbox"] 			= $raw["mg"][$i][1];	// Added by Neerav; 11 July 2005
						$b["index"] 		= $raw["mg"][$i][2];
						$b["id"] 			= $raw["mg"][$i][3];
						$b["is_star"] 		= $raw["mg"][$i][4];
						if ($b["is_star"] == 1) $this->conv_starred = true;
						$b["draft_parent"] 	= $raw["mg"][$i][5];  	// was only defined in draft, now both; Changed by Neerav; 11 July 2005
						$b["sender"] 		= $raw["mg"][$i][6];
						$b["sender_short"]	= $raw["mg"][$i][7];	// Added by Neerav; 11 July 2005
						$b["sender_email"] 	= str_replace("\"", "", $raw["mg"][$i][8]);		// remove annoying d-quotes in address
						$b["recv"] 			= $raw["mg"][$i][9];
						$b["recv_email"] 	= str_replace("\"", "", $raw["mg"][$i][11]);
						$b["cc_email"] 		= str_replace("\"", "", $raw["mg"][$i][12]);	// was only defined in draft, now both; Changed by Neerav; 11 July 
						$b["bcc_email"] 	= str_replace("\"", "", $raw["mg"][$i][13]);	// was only defined in draft, now both; Changed by Neerav; 11 July 							
						$b["reply_email"] 	= str_replace("\"", "", $raw["mg"][$i][14]);
						$b["dt_easy"] 		= $raw["mg"][$i][10];
						$b["dt"] 			= $raw["mg"][$i][15];
						$b["subj"] 			= $raw["mg"][$i][16];
						$b["snippet"] 		= $raw["mg"][$i][17];
						//$raw["mg"][$i][19];	// 0 or 1 What is this??
						$b["attachment"] 	= array();
						if (isset($raw["mg"][$i][18])) {
							foreach ($raw["mg"][$i][18] as $bb) {
								array_push(
									$b["attachment"], 
									array("id"		=> $bb[0],
										"filename"	=> $bb[1],
										"type"		=> $bb[2],
										"size"		=> $bb[3]
									)
								);
							}
						}
						if ($raw["mg"][$i][0] == "mi") {
							$b["is_draft"] 		= false;
							$b["body"] 			= "";
							$b["quote_str"] 	= $raw["mg"][$i][21];
							$b["quote_str_html"]= $raw["mg"][$i][22];
							// $raw["mg"][$i][20];  // ?? repeated date slightly different format  // Added by Neerav; 11 July 2005
						} elseif ($raw["mg"][$i][0] == "di") {
							$b["is_draft"] 		= true;
							$b["body"] 			= $raw["mg"][$i][20];
							// $raw["mg"][$i][21];  // ?? repeated date slightly different format  // Added by Neerav; 11 July 2005
							$b["quote_str"] 	= $raw["mg"][$i][22];
							$b["quote_str_html"]= $raw["mg"][$i][23];
						}
					}
				}
				if (isset($b)) array_push($this->conv, $b);
			}
		}
		
		// Changed from elseif to if; by Neerav; 5 Aug 2005
		if  ($type & GM_CONTACT) {
			//Debugger::say(print_r($raw,true));
			$this->contacts = array();
			// Added by Neerav; 29 June 2005
			// Since gmail changes their Contacts array often, we need to see which
			//    latest flavor (or flavour) they are using!
			// Some accounts use "a" for both lists and details
			// 	  whereas some accounts use "cl" for lists and "cov" for details
			$type = "";
			if (isset($raw["a"])) {
				$c_array = "a";
				// determine is this is a list or contact detail
				if ((count($raw["a"]) == 2) and isset($raw["a"][1][6])) {
					$type 		= "detail";
					$c_id 		= 0;
					$c_name 	= 1;
					$c_email 	= 3;
					$c_notes 	= 5;
					$c_detail 	= 6;
				} else {
					$c_email 	= 3;
					$c_notes 	= 4;
					$type 		= "list";
				}
			} elseif (isset($raw["cl"])) {	// list
				$c_array 	= "cl";
				$c_email 	= 4;
				$c_notes 	= 5;
				$type 		= "list";
			} elseif (isset($raw["cov"])) {	// contact detail in accounts using "cl"
				$c_array 	= "cov";
				$type 		= "detail";
				$c_id 		= 1;
				$c_name 	= 2;
				$c_email 	= 4;
				$c_notes 	= 7;
				$c_detail 	= 8;
			} else {
				array_push(
					$this->contacts, 
					array("id" 	 => "error",
						 "name"  => "libgmailer Error",
						 "email" => "libgmailer@error.net",
						 "notes" => "libgmailer could not find the Contacts information "
						 	. "due to a change in the email service (again!).  Please contact " 
						 	. "the author of this program (which uses libgmailer) for a fix."
					)
				);
			}

			// Changed by Neerav; 
			// from "a" to "cl" 15 June 2005
			// from "cl" to whichever exists 29 June 2005
			if ($type == "list") {
				// An ordinary list of contacts
				for ($i = 1; $i < count($raw["$c_array"]); $i++) {
					$a = $raw["$c_array"][$i];
					$b = array(
						"id"	=> $a[1],				// contact id; Added by Neerav; 6 May 2005
						"name"	=> $a[2],
						"email"	=> str_replace("\"", "", $a[$c_email])	// Last Changed by Neerav; 29 June 2005
					);
					// Last Changed by Neerav; 29 June 2005
					if (isset($a[$c_notes])) {
						$b["notes"] = $a[$c_notes];
					}
					array_push($this->contacts, $b);
				}
			} elseif ($type == "detail") {
				// Added by Neerav; 1 July 2005
				// Contact details (advanced contact information)
				// used when a contact id was supplied for retrieval
				$cov = array();
				$cov["id"]		= $raw["$c_array"][1][$c_id];
				$cov["name"] 	= $raw["$c_array"][1][$c_name];
				$cov["email"] 	= str_replace("\"", "", $raw["$c_array"][1][$c_email]);
				if (isset($raw["$c_array"][1][$c_notes][0])) {
					$cov["notes"] = ($raw["$c_array"][1][$c_notes][0] == "n") ? $raw["$c_array"][1][$c_notes][1] : "";
				} else {
					$cov["notes"] = "";
				}

				$details = array();
				$num_details = count($raw["$c_array"][1][$c_detail]);
				if ($num_details > 0) {
					for ($i = 0; $i < $num_details; $i++) {
						$details[$i][] = array(
								"type"	=> "detail_name",
								"info" 	=> $raw["$c_array"][1][$c_detail][$i][0]
						);
						if (isset($raw["$c_array"][1][$c_detail][$i][1])) {
							$temp = $raw["$c_array"][1][$c_detail][$i][1];
						} else {
							$temp = array();
						}
						for ($j = 0; $j < count($temp); $j += 2) {
							switch ($temp[$j]) {
								case "p": $field = "phone";		break;
								case "e": $field = "email";		break;
								case "m": $field = "mobile";	break;
								case "f": $field = "fax";		break;
								case "b": $field = "pager";		break;
								case "i": $field = "im";		break;
								case "d": $field = "company";	break;
								case "t": $field = "position";	break;	// t = title
								case "o": $field = "other";		break;
								case "a": $field = "address";	break;
								default:  $field = $temp[$j];	break;	// default to the field type
							}
							$details[$i][] = array(
									"type" => $field, 
									"info" => $temp[$j+1]
							);
						}
					}
				}

				$cov["details"] = $details;
				//Debugger::say(print_r($cov["details"],true));
				array_push($this->contacts, $cov);
				//Debugger::say(print_r($this->contacts,true));
			}
			$this->view = GM_CONTACT;

		}
		
		// Changed from elseif to if; by Neerav; 5 Aug 2005
		if ($type & (GM_PREFERENCE)) {
			//Debugger::say(print_r($raw,true));
			
			// go to Preference Panel
			// Added by Neerav; 6 July 2005
			if (isset($raw["pp"][1])) {
				switch ($raw["pp"][1]) {
					case "g": 	$this->goto_pref_panel = "general";		break;
					case "l": 	$this->goto_pref_panel = "labels";		break;
					case "f": 	$this->goto_pref_panel = "filters";		break;
					default:	$this->goto_pref_panel = $raw["pp"][1];	break;
				}
			}

			// SETTINGS (NON-Filters, NON-Labels)
			// Added by Neerav; 29 Jun 2005
			
			$this->setting_gen = array();
			$this->setting_fpop = array();
			$this->setting_other = array();

			//Debugger::say(print_r($raw["p"],true));
			if (isset($raw["p"])) {
				// GENERAL SETTINGS
				$gen = array(
					"expand_labels"	=> 1,
					"expand_invites" => 1,
					"use_cust_name" => 0,
					"name_google" 	=> ((isset($raw["gn"][1])) ? $raw["gn"][1] : ""),
					"name_display" 	=> "",
					"use_reply_to"	=> 0,
					"reply_to" 		=> "",
					"language" 		=> "en",
					"page_size" 	=> 25,
					"shortcuts" 	=> 0,
					"p_indicator" 	=> 0,
					"show_snippets" => 0,
					"use_signature"	=> 0,
					"signature" 	=> ""
				);
	
				// FORWARDING AND POP
				$fpop = array(
					"forward"		=> 0,
					"forward_to" 	=> "",
					"forward_action"=> "",
					"pop_enabled" 	=> 0,
					"pop_action" 	=> 0
				);
	
				// OTHER
				$other = array(
					"rich_text" 	=> 0		// not used yet or has been removed
				);
	
				if (isset($raw["gn"][1])) {
					$gen["name_google"] = $raw["gn"][1];
				}
				
				for ($i = 1; $i < count($raw["p"]); $i++) {
					$pref = $raw["p"][$i][0];
					$value = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "";

					switch ($pref) {
					// SIDE BOXES
						case "bx_show0": $gen["expand_labels"] = $value;	break;	// (boolean) labels box {0 = collapsed, 1 = expanded}
						case "bx_show1": $gen["expand_invites"] = $value;	break;	// (boolean) invite box {0 = collapsed, 1 = expanded}
					// GENERAL SETTINGS
						case "sx_dn":	$gen["name_display"] = $value;		break;	// (string) name on outgoing mail (display name)
						case "sx_rt":	$gen["reply_to"] = $value;			break;	// (string) reply to email address
						case "sx_dl":	$gen["language"] = $value;			break;	// (string) display language
						case "ix_nt":	$gen["page_size"] = $value;			break;	// (integer) msgs per page (maximum page size)
						case "bx_hs":	$gen["shortcuts"] = $value;			break;	// (boolean) keyboard shortcuts {0 = off, 1 = on}
						case "bx_sc":	$gen["p_indicator"] = $value;		break;	// (boolean) personal level indicators {0 = no indicators, 1 = show indicators}
						case "bx_ns":	$gen["show_snippets"] = !$value;	break;	// (boolean) no snippets {0 = show snippets, 1 = no snippets}
																					// 		we INVERSE this for convenience
						case "sx_sg":	$gen["signature"] = $value;			break;	// (string) signature
					// FORWARDING AND POP
						case "sx_em":	$fpop["forward_to"] = $value;		break;	// (string) forward to email
						case "sx_at":	$fpop["forward_action"] = $value;	break;	// (string??) forwarding action {selected (keep), archive, trash}
						case "bx_pe":	$fpop["pop_enabled"] = $value;		break;	// (boolean) pop enabled {0 = disabled, 1 = enabled}
						case "ix_pd":	$fpop["pop_action"] = $value;		break;	// (integer) action after pop access {0 = keep, 1 = archive, 2 = trash}
					// OTHER
						// 		not used yet or has been removed from Gmail
						case "bx_cm":	$other["rich_text"] = $value;		break;	// (boolean) rich text composition {0 = plain text, 1 = rich text}
																					
						default:		$other["$pref"] = $value;			break;
					}
				}
			
				// set useful implicit boolean settings
				if ($gen["name_display"] != "") $gen["use_cust_name"] = 1;
				if ($gen["reply_to"] != "") 	$gen["use_reply_to"]  = 1;
				if ($gen["signature"] != "") 	$gen["use_signature"] = 1;
				if ($fpop["forward_to"] != "")  $fpop["forward"] 	  = 1;

				$this->setting_gen = $gen;
				$this->setting_fpop = $fpop;
				$this->setting_other = $other;
			}

			//Debugger::say(print_r($this->setting_gen,true));
			//Debugger::say(print_r($this->setting_fpop,true));
			//Debugger::say(print_r($this->setting_other,true));
			//Debugger::say(print_r($this,true));

			// LABELS
			$this->label_list = array();
			$this->label_total = array();
			if (isset($raw["cta"][1])) {
				foreach ($raw["cta"][1] as $v) {
					array_push($this->label_list, $v[0]);
					array_push($this->label_total, $v[1]);
				}
			}
						
			// FILTERS
			$this->filter = array();
			if (isset($raw["fi"][1])) {
				foreach ($raw["fi"][1] as $fi) {
					// Changed/Added by Neerav; 23 Jun 2005
					// filter rules/settings
					//     (The "() ? :" notation is used because empty/false fields at the end of an
					//         array are not always defined)
					$b = array(
						// (integer) filter id number
						"id" 		=> 					 	$fi[0],
						// (string) gmail's filter summary
						"query" 	=> ((isset($fi[1]))    ? $fi[1] : ""),						
						// (string) from field has...
						"from" 		=> ((isset($fi[2][0])) ? $fi[2][0] : ""),
						// (string) to field has...
						"to" 		=> ((isset($fi[2][1])) ? $fi[2][1] : ""),
						// (string) subject has...
						"subject" 	=> ((isset($fi[2][2])) ? $fi[2][2] : ""),
						// (string) msg has the words...
						"has" 		=> ((isset($fi[2][3])) ? $fi[2][3] : ""),
						// (string) msg doesn't have the words...
						"hasnot" 	=> ((isset($fi[2][4])) ? $fi[2][4] : ""),
						// (boolean) has an attachment
						"hasattach" => ((isset($fi[2][5]) and ($fi[2][5] == "true" or $fi[2][5] === true)) ? true : false),
						// (boolean) archive (skip the inbox)
						"archive" 	=> ((isset($fi[2][6]) and ($fi[2][6] == "true" or $fi[2][6] === true)) ? true : false),	
						// (boolean) apply star
						"star" 		=> ((isset($fi[2][7]) and ($fi[2][7] == "true" or $fi[2][7] === true)) ? true : false),
						// (boolean) apply label
						"label" 	=> ((isset($fi[2][8]) and ($fi[2][8] == "true" or $fi[2][8] === true)) ? true : false),
						// (string) label name to apply
						"label_name"=> ((isset($fi[2][9])) ? $fi[2][9] : ""),
						// (boolean) forward
						"forward" 	=> ((isset($fi[2][10]) and ($fi[2][10] == "true" or $fi[2][10] === true)) ? true : false),
						// (string email) forward to email address
						"forwardto" => ((isset($fi[2][11])) ? $fi[2][11]: ""),
						// (boolean) trash the message
						"trash" 	=> ((isset($fi[2][12]) and ($fi[2][12] == "true" or $fi[2][12] === true)) ? true : false)
					);
					array_push($this->filter, $b);
					//Debugger::say(print_r($b,true));
				}
			}
			$this->view = GM_PREFERENCE;
		} /* else { */
/* 			$this->created = 0; */
/* 			$this->snapshot_error = "libgmailer: no snapshot type specified";  // Added by Neerav; 3 Aug 2005 */
/* 			return null; */
/* 		} */

		//Debugger::say(print_r($this,true));
		$this->created = 1;
		return 1;
	}				 
}


/**
 * Class Debugger
 *
 * @package GMailer 
*/
class Debugger {	
   /**
    * Record debugging message.
    *
    * @param string $str Message to be recorded
    * @return void
    * @static
   */
	function say($str) {
		global $D_FILE, $D_ON;
		if ($D_ON) {
			$fd = fopen($D_FILE, "a+");
			$str = str_replace("*/", "*", $str);   // possible security hole
			fwrite($fd, "<?php /** ".$str." **/ ?>\n");
			fclose($fd);
		}
	}
}	 


?>