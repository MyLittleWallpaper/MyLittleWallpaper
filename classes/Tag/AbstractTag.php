<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Tag;

use Exception;
use MyLittleWallpaper\classes\Database;
use MyLittleWallpaper\classes\Exception\InvalidParametersException;
use PDO;

use function filter_var;

abstract class AbstractTag
{
    /**
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var Database
     */
    protected Database $db;

    /**
     * @var string
     */
    protected string $tableName = '';

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

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }

        if ($data !== null) {
            $this->bindData($data);
        }
    }

    /**
     * @param array $data tag data
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
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function loadById(int $id): void
    {
        if ($this->tableName !== '') {
            $query  = "SELECT ";
            $query  .= "* FROM `" . $this->tableName . "` WHERE `id` = ? LIMIT 1";
            $result = $this->db->query($query, [$id]);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $this->bindData($row);
            }
        }
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function loadByName(string $name): void
    {
        if ($this->tableName !== '') {
            $query  = "SELECT ";
            $query  .= "* FROM `" . $this->tableName . "` WHERE `name` = ? ORDER BY `name` LIMIT 1";
            $result = $this->db->query($query, [$name]);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $this->bindData($row);
            }
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * Saves the tag to database.
     *
     * @return void
     * @throws InvalidParametersException
     */
    public function save(): void
    {
        if ($this->tableName !== '') {
            $saveData = [
                'name' => $this->name,
            ];
            if ($this->id === null) {
                $this->db->saveArray($this->tableName, $saveData);
            } else {
                $this->db->saveArray($this->tableName, $saveData, $this->id);
            }
        }
    }
}
