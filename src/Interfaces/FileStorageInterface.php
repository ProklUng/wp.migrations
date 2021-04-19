<?php

namespace Arrilot\BitrixMigrations\Interfaces;

interface FileStorageInterface
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function getMigrationFiles(string $path) : array;

    /**
     * Require a file.
     *
     * @param string $path
     *
     * @return void
     */
    public function requireFile(string $path) : void;

    /**
     * Create a directory if it does not exist.
     *
     * @param $dir
     *
     * @return void
     */
    public function createDirIfItDoesNotExist(string $dir) : void;

    /**
     * Get the content of a file.
     *
     * @param string $path
     *
     * @return string
     */
    public function getContent(string $path);

    /**
     * Write the contents of a file.
     *
     * @param string  $path
     * @param string  $contents
     * @param boolean $lock
     *
     * @return integer
     */
    public function putContent(string $path, string $contents, bool $lock = false);

    /**
     * Check if file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path);

    /**
     * Delete file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path);

    /**
     * Move file.
     *
     * @param string $path_from
     * @param string $path_to
     *
     * @return boolean
     */
    public function move(string $path_from, string $path_to) : bool;
}
