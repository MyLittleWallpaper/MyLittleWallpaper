<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Tag;

use MyLittleWallpaper\classes\Exception\InvalidParametersException;

/**
 * Author tag class.
 */
class TagAuthor extends AbstractTag
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
     *
     * @return void
     */
    public function bindData(array $data): void
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
     *
     * @return void
     */
    public function setOldName(string $val): void
    {
        $this->oldName = (string)$val;
    }

    /**
     * @param bool $val
     *
     * @return void
     */
    public function setIsDeleted(bool $val): void
    {
        $this->isDeleted = (bool)$val;
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
