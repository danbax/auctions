<?php
if (!defined('Access')) {
	die('Silence is gold');
}

/** Example to use *************************************

    $mail = new MailBuilder("from@mail.com");
    $isSent = $mail->setSubject("My Subject")
                    ->setMessage("My Message")
                    ->setBcc("bcc@mail.com")
                    ->setCc("cc@mail.com")
                    ->setAttachmentCSVFromString($csvFileString)
					->sendEmail(to@email.com);

 *******************************************************/

class MailBuilder {

	/** @var string end of file */
	const EOL = "\r\n";
	/** @var string mail headers */
	private $headers;
	/** @var string error message */
	private $error;
	/** @var string email from */
	private $from;
	/** @var string body message of email */
	private $message;
	/** @var string subject of email */
	private $subject;
	/** @var string separator for multiple content of email */
	private $multipartSep;
	/** @var string CSV attachment from string with encoding */
	private $csvAttachment;

	/**
	 * Email Builder constructor
	 * @param string $from eMail from address
	 */
	public function __construct($from) {
		$this->from = $from;
		$this->message = '';
		$this->subject = '';
		$this->csvAttachment = null;
		$this->multipartSep = null;
		$this->multipartSep = "-----" . md5(time()) . "-----" ;
		$this->headers = "MIME-Version: 1.0" . self::EOL;
		$this->headers .= "From: {$from}" . self::EOL;
	}


	/**
	 * @param string $csvFileString string of CSV file that will be encoded by base64
	 * @return MailBuilder $this reference to self
	 */
	public function setAttachmentCSVFromString($csvFileString) {
		$csvFileAttachment = chunk_split(base64_encode($csvFileString));
		// attachment
		$this->csvAttachment .= "--" . $this->multipartSep . self::EOL;
		$this->csvAttachment .= "Content-Encoding: UTF-8" . self::EOL;
		$this->csvAttachment .= "Content-Type: text/csv; charset=utf-8;". self::EOL;
		$this->csvAttachment .= "Content-Transfer-Encoding: base64" . self::EOL;
		$this->csvAttachment .= "Content-Disposition: attachment; filename=Report-" . date('j-F-Y') . ".csv" . self::EOL;
		$this->csvAttachment .= $csvFileAttachment . self::EOL;
		$this->csvAttachment .= "--" . $this->multipartSep . "--";

		return $this;
	}

	/**
	 * @param string $to email address
	 * @return bool true if sent false for not
	 */
	public function sendEmail($to) {

		if ($this->csvAttachment) {
			$this->headers .= "Content-Type: multipart/mixed; boundary=\"" . $this->multipartSep . "\"" . self::EOL;
			$this->message .= $this->csvAttachment;
		} else {
			$this->headers .= "Content-type:text/html;charset=UTF-8" . self::EOL;
		}

		if (mail($to, $this->subject, $this->message, $this->headers)) {
			return true;

		} else {
			$this->error = "Problem on sending email";
			return false;
		}
	}

	/**
	 * @param string $toCc email additional address invisible for all
	 * @return MailBuilder $this reference to self
	 */
	public function setCc($toCc) {
		$this->headers .= "Cc: {$toCc}" . self::EOL;

		return $this;
	}

	/**
	 * @param string $toBcc email additional address that will be visible for all
	 * @return MailBuilder $this reference to self
	 */
	public function setBcc($toBcc) {
		$this->headers .= "Bcc: {$toBcc}" . self::EOL;

		return $this;
	}

	/**
	 * @param string $message body message of email
	 * @return MailBuilder $this reference to self
	 */
	public function setMessage($message) {
		$this->message = $message;

		return $this;
	}

	/**
	 * @param string $subject subject of email
	 * @return MailBuilder $this reference to self
	 */
	public function setSubject($subject) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Print error msg function
	 * @return string error
	 */
	public function getError(){
		return $this->error;
	}
}