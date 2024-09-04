<?php

namespace App\Services\CustomTables;

use App\Components\TableComponent;
use App\core\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractTableService
{
    protected Environment $twig;

    protected Router $router;

    /**
     * @var array<int, array<string, mixed>> $columns
     */
    protected array $columns = [];

    /**
     * @var array<string, string> $columnMappings
     */
    protected array $columnMappings = [];

    public function __construct(Environment $twig, Router $router)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->initializeColumns();
    }

    protected function initializeColumns(): void
    {
        foreach ($this->columnMappings as $key => $title) {
            $this->columns[] = [
                'title' => $title,
                'key' => $key,
                'formatter' => $this->getColumnFormatter($key)
            ];
        }
    }

    protected function getColumnFormatter(string $key): ?callable
    {
        return null;
    }

    protected function createTableComponent(): TableComponent
    {
        // Passer le tableau complet des colonnes
        return new TableComponent($this->columns);
    }




    /**
     * @param array<int, array<string, string>> $rows
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    public function renderTable(array $rows): string
    {
        $table = $this->createTableComponent();
        foreach ($rows as $row) {
            $table->addRow($row);
        }

        return $table->render($this->twig);
    }
}
