<?php
/**
 * @author     Petri Haikonen <sharkmachine@ecxol.net>
 * @package    MyLittleWallpaper
 * @subpackage Classes
 */

// Check that correct entry point was used
if (!defined('INDEX')) {
    exit();
}

/**
 * Database class.
 * Database class with easy shorthand functions to execute simple selects etc.
 */
class Database
{
    /**
     * PDO Database class
     * @var PDO
     */
    private PDO $dbh;

    /**
     * @param string $username Database server username
     * @param string $password Database server password
     * @param string $database Database name
     * @param string $host     Database server hostname
     * @param string $port     Database server port
     *
     * @throws PDOException
     */
    public function __construct(string $username, string $password, string $database, string $host = 'localhost', string $port = '3306')
    {
        if (empty($username) || empty($password) || empty($database) || empty($host)) {
            $miss = '';
            if (empty($username)) {
                $miss .= 'username';
            }
            if (empty($password)) {
                if (!empty($miss)) {
                    $miss .= ', ';
                }
                $miss .= 'password';
            }
            if (empty($database)) {
                if (!empty($miss)) {
                    $miss .= ', ';
                }
                $miss .= 'database name';
            }
            if (empty($host)) {
                if (!empty($miss)) {
                    $miss .= ', ';
                }
                $miss .= 'host';
            }
            throw new PDOException('Database connection failed, missing ' . $miss . '.');
        }

        $dsn       = 'mysql:dbn' . 'ame=' . $database . ';host=' . $host;
        $this->dbh = new PDO($dsn, $username, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $value
     * @param string $table
     * @param string $field
     * @param int    $id
     *
     * @return bool
     * @throws PDOException
     */
    public function checkUnique(string $value, string $table, string $field, int $id = 0): bool
    {
        $query = " SELECT ";
        $query .= "id FROM `" . $table . "` WHERE `" . $field . "` = ?";
        if (empty($id)) {
            $sth = $this->query($query, [$value]);
        } else {
            $query .= " AND `id` != ?";
            $sth   = $this->query($query, [$value, $id]);
        }
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($data['id'])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $table
     * @param array  $fields
     * @param array  $conditions
     * @param array  $order
     * @param array  $limit
     * @param array  $join
     * @param bool   $group
     *
     * @return array
     * @throws PDOException
     */
    public function getList(
        string $table,
        array $fields = [],
        array $conditions = [],
        array $order = [],
        array $limit = [],
        array $join = [],
        bool $group
    ): array {
        $return      = [];
        $assign_data = [];
        $select      = "";
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($select != '') {
                    $select .= ', ';
                } else {
                    $select = "SELECT ";
                }
                $select .= "`" . $field['table'] . "`.`" . $field['field'] . "`";
                if (!empty($field['alias'])) {
                    $select .= " `" . $field['alias'] . "`";
                }
            }
        } else {
            $select = "SELECT *";
        }
        $select .= " FROM `" . $table . "` ";
        if (!empty($join)) {
            foreach ($join as $j) {
                if (!empty($j['type']) && strcasecmp('left', $j['type']) === 0) {
                    $select .= "LEFT ";
                }
                $select         .= "JOIN `" . $j['table'] . "` ON (";
                $firstCondition = true;
                foreach ($j['condition'] as $condition) {
                    if (!$firstCondition) {
                        $select .= " AND ";
                    } else {
                        $firstCondition = false;
                    }
                    $select .= "`" . $condition[0]['table'] . "`.`" . $condition[0]['field'] . "`";
                    if (!empty($condition[1]['table'])) {
                        $select .= " = `" . $condition[1]['table'] . "`.`" . $condition[1]['field'] . "`";
                    } elseif (!empty($condition[1]['value'])) {
                        $select        .= " = ?";
                        $assign_data[] = $condition[1]['value'];
                    } else {
                        $select .= "IS NULL";
                    }
                }
                $select .= ") ";
            }
        }
        if (!empty($conditions)) {
            $conditionsString = "";
            foreach ($conditions as $condition) {
                if ($conditionsString != '') {
                    $conditionsString .= " AND ";
                } else {
                    $conditionsString .= "WHERE ";
                }

                if (!empty($condition['operator']) && strcasecmp('in', $condition['operator']) === 0) {
                    if (is_array($condition['value'])) {
                        $conditionsString .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` IN (";
                        $first            = true;
                        foreach ($condition['value'] as $val) {
                            if (!$first) {
                                $conditionsString .= ", ";
                            } else {
                                $first = false;
                            }
                            $conditionsString .= "?";
                            $assign_data[]    = $val;
                        }
                        $conditionsString .= ") ";
                    }
                } elseif (!empty($condition['operator']) && strcasecmp('not in', $condition['operator']) === 0) {
                    if (is_array($condition['value'])) {
                        $conditionsString .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` NOT IN (";
                        $first            = true;
                        foreach ($condition['value'] as $val) {
                            if (!$first) {
                                $conditionsString .= ", ";
                            } else {
                                $first = false;
                            }
                            $conditionsString .= "?";
                            $assign_data[]    = $val;
                        }
                        $conditionsString .= ") ";
                    }
                } else {
                    if (!empty($condition['operator']) && strcasecmp('like', $condition['operator']) === 0) {
                        $operator = 'LIKE';
                    } elseif (!empty($condition['operator']) && strcasecmp('!=', $condition['operator']) === 0) {
                        $operator = '!=';
                    } elseif (!empty($condition['operator']) && strcasecmp('<', $condition['operator']) === 0) {
                        $operator = '<';
                    } elseif (!empty($condition['operator']) && strcasecmp('<=', $condition['operator']) === 0) {
                        $operator = '<=';
                    } elseif (!empty($condition['operator']) && strcasecmp('>', $condition['operator']) === 0) {
                        $operator = '>';
                    } elseif (!empty($condition['operator']) && strcasecmp('>=', $condition['operator']) === 0) {
                        $operator = '>=';
                    } else {
                        $operator = '=';
                    }
                    if (is_array($condition['field'])) {
                        $conditionsString .= "(";
                        $first            = true;
                        foreach ($condition['field'] as $fld) {
                            if (!$first) {
                                $conditionsString .= " OR ";
                            } else {
                                $first = false;
                            }
                            if (!empty($condition['value'])) {
                                $conditionsString .= "`" . $condition['table'] . "`.`" . $fld . "` " . $operator . " ?";
                                $assign_data[]    = $condition['value'];
                            } else {
                                if (strcmp('!=', $operator) === 0) {
                                    $operator = 'IS NOT';
                                } else {
                                    $operator = 'IS';
                                }
                                $conditionsString .= "`" . $condition['table'] . "`.`" . $fld . "` " . $operator .
                                    " NULL";
                            }
                        }
                        $conditionsString .= ")";
                    } elseif (!is_null($condition['value'])) {
                        $conditionsString .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` " .
                            $operator . " ?";
                        $assign_data[]    = $condition['value'];
                    } else {
                        if (strcmp('!=', $operator) === 0) {
                            $operator = 'IS NOT';
                        } else {
                            $operator = 'IS';
                        }
                        $conditionsString .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` " .
                            $operator . " NULL";
                    }
                }
            }
            $select .= $conditionsString . " ";
        }

        if ($group) {
            $select .= " GROUP BY `" . $table . "`.`id` ";
        }

        if (!empty($order)) {
            $order_str = '';
            foreach ($order as $o) {
                if ($order_str != '') {
                    $order_str .= ", ";
                } else {
                    $order_str .= "ORDER BY ";
                }
                $order_str .= "`" . $o['table'] . "`.`" . $o['field'] . "`" . (!empty($o['desc']) ? " DESC" : '');
            }
            $select .= $order_str . " ";
        }
        if (!empty($limit)) {
            $select        .= "LIMIT ? OFFSET ?";
            $assign_data[] = (int)$limit[1];
            $assign_data[] = (int)$limit[0];
        }
        $sth = $this->query($select, $assign_data);
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $return[] = $row;
        }
        return $return;
    }

    /**
     * @param string $table
     * @param array  $conditions
     * @param array  $join
     *
     * @return int
     * @throws PDOException
     */
    public function getCount(string $table, array $conditions = [], array $join = [])
    {
        $return      = [];
        $assign_data = [];
        $select      = "SELECT ";
        $select      .= "COUNT(1) cnt FROM (SELECT DISTINCT `" . $table . "`.`id` FROM `" . $table . "` ";
        if (!empty($join)) {
            foreach ($join as $j) {
                if (!empty($j['type']) && strcasecmp('left', $j['type']) === 0) {
                    $select .= "LEFT ";
                }
                $select         .= "JOIN `" . $j['table'] . "` ON (";
                $firstCondition = true;
                foreach ($j['condition'] as $condition) {
                    if (!$firstCondition) {
                        $select .= " AND ";
                    } else {
                        $firstCondition = false;
                    }
                    $select .= "`" . $condition[0]['table'] . "`.`" . $condition[0]['field'] . "`";
                    if (!empty($condition[1]['table'])) {
                        $select .= " = `" . $condition[1]['table'] . "`.`" . $condition[1]['field'] . "`";
                    } elseif (!empty($condition[1]['value'])) {
                        $select        .= " = ?";
                        $assign_data[] = $condition[1]['value'];
                    } else {
                        $select .= "IS NULL";
                    }
                }
                $select .= ") ";
            }
        }
        if (!empty($conditions)) {
            $condition_str = "";
            foreach ($conditions as $condition) {
                if ($condition_str != '') {
                    $condition_str .= " AND ";
                } else {
                    $condition_str .= "WHERE ";
                }

                if (!empty($condition['operator']) && strcasecmp('in', $condition['operator']) === 0) {
                    if (is_array($condition['value'])) {
                        $condition_str .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` IN (";
                        $first         = true;
                        foreach ($condition['value'] as $val) {
                            if (!$first) {
                                $condition_str .= ", ";
                            } else {
                                $first = false;
                            }
                            $condition_str .= "?";
                            $assign_data[] = $val;
                        }
                        $condition_str .= ") ";
                    }
                } else {
                    if (!empty($condition['operator']) && strcasecmp('like', $condition['operator']) === 0) {
                        $operator = 'LIKE';
                    } elseif (!empty($condition['operator']) && strcasecmp('!=', $condition['operator']) === 0) {
                        $operator = '!=';
                    } elseif (!empty($condition['operator']) && strcasecmp('<', $condition['operator']) === 0) {
                        $operator = '<';
                    } elseif (!empty($condition['operator']) && strcasecmp('<=', $condition['operator']) === 0) {
                        $operator = '<=';
                    } elseif (!empty($condition['operator']) && strcasecmp('>', $condition['operator']) === 0) {
                        $operator = '>';
                    } elseif (!empty($condition['operator']) && strcasecmp('>=', $condition['operator']) === 0) {
                        $operator = '>=';
                    } else {
                        $operator = '=';
                    }
                    if (is_array($condition['field'])) {
                        $condition_str .= "(";
                        $first         = true;
                        foreach ($condition['field'] as $fld) {
                            if (!$first) {
                                $condition_str .= " OR ";
                            } else {
                                $first = false;
                            }
                            if (!empty($condition['value'])) {
                                $condition_str .= "`" . $condition['table'] . "`.`" . $fld . "` " . $operator . " ?";
                                $assign_data[] = $condition['value'];
                            } else {
                                if (strcmp('!=', $operator) === 0) {
                                    $operator = 'IS NOT';
                                } else {
                                    $operator = 'IS';
                                }
                                $condition_str .= "`" . $condition['table'] . "`.`" . $fld . "` " . $operator . " NULL";
                            }
                        }
                        $condition_str .= ")";
                    } elseif (!is_null($condition['value'])) {
                        $condition_str .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` " .
                            $operator . " ?";
                        $assign_data[] = $condition['value'];
                    } else {
                        if (strcmp('!=', $operator) === 0) {
                            $operator = 'IS NOT';
                        } else {
                            $operator = 'IS';
                        }
                        $condition_str .= "`" . $condition['table'] . "`.`" . $condition['field'] . "` " .
                            $operator . " NULL";
                    }
                }
            }
            $select .= $condition_str . " ";
        }
        $sth = $this->query($select . ")  asd", $assign_data);
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $return = (int)$row['cnt'];
        }
        return $return;
    }

