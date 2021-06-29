<?php

namespace Arrilot\BitrixMigrations\Tests;

use Mockery as m;

/**
 * Class MakeCommandTest
 * @package Arrilot\BitrixMigrations\Tests
 */
class MakeCommandTest extends CommandTestCase
{
    /**
     * @return void
     */
    public function testItCreatesAMigrationFile()
    {
        $migrator = m::mock('Arrilot\BitrixMigrations\Migrator');
        $migrator->shouldReceive('createMigration')->once()->andReturn('2021_01_26_162220_bar');

        $command = $this->mockCommand($migrator);
        $command->shouldReceive('message')->once();

        $result = $this->runCommand($command, ['name' => 'test_migration']);

        $this->assertSame(1, $result);
    }

    /**
     * @param $migrator
     * @return mixed
     */
    protected function mockCommand($migrator)
    {
        return m::mock('Arrilot\BitrixMigrations\Commands\MakeCommand[abort, info, message, getMigrationObjectByFileName]', [$migrator])
            ->shouldAllowMockingProtectedMethods();
    }
}