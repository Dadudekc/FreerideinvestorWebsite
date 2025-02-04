<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Error
 * Custom exception class for SmartStock Pro.
 */
class SSP_Error extends Exception {
    /**
     * @var int Error code.
     */
    protected $error_code;

    /**
     * Constructor to initialize the error.
     *
     * @param string $message     Error message.
     * @param int    $error_code  Custom error code.
     */
    public function __construct(string $message, int $error_code = 0) {
        parent::__construct($message);
        $this->error_code = $error_code;
    }

    /**
     * Get the custom error code.
     *
     * @return int Error code.
     */
    public function get_error_code(): int {
        return $this->error_code;
    }
}
?>
