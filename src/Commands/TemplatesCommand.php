<?php

namespace Arrilot\BitrixMigrations\Commands;

use Arrilot\BitrixMigrations\TemplatesCollection;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class TemplatesCommand
 * @package Arrilot\BitrixMigrations\Commands
 */
class TemplatesCommand extends AbstractCommand
{
    /**
     * TemplatesCollection instance.
     *
     * @var TemplatesCollection
     */
    protected $collection;

    /**
     * @var string|null $defaultName Команда.
     */
    protected static $defaultName = 'templates';

    /**
     * Constructor.
     *
     * @param TemplatesCollection $collection Коллекция шаблонов.
     * @param string|null         $name       Команда.
     */
    public function __construct(TemplatesCollection $collection, ?string $name = null)
    {
        $this->collection = $collection;

        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Show the list of available migration templates');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    protected function fire() : int
    {
        $table = new Table($this->output);
        $table->setHeaders(['Name', 'Path', 'Description'])->setRows($this->collectRows());
        $table->setStyle('borderless');
        $table->render();

        return 1;
    }

    /**
     * Collect and return templates from a Migrator.
     *
     * @return array
     */
    private function collectRows() : array
    {
        $rows = collect($this->collection->all())
            ->filter(function (array $template) : bool {
                return $template['is_alias'] === false;
            })
            ->sortBy('name')
            ->map(function (array $template) : array {
                $row = [];

                $names = array_merge([$template['name']], (array)$template['aliases']);
                $row[] = implode("\n/ ", $names);
                $row[] = wordwrap((string)$template['path'], 65, "\n", true);
                $row[] = wordwrap((string)$template['description'], 25, "\n", true);

                return $row;
            });

        return $this->separateRows($rows);
    }

    /**
     * Separate rows with a separator.
     *
     * @param Collection $templates Шаблоны.
     *
     * @return array
     */
    private function separateRows(Collection $templates) : array
    {
        $rows = [];
        foreach ($templates as $template) {
            $rows[] = $template;
            $rows[] = new TableSeparator();
        }
        unset($rows[count($rows) - 1]);

        return $rows;
    }
}
