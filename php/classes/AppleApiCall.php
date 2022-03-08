<?php

class AppleApiCall extends ApiCall {

    public function __construct($registrationId, $title, $body, $options = APPLE_PUSH_NOTIFICATION_CONFIG) {
        $options = json_decode(str_replace("%%REGISTRATION_ID_HERE%%", $registrationId, json_encode($options)), true);
        parent::__construct($options);

        $this->setOption(CURLOPT_POSTFIELDS,
            str_replace("%%BODY_HERE%%", $body,
                str_replace("%%TITLE_HERE%%", $title,  APPLE_PUSH_NOTIFICATION_POSTFIELDS_CONFIG)));
    }

    /**
     * Override of the execution of the API call with the options in memory. Depending if an error occurs or not, the
     * error message will be set up.
     */
    public function execute() {
        $ch = curl_init();
        foreach ($this->options as $option=>$value)
            curl_setopt($ch, $option, $value);

        $result = curl_exec($ch);
        $this->answerInfo = curl_getinfo($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(curl_error($ch) || $httpcode != HTTP_STATUS_SUCCESS) {
            echo "Apple Error\r\n";
            echo "curl: ".curl_error($ch) . "\r\n";
            echo "result: ". $result . "\r\n";
            echo "code: ".$httpcode . "\r\n";
            if(curl_error($ch)) {
                $this->answer = false;
                $this->header = false;
                $this->body = false;
                $this->error = curl_error($ch);
            }
            else {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $this->header = substr($result, 0, $header_size);
                $this->body = substr($result, $header_size);
                $this->error = "HTTP status " . $httpcode . " - $result";
            }
        }
        else {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $this->header = substr($result, 0, $header_size);
            $this->body = substr($result, $header_size);
        }
        $this->phpSessionId = false;
        curl_close($ch);
    }
}