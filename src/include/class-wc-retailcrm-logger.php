<?php
/**
 * RetailCRM Integration.
 *
 * @package  WC_Retailcrm_Logger
 * @category Integration
 * @author   RetailCRM
 */

if (!class_exists('WC_Retailcrm_Logger')) {

    /**
     * Class WC_Retailcrm_Logger. Works like static wrapper for WC_Logger because we lacks DI here.
     *
     * @method static bool add( $handle, $message, $level = WC_Log_Levels::NOTICE )
     * @method static void log( $level, $message, $context = array() )
     * @method static void emergency( $message, $context = array() )
     * @method static void alert( $message, $context = array() )
     * @method static void critical( $message, $context = array() )
     * @method static void error( $message, $context = array() )
     * @method static void warning( $message, $context = array() )
     * @method static void notice( $message, $context = array() )
     * @method static void info( $message, $context = array() )
     * @method static void debug( $message, $context = array() )
     * @method static bool clear( $source = '' )
     * @method static bool clear_expired_logs()
     */
    class WC_Retailcrm_Logger
    {
        /** @var \WC_Logger */
        private static $logger;

        /**
         * @param string $name
         * @param array  $arguments
         *
         * @return mixed
         */
        public static function __callStatic($name, $arguments)
        {
            if (is_null(self::$logger)) {
                self::$logger = new WC_Logger();
            }

            if (!method_exists(self::$logger, $name)) {
                throw new \BadMethodCallException(sprintf('Method \'%s\' doesn\'t exist', $name));
            }

            return call_user_func_array(array(self::$logger, $name), $arguments);
        }
    }
}
