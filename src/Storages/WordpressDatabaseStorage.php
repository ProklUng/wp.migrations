<?php

namespace Arrilot\BitrixMigrations\Storages;

use Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface;
use wpdb;

/**
 * Class WordpressDatabaseStorage
 * @package Arrilot\BitrixMigrations\Storages
 */
class WordpressDatabaseStorage implements DatabaseStorageInterface
{
    /**
     * Wordpress $DB object.
     *
     * @var wpdb $db
     */
    private $db;

    /**
     * Table in DB to store migrations that have been already ran.
     *
     * @var string $table
     */
    private $table;

    /**
     * WordpressDatabaseStorage constructor.
     *
     * @param string $table Таблица.
     */
    public function __construct(string $table)
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->table = $table;
    }

    /**
     * Check if a given table already exists.
     *
     * @return boolean
     */
    public function checkMigrationTableExistence() : bool
    {
        return (bool)$this->db->query('SHOW TABLES LIKE "'.$this->table.'"');
    }

    /**
     * Create migration table.
     *
     * @return void
     */
    public function createMigrationTable() : void
    {
        $this->db->query("CREATE TABLE {$this->table} (ID INT NOT NULL AUTO_INCREMENT, MIGRATION VARCHAR(255) NOT NULL, PRIMARY KEY (ID))");
    }

    /**
     * Get an array of migrations the have been ran previously.
     * Must be ordered by order asc.
     *
     * @return array
     */
    public function getRanMigrations() : array
    {
        $migrations = [];

        $dbRes = $this->db->get_results("SELECT MIGRATION FROM {$this->table} ORDER BY ID ASC");
        foreach ($dbRes as $result) {
            $migrations[] = $result->MIGRATION;
        }

        return $migrations;
    }

    /**
     * Save migration name to the database to prevent it from running again.
     *
     * @param string $name Миграция.
     *
     * @return void
     */
    public function logSuccessfulMigration(string $name)
    {
        $this->db->insert($this->table, [
            'MIGRATION' => $name,
        ]);
    }

    /**
     * Remove a migration name from the database so it can be run again.
     *
     * @param string $name Миграция.
     *
     * @return void
     */
    public function removeSuccessfulMigrationFromLog(string $name)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE MIGRATION = '".$name."'");
    }

    /**
     * @inheritDoc
     */
    public function startTransaction() : void
    {
        $this->db->query('START TRANSACTION');
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction()
    {
        $this->db->query('COMMIT');
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction()
    {
        $this->db->query('ROLLBACK');
    }
}
