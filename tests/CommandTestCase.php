<?php

namespace Arrilot\BitrixMigrations\Tests;

use Exception;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class CommandTestCase
 * @package Arrilot\BitrixMigrations\Tests
 */
abstract class CommandTestCase extends BaseTestCase
{
    /**
     * @param Command $command Команда.
     * @param array   $input
     *
     * @return mixed
     * @throws Exception
     */
    protected function runCommand(Command $command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput());
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return [
            'table' => 'migrations',
            'dir'   => 'migrations',
        ];
    }
}
