<?php

namespace Arrilot\BitrixMigrations;

use Arrilot\BitrixMigrations\Interfaces\DatabaseStorageInterface;
use Arrilot\BitrixMigrations\Interfaces\FileStorageInterface;
use Arrilot\BitrixMigrations\Interfaces\MigrationInterface;
use Arrilot\BitrixMigrations\Storages\FileStorage;
use Arrilot\BitrixMigrations\Storages\WordpressDatabaseStorage;
use Exception;

class Migrator
{
    /**
     * Migrator configuration array.
     *
     * @var array $config
     */
    protected $config;

    /**
     * Directory to store m.
     *
     * @var string $dir
     */
    protected $dir;

    /**
     * Directory to store archive m.
     *
     * @var string $dir_archive
     */
    protected $dir_archive;

    /**
     * User transaction default.
     *
     * @var boolean $use_transaction
     */
    protected $use_transaction;

    /**
     * Files interactions.
     *
     * @var FileStorageInterface $files
     */
    protected $files;

    /**
     * Interface that gives us access to the database.
     *
     * @var DatabaseStorageInterface $database
     */
    protected $database;

    /**
     * TemplatesCollection instance.
     *
     * @var TemplatesCollection $templates
     */
    protected $templates;

    /**
     * Constructor.
     *
     * @param array                         $config    Конфигурация.
     * @param TemplatesCollection           $templates Шаблоны.
     * @param DatabaseStorageInterface|null $database  БД.
     * @param FileStorageInterface|null     $files     Файлы.
     */
    public function __construct($config, TemplatesCollection $templates, DatabaseStorageInterface $database = null, FileStorageInterface $files = null)
    {
        $this->config = $config;
        $this->dir = $config['dir'];
        $this->dir_archive = isset($config['dir_archive']) ? $config['dir_archive'] : 'archive';
        $this->use_transaction = isset($config['use_transaction']) ? $config['use_transaction'] : false;

//        if (isset($config['default_fields']) && is_array($config['default_fields'])) {
//            foreach ($config['default_fields'] as $class => $default_fields) {
//
//            }
//        }

        $this->templates = $templates;
        $this->database = $database ?: new WordpressDatabaseStorage($config['table']);
        $this->files = $files ?: new FileStorage();
    }

    /**
     * Create migration file.
     *
     * @param string $name         Migration name.
     * @param string $templateName Название шаблона.
     * @param array  $replace      Array of placeholders that should be replaced with a given values.
     * @param string $subDir       Поддиректория.
     *
     * @return string
     * @throws Exception
     */
    public function createMigration($name, $templateName, array $replace = [], $subDir = '') : string
    {
        $targetDir = $this->dir;
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        if ($subDir) {
            $targetDir .= '/' . $subDir;
        }

        $this->files->createDirIfItDoesNotExist($targetDir);

        $fileName = $this->constructFileName($name);
        $className = $this->getMigrationClassNameByFileName($fileName);
        $templateName = $this->templates->selectTemplate($templateName);

        $template = $this->files->getContent($this->templates->getTemplatePath($templateName));
        $template = $this->replacePlaceholdersInTemplate($template, array_merge($replace, ['className' => $className]));

        $this->files->putContent($targetDir.'/'.$fileName.'.php', $template);

        return $fileName;
    }

