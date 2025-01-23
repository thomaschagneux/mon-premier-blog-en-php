<?php

namespace App\Components;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TableComponent
 * Represents a table component with configurable columns and rows for rendering in Twig.
 */
class TableComponent
{
    /**
     * @var array<array<string, mixed>> The configuration of table columns.
     */
    private array $columns = [];

    /**
     * @var array<array<string, string>> The data rows of the table.
     */
    private array $rows = [];

    /**
     * TableComponent constructor.
     *
     * @param array<array<string, mixed>> $columns The configuration for the table columns.
     */
    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * Adds a row of data to the table.
     *
     * @param array<string, string> $row A single row of data with key-value pairs.
     *
     * @return void
     */
    public function addRow(array $row): void
    {
        $this->rows[] = $row;
    }

    /**
     * Gets the configured columns of the table.
     *
     * @return array<array<string, mixed>> An array of column configurations.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Gets the rows of data in the table.
     *
     * @return array<array<string, string>> An array of rows, each represented as key-value pairs.
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Renders the table using the provided Twig environment.
     *
     * @param Environment $twig The Twig environment instance.
     *
     * @throws LoaderError   If the template cannot be found.
     * @throws RuntimeError  If an error occurs during rendering.
     * @throws SyntaxError   If there is a syntax error in the template.
     *
     * @return string The rendered HTML of the table.
     */
    public function render(Environment $twig): string
    {
        return $twig->render('components/tables/table.html.twig', [
            'columns' => $this->getColumns(),
            'rows'    => $this->getRows(),
        ]);
    }
}
