<?php

/**
 * Response class
 */
class Response
{
    /**
     * @var Output
     */
    private $outputClass;

    /**
     * @var string
     */
    private string $headerTemplate = 'header.php';

    /**
     * @var string
     */
    private string $footerTemplate = 'footer.php';

    /**
     * @var bool
     */
    private bool $disableHeaderAndFooter = false;

    /**
     * @var int|null
     */
    private ?int $httpCode = null;

    /**
     * @var stdClass
     */
    public stdClass $responseVariables;

    /**
     * @param mixed $class
     */
    public function __construct($class)
    {
        if (is_object($class)) {
            $this->outputClass = $class;
        }
        $this->responseVariables = new stdClass();
    }

    /**
     * Disables header and footer.
     */
    public function setDisableHeaderAndFooter(): void
    {
        $this->disableHeaderAndFooter = true;
    }

    /**
     * @param int|string $httpCode
     */
    public function setHttpCode($httpCode): void
    {
        $this->httpCode = (int)$httpCode;
    }

    /**
     * Outputs the page contents
     */
    public function output(): void
    {
        header('Content-Type: ' . $this->outputClass->getHeaderType());
        if ($this->httpCode !== null) {
            http_response_code($this->httpCode);
        }
        $this->headerOutput();
        echo $this->outputClass->output();
        $this->footer_output();
    }

    private function headerOutput(): void
    {
        global $category_repository;

        if ($this->outputClass->getIncludeHeaderAndFooter() && !$this->disableHeaderAndFooter) {
            $this->responseVariables->rss = '';
            if (method_exists($this->outputClass, 'getRss')) {
                $this->responseVariables->rss = $this->outputClass->getRss();
            }
            $this->responseVariables->metaTags = '';
            if (method_exists($this->outputClass, 'getMetaTags')) {
                $this->responseVariables->metaTags = $this->outputClass->getMetaTags();
            }
            $this->responseVariables->meta = '';
            if (method_exists($this->outputClass, 'getMeta')) {
                $this->responseVariables->meta = $this->outputClass->getMeta();
            }
            $this->responseVariables->javaScript = '';
            if (method_exists($this->outputClass, 'getJavaScript') && $this->outputClass->getJavaScript() != '') {
                $this->responseVariables->javaScript = '<script type="text/javascript">';
                $this->responseVariables->javaScript .= $this->outputClass->getJavaScript();
                $this->responseVariables->javaScript .= '</script>';
            }
            $this->responseVariables->javaScriptFiles = [];
            if (method_exists($this->outputClass, 'getJavaScriptFiles')) {
                $this->responseVariables->javaScriptFiles = $this->outputClass->getJavaScriptFiles();
            }
            $this->responseVariables->titleAddition = '';
            if (method_exists($this->outputClass, 'getPageTitleAddition')) {
                $this->responseVariables->titleAddition = $this->outputClass->getPageTitleAddition();
                if ($this->responseVariables->titleAddition != '') {
                    $this->responseVariables->titleAddition .= ' | ';
                }
            }
            $this->responseVariables->category_list = $category_repository->getCategoryList();
            /** @noinspection PhpIncludeInspection */
            require_once(DOC_DIR . THEME . '/' . $this->headerTemplate);
        }
    }

    private function footer_output(): void
    {
        if ($this->outputClass->getIncludeHeaderAndFooter() && !$this->disableHeaderAndFooter) {
            /** @noinspection PhpIncludeInspection */
            require_once(DOC_DIR . THEME . '/' . $this->footerTemplate);
        }
    }
}