<?php

/**
 * Basic class for XML output.
 * @todo Add support for building XML from an array
 */
class BasicXML extends Output
{
    /**
     * @var array|string|null
     */
    private $contents;

    /**
     * @param array|string|null $contents
     */
    public function __construct($contents = null)
    {
        if ($contents !== null) {
            $this->setContents($contents);
        }
    }

    /**
     * @param array $contents
     *
     * @return void
     */
    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * @return array|null
     */
    public function output(): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $this->contents;
    }

    /**
     * @return string
     */
    public function getHeaderType(): string
    {
        return 'application/xml; charset=utf-8';
    }

    /**
     * @return bool
     */
    public function getIncludeHeaderAndFooter(): bool
    {
        return false;
    }
}