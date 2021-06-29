<?php

namespace Arrilot\BitrixMigrations\Tests;

use Mockery as m;

/**
 * Class InstallCommandTest
 * @package Arrilot\BitrixMigrations\Tests
 */
class InstallCommandTest extends CommandTestCase
{
    /**
     * @param $database
     * @return mixed
     */
    protected function mockCommand($database)
    {
        return m::mock('Arrilot\BitrixMigrations\Commands\InstallCommand[abort]', ['migrations', $database])
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * @return void
     */
    public function testItCreatesMigrationTable() : void
    {
        $database = m::mock('Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface');
        $database->shouldReceive('checkMigrationTableExistence')->once()->andReturn(false);
        $database->shouldReceive('createMigrationTable')->once();

        $command = $this->mockCommand($database);

        $result = $this->runCommand($command);

        $this->assertSame(1, $result);
    }

    /**
     * @return void
     */
    public function testItDoesNotCreateATableIfItExists() : void
    {
        $database = m::mock('Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface');
        $database->shouldReceive('checkMigrationTableExistence')->once()->andReturn(true);
        $database->shouldReceive('createMigrationTable')->never();

        $command = $this->mockCommand($database);
        $command->shouldReceive('abort')->once()->andThrow('DomainException');

        $result = $this->runCommand($command);

        $this->assertSame(1, $result);
    }
}