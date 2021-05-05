<?php
/**
 * smtp.class.php	 邮件发送类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-09-23 
 */

class smtp{

	public $host_name = 'localhost'; 
	public $time_out = 30;
	public $log = false;
	public $debug  = false;
	public $sock = false;
	public $relay_host;
	public $smtp_port;
	public $auth;
	public $user;
	public $pass;


	/**
	 *  初始化邮件配置信息
	 */	
	public function __construct($relay_host, $smtp_port, $auth, $user, $pass){
		$this->smtp_port = $smtp_port;
		$this->relay_host = $relay_host;
		$this->auth = $auth;
		$this->user = $user;
		$this->pass = $pass;
	}

	
	/**
	 * 外部调用的方法
	 * 用来发送电子邮件
	 * @param $to       收件人邮箱
	 * @param $from     发件人邮箱
	 * @param $subject    邮件标题
	 * @param $body       邮件内容
	 * @param $mailtype    邮件内容类型	 
	 */	
	public function sendmail($to, $from, $subject = "", $body = "", $mailtype = "HTML", $cc = "", $bcc = "", $additional_headers = ""){
		$mail_from = $this->get_address($this->strip_comment($from));
		$body = preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $body);
		$header = "MIME-Version:1.0\r\n";
		if($mailtype=="HTML"){
		    $header .= "Content-Type:text/html\r\n";
		}
		$header .= "To: ".$to."\r\n";
		if ($cc != "") {
		    $header .= "Cc: ".$cc."\r\n";
		}
		$header .= "From: $from<".$from.">\r\n";
		$header .= "Subject: ".$subject."\r\n";
		$header .= $additional_headers;
		$header .= "Date: ".date("r")."\r\n";
		$header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$mail_from.">\r\n";
		$TO = explode(",", $this->strip_comment($to));
		if ($cc != "") {
		    $TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
		}
		if ($bcc != "") {
	    	$TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
		}
		$sent = true;
		foreach ($TO as $rcpt_to) {
		$rcpt_to = $this->get_address($rcpt_to);
		if (!$this->smtp_sockopen($rcpt_to)) {
			$this->log_write("Error: Cannot send email to ".$rcpt_to."\n");
			$sent = false;
			continue;
		}

		if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
		    $this->log_write("E-mail has been sent to <".$rcpt_to.">\n");
		} else {
			$this->log_write("Error: Cannot send email to <".$rcpt_to.">\n");
			$sent = false;
		}
		fclose($this->sock);
		    $this->log_write("Disconnected from remote host\n");
		}
		return $sent;
	}



	private function smtp_send($helo, $from, $to, $header, $body = ""){

		if (!$this->smtp_putcmd("HELO", $helo)) {
		    return $this->smtp_error("sending HELO command");
		}
		if($this->auth){
			if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user))) {
			    return $this->smtp_error("sending HELO command");
			}
			if (!$this->smtp_putcmd("", base64_encode($this->pass))) {
			    return $this->smtp_error("sending HELO command");
			}
		}


		if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">")) {
		    return $this->smtp_error("sending MAIL FROM command");
		}

		if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">")) {
		    return $this->smtp_error("sending RCPT TO command");
		}

		if (!$this->smtp_putcmd("DATA")) {
		    return $this->smtp_error("sending DATA command");
		}

		if (!$this->smtp_message($header, $body)) {
		    return $this->smtp_error("sending message");
		}

		if (!$this->smtp_eom()) {
		    return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}

		if (!$this->smtp_putcmd("QUIT")) {
		    return $this->smtp_error("sending QUIT command");
		}

		return true;

	}


	public function smtp_sockopen($address){
		if ($this->relay_host == "") {
		    return $this->smtp_sockopen_mx($address);
		} else {
		    return $this->smtp_sockopen_relay();
		}
	}


	public function smtp_sockopen_relay(){
		$this->log_write("Trying to ".$this->relay_host.":".$this->smtp_port."\n");
		$this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
		if (!($this->sock && $this->smtp_ok())) {
			$this->log_write("Error: Cannot connenct to relay host ".$this->relay_host."\n");
			$this->log_write("Error: ".$errstr." (".$errno.")\n");
			return false;
		}
		$this->log_write("Connected to relay host ".$this->relay_host."\n");
		return true;
	}


	public function smtp_sockopen_mx($address){
		$domain = preg_replace("/^.+@([^@]+)$/", "\1", $address);
		if (!@getmxrr($domain, $MXHOSTS)) {
			$this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
			return false;
		}

		foreach ($MXHOSTS as $host) {
			$this->log_write("Trying to ".$host.":".$this->smtp_port."\n");
			$this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok())) {
				$this->log_write("Warning: Cannot connect to mx host ".$host."\n");
				$this->log_write("Error: ".$errstr." (".$errno.")\n");
				continue;
			}

			$this->log_write("Connected to mx host ".$host."\n");
			return true;
		}
		$this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
		return false;
	}


	public function smtp_message($header, $body){
		fputs($this->sock, $header."\r\n".$body);
		$this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
		return true;
	}


	public function smtp_eom(){
		fputs($this->sock, "\r\n.\r\n");
		$this->smtp_debug(". [EOM]\n");
		return $this->smtp_ok();
	}


	public function smtp_ok(){
		$response = str_replace("\r\n", "", fgets($this->sock, 512));
		$this->smtp_debug($response."\n");
		if (!preg_match("/^[23]/", $response)) {
			fputs($this->sock, "QUIT\r\n");
			fgets($this->sock, 512);
			$this->log_write("Error: Remote host returned \"".$response."\"\n");
			return false;
		}
		return true;
	}


	public function smtp_putcmd($cmd, $arg = ""){
		if ($arg != "") {
		    if($cmd=="") $cmd = $arg;
		    else $cmd = $cmd." ".$arg;
		}
		fputs($this->sock, $cmd."\r\n");
		$this->smtp_debug("> ".$cmd."\n");
		return $this->smtp_ok();

	}


	public function smtp_error($string){
		$this->log_write("Error: Error occurred while ".$string.".\n");
		return false;
	}


	public function log_write($message){
		$this->smtp_debug($message);
		if ($this->log) {
		    write_log($message);
		}
		return true;

	}


	public function strip_comment($address){
		$comment = "/\([^()]*\)/";
		while (preg_match($comment, $address)) {
			$address = preg_replace($comment, "", $address);
		}
		return $address;
	}


	public function get_address($address){
		$address = preg_replace("/([ \t\r\n])+/", "", $address);
		$address = preg_replace("/^.*<(.+)>.*$/", "\1", $address);
		return $address;
	}

			
	public function smtp_debug($message){
		if ($this->debug) {
		    echo '<p>'.$message.'</p>';
		}
	}

}