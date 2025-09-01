<?php
/**
 * PHPMailer - A full-featured email creation and transfer class for PHP.
 * PHP Version 5.5.
 * @package   PHPMailer
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2010-2020, Marcus Bointon
 * @copyright 2004-2009, Jim Jagielski
 * @copyright 2001-2003, Brent R. Matzelle
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://github.com/PHPMailer/PHPMailer
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer - A full-featured email creation and transfer class for PHP.
 * @package  PHPMailer
 * @author   Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 * @author   Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author   Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author   Brent R. Matzelle (original founder)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class PHPMailer
{
    //Email priority settings
    const PRIORITY_HIGH = 1;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 5;

    /**
     * The PHPMailer Version number.
     * @var string
     */
    public $Version = '6.5.0';

    /**
     * Email priority.
     * Options: null (default), 1 (High), 3 (Normal), 5 (Low).
     * When null, the header is not set at all.
     * @var integer
     */
    public $Priority = null;

    /**
     * The character set of the message.
     * @var string
     */
    public $CharSet = self::CHARSET_ISO88591;

    /**
     * The character set of the message.
     * @var string
     * @deprecated Use CharSet instead
     */
    public $Charset;

    /**
     * The MIME Content-type of the message.
     * @var string
     */
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;

    /**
     * The message's encoding.
     * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
     * @var string
     */
    public $Encoding = self::ENCODING_8BIT;

    /**
     * Holds the most recent mailer error message.
     * @var string
     */
    public $ErrorInfo = '';

    /**
     * The From email address for the message.
     * @var string
     */
    public $From = 'root@localhost';

    /**
     * The From name of the message.
     * @var string
     */
    public $FromName = 'Root User';

    /**
     * The Sender email (Return-Path) of the message.
     * If not empty, will be sent via -f to sendmail or as the MAIL FROM address in SMTP.
     * @var string
     */
    public $Sender = '';

    /**
     * The Subject of the message.
     * @var string
     */
    public $Subject = '';

    /**
     * An HTML or plain text message body.
     * If HTML then call isHTML(true).
     * @var string
     */
    public $Body = '';

    /**
     * The HTML message body.
     * If this is set, the email will be sent as HTML by default.
     * Set Body to a plain-text version of this for delivery to non-HTML clients.
     * isHTML(true) is called internally if this contains any HTML tags.
     * @var string
     */
    public $AltBody = '';

    /**
     * An iCal message part body.
     * Only supported in simple alt or alt_inline message types
     * To generate iCal event structures, use the bundled IcalEvent class.
     * @see IcalEvent
     * @var string
     */
    public $Ical = '';

    /**
     * The plain-text message body.
     * This is used as a fallback for email clients that do not support HTML.
     * @var string
     * @deprecated This is now just an alias for AltBody
     */
    protected $MIMEBody = '';

    /**
     * The complete compiled MIME message body.
     * @var string
     * @access protected
     */
    protected $MIMEHeader = '';

    /**
     * The complete compiled MIME message headers.
     * @var string
     * @access protected
     */
    protected $mailHeader = '';

    /**
     * Extra headers that create() adds to the message.
     * @var array
     * @access protected
     */
    protected $CustomHeader = [];

    /**
     * The message ID to be used in the Message-ID header.
     * If empty, a unique id will be generated.
     * You can set your own, but it must be in the format "<id@domain>",
     * as defined in RFC5322 section 3.6.4 or it will be ignored.
     * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
     * @var string
     */
    public $MessageID = '';

    /**
     * The message Date to be used in the Date header.
     * If empty, the current date will be used.
     * You can set your own, but it must be in a format acceptable to strtotime().
     * @var string
     */
    public $MessageDate = '';

    /**
     * An array of 'to' addresses.
     * @var array
     * @access protected
     */
    protected $to = [];

    /**
     * An array of 'cc' addresses.
     * @var array
     * @access protected
     */
    protected $cc = [];

    /**
     * An array of 'bcc' addresses.
     * @var array
     * @access protected
     */
    protected $bcc = [];

    /**
     * An array of reply-to addresses.
     * @var array
     * @access protected
     */
    protected $ReplyTo = [];

    /**
     * An array of all kinds of addresses.
     * Includes to, cc, bcc, reply-to.
     * @var array
     * @access protected
     */
    protected $all_recipients = [];

    /**
     * An array of attachments.
     * @var array
     * @access protected
     */
    protected $attachment = [];

    /**
     * The method to send mail.
     * Options: "mail", "sendmail", or "smtp".
     * @var string
     */
    public $Mailer = 'mail';

    /**
     * The path to the sendmail program.
     * @var string
     */
    public $Sendmail = '/usr/sbin/sendmail';

    /**
     * Whether to use POP-before-SMTP authentication.
     * @var boolean
     */
    public $UseSendmailOptions = true;

    /**
     * The email address that a reading confirmation will be sent to.
     * @var string
     */
    public $ConfirmReadingTo = '';

    /**
     * The hostname to use in Message-ID and Received headers
     * and as default HELO string.
     * If empty, the value returned by SERVER_NAME is used or 'localhost.localdomain'.
     * @var string
     */
    public $Hostname = '';

    /**
     * An ID to be used in the X-Mailer header.
     * If empty, is set to 'PHPMailer 6.5.0 (https://github.com/PHPMailer/PHPMailer)'.
     * @var string
     */
    public $XMailer = '';

    /**
     * The SMTP server host.
     * You can specify multiple servers by separating them with a semicolon.
     * If you need to specify a different port for each server, use this format: [hostname:port].
     * You can also specify encryption type, for example: [tls://smtp1.example.com:587].
     * @var string
     */
    public $Host = 'localhost';

    /**
     * The default SMTP server port.
     * @var integer
     */
    public $Port = 25;

    /**
     * The SMTP HELO of the message.
     * Default is $Hostname. If $Hostname is empty, PHPMailer tries to find
     * one with SERVER_NAME or sets 'localhost.localdomain'.
     * @var string
     * @see PHPMailer::$Hostname
     */
    public $Helo = '';

    /**
     * What kind of encryption to use on the SMTP connection.
     * Options: '', 'ssl' or 'tls'.
     * @var string
     */
    public $SMTPSecure = '';

    /**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     * @var boolean
     * @see PHPMailer::$Username
     * @see PHPMailer::$Password
     */
    public $SMTPAuth = false;

    /**
     * Options array for SMTP connection.
     * Currently only applicable to PHP 5.6+ and only to get around TLS certificate verification problems.
     * The main use of this is to pass SMTPOptions to the underlying mail function.
     * @var array
     */
    public $SMTPOptions = [];

    /**
     * SMTP username.
     * @var string
     */
    public $Username = '';

    /**
     * SMTP password.
     * @var string
     */
    public $Password = '';

    /**
     * SMTP auth type.
     * Options are LOGIN (default), PLAIN, NTLM, CRAM-MD5.
     * @var string
     */
    public $AuthType = '';

    /**
     * The SMTP server timeout in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     * This needs to be longer than the server's own timeout.
     * @var integer
     */
    public $Timeout = 300;

    /**
     * The SMTP server timeout in seconds.
     * @var integer
     * @deprecated Use Timeout instead
     */
    public $Timelimit;

    /**
     * How to handle debug output.
     * Options:
     * * `0` No output
     * * `1` Commands
     * * `2` Data and commands
     * * `3` As 2 plus connection status
     * * `4` Low-level data output.
     * @var integer
     */
    public $SMTPDebug = 0;

    /**
     * How to handle debug output.
     * @var integer
     * @deprecated Use SMTPDebug instead
     */
    public $Debugoutput;

    /**
     * Whether to keep the SMTP connection open after each send.
     * If this is true, call smtpClose() manually.
     * @var boolean
     */
    public $SMTPKeepAlive = false;

    /**
     * Whether to use VERP.
     * @see http://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @var boolean
     */
    public $SingleTo = false;

    /**
     * The path to the language file.
     * The default language is English.
     * @var string
     */
    public $language = [];

    /**
     * The DKIM selector.
     * @var string
     */
    public $DKIM_selector = '';

    /**
     * The DKIM Identity.
     * Usually the email address used as the source of the email.
     * @var string
     */
    public $DKIM_identity = '';

    /**
     * The DKIM pass phrase.
     * Used with DKIM_private.
     * @var string
     */
    public $DKIM_passphrase = '';

    /**
     * The DKIM private key file path.
     * @var string
     */
    public $DKIM_private = '';

    /**
     * The DKIM private key string.
     * If this is set, DKIM_private is ignored.
     * @var string
     */
    public $DKIM_private_string = '';

    /**
     * The DKIM domain.
     * @var string
     */
    public $DKIM_domain = '';

    /**
     * Whether to allow empty strings in the body.
     * @var boolean
     */
    public $AllowEmpty = false;

    /**
     * The callback function for processing debug output.
     * @var mixed
     */
    public $DebugOutput = 'echo';

    /**
     * The S/MIME certificate file path.
     * @var string
     */
    public $sign_cert_file = '';

    /**
     * The S/MIME private key file path.
     * @var string
     */
    public $sign_key_file = '';

    /**
     * The S/MIME private key password.
     * @var string
     */
    public $sign_key_pass = '';

    /**
     * The S/MIME extra certificates file path.
     * @var string
     */
    public $sign_extracerts_file = '';

    /**
     * The RFC 822 formatted message.
     * @var string
     * @access protected
     */
    protected $RFC822 = '';

    /**
     * The most recent SMTP server response.
     * @var string
     * @access protected
     */
    protected $smtp_conn;

    /**
     * The most recent SMTP server response.
     * @var string
     * @access protected
     */
    protected $last_smtp_response;

    /**
     * The most recent SMTP server response code.
     * @var string
     * @access protected
     */
    protected $last_smtp_response_code;

    /**
     * The list of plugins.
     * @var array
     * @access protected
     */
    protected $plugins = [];

    /**
     * The list of custom SMTP headers.
     * @var array
     * @access protected
     */
    protected $CustomSMTPHeader = [];

    /**
     * The SMTP connection resource.
     * @var resource
     * @access protected
     */
    protected $smtp;

    /**
     * Constructor.
     * @param boolean $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool)$exceptions;
        }
        $this->Debugoutput = (ini_get('html_errors') ? 'html' : 'echo');
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        //Close any open SMTP connection
        $this->smtpClose();
    }

    /**
     * Call mail() in a safe_mode-aware way.
     * Also checks for whether mail() is disabled in php.ini.
     * @param string $to To
     * @param string $subject Subject
     * @param string $body Body
     * @param string $header Headers
     * @param string $params Params
     * @return boolean
     * @access private
     */
    private function mailPassthru($to, $subject, $body, $header, $params)
    {
        //Check if mail() is disabled in php.ini
        if (ini_get('safe_mode') || !($this->UseSendmailOptions)) {
            $rt = @mail($to, $this->encodeHeader($this->Subject), $body, $header);
        } else {
            $rt = @mail($to, $this->encodeHeader($this->Subject), $body, $header, $params);
        }
        return $rt;
    }

    /**
     * Output debugging info.
     * Only generates output if SMTPDebug > 0.
     * @param string $str
     */
    protected function edebug($str)
    {
        if ($this->SMTPDebug <= 0) {
            return;
        }
        //Avoid clash with built-in function names
        if (!in_array($this->DebugOutput, ['error_log', 'html', 'echo']) and is_callable($this->DebugOutput)) {
            call_user_func($this->DebugOutput, $str, $this->SMTPDebug);
            return;
        }
        switch ($this->DebugOutput) {
            case 'error_log':
                //Don't output successful server responses
                if ($this->SMTPDebug > 2 and substr($str, 0, 3) !== '250') {
                    error_log($str);
                }
                break;
            case 'html':
                //Cleans up output a bit for a better looking display that's HTML-safe
                echo htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                ) . "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/m', "\n", $str);
                echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
                    "\n",
                    "\n \t \t \t",
                    trim($str)
                ) . "\n";
        }
    }

    /**
     * Sets message type to HTML or plain.
     * @param boolean $isHtml True for HTML mode.
     * @return void
     */
    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = self::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->ContentType = self::CONTENT_TYPE_PLAINTEXT;
        }
    }

    /**
     * Send messages using SMTP.
     * @return void
     */
    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    /**
     * Send messages using PHP's mail() function.
     * @return void
     */
    public function isMail()
    {
        $this->Mailer = 'mail';
    }

    /**
     * Send messages using $Sendmail.
     * @return void
     */
    public function isSendmail()
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'sendmail')) {
            $this->Sendmail = '/usr/sbin/sendmail';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'sendmail';
    }

    /**
     * Send messages using qmail.
     * @return void
     */
    public function isQmail()
    {
        $ini_sendmail_path = ini_get('sendmail_path');
        if (false === stripos($ini_sendmail_path, 'qmail')) {
            $this->Sendmail = '/var/qmail/bin/qmail-inject';
        } else {
            $this->Sendmail = $ini_sendmail_path;
        }
        $this->Mailer = 'qmail';
    }

    /**
     * Add a "To" address.
     * @param string $address The email address to send to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     */
    public function addAddress($address, $name = '')
    {
        return $this->addAnAddress('to', $address, $name);
    }

    /**
     * Add a "CC" address.
     * @param string $address The email address to send to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     */
    public function addCC($address, $name = '')
    {
        return $this->addAnAddress('cc', $address, $name);
    }

    /**
     * Add a "BCC" address.
     * @param string $address The email address to send to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     */
    public function addBCC($address, $name = '')
    {
        return $this->addAnAddress('bcc', $address, $name);
    }

    /**
     * Add a "Reply-To" address.
     * @param string $address The email address to reply to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     */
    public function addReplyTo($address, $name = '')
    {
        return $this->addAnAddress('Reply-To', $address, $name);
    }

    /**
     * Add an address to one of the recipient arrays.
     * Addresses that have been added already return false, but do not throw exceptions.
     * @param string $kind One of 'to', 'cc', 'bcc', 'Reply-To'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     * @throws Exception
     */
    protected function addAnAddress($kind, $address, $name = '')
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf(
                $this->lang('Invalid recipient kind: %s'),
                $kind
            );
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if (!static::validateAddress($address)) {
            $error_message = sprintf(
                '%s (%s): %s',
                $this->lang('invalid_address'),
                $kind,
                $address
            );
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ('Reply-To' !== $kind) {
            if (!array_key_exists(strtolower($address), $this->all_recipients)) {
                $this->{$kind}[] = [$address, $name];
                $this->all_recipients[strtolower($address)] = true;
                return true;
            }
        } else {
            if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
                $this->ReplyTo[strtolower($address)] = [$address, $name];
                return true;
            }
        }
        return false;
    }

    /**
     * Set the From and FromName properties.
     * @param string $address
     * @param string $name
     * @param boolean $auto Whether to also set the Sender address, defaults to true
     * @return boolean
     * @throws Exception
     */
    public function setFrom($address, $name = '', $auto = true)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        if (!static::validateAddress($address)) {
            $error_message = sprintf('%s (From): %s', $this->lang('invalid_address'), $address);
            $this->setError($error_message);
            $this->edebug($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $this->From = $address;
        $this->FromName = $name;
        if ($auto) {
            if (empty($this->Sender)) {
                $this->Sender = $address;
            }
        }
        return true;
    }

    /**
     * Return the Message-ID header of the last email.
     * @return string
     */
    public function getLastMessageID()
    {
        return $this->MessageID;
    }

    /**
     * Check that a string looks like an email address.
     * @param string $address The email address to check
     * @param string|callable $patternselect A selector for the validation pattern to use :
     * * `auto` Pick best pattern automatically;
     * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0, PHP >= 5.3.2, POSIX compliant response (see notes);
     * * `pcre` Use old PCRE implementation;
     * * `html5` Use the pattern given by the HTML5 spec for `<input type="email">` A RFC5322 compliant address will fail this test, but is sufficient for most needs.
     * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL; same as html5 but weaker.
     * * A callable which returns true/false.
     * @return boolean
     */
    public static function validateAddress($address, $patternselect = 'auto')
    {
        if (empty($address)) {
            return false;
        }
        if ('auto' === $patternselect) {
            //Check this constant first so it works when extension_loaded() is disabled by safe mode
            if (defined('PCRE_VERSION')) {
                //This pattern is downloaded from https://github.com/squiloople/email-validator-php
                if (version_compare(PCRE_VERSION, '8.0.3') >= 0) {
                    $patternselect = 'pcre8';
                } else {
                    $patternselect = 'pcre';
                }
            } else {
                //Fall back to best option for servers without PCRE
                $patternselect = 'php';
            }
        }
        switch ($patternselect) {
            case 'pcre8':
                /**
                 * A more complex and more permissive version of the RFC5322 regex on which FILTER_VALIDATE_EMAIL
                 * is based.
                 * This is the future default validator in PHPMailer.
                 * Can be enabled by setting PHPMailer::$validator = 'pcre8';
                 *
                 * @see http://squiloople.com/2009/12/20/email-address-validation/
                 * @copyright 2009-2010 Michael Rushton
                 * Feel free to use and redistribute this code. But please keep this copyright notice.
                 */
                return (bool)preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>([a-f0-9]{1,4})' .
                    '(?>:(?8)){5}:|(?!(?:.*[a-f0-9]:){6,})(?8)(?>:(?8)){0,4})?::(?8)(?>:(?8)){0,4}:)?(25[0-5]' .
                    '|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $address
                );
            case 'pcre':
                //An older and simplified version of the RFC5322 regex.
                //This is the default validator in previous versions of PHPMailer.
                return (bool)preg_match(
                    '/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\[ -~]|[^"])"?){65,}@)' .
                    '((?>(?=(?>(?>\x0D\x0A)?[\t ])+|(?>(?>[\t ]*\x0D\x0A)?[\t ]+))(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]))*(?2)\)))|(?2))' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?3))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?4)){0,126}|\[(?:(?>IPv6:(?>(?>([a-f0-9]{1,4})(?>:(?5)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?5)(?>:(?5)){0,6})?::(?5)?))|(?>(?>IPv6:(?>([a-f0-9]{1,4})' .
                    '(?>:(?6)){5}:|(?!(?:.*[a-f0-9]:){6,})(?6)(?>:(?6)){0,4})?::(?6)(?>:(?6)){0,4}:)?(25[0-5]' .
                    '|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\.(?7)){3}))\])(?1)$/isD',
                    $address
                );
            case 'html5':
                /**
                 * This is the pattern used by the HTML5 spec for validating email addresses.
                 * @see http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
                 */
                return (bool)preg_match(
                    '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                    $address
                );
            case 'php':
                return (bool)filter_var($address, FILTER_VALIDATE_EMAIL);
            default:
                //Fall back to a user-supplied callable pattern
                return call_user_func($patternselect, $address);
        }
    }

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     * @return boolean false on error - See the ErrorInfo property for details of the error.
     * @throws Exception
     */
    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $e) {
            $this->mailHeader = '';
            $this->setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Prepare a message for sending.
     * @return boolean
     * @throws Exception
     */
    public function preSend()
    {
        if ('smtp' === $this->Mailer or ('mail' === $this->Mailer and stripos(PHP_OS, 'WIN') === 0)) {
            //SMTP mandates RFC-compliant line endings
            static::setLE("\r\n");
        } else {
            //Server native line endings for mail and sendmail
            static::setLE(PHP_EOL);
        }

        //Validate address format
        if (('mail' === $this->Mailer or 'sendmail' === $this->Mailer) and
            '' !== $this->Sender and
            !self::validateAddress($this->Sender)
        ) {
            throw new Exception($this->lang('invalid_address') . ' (Sender): ' . $this->Sender);
        }

        if ('' === $this->From) {
            throw new Exception($this->lang('from_failed') . $this->From);
        }

        //Validate From address
        if (!self::validateAddress($this->From)) {
            $error_message = sprintf(
                '%s (From): %s',
                $this->lang('invalid_address'),
                $this->From
            );
            throw new Exception($error_message);
        }

        // Build recipient lists
        foreach (['to', 'cc', 'bcc'] as $kind) {
            foreach ($this->{$kind} as $addr) {
                if (!self::validateAddress($addr[0])) {
                    $error_message = sprintf(
                        '%s (%s): %s',
                        $this->lang('invalid_address'),
                        $kind,
                        $addr[0]
                    );
                    throw new Exception($error_message);
                }
            }
        }

        if (!$this->createHeader()) {
            return false;
        }

        if (!$this->createBody()) {
            return false;
        }

        return true;
    }

    /**
     * Actually send a message.
     * @return boolean
     */
    protected function postSend()
    {
        try {
            // Choose the mailer and send through it
            switch ($this->Mailer) {
                case 'sendmail':
                case 'qmail':
                    return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
                case 'smtp':
                    return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
                case 'mail':
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
                default:
                    $sendMethod = $this->Mailer . 'Send';
                    if (method_exists($this, $sendMethod)) {
                        return $this->{$sendMethod}($this->MIMEHeader, $this->MIMEBody);
                    }
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            $this->edebug($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
        }
        return false;
    }

    /**
     * Send mail using the sendmail MTA.
     * @param string $header The message headers
     * @param string $body The message body
     * @return boolean
     */
    protected function sendmailSend($header, $body)
    {
        // CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (!empty($this->Sender) and self::isShellSafe($this->Sender)) {
            if ($this->UseSendmailOptions) {
                $params = sprintf('-f%s', $this->Sender);
            }
        }
        if (!empty($this->Sender) and !empty($params) and ini_get('safe_mode')) {
            // The -f parameter is not allowed when in safe_mode, even though it would be safe.
            // It's disabled (and not detected) when the UseSendmailOptions property is false.
            $this->edebug($this->lang('safe_mode_disabled') . ' sendmail -f');
            $params = '';
        }

        if (empty($params)) {
            //No params to pass
            $sendmail = @popen($this->Sendmail, 'w');
        } else {
            $sendmail = @popen($this->Sendmail . ' ' . $params, 'w');
        }
        if (!$sendmail) {
            $this->setError($this->lang('execute') . $this->Sendmail);
            return false;
        }
        $this->edebug($this->lang('execute') . $this->Sendmail . $params);
        fputs($sendmail, $header);
        fputs($sendmail, $body);

        $result = pclose($sendmail);

        if (0 !== $result) {
            $this->setError($this->lang('execute') . $this->Sendmail);
            $this->edebug($this->lang('exit_status') . $result);
            return false;
        }
        return true;
    }

    /**
     * Send mail using the PHP mail() function.
     * @param string $header The message headers
     * @param string $body The message body
     * @return boolean
     * @see http://www.php.net/manual/en/function.mail.php
     */
    protected function mailSend($header, $body)
    {
        $toArr = [];
        foreach ($this->to as $toaddr) {
            $toArr[] = $this->addrFormat($toaddr);
        }
        $to = implode(', ', $toArr);

        $params = null;
        //This sets the SMTP envelope sender, which gets turned into a return-path header by the receiver
        if (!empty($this->Sender)) {
            if ($this->UseSendmailOptions and self::isShellSafe($this->Sender)) {
                $params = sprintf('-f%s', $this->Sender);
            }
        }
        if (!empty($this->Sender) and !empty($params) and ini_get('safe_mode')) {
            // The -f parameter is not allowed when in safe_mode, even though it would be safe.
            // It's disabled (and not detected) when the UseSendmailOptions property is false.
            $this->edebug($this->lang('safe_mode_disabled') . ' mail() -f');
            $params = '';
        }
        $this->edebug('mail() To: ' . $to . ', Subject: ' . $this->Subject . ', Headers: ' . $header);
        $this->mailHeader = $header;
        if (!$this->mailPassthru($to, $this->Subject, $body, $this->mailHeader, $params)) {
            $this->setError($this->lang('instantiate'));
            return false;
        }
        return true;
    }

    /**
     * Get the SMTP connection resource.
     * @return resource|null
     */
    public function getSMTPInstance()
    {
        return $this->smtp;
    }

    /**
     * Initiate a connection to an SMTP server.
     * @param array $options An array of options compatible with stream_context_create()
     * @return boolean
     * @throws Exception
     */
    public function smtpConnect($options = [])
    {
        if (null !== $this->smtp) {
            return true;
        }
        //If no explicit timeout is provided, use the default
        if ($this->Timeout === 0) {
            $this->Timeout = 300;
        }
        //If no explicit HELO is provided, use the default
        if (empty($this->Helo)) {
            $this->Helo = $this->serverHostname();
        }
        $this->last_smtp_response = null;
        $this->last_smtp_response_code = 0;
        $this->smtp = $this->getSMTPConnection();
        if (null === $this->smtp) {
            return false;
        }

        //Say hello
        $this->smtp->hello($this->Helo);
        //TLS?
        if ($this->SMTPSecure === 'tls') {
            $this->smtp->startTLS();
            //Say hello again after starting TLS
            $this->smtp->hello($this->Helo);
        }
        //Check connection status
        if (!$this->smtp->connected()) {
            throw new Exception($this->lang('connect_host'));
        }
        //SMTP Auth
        if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate(
                $this->Username,
                $this->Password,
                $this->AuthType
            )) {
                throw new Exception($this->lang('authenticate'));
            }
        }
        return true;
    }

    /**
     * Get an SMTP connection.
     * @return SMTP
     */
    protected function getSMTPConnection()
    {
        $smtp = new SMTP();
        $smtp->setDebugLevel($this->SMTPDebug);
        $smtp->setDebugOutput($this->DebugOutput);
        $smtp->setTimeout($this->Timeout);
        $hosts = explode(';', $this->Host);
        $host = '';
        $port = $this->Port;
        $secure = $this->SMTPSecure;
        foreach ($hosts as $hostinfo) {
            $hostinfo = trim($hostinfo);
            if (empty($hostinfo)) {
                continue;
            }
            $host = $hostinfo;
            $port = $this->Port;
            $secure = $this->SMTPSecure;
            if (preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*):([0-9]*)$/', $hostinfo, $match)) {
                $host = $match[3];
                $port = (int)$match[4];
                $secure = !empty($match[1]) ? substr($match[1], 0, -3) : '';
            }
            if ($smtp->connect($host, $port, $this->Timeout, $this->SMTPOptions)) {
                break;
            }
        }
        if (!$smtp->connected()) {
            throw new Exception($this->lang('connect_host'));
        }
        return $smtp;
    }

    /**
     * Close the SMTP connection.
     * @return void
     */
    public function smtpClose()
    {
        if (null !== $this->smtp) {
            if ($this->smtp->connected()) {
                $this->smtp->quit();
                $this->smtp->close();
            }
            $this->smtp = null;
        }
    }

    /**
     * Send mail using SMTP.
     * @param string $header The message headers
     * @param string $body The message body
     * @return boolean
     * @throws Exception
     */
    protected function smtpSend($header, $body)
    {
        $bad_rcpt = [];
        if (!$this->smtpConnect($this->SMTPOptions)) {
            throw new Exception($this->lang('smtp_connect_failed'));
        }
        //Sender already validated in preSend()
        $smtp_from = ('' === $this->Sender) ? $this->From : $this->Sender;
        if (!$this->smtp->mail($smtp_from)) {
            $this->setError($this->lang('from_failed') . $smtp_from . ' : ' .
                implode(',', $this->smtp->getError()));
            throw new Exception($this->ErrorInfo);
        }

        //SingleTo forces severing the connection after each recipient
        //and sending each recipient a separate message.
        if ($this->SingleTo) {
            foreach ($this->to as $rcpt) {
                $this->smtp->recipient($rcpt[0]);
            }
        } else {
            foreach (array_merge($this->to, $this->cc, $this->bcc) as $rcpt) {
                if (!$this->smtp->recipient($rcpt[0])) {
                    $bad_rcpt[] = $rcpt[0];
                }
            }
        }

        if (count($bad_rcpt) > 0) {
            $error_message = $this->lang('recipients_failed') . implode(', ', $bad_rcpt);
            $this->setError($error_message);
            throw new Exception($error_message);
        }

        if (!$this->smtp->data($header . $body)) {
            throw new Exception($this->lang('data_not_accepted'));
        }
        if ($this->SMTPKeepAlive) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        return true;
    }

    /**
     * Set the language for error messages.
     * @param string $langcode ISO 639-1 2-letter language code (e.g. 'en')
     * @param string $lang_path Path to the language file directory
     * @return boolean
     */
    public function setLanguage($langcode = 'en', $lang_path = '')
    {
        //Define full set of translatable strings in English
        $PHPMAILER_LANG = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address: ',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
            'extension_missing' => 'Extension missing: ',
        ];
        if (empty($lang_path)) {
            //Calculates the path to the language files, based on the location of the PHPMailer class
            $lang_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        }
        //Make sure language code is lowercase
        $langcode = strtolower($langcode);
        $foundlang = true;
        $lang_file = $lang_path . 'phpmailer.lang-' . $langcode . '.php';
        if ('en' !== $langcode) { //English is built-in, so no need to load a file for it
            //Make sure language file exists and is readable
            if (!is_readable($lang_file)) {
                $foundlang = false;
            } else {
                //Overwrite English strings with the loaded language file
                $foundlang = include $lang_file;
            }
        }
        $this->language = $PHPMAILER_LANG;
        return (bool)$foundlang; //Returns false if language file is not found
    }

    /**
     * Get the array of strings for the current language.
     * @return array
     */
    public function getTranslations()
    {
        return $this->language;
    }

    /**
     * Create recipient headers.
     * @return string
     */
    public function addrAppend($type, $addr)
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = $this->addrFormat($address);
        }
        if (empty($addresses)) {
            return '';
        }
        return $type . ': ' . implode(', ', $addresses) . $this->LE;
    }

    /**
     * Format an address for use in a message header.
     * @param array $addr A 2-element array containing an address and a name
     * @return string
     */
    public function addrFormat($addr)
    {
        if (empty($addr[1])) { //No name set
            return $this->secureHeader($addr[0]);
        }
        return $this->encodeHeader($addr[1], 'phrase') . ' <' . $this->secureHeader($addr[0]) . '>';
    }

    /**
     * Word-wrap message.
     * For use with mailers that do not automatically perform wrapping
     * and for quoted-printable encoded messages.
     * Original written by Richard Heyes.
     * @param string $message The message to wrap
     * @param integer $length The line length to wrap to
     * @param boolean $qp_mode Whether to run in Quoted-Printable mode
     * @return string
     */
    public function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', $this->LE);
        } else {
            $soft_break = $this->LE;
        }
        //If utf-8 encoding is used, we will need to make sure there are no
        //overlong sequences in sources (e.g. Devel-CheckOS-1.33)
        $is_utf8 = (strtolower($this->CharSet) === 'utf-8');
        $lelen = strlen($soft_break);
        $c = 0;
        $output = '';
        $line = '';

        for ($i = 0; $i < strlen($message); ++$i) {
            $char = $message[$i];
            if ($is_utf8) {
                $c += (ord($char) & 0xC0) === 0x80 ? 0 : 1;
            } else {
                ++$c;
            }
            if ("\n" === $char) { //Newline
                $output .= $line . $this->LE;
                $line = '';
                $c = 0;
                continue;
            }
            if ($c > $length) { //Got a long line
                $output .= $line . $soft_break;
                $line = '';
                $c = 0;
            }
            $line .= $char;
        }
        if ('' !== $line) {
            $output .= $line;
        }
        return $output;
    }

    /**
     * Set the line ending format.
     * @param string $le
     * @return void
     */
    public static function setLE($le)
    {
        static::$LE = $le;
    }

    /**
     * Get the line ending format.
     * @return string
     */
    protected static function getLE()
    {
        return static::$LE;
    }

    /**
     * Set the message body.
     * @param string $body
     * @param string $basedir
     * @param boolean $advanced Whether to use the advanced HTML to text converter
     * @return string
     */
    public function msgHTML($body, $basedir = '', $advanced = false)
    {
        preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $body, $images);
        if (isset($images[2])) {
            foreach ($images[2] as $imgindex => $url) {
                //Convert all relative URLs to absolute URLs
                if (preg_match('#^data:image/#', $url)) {
                    //Data URI images can't be from remote sites, so we don't need to resolve them
                    //and we don't want to mess with them
                    continue;
                }
                if (
                    //Only process relative URLs if a basedir is provided
                    !empty($basedir) and
                    //An absolute URL would start with http://, https://, // or have a protocol-relative URL
                    strpos($url, '//') !== 0 and
                    //Don't process absolute URLs
                    strpos($url, 'http') !== 0 and
                    //Don't process relative URLs that have been resolved before
                    strpos($url, 'cid:') !== 0
                ) {
                    $body = preg_replace(
                        '/' . $images[0][$imgindex] . '/s',
                        $images[1][$imgindex] . '="' . $basedir . $url . '"',
                        $body
                    );
                }
            }
        }
        $this->isHTML(true);
        $this->Body = $body;
        if (empty($this->AltBody)) {
            $this->AltBody = 'To view this email message, open it in a program that supports HTML!' .
                $this->LE . $this->LE;
        }
        if ($advanced) {
            $this->AltBody = $this->html2text($body, $advanced);
        }
        return $this->Body;
    }

    /**
     * Convert an HTML string into plain text.
     * @param string $html The HTML to convert
     * @param boolean $advanced Whether to use the advanced HTML to text converter
     * @return string
     */
    public function html2text($html, $advanced = false)
    {
        if (!$advanced) {
            return html_entity_decode(
                trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $html))),
                ENT_QUOTES,
                $this->CharSet
            );
        }
        //Create instance of HTML to text converter
        $converter = new Html2Text($html);
        return $converter->getText();
    }

    /**
     * Get the attachments array.
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachment;
    }

    /**
     * Add an attachment from a path on the filesystem.
     * @param string $path Path to the attachment.
     * @param string $name Overrides the attachment name.
     * @param string $encoding File encoding (see $Encoding).
     * @param string $type File extension (MIME type).
     * @param string $disposition Disposition to use
     * @return boolean
     * @throws Exception
     */
    public function addAttachment($path, $name = '', $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'attachment')
    {
        if (!@is_file($path)) {
            throw new Exception($this->lang('file_access') . $path, self::STOP_CONTINUE);
        }
        //If a MIME type is not specified, try to work it out from the file name
        if ('' === $type) {
            $type = static::filenameToType($path);
        }
        $filename = basename($path);
        if ('' === $name) {
            $name = $filename;
        }

        $this->attachment[] = [
            0 => $path,
            1 => $filename,
            2 => $name,
            3 => $encoding,
            4 => $type,
            5 => false, // isStringAttachment
            6 => $disposition,
            7 => 0,
        ];
        return true;
    }

    /**
     * Return the current line ending format.
     * @return string
     */
}