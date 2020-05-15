<?php
namespace Core;

class Output
{
    protected $application;
    
    public function __construct(\Core\Application $application)
    {
        $this->application = $application;
    }
    
    public function send($results, $json_encode = true, $response_code = 200)
    {
        if (!empty($results)) {
            if ($json_encode) {
                $output = json_encode($results);
            } else {
                $output = $results;
            }
        } else {
            $response_code = 400;
            $output = '{"error": "empty data"}';
        }
        
        if ($output === false) {
            $output = json_encode(array("error", json_last_error_msg()));
            if ($json === false) {
                $output = '{"error": "unknown"}';
            }
            $response_code = 500;
        }
        if ($json_encode) {
            header('Content-Type: application/json');
        }
        http_response_code($response_code);
        echo $output;
    }
    
    public function sendHeader($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        } else {
            header($headers);
        }
    }
    
    public function sendRedirect($uri, $is_absolute = false)
    {
        if ($is_absolute) {
            $url = $uri;
        } else {
            $uri = (substr($uri, 0, 1) == '/') ? $uri : '/'.$uri;
            $url = $this->getRouter()->getBaseUrl().$uri;
        }
        header('Location: '.$url);
    }
    
    public function getApplication()
    {
        return $this->application;
    }
    
    public function getRouter()
    {
        return $this->getApplication()->getRouter();
    }
}
