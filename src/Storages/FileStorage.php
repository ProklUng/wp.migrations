<?php

namespace Arrilot\BitrixMigrations\Storages;

use Arrilot\BitrixMigrations\Helpers;
use Arrilot\BitrixMigrations\Interfaces\FileStorageInterface;
use Exception;

/**
 * Class FileStorage
 * @package Arrilot\BitrixMigrations\Storages
 */
class FileStorage implements FileStorageInterface
{
    /**
     * @inheritDoc
     */
    public function getMigrationFiles(string $path) : array
    {
        $files = Helpers::rGlob($path.'/*_*.php');

        if (!$files) {
            return [];
        }

        $files = array_map(function (string $file) : string {
            return str_replace('.php', '', basename($file));

        }, $files);

        sort($files);

        return $files;
    }

    /**
     * @inheritDoc
     */
    public function requireFile(string $path) : void
    {
        require_once $path;
    }

    /**
     * @inheritDoc
     */
    public function createDirIfItDoesNotExist(string $dir) : void
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getContent(string $path) : string
    {
        if (!file_exists($path)) {
            throw new Exception("File does not exist at path {$path}");
        }

        return (string)file_get_contents($path);
    }

    /**
     * @inheritDoc
     */
    public function putContent(string $path, string $contents, bool $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * @inheritDoc
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * @inheritDoc
     */
    public function delete($path)
    {
        return $this->exists($path) ? unlink($path) : false;
    }

    /**
     * @inheritDoc
     */
    public function move(string $path_from, string $path_to) : bool
    {
        return $this->exists($path_from) ? rename($path_from, $path_to) : false;
    }
}
