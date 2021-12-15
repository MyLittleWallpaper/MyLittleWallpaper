<?php

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