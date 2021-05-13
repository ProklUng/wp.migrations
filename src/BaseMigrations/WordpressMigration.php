<?php

namespace Arrilot\BitrixMigrations\BaseMigrations;

use Arrilot\BitrixMigrations\Interfaces\MigrationInterface;
use wpdb;

/**
 * Class WordpressMigration
 * @package Arrilot\BitrixMigrations\BaseMigrations
 */
class WordpressMigration implements MigrationInterface
{
    /**
     * DB connection.
     *
     * @var wpdb $db
     */
    protected $db;

    /**
     * @var boolean $use_transaction
     */
    public $use_transaction = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
    }

    /**
     * @inheritDoc
     */
    public function up()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function useTransaction(bool $default = false) : bool
    {
        if ($this->use_transaction) {
            return $this->use_transaction;
        }

        return $default;
    }

    /**
     * Get database collation.
     *
     * @return string
     */
    protected function get_collation() : string
    {
        if (!$this->db->has_cap('collation')) {
            return '';
        }

        return $this->db->get_charset_collate();
    }
}
