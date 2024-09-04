<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TableComponent
{
    /**
     * @var array<array<string, mixed>>
     */
    private array $columns = [];

    /**
     * @var array<array<string, string>>
     */
    private array $rows = [];

    /**
     * @param array<array<string, mixed>> $columns
     */
    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * @param array<string, string> $row
     * @return void
     */
    public function addRow(array $row): void
    {
        $this->rows[] = $row;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<array<string, string>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param Environment $twig
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    public function render(Environment $twig): string
    {
        return $twig->render('components/tables/table.html.twig', [
            'columns' => $this->getColumns(),
            'rows' => $this->getRows()
        ]);
    }
}