    /**
     * @param string    $table
     * @param int|array $id
     * @param array     $fields
     * @param array     $join
     *
     * @return array
     * @throws PDOException
     */
    public function getRecord(string $table, $id, array $fields = [], array $join = []): array
    {
        $select = "";
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($select != '') {
                    $select .= ', ';
                } else {
                    $select = "SELECT ";
                }
                $select .= "`" . $field['table'] . "`.`" . $field['field'] . "`";
            }
        } else {
            $select = "SELECT *";
        }
        $select .= " FROM `" . $table . "` ";
        if (!empty($join)) {
            foreach ($join as $j) {
                $select .= "JOIN `" . $j['table'] . "` ON (`" . $j['condition'][0]['table'] . "`.`" .
                    $j['condition'][0]['field'] . "` = `" . $j['condition'][1]['table'] . "`.`" .
                    $j['condition'][1]['field'] . "`) ";
            }
        }
        if (!is_array($id)) {
            $select .= "WHERE `" . $table . "`.`id` = ?";
            $id_sel = $id;
        } else {
            $select .= "WHERE `" . $table . "`.`" . $id['field'] . "` = ?";
            $id_sel = $id['value'];
        }

        $sth    = $this->query($select, [$id_sel]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param int    $id
     *
     * @return bool
     */
    public function deleteRecord(string $table, int $id): bool
    {
        if (is_array($id)) {
            $where   = "WHERE `" . $id['field'] . "` = ?";
            $whereId = $id['value'];
        } else {
            $where   = "WHERE `id` = ?";
            $whereId = $id;
        }

        // Save the deleted data to "trash can" aka deleted -table
        $recordData = $this->getRecord($table, $id);
        if (!empty($recordData['id'])) {
            $query = "DELETE ";
            $query .= "FROM `" . $table . "` " . $where;
            $this->query($query, [$whereId]);
            return true;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array  $data
     * @param int    $id
     *
     * @return int|null
     * @throws InvalidParametersException
     * @throws PDOException
     */
    public function saveArray(string $table, array $data, int $id = 0): ?int
    {
        if (!empty($table)) {
            if (!empty($data) && is_array($data)) {
                if (empty($id)) { // Saves a new record
                    $insert_fields     = '';
                    $insert_data       = '';
                    $insert_data_array = [];

                    // Statement input parameters
                    foreach ($data as $field => $value) {
                        if (!empty($insert_fields)) {
                            $insert_fields .= ', ';
                            $insert_data   .= ', ';
                        }
                        $insert_fields       .= '`' . str_replace('`', '', $field) . '`';
                        $insert_data         .= '?';
                        $insert_data_array[] = $value;
                    }

                    // Execute INSERT query
                    $query = "INSERT ";
                    $query .= "INTO `" . str_replace('`', '', $table) . "` (" . $insert_fields . ") VALUES (" .
                        $insert_data . ")";
                    $this->query($query, $insert_data_array);

                    // Return the inserted record ID
                    $return_id = 0;
                    foreach ($this->dbh->query("SELECT LAST_INSERT_ID() `id`") as $row) {
                        $return_id = $row['id'];
                    }
                    return $return_id;
                }

                // Updates an existing record
                $update_data       = '';
                $update_data_array = [];

                // Statement input parameters
                foreach ($data as $field => $value) {
                    if (!empty($update_data)) {
                        $update_data .= ', ';
                    }
                    $update_data         .= '`' . str_replace('`', '', $field) . '` = ?';
                    $update_data_array[] = $value;
                }
                $update_data_array[] = $id;

                // Execute UPDATE query
                $this->query(
                    "UPDATE `" . str_replace('`', '', $table) . "` SET " . $update_data . " WHERE id = ?",
                    $update_data_array
                );

                // Return the record ID
                return $id;
            } else {
                throw new InvalidParametersException('No data');
            }
        } else {
            throw new InvalidParametersException('No database table given');
        }
    }

    /**
     * Prepares and executes an SQL statement
     *
     * @param string $sql  This must be a valid SQL statement
     * @param array  $data An array of values with as many elements as there are bound parameters in the SQL statement
     *                     being executed.
     *
     * @return PDOStatement Returns the PDO Statement on success, false on failure
     * @throws PDOException
     */
    public function query(string $sql, array $data = []): \PDOStatement
    {
        $sth = $this->dbh->prepare($sql);

        if (!empty($data)) {
            foreach ($data as $k => $val) {
                if (is_int($val)) {
                    $sth->bindValue(($k + 1), $val, PDO::PARAM_INT);
                } else {
                    $sth->bindValue(($k + 1), $val);
                }
            }
        }
        $execute_res = $sth->execute();
        if (!$execute_res) {
            $error = $sth->errorInfo();
            throw new PDOException('SQL statement failed: ' . $error[1] . ' - ' . $error[2]);
        }
        return $sth;
    }
}