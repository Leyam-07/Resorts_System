<?php
/**
 * PHPMailer Exception class.
 * PHP Version 5.5.
 * @package PHPMailer
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer exception handler.
 * @package PHPMailer
 */
class Exception extends \Exception
{
    /**
     * Prettify error message output.
     * @return string
     */
    public function errorMessage()
    {
        return '<strong>' . htmlspecialchars($this->getMessage(), ENT_COMPAT | ENT_HTML401) . "</strong><br />\n";
    }
}