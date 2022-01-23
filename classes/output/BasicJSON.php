<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\output;

use JsonException;

/**
 * Basic class for JSON output.
 */
class BasicJSON extends Output
{
    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @param array|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function output(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string
     */
    public function getHeaderType(): string
    {
        return 'application/json';
    }

    /**
     * @return bool
     */
    public function getIncludeHeaderAndFooter(): bool
    {
        return false;
    }
}
