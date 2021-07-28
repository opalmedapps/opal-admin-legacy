<?php


class ApiCall {

    private $options = array();
    private $answer;
    private $answerInfo;
    private $body;
    private $header;
    private $error;
    private $phpSessionId;

    /**
     * ApiCall constructor.
     * @param array $options - list of cURL options
     */
    public function __construct($options = array()) {
        $this->options = $options;
        $this->answer = false;
        $this->answerInfo = false;
        $this->error = false;
        $this->body = false;
        $this->header = false;
        $this->phpSessionId = false;
    }

    /**
     * Return the last answer from the last cURL call
     * @return string|false
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Get the info on the answer from the last cURL call
     * @return string|false
     */
    public function getAnswerInfo() {
        return $this->answerInfo;
    }

    /**
     * Get the error from the last cURL call
     * @return string|false
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Get the body message of the last cURL call
     * @return string|false
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Get the header message of the last cURL call
     * @return string|false
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * Get the current PHP session ID used as a token
     * @return string|false
     */
    public function getPhpSessionId()
    {
        return $this->phpSessionId;
    }

    /**
     * Set the default config value for the API call.
     */
    public function setDefaultValues():void {
        $this->options = DEFAULT_API_CONFIG;
    }

    /**
     * Get the list of options for the cURL call
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the value of a specific option if it exists.
     * @param $option
     * @return mixed|string
     */
    public function getOptionValue($option) {
        if(in_array($option, $this->options))
            return $this->options[$option];
        else
            return "";
    }

    /**
     * Setup the PHP session ID and prepare the cookie option for the cURL
     * @param string $sessId
     */
    public function setPhpSessionId($sessId = "") {
        if($sessId != "")
            $this->phpSessionId = $sessId;
        $this->setCookie('PHPSESSID=' . $this->phpSessionId . '; path=/');
    }

    /**
     * Set a list of options of an associative array to the options of the cURL call
     * @param $options
     */
    public function setOptions($options): void {
        foreach ($options as $key=>$item)
            $this->options[$key] = $item;
    }

    /**
     * Set one option and its value to the option list of the cURL
     * @param $option
     * @param $value
     */
    public function setOption($option, $value): void {
        $this->setOptions(array($option=>$value));
    }

    /**
     * Remove a list of options from the the list of options of the cURL call
     * @param $options
     */
    public function removeOptions($options): void {
        foreach ($options as $item)
            unset($this->options[$item]);
    }

    /**
     * Remove a specific option from the the list of options of the cURL call
     * @param $option
     */
    public function removeOption($option): void {
        $this->removeOptions(array($option));
    }

    /**
     * Activate the return transfer option for cURL if no yet active
     */
    public function activateReturnMessage(): void {
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Deactivate the return transfer option for cURL if no yet deactivated
     */
    public function deactivateReturnMessage(): void {
        $this->setOption(CURLOPT_RETURNTRANSFER, false);
    }

    /**
     * Remove the return transfer option for cURL if present
     */
    public function unsetReturnMessage(): void {
        $this->removeOption(CURLOPT_RETURNTRANSFER);
    }

    /**
     * Activate the post option for cURL if no yet active
     */
    public function activatePost(): void {
        $this->setOption(CURLOPT_POST, true);
    }

    /**
     * Deactivate the post option for cURL if no yet deactivated
     */
    public function deactivatePost(): void {
        $this->setOption(CURLOPT_POST, false);
    }

    /**
     * Remove the post option for cURL if present
     */
    public function unsetPost(): void {
        $this->removeOption(CURLOPT_POST);
    }

    /**
     * Activate the post fields option for cURL if no yet active
     * @param $fields - data to send to the server
     */
    public function setPostFields($fields): void {
        $this->activatePost();
        $this->_httpBuildQueryForCurl($fields, $results);
        $this->setOption(CURLOPT_POSTFIELDS, $results);
    }

    /**
     * Remove the post fields option for cURL if present
     */
    public function removePostFields(): void {
        $this->removeOption(CURLOPT_POSTFIELDS);
    }

    /**
     * Activate the url option for cURL if no yet active
     * @param $url string - url of the API call to do
     */
    public function setUrl($url): void {
        $this->setOption(CURLOPT_URL, $url);
    }

    /**
     * Activate the cookie session option for cURL if no yet active
     */
    public function activateCookieSession(): void {
        $this->setOption(CURLOPT_COOKIESESSION, true);
    }

    /**
     * Deactivate the cookie session option for cURL if no yet deactivated
     */
    public function deactivateCookieSession(): void {
        $this->setOption(CURLOPT_COOKIESESSION, false);
    }

    /**
     * Remove the post fields option for cURL if present
     */
    public function unsetCookieSession(): void {
        $this->removeOption(CURLOPT_COOKIESESSION);
    }

    /**
     * Activate the cookie option for cURL if no yet active
     * @param $cookies string - cookie info to send to the API through cURL
     */
    public function setCookie($cookies):void {
        $this->setOption(CURLOPT_COOKIE, $cookies);
    }

    /**
     * Deactivate the cookie option for cURL if no yet deactivated
     */
    public function unsetCookie():void {
        $this->removeOption(CURLOPT_COOKIE);
    }

    /**
     * Return the http code from the last answer of the cURL
     * @return false|mixed
     */
    public function getHttpCode() {
        if(is_array($this->answerInfo) && array_key_exists("http_code", $this->answerInfo))
            return $this->answerInfo["http_code"];
        else
            return false;
    }

    /**
     * Execute the API call with the options in memory. Depending if an error occurs or not, the error message will
     * be set up. The PHP session ID will be stored (if any), as for the answer info, message body and header.
     */
    public function execute() {
        $ch = curl_init();
        foreach ($this->options as $option=>$value)
            curl_setopt($ch, $option, $value);
        $result = curl_exec($ch);

        $this->answerInfo = curl_getinfo($ch);

        if($result === false) {
            $this->error = curl_error($ch);
            $this->answer = false;
            $this->phpSessionId = false;
            $this->header = false;
            $this->body = false;
        }
        else {
            $this->answer = $result;
            $this->error = false;
            if(preg_match("/PHPSESSID=(.*?)(?:;|\r\n)/", $result, $matches))
                $this->phpSessionId = $matches[1];
            else
                $this->phpSessionId = false;

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $this->header = substr($result, 0, $header_size);
            $this->body = substr($result, $header_size);
        }
        curl_close($ch);
    }

    /**
     * Recursive function to build a query for cURL. Used only internally.
     * @param $arrays
     * @param array $new
     * @param null $prefix
     */
    protected function _httpBuildQueryForCurl( $arrays, &$new = array(), $prefix = null ) {
        if ( is_object( $arrays ) ) {
            $arrays = get_object_vars( $arrays );
        }

        foreach ( $arrays AS $key => $value ) {
            $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
            if ( is_array( $value ) OR is_object( $value )  ) {
                http_build_query_for_curl( $value, $new, $k );
            } else {
                $new[$k] = $value;
            }
        }
    }
}