<?php

namespace Sovit\TikTokPrivate;

if (!\class_exists('\Sovit\TikTokPrivate\Stream')) {

    class Stream
    {
        protected $buffer_size = 256 * 1024;

        protected $headers = [];

        protected $headers_sent = false;

        public function __construct($config = [])
        {
            $this->config = array_merge(['user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36"], $config);
        }

        public function bodyCallback($ch, $data)
        {
            if (true) {
                echo $data;
                flush();
            }

            return strlen($data);
        }

        public function headerCallback($ch, $data)
        {
            if (preg_match('/HTTP\/[\d.]+\s*(\d+)/', $data, $matches)) {
                $status_code = $matches[1];

                if (200 == $status_code || 206 == $status_code || 403 == $status_code || 404 == $status_code) {
                    $this->headers_sent = true;
                    $this->sendHeader(rtrim($data));
                }
            } else {

                $forward = ['content-type', 'content-length', 'accept-ranges', 'content-range', 'cache-control', 'cross-origin-resource-policy'];
                $parts = explode(':', $data, 2);
                if ($this->headers_sent && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                    $this->sendHeader(rtrim($data));
                }
            }

            return strlen($data);
        }

        public function stream($url)
        {
            $ch = curl_init();
            $options = [
                CURLOPT_URL            => $url,
                CURLOPT_FORBID_REUSE => 1,
                CURLOPT_FRESH_CONNECT => 1,
                CURLOPT_HTTPHEADER     => [
                    'Referer: https://www.tiktok.com',
                ],
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_BUFFERSIZE => $this->buffer_size,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => 0,
                CURLOPT_HEADER         => 0,
                CURLOPT_USERAGENT      => $this->_config['user-agent'],
                CURLOPT_HEADERFUNCTION => [$this, 'headerCallback'],
                CURLOPT_WRITEFUNCTION => [$this, 'bodyCallback'],
            ];
            curl_setopt_array($ch, $options);

            $ret = curl_exec($ch);
            curl_close($ch);
            return true;
        }

        protected function sendHeader($header)
        {
            header($header);
        }
    }
}
