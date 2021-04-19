<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface;

/**
 * Class InstallCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class InstallCommand extends AbstractCommand
{
    /**
     * Interface that gives us access to the database.
     *
     * @var DatabaseStorageInterface
     */
    protected $database;

    /**
     * Table in DB to store migrations that have been already run.
     *
     * @var string
     */
    protected $table;

    /**
     * @var string $defaultName Команда.
     */
    protected static $defaultName = 'install';

    /**
     * Constructor.
     *
     * @param string                   $table    Таблица.
     * @param DatabaseStorageInterface $database Операции с базой.
     * @param string|null              $name     Команда.
     */
    public function __construct(string $table, DatabaseStorageInterface $database, $name = null)
    {
        $this->table = $table;
        $this->database = $database;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Create the migration database table');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    protected function fire() : int
    {
        if ($this->database->checkMigrationTableExistence()) {
            $this->abort("Table \"{$this->table}\" already exists");
        }

        $this->database->createMigrationTable();

        $this->info('Migration table has been successfully created!');

        return 1;
    }
}
