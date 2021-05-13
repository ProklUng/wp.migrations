<?php

namespace Arrilot\BitrixMigrations\Commands;

use DomainException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * Configures the current command.
     *
     * @param string $message
     *
     * @return void
     */
    protected function abort(string $message = '') : void
    {
        if ($message) {
            $this->error($message);
        }

        $this->error('Abort!');

        throw new DomainException();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return string|integer null or 0 if everything went fine, or an error code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        try {
            return $this->fire();
        } catch (DomainException $e) {
            return 1;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->error('Abort!');

            return $e->getCode();
        }
    }

    /**
     * Echo an error message.
     *
     * @param string $message
     *
     * @return void
     */
    protected function error(string $message) : void
    {
        $this->output->writeln("<error>{$message}</error>");
    }

    /**
     * Echo an info.
     *
     * @param string $message
     *
     * @return void
     */
    protected function info(string $message) : void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    /**
     * Echo a message.
     *
     * @param string $message
     *
     * @return void
     */
    protected function message(string $message) : void
    {
        $this->output->writeln("{$message}");
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    abstract protected function fire() : int;
}
