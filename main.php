<?php

class translate
{
    public $sourceFile;

    public $sourceLang = "en_US";

    public $targetLang = "zh_TW";

    public $googleAPI = "https://translate.googleapis.com/translate_a/single?client=gtx&sl={{source}}&tl={{target}}&dt=t&q={{text}}";

    public function __construct($argv)
    {
        if (!isset($argv[1]) or empty($argv[1]))
            die('請輸入 ini 檔案');

        $this->sourceFile = $argv[1];

        if (isset($argv[2]) and !empty($argv[2]))
            $this->sourceLang = $argv[2];

        if (isset($argv[3]) and !empty($argv[3]))
            $this->targetLang = $argv[3];

        $this->googleAPI = str_replace("{{source}}", $this->sourceLang, $this->googleAPI);
        $this->googleAPI = str_replace("{{target}}", $this->targetLang, $this->googleAPI);
    }

    public function controller()
    {
        $sourceContent = $this->fileModule($this->sourceFile, "ini");

        $wordsContent = implode("\n", $sourceContent);
        $translateUrl = str_replace("{{text}}", rawurlencode($wordsContent), $this->googleAPI);
        $translateContent = $this->fileModule($translateUrl, "json");

        $newContent = $this->mergerGoogleContent($sourceContent, $translateContent);

        $this->createIni(
            $this->encoding_ini($newContent)
        );
    }

    public function mergerGoogleContent($org, $google)
    {
        $returnArray = array();
        $time = 0;

        foreach($org as $key => $value)
        {
            $returnArray[$key] = trim($google[0][$time][0], "\t\n");
            $time++;
        }

        return $returnArray;
    }

    /**
     * fileModule
     *
     * @param        $filePath
     * @param string $fileType
     *
     * @return  array|mixed|string
     */
    public function fileModule($filePath, $fileType = "text")
    {
        $content = file($filePath);
        $content = implode("\n", $content);

        if ("json" === $fileType)
            return json_decode($content);

        if ("ini" === $fileType)
            return parse_ini_string($content);

        return $content;
    }

    /**
     * encoding_ini
     *
     * @param array $ini
     *
     * @return  string
     */
    public function encoding_ini($ini = array())
    {
        $content = "";

        foreach($ini as $key => $value)
        {
            $content = $content . "{$key}=\"{$value}\"\n";
        }

        return $content;
    }

    public function createIni($content)
    {
        $fileName = "./{$this->sourceFile}.{$this->targetLang}.ini";

        file_put_contents($fileName, $content);
    }

    /**
     * 過濾不需要翻譯的字
     *
     * @param $string
     *
     * @return  mixed
     */
    public function googleEncoding($string)
    {
        return str_replace("\%s", "\"\%s\"", $string);
    }

    /**
     * 還原不需要翻譯的文字
     *
     * @param $string
     *
     * @return  mixed
     */
    public function googleDecoding($string)
    {
        return str_replace("\"\%s\"", "\%s", $string);
    }
}

(new translate($argv))->controller();