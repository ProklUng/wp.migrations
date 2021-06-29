<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Migrator;

/**
 * Class StatusCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class StatusCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var string|null $defaultName Команда.
     */
    protected static $defaultName = 'status';

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
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Show status about last migrations');
    }

    /**
     * @inheritDoc
     *
     * @return integer
     */
    protected function fire() : int
    {
        $this->showOldMigrations();

        $this->output->write("\r\n");

        $this->showNewMigrations();

        return 1;
    }

    /**
     * Show old migrations.
     *
     * @return void
     */
    private function showOldMigrations() : void
    {
        $old = collect($this->migrator->getRanMigrations());

        $this->output->writeln("<fg=yellow>Old migrations:\r\n</>");

        $max = 5;
        if ($old->count() > $max) {
            $this->output->writeln('<fg=yellow>...</>');

            $old = $old->take(-$max);
        }

        foreach ($old as $migration) {
            $this->output->writeln("<fg=yellow>{$migration}.php</>");
        }
    }

    /**
     * Show new migrations.
     *
     * @return void
     */
    private function showNewMigrations() : void
    {
        $new = collect($this->migrator->getMigrationsToRun());

        $this->output->writeln("<fg=green>New migrations:\r\n</>");

        foreach ($new as $migration) {
            $this->output->writeln("<fg=green>{$migration}.php</>");
        }
    }
}
