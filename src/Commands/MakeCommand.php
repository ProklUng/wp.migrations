<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\Migrator;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class MakeCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected static $defaultName = 'make';

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
        $this->setDescription('Create a new migration file')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the migration'
            )
            ->addOption(
                'template',
                't',
                InputOption::VALUE_REQUIRED,
                'Migration template'
            )
            ->addOption(
                'directory',
                'd',
                InputOption::VALUE_REQUIRED,
                'Migration directory'
            );
    }

    /**
     * Execute the console command.
     *
     * @return integer
     * @throws Exception
     */
    protected function fire() : int
    {
        $migration = $this->migrator->createMigration(
            $this->input->getArgument('name'),
            (string)$this->input->getOption('template'),
            [],
            (string)$this->input->getOption('directory')
        );

        $this->message("<info>Migration created:</info> {$migration}.php");

        return 1;
    }
}
