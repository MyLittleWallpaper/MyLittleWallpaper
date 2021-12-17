<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Category;

/**
 * Category object.
 */
class Category
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $urlName;

    /**
     * @var string
     */
    private string $footerDescription;

    /**
     * @param array|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($data !== null) {
            $this->bindData($data);
        }
    }

    /**
     * @param int $val
     *
     * @return void
     */
    public function setId(int $val): void
    {
        $this->id = (int)$val;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setName(string $val): void
    {
        $this->name = $val;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setUrlName(string $val): void
    {
        $this->urlName = $val;
    }

    /**
     * @return string|null
     */
    public function getUrlName(): ?string
    {
        return $this->urlName;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setFooterDescription(string $val): void
    {
        $this->footerDescription = $val;
    }

    /**
     * @return string|null
     */
    public function getFooterDescription(): ?string
    {
        return $this->footerDescription;
    }

    /**
     * @param array $data category data
     *
     * @return void
     */
    public function bindData(array $data): void
    {
        if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== false) {
            $this->id = (int)$data['id'];
        }
        if (!empty($data['name'])) {
            $this->name = (string)$data['name'];
        }
        if (!empty($data['urlname'])) {
            $this->urlName = (string)$data['urlname'];
        }
        if (!empty($data['footerdescription'])) {
            $this->footerDescription = (string)$data['footerdescription'];
        }
    }
}
