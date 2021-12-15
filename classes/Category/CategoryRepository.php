<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Category;

use Exception;
use MyLittleWallpaper\classes\Database;
use PDO;

/**
 * Category Repository class.
 * Used for loading categories
 */
class CategoryRepository
{
    /**
     * @var Database
     */
    private Database $db;

    /**
     * @param Database|null $db If null, looks for $GLOBALS['db']
     */
    public function __construct(Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }
            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
    }

    /**
     * @return Category[]
     */
    public function getCategoryList(): array
    {
        $return = [];
        $sql    = "SELECT * FROM category ORDER BY name";
        $res    = $this->db->query($sql);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $return[(int)$row['id']] = new Category($row, $this->db);
        }
        return $return;
    }

    /**
     * @param string $urlName
     *
     * @return Category|null
     */
    public function getCategoryByUrlName(string $urlName): ?Category
    {
        $sql  = "SELECT * FROM category WHERE urlname = ?";
        $data = [$urlName];

        return $this->selectCategory($sql, $data);
    }

    /**
     * @param int $id
     *
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        $sql  = "SELECT * FROM category WHERE id = ?";
        $data = [$id];

        return $this->selectCategory($sql, $data);
    }

    /**
     * @param string $sql
     * @param array  $data
     *
     * @return Category|null
     */
    private function selectCategory(string $sql, array $data): ?Category
    {
        $category = null;
        $res      = $this->db->query($sql, $data);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $category = $row;
        }

        if ($category !== null) {
            return new Category($category, $this->db);
        }
        return null;
    }
}
