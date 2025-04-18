<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("ApiCall.php");


class NewOpalApiCall extends ApiCall
{

    protected $headers = [];

    public function __construct($api_route, $method, $language, $data, $content_type = '')
    {
        if ($method == 'POST') {
            $this->setOption(CURLOPT_POST, 1);
        } else {
            $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($api_route == '/api/auth/login/') {
            $header = [
                $content_type,
            ];
        } else {
            $header = [
                'Authorization: Token ' . NEW_OPALADMIN_TOKEN,
                'Accept-Language: ' . $language,
                // TODO: why are these needed?
                'Cookie: sessionid=' . $_COOKIE["sessionid"] . ';csrftoken=' . $_COOKIE["csrftoken"],
                'X-CSRFToken: ' . $_COOKIE["csrftoken"],
                $content_type,
            ];
        }

        $this->setOption(CURLOPT_URL, NEW_OPALADMIN_HOST_INTERNAL . $api_route);
        $this->setOption(CURLOPT_HTTP_VERSION, 3);
        $this->setOption(CURLOPT_HTTPHEADER, $header);
        if ($content_type == '')
            $this->setOption(CURLOPT_POSTFIELDS, http_build_query($data));
        else
            $this->setOption(CURLOPT_POSTFIELDS, $data);

        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_TIMEOUT, 5);
        $this->setOption(CURLOPT_CONNECTTIMEOUT, 5);
    }

    /**
     * Override of the execution of the API call with the options in memory. Depending if an error occurs or not, the
     * error message will be set up.
     */
    public function execute()
    {
        $ch = curl_init();

        foreach ($this->options as $option => $value)
            curl_setopt($ch, $option, $value);

        $this->headers = [];
        // this function is called by curl for each header received
        // source: https://stackoverflow.com/a/41135574
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $length = strlen($header);
                $header = explode(':', $header, 2);

                // ignore invalid headers
                if (count($header) < 2)
                    return $length;

                $this->headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $length;
            }
        );

        $result = curl_exec($ch);

        $this->answerInfo = curl_getinfo($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_error($ch) || $httpcode != HTTP_STATUS_SUCCESS || $httpcode != HTTP_STATUS_CREATED) {
            if (curl_error($ch)) {
                $this->answer = false;
                $this->header = false;
                $this->body = false;
                $this->error = curl_error($ch);
            } else {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $this->header = substr($result, 0, $header_size);
                $this->body = substr($result, $header_size);
                $this->error = "HTTP status " . $httpcode . " - $result";
            }
        } else {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $this->header = substr($result, 0, $header_size);
            $this->body = substr($result, $header_size);
        }
        $this->phpSessionId = false;
        curl_close($ch);
        return $result;
    }

    /**
     * Get the headers of the last response.
     * @return list
     */
    public function getHeaders() {
        return $this->headers;
    }
}
