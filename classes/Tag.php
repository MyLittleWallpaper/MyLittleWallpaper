<?php

/**
 * Basic tag class.
 * This class is only used as a base for different tag types.
 */
class TagBase
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
     *
     * @throws Exception if database not found
     */
    public function __construct(array $data = null, Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }

        if (!empty($data) && is_array($data)) {
            $this->bindData($data);
        }
    }

    /**
     * @param array $data tag data
     */
    public function bindData(array $data)
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
     */
    public function setName(string $val): void
    {
        $this->name = (string)$val;
    }

    /**
     * Saves the tag to database.
     */
    public function save()
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

/**
 * Normal tag class.
 * Tag can be type of Tag::TAG_TYPE_CHARACTER, Tag::TAG_TYPE_GENERAL or Tag::TAG_TYPE_STYLE
 */
class Tag extends TagBase
{
    public const TAG_TYPE_CHARACTER = 'character';
    public const TAG_TYPE_GENERAL   = 'general';
    public const TAG_TYPE_STYLE     = 'style';

    /**
     * @var string
     */
    private string $alternate = '';

    /**
     * @var string
     */
    private string $type = self::TAG_TYPE_GENERAL;

    /**
     * @var string
     */
    protected string $tableName = 'tag';

    /**
     * @param array $data tag data
     */
    public function bindData(array $data)
    {
        parent::bindData($data);
        if (!empty($data['alternate'])) {
            $this->alternate = (string)$data['alternate'];
        }
        if (!empty($data['type'])) {
            $this->setType($data['type']);
        }
    }

    /**
     * @return string
     */
    public function getAlternate(): string
    {
        return $this->alternate;
    }

    /**
     * Returns self::TAG_TYPE_*
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $val
     */
    public function setAlternate(string $val): void
    {
        $this->alternate = (string)$val;
    }

    /**
     * @param string $val use self::TAG_TYPE_*
     */
    public function setType(string $val): void
    {
        switch ($val) {
            case self::TAG_TYPE_CHARACTER:
            case self::TAG_TYPE_GENERAL:
            case self::TAG_TYPE_STYLE:
                $this->type = $val;
        }
    }

    /**
     * Saves the tag to database.
     */
    public function save()
    {
        if ($this->tableName !== '') {
            $saveData = [
                'name'      => $this->name,
                'alternate' => $this->alternate,
                'type'      => $this->type,
            ];
            if ($this->id === null) {
                $this->db->saveArray($this->tableName, $saveData);
            } else {
                $this->db->saveArray($this->tableName, $saveData, $this->id);
            }
        }
    }
}

/**
 * Author tag class.
 */
class TagAuthor extends TagBase
{
    /**
     * @var string
     */
    private string $oldName = '';

    /**
     * @var bool
     */
    private bool $isDeleted = false;

    /**
     * @var string
     */
    protected string $tableName = 'tag_artist';

    /**
     * @param array $data tag data
     */
    public function bindData(array $data)
    {
        parent::bindData($data);
        if (!empty($data['oldname'])) {
            $this->oldName = (string)$data['oldname'];
        }
        if (!empty($data['deleted'])) {
            $this->isDeleted = true;
        }
    }

    /**
     * @return string
     */
    public function getOldName(): string
    {
        return $this->oldName;
    }

    /**
     * @return bool
     */
    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param string $val
     */
    public function setOldName(string $val): void
    {
        $this->oldName = (string)$val;
    }

    /**
     * @param bool $val
     */
    public function setIsDeleted(bool $val): void
    {
        $this->isDeleted = (bool)$val;
    }

    /**
     * Saves the tag to database.
     */
    public function save()
    {
        if ($this->tableName !== '') {
            $saveData = [
                'name'    => $this->name,
                'oldname' => $this->oldName,
                'deleted' => ($this->isDeleted ? 1 : 0),
            ];
            if ($this->id === null) {
                $this->db->saveArray($this->tableName, $saveData);
            } else {
                $this->db->saveArray($this->tableName, $saveData, $this->id);
            }
        }
    }
}

/**
 * Aspect ratio tag class.
 */
class TagAspect extends TagBase
{
    /**
     * @var string
     */
    protected string $tableName = 'tag_aspect';
}

/**
 * Platform tag class.
 */
class TagPlatform extends TagBase
{
    /**
     * @var string
     */
    protected string $tableName = 'tag_platform';
}

/**
 * Colour tag class.
 */
class TagColour
{
    /**
     * @var string
     */
    private string $colour = '';

    /**
     * @var string
     */
    private string $similarColour = '';

    /**
     * @var float
     */
    private $amount = 0;

    /**
     * @param string|null $colour
     * @param float|null  $amount
     * @param string|null $similar_colour
     */
    public function __construct(string $colour = null, float $amount = null, string $similar_colour = null)
    {
        if ($colour !== null) {
            $this->setColourHex($colour);
            if ($amount !== null) {
                $this->setAmount($amount);
            }
            if ($similar_colour !== null) {
                $this->setSimilarColourHex($similar_colour);
            } else {
                $this->setSimilarColourHex($colour);
            }
        }
    }

    /**
     * Returns false if colour isn't set.
     * @return string|bool
     */
    public function getColourHex()
    {
        if ($this->colour == '') {
            return false;
        }
        return $this->colour;
    }

    /**
     * Returns false if colour isn't set.
     * @return string|bool
     */
    public function getSimilarColourHex()
    {
        if ($this->similarColour == '') {
            return false;
        }
        return $this->similarColour;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $colour
     *
     * @return bool
     */
    public function setColourHex(string $colour): bool
    {
        if (preg_match("/^#{0,1}[0-9a-f]{6}$/", strtolower($colour))) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->colour = strtolower($colour);
            return true;
        }

        if (preg_match("/^#{0,1}[0-9a-f]{3}$/", strtolower($colour))) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->colour = str_repeat(substr($colour, 0, 1), 2) . str_repeat(substr($colour, 1, 1), 2) .
                str_repeat(substr($colour, 2, 1), 2);
            return true;
        }
        return false;
    }

    /**
     * @param string $colour
     *
     * @return bool
     */
    public function setSimilarColourHex(string $colour): bool
    {
        if (preg_match("/^#{0,1}[0-9a-f]{6}$/", strtolower($colour))) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->similarColour = strtolower($colour);
            return true;
        }

        if (preg_match("/^#{0,1}[0-9a-f]{3}$/", strtolower($colour))) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->similarColour = str_repeat(substr($colour, 0, 1), 2) . str_repeat(substr($colour, 1, 1), 2) .
                str_repeat(substr($colour, 2, 1), 2);
            return true;
        }
        return false;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        if (is_numeric($amount) && $amount >= 0 && $amount <= 100) {
            $this->amount = (float)$amount;
        }
    }
}