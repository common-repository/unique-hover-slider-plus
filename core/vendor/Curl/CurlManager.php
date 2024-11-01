<?php

namespace LoM\Curl;

require_once('Curl.php');

use Curl\Curl;
use Exception;

class CurlManager
{

    /**
     * The other cURL library.
     * @var Curl
     */
    private $curl;

    /**
     * Array containing form data attributes.
     * @var array
     */
    private $_data = [];

    /**
     * Array containing multipart files.
     * @var array
     */
    private $_files = [];

    /**
     * The separator to use in the request body. Set in the constructor.
     * @var string
     */
    private $separator;

    /**
     * Automatically generates a separator.
     */
    public function __construct()
    {
        $this->curl = new Curl;
        $this->generateSeparator();
    }

    /**
     * Wraps the parents get method.
     * @param  string $url
     * @return void
     */
    public function get($url)
    {
        $this->curl->get($url, $this->getRequestData());
    }

    /**
     * Wraps the parents post method.
     * @param  string $url
     * @return void
     */
    public function post($url)
    {
        $this->setContentType();
        $this->curl->post($url, $this->getRequestData());
    }

    /**
     * Doesn't wrap the parents put method cause the parent
     * is doing it wrong.
     * @param  string $url
     * @return void
     */
    public function put($url)
    {
        if (count($this->_files) > 0) {
            $this->setContentType();
            $this->curl->setopt(CURLOPT_URL, $url);
            $this->curl->setopt(CURLOPT_PUT, true);
            $this->curl->setopt(CURLOPT_POSTFIELDS, $this->getRequestData());
            $this->curl->_exec();
        } else {
            $this->curl->put($url, $this->getRequestData());
        }
    }

    /**
     * Wraps the parents patch method.
     * @param  string $url
     * @return void
     */
    public function patch($url)
    {
        $this->setContentType();
        $this->curl->patch($url, $this->getRequestData());
    }

    /**
     * Wraps the parents delete method.
     * @param  string $url
     * @return void
     */
    public function delete($url)
    {
        $this->curl->delete($url, $this->getRequestData());
    }

    /**
     * Generates a unique separator for use in the request body.
     * @return void
     */
    public function generateSeparator()
    {
        $this->separator = '-----'.md5(microtime()).'-----';
    }

    /**
     * Adds a line of data, or an array of data.
     * @param mixed $key
     * @param mixed $value
     */
    public function addData($key, $value)
    {
        if (is_array($key)) {
            $this->_data = array_merge($this->_data, $key);
        } else {
            $this->_data[$key] = $value;
        }
    }

    /**
     * Overwrites the data.
     * @param array|string $data
     */
    public function setData($data)
    {
        if (is_array($data) || is_string($data)) {
            $this->_data = $data;
        }
    }

    /**
     * Sets an array of key/value pairs in similar fashion to a GET request URL data.
     * @param array $params
     */
    public function setDataAsBody($params)
    {
        if (is_array($params)) {
            $this->_data = http_build_query($params);
        }
    }

    /**
     * Sets the request data as a json encoded body.
     * @param array $params
     */
    public function setDataAsJson($params)
    {
        if (is_array($params)) {
            $this->_data = json_encode($params);
        }
    }

    /**
     * Adds a file to the filestack.
     * @param  string $handle
     * @param  string $file     Retrieved from file_get_contents(...)
     * @param  string $filename
     * @param  string $mimetype
     * @return void
     */
    public function addFile($handle, $file, $filename = '', $mimetype = '')
    {
        if (strlen($filename) === 0) {
            throw new Exception('Filename not allowed.');
        }

        if (strlen($mimetype) === 0) {
            throw new Exception('Mimetype not allowed.');
        }

        $this->_files[] = [
            'content' => $file,
            'name' => $handle,
            'filename' => $filename,
            'mimetype' => $mimetype
        ];
    }

    /**
     * Sets the content type depending on if we have files or not.
     * @return void
     */
    public function setContentType()
    {
        // Make sure to set the content type as multipart/form-data if files exist.
        if (count($this->_files) > 0) {
            $this->curl->setHeader('Content-Type', 'multipart/form-data; boundary="' . $this->separator . '"');
        }
    }

    /**
     * Merges the data and files into a request body string.
     * @return string
     */
    public function mergeRequestBody()
    {
        $body = '';
        $this->mergeData($body);
        $this->mergeFiles($body);
        $body .= "--$this->separator--";
        return $body;
    }

    /**
     * Retrieves the data we want to send with the request.
     * @return string|array
     */
    public function getRequestData()
    {
        if (count($this->_files) > 0) {
            return $this->mergeRequestBody();
        } else {
            return $this->_data;
        }
    }

    /**
     * Recursively loops through the data to add them to the output.
     * @param  string &$output
     * @param  string $prefix
     * @param  array  $data
     * @return void
     */
    public function mergeData(&$output, $prefix = '', $data = null)
    {
        // Recurses through a multidimensional array and populates $output with a
        // multipart/form-data string representing the data.
        $data = is_array($data) ? $data : $this->_data;

        foreach ($data as $key => $val) {
            $name = ($prefix) ? $prefix."[".$key."]" : $key;
            if (is_array($val)) {
                $this->mergeData($output, $name, $val);
            } else {
                $output .= "--$this->separator\r\n"
                . "Content-Disposition: form-data; name=\"$name\"\r\n"
                . "\r\n"
                . "$val\r\n";
            }
        }
    }

    /**
     * Loops through the files to add them to the output.
     * @param  string &$output
     * @return void
     */
    public function mergeFiles(&$output)
    {
        foreach ($this->_files as $data) {
            $fn = $data['filename'];
            $mt = $data['mimetype'];
            $c  = $data['content'];
            $n  = $data['name'];

            $output .= "--$this->separator\r\n"
            . "Content-Disposition: form-data; name=\"$n\"; filename=\"$fn\"\r\n"
            . "Content-Length: ".strlen($c)."\r\n"
            . "Content-Type: $mt\r\n"
            . "Content-Transfer-Encoding: binary\r\n"
            . "\r\n"
            . "$c\r\n";
        }
    }

    /**
     * Resets properties.
     * @return void
     */
    public function reset()
    {
        $this->_data = [];
        $this->_files = [];
        $this->curl->reset();
    }

    /**
     * Pass the undefined methods through to the cURL library.
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!is_callable([$this->curl, $method])) {
            throw new Exception('Method ' . $method . ' does not exist on the cURL library.');
        }

        return call_user_func_array([$this->curl, $method], $args);
    }

    /**
     * Tries to fetch non-existant properties from the cURL class.
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->curl->{$name})) {
            return $this->curl->{$name};
        } else {
            return null;
        }
    }
}
