<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Category;

use Exception;
use MyLittleWallpaper\classes\Database;

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
     * @var Database
     */
    private Database $db;

    /**
     * @param array|null    $data
     * @param Database|null $db If null, looks for $GLOBALS['db']
     */
    public function __construct(?array $data = null, ?Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db = $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
        if (!empty($data) && is_array($data)) {
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
        $this->name = (string)$val;
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
        $this->urlName = (string)$val;
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
        $this->footerDescription = (string)$val;
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
