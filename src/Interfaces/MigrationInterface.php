<?php

namespace Arrilot\BitrixMigrations\Interfaces;

/**
 * Interface MigrationInterface
 * @package Arrilot\BitrixMigrations\Interfaces
 */
interface MigrationInterface
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up();

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down();

    /**
     * Use transaction.
     *
     * @param boolean $default
     *
     * @return boolean
     */
    public function useTransaction(bool $default = false) : bool;
}
