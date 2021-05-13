<?php

namespace Arrilot\BitrixMigrations\Interfaces;

/**
 * Interface DatabaseStorageInterface
 * @package Arrilot\BitrixMigrations\Interfaces
 */
interface DatabaseStorageInterface
{
    /**
     * Check if a given table already exists.
     *
     * @return boolean
     */
    public function checkMigrationTableExistence() : bool;

    /**
     * Create migration table.
     *
     * @return void
     */
    public function createMigrationTable() : void;

    /**
     * Get an array of migrations that have been ran previously.
     * Must be ordered by order asc.
     *
     * @return array
     */
    public function getRanMigrations() : array;

    /**
     * Save a migration name to the database to prevent it from running again.
     *
     * @param string $name
     *
     * @return void
     */
    public function logSuccessfulMigration(string $name);

    /**
     * Remove a migration name from the database so it can be run again.
     *
     * @param string $name
     *
     * @return void
     */
    public function removeSuccessfulMigrationFromLog(string $name);

    /**
     * Start transaction.
     *
     * @return void
     */
    public function startTransaction();

    /**
     * Commit transaction
     *
     * @return void
     */
    public function commitTransaction();

    /**
     * Rollback transaction.
     *
     * @return void
     */
    public function rollbackTransaction();
}
