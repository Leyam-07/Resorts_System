<?php
/**
 * PHPMailer RFC821 SMTP email transport class.
 * PHP Version 5.5.
 * @package PHPMailer
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer RFC821 SMTP email transport class.
 * Implements RFC 821 SMTP commands and provides some utility methods for sending mail to an SMTP server.
 * @package PHPMailer
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class SMTP
{
    /**
     * The PHPMailer SMTP version number.
     * @var string
     */
    const VERSION = '6.5.0';

    /**
     * SMTP line break constant.
     * @var string
     */
    const LE = "\r\n";

    /**
     * The SMTP port to use if one is not specified.
     * @var integer
     */
    const DEFAULT_PORT = 25;

    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1
     * @var integer
     */
    const MAX_LINE_LENGTH = 998;

    /**
     * Debug level for no output
     */
    const DEBUG_OFF = 0;

    /**
     * Debug level to show client -> server messages
     */
    const DEBUG_CLIENT = 1;

    /**
     * Debug level to show client -> server and server -> client messages
     */
    const DEBUG_SERVER = 2;

    /**
     * Debug level to show connection status, client -> server and server -> client messages
     */
    const DEBUG_CONNECTION = 3;

    /**
     * Debug level to show all messages
     */
    const DEBUG_LOWLEVEL = 4;

    /**
     * The socket for the server connection.
     * @var resource
     */
    protected $smtp_conn;

    /**
     * Error information, if any, for the last SMTP command.
     * @var array
     */
    protected $error = [
        'error' => '',
        'detail' => '',
        'smtp_code' => '',
        'smtp_code_ex' => '',
    ];

    /**
     * The reply codes received from the server.
     * @var array
     */
    protected $helo_rply = [];

    /**
     * The most recent reply received from the server.
     * @var string
     */
    protected $last_reply = '';

    /**
     * Debug output level.
     * @var integer
     */
    protected $Debugoutput = self::DEBUG_OFF;

    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is
     * * `html` Output escaped, line-breaked HTML
     * * `error_log` Output to error log
     * * A callable function or method
     * @var string|callable
     */
    protected $Debugoutput_type = 'echo';

    /**
     * The timeout value for connection, in seconds.
     * @var integer
     */
    protected $Timeout = 300;

    /**
     * The timeout value for connection, in seconds.
     * @var integer
     */
    protected $Timelimit = 300;

    /**
     * How to handle debug output.
     * @var string|callable
     */
    /**
     * The name of the server host to connect to.
     * @var string
     */
    public $Host = 'localhost';

    /**
     * The port to connect to.
     * @var integer
     */
    public $Port = 25;

    /**
     * The encryption type to use.
     * @var string
     */
    public $SMTPSecure = '';

    /**
     * Options array for stream_socket_client.
     * @var array
     */
    public $SMTPOptions = [];

    /**
     * Get the last error that occurred.
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the debug output level.
     * @param integer $level
     */
    public function setDebugLevel($level)
    {
        $this->Debugoutput = $level;
    }

    /**
     * Set the debug output handler.
     * @param mixed $method
     */
    public function setDebugOutput($method)
    {
        $this->Debugoutput_type = $method;
    }

    /**
     * Set the connection timeout.
     * @param integer $timeout
     */
    public function setTimeout($timeout)
    {
        $this->Timeout = $timeout;
    }

    /**
     * Connect to an SMTP server.
     * @param string $host
     * @param integer $port
     * @param integer $timeout
     * @param array $options
     * @return boolean
     */
    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        static::setLE("\r\n");
        //Clear errors
        $this->error = [
            'error' => '',
            'detail' => '',
            'smtp_code' => '',
            'smtp_code_ex' => '',
        ];
        if ($this->connected()) {
            $this->setError('Already connected to a server');
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }
        $this->Host = $host;
        $this->Port = $port;
        $this->Timeout = $timeout;
        $this->SMTPOptions = $options;
        $this->smtp_conn = $this->getSMTPConnection();
        if ($this->smtp_conn) {
            $this->last_reply = $this->get_lines();
            $this->edebug('SERVER -> CLIENT: ' . $this->last_reply, self::DEBUG_CONNECTION);
            if (substr($this->last_reply, 0, 3) === '220') {
                return true;
            }
        }
        $this->setError('Failed to connect to server');
        return false;
    }

    /**
     * Get an SMTP connection.
     * @return resource
     */
    protected function getSMTPConnection()
    {
        $errno = 0;
        $errstr = '';
        $socket_context = stream_context_create($this->SMTPOptions);
        $this->smtp_conn = @stream_socket_client(
            $this->Host . ':' . $this->Port,
            $errno,
            $errstr,
            $this->Timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );
        if (!$this->smtp_conn) {
            $this->setError(
                'Connection failed',
                '',
                $errno,
                $errstr
            );
            return null;
        }
        return $this->smtp_conn;
    }

    /**
     * Check if connected to an SMTP server.
     * @return boolean
     */
    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->edebug(
                    'SMTP NOTICE: EOF caught while checking if connected',
                    self::DEBUG_CLIENT
                );
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Close the connection to the SMTP server.
     * @return void
     */
    public function close()
    {
        $this->setError('Connection is closed');
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }

    /**
     * Send the HELO command to the SMTP server.
     * @param string $host
     * @return boolean
     */
    public function hello($host = '')
    {
        if ($this->send('HELO ' . $host, 250)) {
            return true;
        }
        return false;
    }

    /**
     * Send the MAIL FROM command to the SMTP server.
     * @param string $from
     * @return boolean
     */
    public function mail($from)
    {
        return $this->send('MAIL FROM:<' . $from . '>', 250);
    }

    /**
     * Send the RCPT TO command to the SMTP server.
     * @param string $to
     * @return boolean
     */
    public function recipient($to)
    {
        return $this->send('RCPT TO:<' . $to . '>', [250, 251]);
    }

    /**
     * Send the DATA command to the SMTP server.
     * @param string $data
     * @return boolean
     */
    public function data($data)
    {
        return $this->send('DATA', 354) && $this->send($data . static::LE . '.', 250);
    }

    /**
     * Send the RSET command to the SMTP server.
     * @return boolean
     */
    public function reset()
    {
        return $this->send('RSET', 250);
    }

    /**
     * Send the QUIT command to the SMTP server.
     * @return boolean
     */
    public function quit()
    {
        return $this->send('QUIT', 221);
    }

    /**
     * Send the NOOP command to the SMTP server.
     * @return boolean
     */
    public function noop()
    {
        return $this->send('NOOP', 250);
    }

    /**
     * Send the VRFY command to the SMTP server.
     * @param string $name
     * @return boolean
     */
    public function verify($name)
    {
        return $this->send('VRFY ' . $name, [250, 251, 252]);
    }

    /**
     * Send the EXPN command to the SMTP server.
     * @param string $name
     * @return boolean
     */
    public function expand($name)
    {
        return $this->send('EXPN ' . $name, [250, 252]);
    }

    /**
     * Send the HELP command to the SMTP server.
     * @param string $keyword
     * @return boolean
     */
    public function help($keyword = '')
    {
        return $this->send('HELP ' . $keyword, [211, 214]);
    }

    /**
     * Send the AUTH command to the SMTP server.
     * @param string $username
     * @param string $password
     * @param string $authtype
     * @return boolean
     */
    public function authenticate($username, $password, $authtype = 'LOGIN')
    {
        if (!$this->server_caps || !array_key_exists('AUTH', $this->server_caps)) {
            $this->setError('Authentication is not supported');
            return false;
        }
        if (!in_array($authtype, $this->server_caps['AUTH'])) {
            $this->setError('Authentication method ' . $authtype . ' is not supported');
            return false;
        }
        switch ($authtype) {
            case 'PLAIN':
                if (!$this->send('AUTH PLAIN', 334)) {
                    return false;
                }
                if (!$this->send(base64_encode("\0" . $username . "\0" . $password), 235)) {
                    return false;
                }
                break;
            case 'LOGIN':
                if (!$this->send('AUTH LOGIN', 334)) {
                    return false;
                }
                if (!$this->send(base64_encode($username), 334)) {
                    return false;
                }
                if (!$this->send(base64_encode($password), 235)) {
                    return false;
                }
                break;
            case 'CRAM-MD5':
                if (!$this->send('AUTH CRAM-MD5', 334)) {
                    return false;
                }
                $challenge = base64_decode(substr($this->last_reply, 4));
                $response = $username . ' ' . hash_hmac('md5', $challenge, $password);
                return $this->send(base64_encode($response), 235);
        }
        return true;
    }

    /**
     * Send the STARTTLS command to the SMTP server.
     * @return boolean
     */
    public function startTLS()
    {
        if (!$this->send('STARTTLS', 220)) {
            return false;
        }
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
        return stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            $crypto_method
        );
    }

    /**
     * Send a command to the SMTP server.
     * @param string $command
     * @param integer|array $expect
     * @return boolean
     */
    protected function send($command, $expect = null)
    {
        if (!$this->connected()) {
            $this->setError('Not connected!');
            return false;
        }
        $this->client_send($command . static::LE);
        $this->last_reply = $this->get_lines();
        $this->edebug('SERVER -> CLIENT: ' . $this->last_reply, self::DEBUG_SERVER);
        if (null !== $expect) {
            if (!is_array($expect)) {
                $expect = [$expect];
            }
            if (!in_array(substr($this->last_reply, 0, 3), $expect)) {
                $this->setError(
                    $command . ' command failed',
                    $this->last_reply,
                    substr($this->last_reply, 0, 3),
                    substr($this->last_reply, 4)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Send data to the SMTP server.
     * @param string $data
     * @return integer
     */
    protected function client_send($data)
    {
        $this->edebug('CLIENT -> SERVER: ' . $data, self::DEBUG_CLIENT);
        return fwrite($this->smtp_conn, $data);
    }

    /**
     * Get a line from the SMTP server.
     * @return string
     */
    protected function get_lines()
    {
        if (!is_resource($this->smtp_conn)) {
            return '';
        }
        $data = '';
        $endtime = 0;
        stream_set_timeout($this->smtp_conn, $this->Timeout);
        if ($this->Timelimit > 0) {
            $endtime = time() + $this->Timelimit;
        }
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = @fgets($this->smtp_conn, 515);
            $this->edebug('SMTP INBOUND: "' . trim($str) . '"', self::DEBUG_LOWLEVEL);
            $data .= $str;
            if (substr($str, 3, 1) == ' ') {
                break;
            }
            $info = stream_get_meta_data($this->smtp_conn);
            if ($info['timed_out']) {
                $this->edebug(
                    'SMTP NOTICE: Tval timeout',
                    self::DEBUG_CLIENT
                );
                break;
            }
            if ($endtime and time() > $endtime) {
                $this->edebug(
                    'SMTP NOTICE: Timelimit reached',
                    self::DEBUG_CLIENT
                );
                break;
            }
        }
        return $data;
    }

    /**
     * Set an error message.
     * @param string $message
     * @param string $detail
     * @param string $smtp_code
     * @param string $smtp_code_ex
     */
    protected function setError($message, $detail = '', $smtp_code = '', $smtp_code_ex = '')
    {
        $this->error = [
            'error' => $message,
            'detail' => $detail,
            'smtp_code' => $smtp_code,
            'smtp_code_ex' => $smtp_code_ex,
        ];
    }

    /**
     * Set the line ending constant.
     * @param string $le
     */
    protected static function setLE($le)
    {
        static::$LE = $le;
    }

    /**
     * Output debugging info.
     * @param string $str
     * @param integer $level
     */
    protected function edebug($str, $level = self::DEBUG_OFF)
    {
        if ($level > $this->Debugoutput) {
            return;
        }
        if ($this->Debugoutput_type === 'error_log') {
            error_log($str);
            return;
        }
        if ($this->Debugoutput_type === 'html') {
            echo htmlentities(
                preg_replace('/[\r\n]+/', '', $str),
                ENT_QUOTES,
                'UTF-8'
            ) . "<br>\n";
            return;
        }
        //echo or callable
        if (is_callable($this->Debugoutput_type)) {
            call_user_func($this->Debugoutput_type, $str, $level);
            return;
        }
        echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
            "\n",
            "\n" . str_repeat("\t", 2),
            trim($str)
        ) . "\n";
    }
}