<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Migrator;
use Exception;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class RollbackCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class RollbackCommand extends AbstractCommand
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
    protected static $defaultName = 'rollback';

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
        $this->setDescription('Rollback the last migration')
            ->addOption('hard', null, InputOption::VALUE_NONE, 'Rollback without running down()')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete migration file after rolling back');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     * @throws Exception
     */
    protected function fire() : int
    {
        $ran = $this->migrator->getRanMigrations();

        if (empty($ran)) {
            $this->message('Nothing to rollback');
            return 1;
        }

        $migration = (string)$ran[count($ran) - 1];

        $this->input->getOption('hard')
            ? $this->hardRollbackMigration($migration)
            : $this->rollbackMigration($migration);

        $this->deleteIfNeeded($migration);

        return 1;
    }

    /**
     * Call rollback.
     *
     * @param string $migration Миграция.
     *
     * @return void
     * @throws Exception
     */
    private function rollbackMigration(string $migration) : void
    {
        if ($this->migrator->doesMigrationFileExist($migration)) {
            $this->migrator->rollbackMigration($migration);
        } else {
            $this->markRolledBackWithConfirmation($migration);
        }

        $this->message("<info>Rolled back:</info> {$migration}.php");
    }

    /**
     * Call hard rollback.
     *
     * @param string $migration Миграция.
     *
     * @return void
     */
    private function hardRollbackMigration(string $migration) : void
    {
        $this->migrator->removeSuccessfulMigrationFromLog($migration);

        $this->message("<info>Rolled back with --hard:</info> {$migration}.php");
    }

    /**
     * Ask a user to confirm rolling back non-existing migration and remove it from log.
     *
     * @param string $migration Миграция.
     *
     * @return void
     */
    private function markRolledBackWithConfirmation(string $migration) : void
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("<error>Migration $migration was not found.\r\nDo you want to mark it as rolled back? (y/n)</error>\r\n", false);

        if (!$helper->ask($this->input, $this->output, $question)) {
            $this->abort();
        }

        $this->migrator->removeSuccessfulMigrationFromLog($migration);
    }

    /**
     * Delete migration file if options is set.
     *
     * @param string $migration
     *
     * @return void
     * @throws Exception
     */
    private function deleteIfNeeded(string $migration) : void
    {
        if (!$this->input->getOption('delete')) {
            return;
        }

        if ($this->migrator->deleteMigrationFile($migration)) {
            $this->message("<info>Deleted:</info> {$migration}.php");
        }
    }
}
