<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Tag;

use MyLittleWallpaper\classes\Exception\InvalidParametersException;

/**
 * Normal tag class.
 * Tag can be type of Tag::TAG_TYPE_CHARACTER, Tag::TAG_TYPE_GENERAL or Tag::TAG_TYPE_STYLE
 */
class Tag extends AbstractTag
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
     *
     * @return void
     */
    public function bindData(array $data): void
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
     *
     * @return void
     */
    public function setAlternate(string $val): void
    {
        $this->alternate = (string)$val;
    }

    /**
     * @param string $val use self::TAG_TYPE_*
     *
     * @return void
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
     *
     * @return void
     * @throws InvalidParametersException
     */
    public function save(): void
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
