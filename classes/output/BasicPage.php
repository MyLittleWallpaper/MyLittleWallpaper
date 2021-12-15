<?php

/**
 * Class for basic (& static) pages.
 */
class BasicPage extends Output
{
    /**
     * @var string
     */
    private string $pageTitleAddition;

    /**
     * @var string
     */
    private string $html;

    /**
     * @var string
     */
    private string $javaScript;

    /**
     * @var string
     */
    private string $meta;

    /**
     * @var bool
     */
    private bool $noContainer = false;

    /**
     * @param string|null $title
     * @param string|null $html
     */
    public function __construct(?string $title = null, ?string $html = null)
    {
        if ($title !== null) {
            $this->setPageTitleAddition($title);
        }
        if ($html !== null) {
            $this->setHtml($html);
        }
    }

    /**
     * @param string $html
     *
     * @return void
     */
    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setPageTitleAddition(string $title): void
    {
        $this->pageTitleAddition = $title;
    }

    /**
     * @param string $javaScript
     *
     * @return void
     */
    public function setJavascript(string $javaScript): void
    {
        $this->javaScript = (string)$javaScript;
    }

    /**
     * @param string $meta
     *
     * @return void
     */
    public function setMeta(string $meta): void
    {
        $this->meta = (string)$meta;
    }

    /**
     * @return void
     */
    public function setNoContainer(): void
    {
        $this->noContainer = true;
    }

    /**
     * @return string
     */
    public function getPageTitleAddition(): string
    {
        return $this->pageTitleAddition;
    }

    /**
     * @return string
     */
    public function getJavaScript(): string
    {
        return $this->javaScript;
    }

    /**
     * @return string
     */
    public function getMeta(): string
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function output(): string
    {
        global $response;
        ob_start();
        $response->responseVariables->html = $this->html;
        if (!$this->noContainer) {
            require_once(DOC_DIR . THEME . '/basic_page.php');
        } else {
            echo $response->responseVariables->html;
        }
        return (string)ob_get_clean();
    }

    /**
     * @return string
     */
    public function getHeaderType(): string
    {
        return 'text/html; charset=utf-8';
    }

    /**
     * @return bool
     */
    public function getIncludeHeaderAndFooter(): bool
    {
        return true;
    }
}
