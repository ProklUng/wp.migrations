<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Migrator;
use Exception;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ArchiveCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class ArchiveCommand extends AbstractCommand
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
    protected static $defaultName = 'archive';

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
        $this->setDescription('Move migration into archive')
            ->addOption('without', 'w', InputOption::VALUE_REQUIRED, 'Archive without last N migration');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     * @throws Exception
     */
    protected function fire() : int
    {
        $files = $this->migrator->getAllMigrations();
        $without = (int)$this->input->getOption('without') ?: 0;
        if ($without > 0) {
            $files = array_slice($files, 0, $without * -1);
        }

        $count = $this->migrator->moveMigrationFiles($files);

        if ($count) {
            $this->message("<info>Moved to archive:</info> {$count}");
        } else {
            $this->info('Nothing to move');
        }

        return 1;
    }
}
