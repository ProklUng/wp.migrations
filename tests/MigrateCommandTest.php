<?php

namespace Arrilot\BitrixMigrations\Tests;

use Mockery as m;

/**
 * Class MigrateCommandTest
 * @package Arrilot\BitrixMigrations\Tests
 */
class MigrateCommandTest extends CommandTestCase
{
    /**
     * @return void
     */
    public function testItMigratesNothingIfThereIsNoOutstandingMigrations()
    {
        $migrator = m::mock('Arrilot\BitrixMigrations\Migrator');
        $migrator->shouldReceive('getMigrationsToRun')->once()->andReturn([]);
        $migrator->shouldReceive('runMigration')->never();

        $command = $this->mockCommand($migrator);
        $command->shouldReceive('info')->with('Nothing to migrate')->once();

        $result = $this->runCommand($command);

        $this->assertSame(1, $result);
    }

    /**
     * @return void
     */
    public function testItMigratesOutstandingMigrations()
    {
        $migrator = m::mock('Arrilot\BitrixMigrations\Migrator');
        $migrator->shouldReceive('getMigrationsToRun')->once()->andReturn([
            '2021_01_26_162220_bar',
        ]);
        $migrator->shouldReceive('runMigration')->with('2021_01_26_162220_bar')->once();

        $command = $this->mockCommand($migrator);
        $command->shouldReceive('message')->with('<info>Migrated:</info> 2021_01_26_162220_bar.php')->once();
        $command->shouldReceive('info')->with('Nothing to migrate')->never();

        $result = $this->runCommand($command);

        $this->assertSame(1, $result);
    }

    /**
     * @param $migrator
     * @return mixed
     */
    protected function mockCommand($migrator)
    {
        return m::mock('Arrilot\BitrixMigrations\Commands\MigrateCommand[abort, info, message, getMigrationObjectByFileName]', [$migrator])
            ->shouldAllowMockingProtectedMethods();
    }
}