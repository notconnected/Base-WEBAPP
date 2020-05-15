<?php
namespace Core;

class Router
{
    protected $properties = null;
    
    protected $specialHeaders = array(
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    );
    
    public function __construct()
    {
        if (!$this->getProperties()) {
                        $env = array();

            //The HTTP request method
            $env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

            //The IP
            $env['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

            // Server params
            $scriptName = $_SERVER['SCRIPT_NAME']; // <-- "/foo/index.php"
            $requestUri = $_SERVER['REQUEST_URI']; // <-- "/foo/bar?test=abc" or "/foo/index.php/bar?test=abc"
            $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''; // <-- "test=abc" or ""

            // Physical path
            if (strpos($requestUri, $scriptName) !== false) {
                $physicalPath = $scriptName; // <-- Without rewriting
            } else {
                $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
            }
            $env['SCRIPT_NAME'] = rtrim($physicalPath, '/'); // <-- Remove trailing slashes

            // Virtual path
            $env['PATH_INFO'] = $requestUri;
            if (substr($requestUri, 0, strlen($physicalPath)) == $physicalPath) {
                $env['PATH_INFO'] = substr($requestUri, strlen($physicalPath)); // <-- Remove physical path
            }
            $env['PATH_INFO'] = str_replace('?' . $queryString, '', $env['PATH_INFO']); // <-- Remove query string
            $env['PATH_INFO'] = '/' . ltrim($env['PATH_INFO'], '/'); // <-- Ensure leading slash

            // Query string (without leading "?")
            $env['QUERY_STRING'] = $queryString;
            
            //POST DATA
            $env['POST_DATA'] = $_REQUEST;

            //Name of server host that is running the script
            $env['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

            //Number of server port that is running the script
            $env['SERVER_PORT'] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;

            //HTTP request headers (retains HTTP_ prefix to match $_SERVER)
            $headers = $this->extract($_SERVER);
            foreach ($headers as $key => $value) {
                $env[$key] = $value;
            }

            //Is the application running under HTTPS or HTTP protocol?
            $env['url_scheme'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

            //Input stream (readable one time only; not available for multipart/form-data requests)
            $rawInput = @file_get_contents('php://input');
            if (!$rawInput) {
                $rawInput = '';
            }
            $env['input'] = $rawInput;

            //Error stream
            $env['errors'] = @fopen('php://stderr', 'w');
            
            $this->properties = $env;
        }
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    private function extract($data)
    {
        $results = array();
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, $this->specialHeaders)) {
                if ($key === 'HTTP_CONTENT_LENGTH') {
                    continue;
                }
                $results[$key] = $value;
            }
        }

        return $results;
    }
    
    public function getControllerName()
    {
        $args = explode('/', $this->properties['PATH_INFO']);
        return $args[1] ?? null;
    }
    
    public function getSecondParameter()
    {
        $args = explode('/', $this->properties['PATH_INFO']);
        return (isset($args[2]) && !empty($args[2])) ? $args[2] : false;
    }

    public function getThirdParameter()
    {
        $args = explode('/', $this->properties['PATH_INFO']);
        return (isset($args[3]) && !empty($args[3])) ? $args[3] : false;
    }

    public function getMethod()
    {
        return $this->properties['REQUEST_METHOD'];
    }
    
    public function getRequestData()
    {
        return $this->properties['POST_DATA'];
    }
    
    public function getUrlScheme()
    {
        return $this->properties['url_scheme'];
    }
    
    public function getBaseUrl()
    {
        return $this->properties['url_scheme'].'://'.$_SERVER['HTTP_HOST'];
    }
}