    /**
     * Run all migrations that were not run before.
     *
     * @return array
     * @throws Exception
     */
    public function runMigrations() : array
    {
        $migrations = $this->getMigrationsToRun();
        $ran = [];

        if (count($migrations) === 0) {
            return $ran;
        }

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Run a given migration.
     *
     * @param string $file Файл.
     *
     * @throws Exception
     *
     * @return void
     */
    public function runMigration(string $file) : void
    {
        $migration = $this->getMigrationObjectByFileName($file);
        $this->checkTransactionAndRun($migration,
            function () use ($migration, $file) {
                if ($migration->up() === false) {
                    throw new Exception("Migration up from {$file}.php returned false");
                }
            });

        $this->logSuccessfulMigration($file);
    }

    /**
     * Log successful migration.
     *
     * @param string $migration Миграция.
     *
     * @return void
     */
    public function logSuccessfulMigration($migration) : void
    {
        $this->database->logSuccessfulMigration($migration);
    }

    /**
     * Get ran migrations.
     *
     * @return array
     */
    public function getRanMigrations() : array
    {
        return $this->database->getRanMigrations();
    }

    /**
     * Get all migrations.
     *
     * @return array
     */
    public function getAllMigrations() : array
    {
        return $this->files->getMigrationFiles($this->dir);
    }

    /**
     * Determine whether migration file for migration exists.
     *
     * @param string $migration Миграция.
     *
     * @return boolean
     * @throws Exception
     */
    public function doesMigrationFileExist(string $migration) : bool
    {
        return $this->files->exists($this->getMigrationFilePath($migration));
    }

    /**
     * Rollback a given migration.
     *
     * @param string $file Файл с миграцией.
     *
     * @throws Exception
     *
     * @return void
     */
    public function rollbackMigration(string $file) : void
    {
        $migration = $this->getMigrationObjectByFileName($file);

        $this->checkTransactionAndRun($migration, function () use ($migration, $file) {
            if ($migration->down() === false) {
                throw new Exception("<error>Can't rollback migration:</error> {$file}.php");
            }
        });

        $this->removeSuccessfulMigrationFromLog($file);
    }

    /**
     * Remove a migration name from the database so it can be run again.
     *
     * @param string $file Файл с миграцией.
     *
     * @return void
     */
    public function removeSuccessfulMigrationFromLog(string $file) : void
    {
        $this->database->removeSuccessfulMigrationFromLog($file);
    }

    /**
     * Delete migration file.
     *
     * @param string $migration Миграция.
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteMigrationFile($migration) : bool
    {
        return $this->files->delete($this->getMigrationFilePath($migration));
    }

    /**
     * Get array of migrations that should be ran.
     *
     * @return array
     */
    public function getMigrationsToRun() : array
    {
        $allMigrations = $this->getAllMigrations();
        $ranMigrations = $this->getRanMigrations();

        return array_diff($allMigrations, $ranMigrations);
    }

    /**
     * Move migration files.
     *
     * @param array  $files Файлы.
     * @param string $toDir Директория, куда переместить миграции.
     *
     * @return integer
     * @throws Exception
     */
    public function moveMigrationFiles(array $files = [], string $toDir = '') : int
    {
        $toDir = trim($toDir ?: $this->dir_archive, '/');
        $files = $files ?: $this->getAllMigrations();
        $this->files->createDirIfItDoesNotExist("$this->dir/$toDir");

        $count = 0;
        foreach ($files as $migration) {
            $from = $this->getMigrationFilePath($migration);
            $to = "$this->dir/$toDir/$migration.php";

            if ($from == $to) {
                continue;
            }

            $flag = $this->files->move($from, $to);

            if ($flag) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Construct migration file name from migration name and current time.
     *
     * @param string $name Название миграции.
     *
     * @return string
     */
    protected function constructFileName(string $name) : string
    {
        list($usec, $sec) = explode(' ', microtime());

        $usec = substr($usec, 2, 6);

        return date('Y_m_d_His', $sec).'_'.$usec.'_'.$name;
    }

    /**
     * Get a migration class name by a migration file name.
     *
     * @param string $file Файл с миграцией.
     *
     * @return string
     */
    protected function getMigrationClassNameByFileName(string $file) : string
    {
        $fileExploded = explode('_', $file);

        $datePart = implode('_', array_slice($fileExploded, 0, 5));
        $namePart = implode('_', array_slice($fileExploded, 5));

        return Helpers::studly($namePart.'_'.$datePart);
    }

    /**
     * Replace all placeholders in the stub.
     *
     * @param string $template Шаблон.
     * @param array  $replace  Замены.
     *
     * @return string
     */
    protected function replacePlaceholdersInTemplate(string $template, array $replace) : string
    {
        foreach ($replace as $placeholder => $value) {
            $template = str_replace("__{$placeholder}__", $value, $template);
        }

        return $template;
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param string $file Файл с миграцией.
     *
     * @throws Exception
     *
     * @return MigrationInterface
     */
    protected function getMigrationObjectByFileName(string $file) : MigrationInterface
    {
        $class = $this->getMigrationClassNameByFileName($file);

        $this->requireMigrationFile($file);

        $object = new $class();

        if (!$object instanceof MigrationInterface) {
            throw new Exception("Migration class {$class} must implement Arrilot\\BitrixMigrations\\Interfaces\\MigrationInterface");
        }

        return $object;
    }

    /**
     * Require migration file.
     *
     * @param string $file Файл с миграцией.
     *
     * @return void
     * @throws Exception
     */
    protected function requireMigrationFile(string $file) : void
    {
        $this->files->requireFile($this->getMigrationFilePath($file));
    }

    /**
     * Get path to a migration file.
     *
     * @param string $migration Миграция.
     *
     * @return string
     * @throws Exception
     */
    protected function getMigrationFilePath(string $migration) : string
    {
        $files = Helpers::rGlob("$this->dir/$migration.php");
        if (count($files) != 1) {
            throw new Exception("Not found migration file");
        }

        return $files[0];
    }

    /**
     * @param MigrationInterface $migration Миграция.
     * @param callable           $callback  Callback.
     *
     * @return void
     * @throws Exception
     */
    protected function checkTransactionAndRun(MigrationInterface $migration, $callback) : void
    {
        if ($migration->useTransaction($this->use_transaction)) {
            $this->database->startTransaction();
            //Logger::log("Начало транзакции", Logger::COLOR_LIGHT_BLUE);
            try {
                $callback();
            } catch (Exception $e) {
                $this->database->rollbackTransaction();
                Logger::log("Откат транзакции из-за ошибки '{$e->getMessage()}'", Logger::COLOR_LIGHT_RED);
                throw $e;
            }
            $this->database->commitTransaction();
            // Logger::log("Конец транзакции", Logger::COLOR_LIGHT_BLUE);
        } else {
            $callback();
        }
    }
}
