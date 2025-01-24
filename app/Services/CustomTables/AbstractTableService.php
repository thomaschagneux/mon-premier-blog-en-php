<?php

namespace App\Services\CustomTables;

use App\Components\TableComponent;
use App\core\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AbstractTableService
 * Provides reusable functionalities for creating and rendering table components with customizable columns and rows.
 */
abstract class AbstractTableService
{
    /**
     * @var Environment The Twig environment used for rendering templates.
     */
    protected Environment $twig;

    /**
     * @var Router The router used for generating URLs.
     */
    protected Router $router;

    /**
     * @var array<int, array<string, mixed>> The configuration of table columns.
     */
    protected array $columns = [];

    /**
     * @var array<string, string> A mapping of column keys to their titles.
     */
    protected array $columnMappings = [];

    /**
     * AbstractTableService constructor.
     * Initializes the service with Twig and Router, and sets up the columns.
     *
     * @param Environment $twig The Twig environment.
     * @param Router      $router The router instance.
     */
    public function __construct(Environment $twig, Router $router)
    {
        $this->twig   = $twig;
        $this->router = $router;
        $this->initializeColumns();
    }

    /**
     * Initializes the columns based on columnMappings.
     *
     * @return void
     */
    protected function initializeColumns(): void
    {
        foreach ($this->columnMappings as $key => $title) {
            $this->columns[] = [
                'title'     => $title,
                'key'       => $key,
                'formatter' => $this->getColumnFormatter($key),
                'cssClass'  => $this->getColumnClass($key),
            ];
        }
    }

    /**
     * Returns the CSS class for a given column key.
     *
     * @param string $key The column key.
     *
     * @return string The CSS class name.
     */
    protected function getColumnClass(string $key): string
    {
        return 'column-' . $key;
    }

    /**
     * Returns a formatter callable for a given column key.
     *
     * @param string $key The column key.
     *
     * @return callable|null The formatter callable, or null if none is defined.
     */
    protected function getColumnFormatter(string $key): ?callable
    {
        return null;
    }

    /**
     * Creates a new TableComponent instance with the configured columns.
     *
     * @return TableComponent The table component instance.
     */
    protected function createTableComponent(): TableComponent
    {
        return new TableComponent($this->columns);
    }

    /**
     * Renders the table with the given rows.
     *
     * @param array<int, array<string, string>> $rows The rows to render in the table.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string The rendered table HTML.
     */
    public function renderTable(array $rows): string
    {
        $table = $this->createTableComponent();
        foreach ($rows as $row) {
            $table->addRow($row);
        }

        return $table->render($this->twig);
    }

    /**
     * Generates an HTML link.
     *
     * @param string                                          $route The route name.
     * @param string                                          $label The link label.
     * @param string|null                                     $cssClass The CSS class for the link.
     * @param array<int|string, string|array<string, string>> $params Parameters for the route.
     *
     * @return string The generated link HTML.
     */
    protected function getLink(string $route, string $label, string $cssClass = null, array $params = []): string
    {
        $url = $this->router->getRouteUrl($route, $params);
        return sprintf('<a href="%s" class="btn btn-sm rounded %s">%s</a>', $url, $cssClass, $label);
    }

    /**
     * Generates an HTML "Edit" link.
     *
     * @param string                                          $route The route name.
     * @param array<int|string, string|array<string, string>> $params Parameters for the route.
     *
     * @return string The generated "Edit" link HTML.
     */
    protected function getEditLink(string $route, array $params = []): string
    {
        return $this->getLink($route, 'Modifier', 'btn-warning', $params);
    }

    /**
     * Generates an HTML "Delete" link.
     *
     * @param string                                          $route The route name.
     * @param array<int|string, string|array<string, string>> $params Parameters for the route.
     *
     * @return string The generated "Delete" link HTML.
     */
    protected function getDeleteLink(string $route, array $params = []): string
    {
        return $this->getLink($route, 'Supprimer', 'btn-danger', $params);
    }

    /**
     * Generates an HTML "Show" link.
     *
     * @param string                                          $route The route name.
     * @param array<int|string, string|array<string, string>> $params Parameters for the route.
     *
     * @return string The generated "Show" link HTML.
     */
    protected function getShowLink(string $route, array $params = []): string
    {
        return $this->getLink($route, 'Voir', 'btn-primary', $params);
    }
}
