<?php

/*interface Questionnaire_Data_Provider
{
    public function getIdentifier();
    public function getData();
}*/

class Questionnaire_PHP_Data_Provider
{
    function getIdentifier()
    {
        return 'PHP';
    }

    /**
     * Get data about the PHP runtime setup.
     *
     * @return  array
     */
    function getData()
    {
        return array(
            'version'                       => PHP_VERSION,
            'sapi'                          => PHP_SAPI,
            'int_size'                      => defined('PHP_INT_SIZE') ? PHP_INT_SIZE : '',
            'safe_mode'                     => (int)ini_get('safe_mode'),
            'open_basedir'                  => (int)ini_get('open_basedir'),
            'memory_limit'                  => ini_get('memory_limit'),
            'allow_url_fopen'               => (int)ini_get('allow_url_fopen'),
            'allow_url_include'             => (int)ini_get('allow_url_include'),
            'file_uploads'                  => (int)ini_get('file_uploads'),
            'upload_max_filesize'           => ini_get('upload_max_filesize'),
            'post_max_size'                 => ini_get('post_max_size'),
            'disable_functions'             => ini_get('disable_functions'),
            'disable_classes'               => ini_get('disable_classes'),
            'enable_dl'                     => (int)ini_get('enable_dl'),
            'magic_quotes_gpc'              => (int)ini_get('magic_quotes_gpc'),
            'register_globals'              => (int)ini_get('register_globals'),
            'filter.default'                => ini_get('filter.default'),
            'zend.ze1_compatibility_mode'   => (int)ini_get('zend.ze1_compatibility_mode'),
            'unicode.semantics'             => (int)ini_get('unicode.semantics'),
            'zend_thread_safty'             => (int)function_exists('zend_thread_id'),
            'extensions'                    => get_loaded_extensions()
        );
    }
}


class Questionnaire_System_Data_Provider
{
    function getIdentifier()
    {
        return 'System';
    }

    /**
     * Get data about the general system information, like OS or IP (shortened).
     *
     * @return  array
     */
    function getData()
    {
        // Start discovering the IPV4 server address, if available
        $serverAddress = '0.0.0.0';
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverAddress = $_SERVER['SERVER_ADDR'];
        }
        // Running on IIS?
        if (isset($_SERVER['LOCAL_ADDR'])) {
            $serverAddress = $_SERVER['LOCAL_ADDR'];
        }
        $aIPAddress = explode('.', $serverAddress);

        return array(
            'os'    => PHP_OS,
            'httpd' => $_SERVER['SERVER_SOFTWARE'],
            // we don't want the real IP address (for privacy policy reasons) but only
            // a network address to see whether your installation is running on a private or public network.
            // IANA reserved addresses for private networks (RFC 1918) are:
            // - 10.0.0.0/8
            // - 172.16.0.0/12
            // - 192.168.0.0/16
            'ip'    => $aIPAddress[0].'.'.$aIPAddress[1].'.XXX.YYY'
        );
    }
}

/**
 * This class collects data which is used to create some usage statistics.
 *
 * The collected data is - after authorization of the administrator - submitted
 * to a central server. For privacy reasons we try to collect only data which aren't private
 * or don't give any information which might help to identify the user.
 *
 * @author      Johannes Schlueter <johannes@php.net>
 * @copyright   (c) 2007-2008 Johannes Schlueter 
 */

class Questionnaire_Data_Collector
{
    var $providers;
    var $data = null;

    /**
     * Constructor.
     *
     * @param   array
     * @param   string
     */
    function Questionnaire_Data_Collector()
    {
        $this->providers = array();
    }

    function addDataProvider(&$provider)
    {
        $this->providers[] = &$provider;
    }

    /**
     * Get data as an array.
     *
     * @return  array All Data
     */
    function getDataRaw()
    {
        if (!$this->data) {
            $this->collect();
        }

        return $this->data;
    }

    function getDataForForm()
    {
        return base64_encode(serialize($this->getDataRaw()));
    }

    /**
     * Collect info into the data property.
     *
     * @return  void
     */
    function collect()
    {
        foreach (array_keys($this->providers) as $key) {
			$provider = &$this->providers[$key];
            $this->data[$provider->getIdentifier()] = $provider->getData();
        } 
    }
}
