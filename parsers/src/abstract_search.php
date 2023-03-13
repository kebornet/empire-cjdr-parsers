<?php

abstract class AbstractSearch
{
    protected $proxyHost = null;
    protected $proxyLogin = null;
    protected $proxyPassword = null;

    public function _construct(array $proxy = null)
    {
        $this->proxyHost = $proxy['host'] ?? null;
        $this->proxyLogin = $proxy['login'] ?? null;
        $this->proxyPassword = $proxy['password'] ?? null;
    }

    private function _getProxy()
    {
        if ($this->proxyHost) {
            return [
                'host' => $this->proxyHost,
                'login' => $this->proxyLogin,
                'password' => $this->proxyPassword
            ];
        }

        return null;
    }

    protected function useProxyIfAvailable($ch)
    {
        if ($proxy = $this->_getProxy()) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, implode(':', [$proxy['login'], $proxy['password']]));
        }
    }

    protected function loadWithCurlMain($url, $h = 0)
    {
        $header = [];
        $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
        $header[] = "Connection: keep-alive";
        $header[] = "Accept-Encoding: gzip, deflate";
        $header[] = "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4";
        $header[] = "Referer: $url";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt(
            $ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36'
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, $h);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $this->useProxyIfAvailable($ch);

        $data = curl_exec($ch);

        if ($data === false) {
            throw new Exception('Connection error: '  . curl_error($ch) . ' URL:' . $url);
        }

        curl_close($ch);
        return $data;
    }
}
