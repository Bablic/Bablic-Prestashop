        $this->pos = 0;
        curl_setopt($curl, CURLOPT_READFUNCTION, array(&$this, 'writeBuffer'));
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (($status != 200) && ($status != 301)) {
            return $html;
        }
        curl_close($curl);
        $this->saveHtml($response, $this->fullPathFromUrl($url));

        return $response;
    }
    private function saveHtml($content, $filename)
    {   
        $file = fopen($filename, 'w') or die('Unable to open file!');
        fwrite($file, $content);
        fclose($file);
    }
    public function noop()
    {   
        return '';
    }
    public function getHtmlForUrl($url)
    {   
        $cached_file = $this->readFromCache($this->fullPathFromUrl($url));
        if ($cached_file) {
            exit;

            return;
        }
        ob_start(array(&$this, 'processBuffer'));

        return;
    }
    private function readFromCache($filename)
    {   
        if ($this->nocache == true) {
            return false;
        }
        try {
            $html_file = file_exists($filename);
            if ($html_file) {
                $file_modified = filemtime($filename);
                $now = round(microtime(true) * 1000);
                $validity = ($now - (2 * 24 * 60 * 60 * 1000) > $file_modified);
                if ($validity === false) {
                    return false;
                }
                readfile($filename);

                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}