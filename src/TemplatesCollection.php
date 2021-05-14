<?php

namespace Arrilot\BitrixMigrations;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class TemplatesCollection
 * @package Arrilot\BitrixMigrations
 */
class TemplatesCollection
{
    /**
     * @var string $dir Path to directory where basic templates are.
     */
    private $dir;

    /**
     * Array of available migration file templates.
     *
     * @var array $templates Шаблоны.
     */
    private $templates = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dir = dirname(__DIR__).'/templates';

        $this->registerTemplate([
            'name'        => 'default',
            'path'        => $this->dir.'/default.template',
            'description' => 'Default migration template',
        ]);
    }

    /**
     * Register basic templates.
     *
     * @return void
     */
    public function registerBasicTemplates() : void
    {
        $templates = [
            [
                'name'        => 'add_table',
                'path'        => $this->dir.'/add_table.template',
                'description' => 'Create table',
                'aliases'     => [
                    'create_table',
                ],
            ],
            [
                'name'        => 'delete_table',
                'path'        => $this->dir.'/delete_table.template',
                'description' => 'Drop table',
                'aliases'     => [
                    'drop_table',
                ],
            ],
            [
                'name'        => 'query',
                'path'        => $this->dir.'/query.template',
                'description' => 'Simple database query',
            ],
        ];

        foreach ($templates as $template) {
            $this->registerTemplate($template);
        }
    }

    /**
     * Getter for registered templates.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->templates;
    }

    /**
     * Dynamically register migration template.
     *
     * @param array $template Шаблоны.
     *
     * @return void
     */
    public function registerTemplate(array $template) : void
    {
        $template = $this->normalizeTemplateDuringRegistration($template);

        $this->templates[(string)$template['name']] = $template;

        $this->registerTemplateAliases($template, (array)$template['aliases']);
    }

    /**
     * Path to the file where a template is located.
     *
     * @param string $name Название шаблона.
     *
     * @return string
     */
    public function getTemplatePath(string $name) : string
    {
        return (string)$this->templates[$name]['path'];
    }

    /**
     * Find out template name from user input.
     *
     * @param string|null $template Шаблон.
     *
     * @return string
     */
    public function selectTemplate(?string $template) : string
    {
        if (!$template) {
            return 'default';
        }

        if (!array_key_exists($template, $this->templates)) {
            throw new RuntimeException("Template \"{$template}\" is not registered");
        }

        return $template;
    }

    /**
     * Check template fields and normalize them.
     *
     * @param array $template Информация о шаблоне.
     *
     * @return array
     */
    protected function normalizeTemplateDuringRegistration(array $template) : array
    {
        if (empty($template['name'])) {
            throw new InvalidArgumentException('Impossible to register a template without "name"');
        }

        if (empty($template['path'])) {
            throw new InvalidArgumentException('Impossible to register a template without "path"');
        }

        $template['description'] = $template['description'] ?? '';
        $template['aliases'] = $template['aliases'] ?? [];
        $template['is_alias'] = false;

        return $template;
    }

    /**
     * Register template aliases.
     *
     * @param array $template Информация о шаблоне.
     * @param array $aliases  Aliases.
     *
     * @return void
     */
    protected function registerTemplateAliases(array $template, array $aliases = []) : void
    {
        /** @var string $alias */
        foreach ($aliases as $alias) {
            $template['is_alias'] = true;
            $template['name'] = $alias;
            $template['aliases'] = [];

            $this->templates[$template['name']] = $template;
        }
    }
}
