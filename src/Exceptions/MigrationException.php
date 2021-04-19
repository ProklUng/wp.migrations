<?php

namespace Arrilot\BitrixMigrations\Exceptions;

use Exception;

/**
 * Class MigrationException
 * @package Arrilot\BitrixMigrations\Exceptions
 */
class MigrationException extends Exception
{
    protected $code = 1;
}
