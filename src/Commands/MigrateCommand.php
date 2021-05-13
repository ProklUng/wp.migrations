<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Migrator;
use Exception;

/**
 * Class MigrateCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class MigrateCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator $migrator
     */
    protected $migrator;

    /**
     * @var string|null $defaultName Команда.
     */
    protected static $defaultName = 'migrate';

    /**
     * Constructor.
     *
     * @param Migrator    $migrator Мигратор.
     * @param string|null $name     Команда.
     */
    public function __construct(Migrator $migrator, $name = null)
    {
        $this->migrator = $migrator;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run all outstanding migrations');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     * @throws Exception
     */
    protected function fire() : int
    {
        $toRun = $this->migrator->getMigrationsToRun();

        if (!empty($toRun)) {
            foreach ($toRun as $migration) {
                /**
                 * @var string $migration
                 */
                $this->migrator->runMigration($migration);
                $this->message("<info>Migrated:</info> {$migration}.php");
            }
        } else {
            $this->info('Nothing to migrate');
        }

        return 1;
    }
}
