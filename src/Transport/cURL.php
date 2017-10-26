<?php
namespace PhpRush\Requests\Transport;

use PhpRush\Requests\Transport;
use PhpRush\Requests\Http;
use PhpRush\Requests\Exceptions\TimeoutException;
use PhpRush\Requests\Exceptions\cURLException;

class cURL implements Transport
{

    const CURL_7_10_5 = 0x070A05;

    const CURL_7_16_2 = 0x071002;

    private $version = null;

    private $handle = null;

    private $info = null;

    private $headers = null;

    private $response = null;

    public static $errno = array(
        1 => "CURLE_UNSUPPORTED_PROTOCOL",
        2 => "CURLE_FAILED_INIT",
        3 => "CURLE_URL_MALFORMAT",
        4 => "CURLE_URL_MALFORMAT_USER",
        5 => "CURLE_COULDNT_RESOLVE_PROXY",
        6 => "CURLE_COULDNT_RESOLVE_HOST",
        7 => "CURLE_COULDNT_CONNECT",
        8 => "CURLE_FTP_WEIRD_SERVER_REPLY",
        9 => "CURLE_FTP_ACCESS_DENIED",
        10 => "CURLE_FTP_USER_PASSWORD_INCORRECT",
        11 => "CURLE_FTP_WEIRD_PASS_REPLY",
        12 => "CURLE_FTP_WEIRD_USER_REPLY",
        13 => "CURLE_FTP_WEIRD_PASV_REPLY",
        14 => "CURLE_FTP_WEIRD_227_FORMAT",
        15 => "CURLE_FTP_CANT_GET_HOST",
        16 => "CURLE_FTP_CANT_RECONNECT",
        17 => "CURLE_FTP_COULDNT_SET_BINARY",
        18 => "CURLE_FTP_PARTIAL_FILE or CURLE_PARTIAL_FILE",
        19 => "CURLE_FTP_COULDNT_RETR_FILE",
        20 => "CURLE_FTP_WRITE_ERROR",
        21 => "CURLE_FTP_QUOTE_ERROR",
        22 => "CURLE_HTTP_NOT_FOUND or CURLE_HTTP_RETURNED_ERROR",
        23 => "CURLE_WRITE_ERROR",
        24 => "CURLE_MALFORMAT_USER",
        25 => "CURLE_FTP_COULDNT_STOR_FILE",
        26 => "CURLE_READ_ERROR",
        27 => "CURLE_OUT_OF_MEMORY",
        28 => "CURLE_OPERATION_TIMEDOUT or CURLE_OPERATION_TIMEOUTED",
        29 => "CURLE_FTP_COULDNT_SET_ASCII",
        30 => "CURLE_FTP_PORT_FAILED",
        31 => "CURLE_FTP_COULDNT_USE_REST",
        32 => "CURLE_FTP_COULDNT_GET_SIZE",
        33 => "CURLE_HTTP_RANGE_ERROR",
        34 => "CURLE_HTTP_POST_ERROR",
        35 => "CURLE_SSL_CONNECT_ERROR",
        36 => "CURLE_BAD_DOWNLOAD_RESUME or CURLE_FTP_BAD_DOWNLOAD_RESUME",
        37 => "CURLE_FILE_COULDNT_READ_FILE",
        38 => "CURLE_LDAP_CANNOT_BIND",
        39 => "CURLE_LDAP_SEARCH_FAILED",
        40 => "CURLE_LIBRARY_NOT_FOUND",
        41 => "CURLE_FUNCTION_NOT_FOUND",
        42 => "CURLE_ABORTED_BY_CALLBACK",
        43 => "CURLE_BAD_FUNCTION_ARGUMENT",
        44 => "CURLE_BAD_CALLING_ORDER",
        45 => "CURLE_HTTP_PORT_FAILED",
        46 => "CURLE_BAD_PASSWORD_ENTERED",
        47 => "CURLE_TOO_MANY_REDIRECTS",
        48 => "CURLE_UNKNOWN_TELNET_OPTION",
        49 => "CURLE_TELNET_OPTION_SYNTAX",
        50 => "CURLE_OBSOLETE",
        51 => "CURLE_SSL_PEER_CERTIFICATE",
        52 => "CURLE_GOT_NOTHING",
        53 => "CURLE_SSL_ENGINE_NOTFOUND",
        54 => "CURLE_SSL_ENGINE_SETFAILED",
        55 => "CURLE_SEND_ERROR",
        56 => "CURLE_RECV_ERROR",
        57 => "CURLE_SHARE_IN_USE",
        58 => "CURLE_SSL_CERTPROBLEM",
        59 => "CURLE_SSL_CIPHER",
        60 => "CURLE_SSL_CACERT",
        61 => "CURLE_BAD_CONTENT_ENCODING",
        62 => "CURLE_LDAP_INVALID_URL",
        63 => "CURLE_FILESIZE_EXCEEDED",
        64 => "CURLE_FTP_SSL_FAILED",
        79 => "CURLE_SSH"
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $curl = curl_version();
        $this->version = $curl['version_number'];
        $this->handle = curl_init();
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        if ($this->version >= self::CURL_7_10_5) {
            curl_setopt($this->handle, CURLOPT_ENCODING, '');
        }
        if (defined('CURLOPT_PROTOCOLS')) {
            curl_setopt($this->handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }
        if (defined('CURLOPT_REDIR_PROTOCOLS')) {
            curl_setopt($this->handle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            curl_close($this->handle);
        }
    }

    public function request($url, $headers = array(), $data = array(), $options = array())
    {
        $this->setupHandle($url, $headers, $data, $options);
        
        $this->response = curl_exec($this->handle);
        
        if (curl_errno($this->handle) === 23 || curl_errno($this->handle) === 61) {
            curl_setopt($this->handle, CURLOPT_ENCODING, 'none');
            $this->response = curl_exec($this->handle);
        }
        
        if (curl_errno($this->handle)) {
            $errno = curl_errno($this->handle);
            $error = sprintf('cURL error %s: %s', $errno, curl_error($this->handle));
            
            if ($errno == 28) {
                throw new TimeoutException($error, $errno);
            } else {
                throw new cURLException($error, $errno);
            }
        }
        $this->info = curl_getinfo($this->handle);
        
        curl_setopt($this->handle, CURLOPT_HEADERFUNCTION, null);
        curl_setopt($this->handle, CURLOPT_WRITEFUNCTION, null);
    }

    public function getInfo($key = NULL)
    {
        if (! is_null($key)) {
            return isset($this->info[$key]) ? $this->info[$key] : null;
        }
        return $this->info;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getHeaders()
    {
        if (is_null($this->headers)) {
            $header = substr($response, 0, $this->info['header_size']);
            $this->headers = $headers;
        }
        
        return $this->headers;
    }

    private function setupHandle($url, $headers, $data, $options)
    {
        // Force closing the connection for old versions of cURL (<7.22).
        if (! isset($headers['Connection'])) {
            $headers['Connection'] = 'close';
        }
        
        if (! empty($data) && ! is_string($data)) {
            $data = http_build_query($data, null, '&');
        }
        
        switch ($options['method']) {
            case Http::METHOD_POST:
                curl_setopt($this->handle, CURLOPT_POST, true);
                curl_setopt($this->handle, CURLOPT_POSTFIELDSRequests, $data);
                break;
            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $options['method']);
                if (! empty($data)) {
                    curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                }
        }
        
        $timeout = max($options['timeout'], 1);
        if (is_int($timeout) || $this->version < self::CURL_7_16_2) {
            curl_setopt($this->handle, CURLOPT_TIMEOUT, ceil($timeout));
        } else {
            curl_setopt($this->handle, CURLOPT_TIMEOUT_MS, round($timeout * 1000));
        }
        
        if (is_int($options['connect_timeout']) || $this->version < self::CURL_7_16_2) {
            curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, ceil($options['connect_timeout']));
        } else {
            curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, round($options['connect_timeout'] * 1000));
        }
        
        curl_setopt($this->handle, CURLOPT_URL, $url);
        
        if (! empty($options['referer'])) {
            curl_setopt($this->handle, CURLOPT_REFERER, $options['referer']);
        }
        
        if (! empty($options['useragent'])) {
            curl_setopt($this->handle, CURLOPT_USERAGENT, $options['useragent']);
        }
        
        if (! empty($headers)) {
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        }
        
        if ($options['protocol_version'] === 1.1) {
            curl_setopt($this->handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        } else {
            curl_setopt($this->handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        }
        
        if (isset($options['verify'])) {
            if ($options['verify'] === false) {
                curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, 0);
            } elseif (is_string($options['verify'])) {
                curl_setopt($this->handle, CURLOPT_CAINFO, $options['verify']);
            }
        }
        
        if (isset($options['verifyname']) && $options['verifyname'] === false) {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, 0);
        }
    }
}