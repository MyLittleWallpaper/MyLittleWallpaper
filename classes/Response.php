<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use MyLittleWallpaper\classes\output\Output;
use stdClass;

use function is_object;

/**
 * Response class
 */
class Response
{
    /**
     * @var Output
     */
    private Output $outputClass;

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
    private stdClass $responseVariables;

    /**
     * @param Output|null $class
     */
    public function __construct(?Output $class)
    {
        if (is_object($class)) {
            $this->outputClass = $class;
        }
        $this->responseVariables = new stdClass();
    }

    /**
     * Disables header and footer.
     *
     * @return void
     */
    public function setDisableHeaderAndFooter(): void
    {
        $this->disableHeaderAndFooter = true;
    }

    /**
     * @param int|string $httpCode
     *
     * @return void
     */
    public function setHttpCode($httpCode): void
    {
        $this->httpCode = (int)$httpCode;
    }

    /**
     * Outputs the page contents
     *
     * @return void
     */
    public function output(): void
    {
        header('Content-Type: ' . $this->outputClass->getHeaderType());
        if ($this->httpCode !== null) {
            http_response_code($this->httpCode);
        }
        $this->headerOutput();
        echo $this->outputClass->output();
        $this->footerOutput();
    }

    /**
     * @return void
     */
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
            if (method_exists($this->outputClass, 'getJavaScript') && $this->outputClass->getJavaScript() !== '') {
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
                if ($this->responseVariables->titleAddition !== '') {
                    $this->responseVariables->titleAddition .= ' | ';
                }
            }
            $this->responseVariables->category_list = $category_repository->getCategoryList();
            require_once(DOC_DIR . THEME . '/' . $this->headerTemplate);
        }
    }

    /**
     * @return stdClass
     */
    public function getResponseVariables(): stdClass
    {
        return $this->responseVariables;
    }

    /**
     * @return void
     */
    private function footerOutput(): void
    {
        if (!$this->disableHeaderAndFooter && $this->outputClass->getIncludeHeaderAndFooter()) {
            require_once(DOC_DIR . THEME . '/' . $this->footerTemplate);
        }
    }
}
