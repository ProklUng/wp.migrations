<?php

namespace Arrilot\BitrixMigrations\Tests;

use Arrilot\BitrixMigrations\Migrator;
use Arrilot\BitrixMigrations\TemplatesCollection;
use Exception;
use Mockery;

/**
 * Class MigratorTest
 * @package Arrilot\BitrixMigrations\Tests
 */
class MigratorTest extends CommandTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testItCreatesMigration() : void
    {
        $database = $this->mockDatabase();
        $files = $this->mockFiles();

        $files->shouldReceive('createDirIfItDoesNotExist')->once();
        $files->shouldReceive('getContent')->once()->andReturn('some content');
        $files->shouldReceive('putContent')->once()->andReturn(1000);

        $migrator = $this->createMigrator($database, $files);

        $this->assertMatchesRegularExpression(
            '/[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[0-9]{6}_test_migration/',
            $migrator->createMigration('test_migration', '')
        );
    }

    /**
     * Create migrator.
     *
     * @param $database
     * @param $files
     *
     * @return Migrator
     */
    protected function createMigrator($database, $files) : Migrator
    {
        $config = [
            'table'        => 'migrations',
            'dir'          => 'migrations',
        ];

        $templatesCollection = new TemplatesCollection();
        $templatesCollection->registerBasicTemplates();

        return new Migrator($config, $templatesCollection, $database, $files);
    }

    /**
     * @return mixed
     */
    protected function mockDatabase()
    {
        return Mockery::mock('Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface');
    }

    /**
     * @return mixed
     */
    protected function mockFiles()
    {
        return Mockery::mock('Arrilot\BitrixMigrations\Interfaces\FileStorageInterface');
    }
}