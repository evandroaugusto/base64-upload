<?php

namespace evandroaugusto\base64;

class Base64Upload
{
  
    private $destination;
    private $data;
    private $isBase64;
    private $extension;


    public function __construct($destination, $data, $extension='jpeg')
    {
        $this->destination = $destination;
        $this->extension = '.' . $extension;

        // get base64 from file
        $base64 = $this->extractBase64($data);

        if ($base64) {
            $this->data = $base64;
            $this->isBase64 = true;
        } else {
            $this->data = $data;
            $this->isBase64 = false;
        }
    }


    // ----------------------------------------------------------
    //
    // @ Public Methods
    //
    // -----------------------------------------------------------
        

    /**
     * Upload base64 file
     */
    public function upload()
    {
        if (!$this->isBase64()) {
            throw new \Exception("Invalid format", 1);
        }

        // prepare destination
        $destination = $this->prepareDestination(
            $this->getBaseDestination() . $this->getDestination()
        );

        // generate file and get full path and upload
        $fileName = $this->generateFileName();
        $fullPath = $destination . '/' . $fileName;

        $uploadFile = file_put_contents($fullPath, $this->getData());

        if (!$uploadFile) {
            throw new \Exception("Unable to upload image", 1);
        }

        // get absolute/url path
        $httpHost = strtolower($_SERVER['HTTP_HOST']);

        $urlPath = $this->getProtocol() . $httpHost . $this->getDestination() . '/' . $fileName;

        return [
            'full_path' => $fullPath,
            'file_name' => $fileName,
            'url_path'  => $urlPath,
            'extension' => $this->getExtension(),
            'size'		  => $uploadFile
        ];
    }

    /**
     * Check if file is base64 encoded
     */
    public function isBase64()
    {
        if (!$this->isBase64) {
            return false;
        }

        return true;
    }


    // ----------------------------------------------------------
    //
    // @ Private Methods
    //
    // -----------------------------------------------------------
            

    /**
     * Get base destination based on server system
     */
    private function getBaseDestination()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Validate base64 content
     */
    private function extractBase64($data)
    {
        if (!$data) {
            return false;
        }

        // get base64 from content
        $img = str_replace('data:image/jpeg;base64,', '', $data);
        $img = str_replace(' ', '+', $img);

        $base64 = base64_decode($img, true);

        if (!$base64) {
            return false;
        }

        return $base64;
    }

    /**
     * Validate/prepare content destination
     */
    private function prepareDestination($destination)
    {
        if (!$destination) {
            throw new \Exception("Empty destination", 1);
        }

        // create directories if needed
        if (!is_dir($destination)) {
            if (!mkdir($destination, 0755, true)) {
                throw new \Exception("Unable to create path", 1);
            }
        }

        return $destination;
    }

    /**
     * Create unique file name
     */
    private function generateFileName()
    {
        return md5(uniqid()) . $this->extension;
    }

    /**
     * Check current protocol do crete absolute path
     */
    private function getProtocol()
    {
        $isHttps =
            $_SERVER['HTTPS'] ??
            $_SERVER['REQUEST_SCHEME'] ??
            $_SERVER['HTTP_X_FORWARDED_PROTO'] ??
            null
        ;

        $isHttps =
            $isHttps && (
                strcasecmp('on', $isHttps) == 0 ||
                strcasecmp('https', $isHttps) == 0
            )
        ;

        return $isHttps ? 'https://' : 'http://';
    }

    // -----------------------------------------------------------
    //
    // @ Getters and Setters
    //
    // -----------------------------------------------------------
        
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    public function getDestination()
    {
        return $this->destination;
    }
}
