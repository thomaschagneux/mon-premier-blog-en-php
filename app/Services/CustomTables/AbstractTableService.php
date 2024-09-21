<?php

namespace App\Services\CustomTables;

use App\Components\TableComponent;
use App\core\Router;
use App\Models\AbstractModel;
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

    /**
     * @param string $route
     * @param string $label
     * @param string|null $cssClass
     * @param array<int|string, string|array<string, string>> $params
     * @return string
     */
    protected function getLink(string $route, string $label, string $cssClass = null, array $params = []): string
    {
        $url = $this->router->getRouteUrl($route, $params);
        return sprintf('<a href="%s" class="btn btn-sm rounded %s">%s</a>', $url, $cssClass, $label);
    }

    /**
     * @param string $route
     * @param array<int|string, string|array<string, string>> $params
     * @return string
     */
    protected function getEditLink(string $route, array $params = []): string
    {
        return $this->getLink($route, 'Modifier', 'btn-warning', $params);
    }

    /**
     * @param string $route
     * @param array<int|string, string|array<string, string>> $params
     * @return string
     */
    protected function getDeleteLink(string $route, array $params = []): string
    {
        return $this->getLink($route,'Supprimer' ,'btn-danger', $params);
    }

    /**
     * @param string $route
     * @param array<int|string, string|array<string, string>> $params
     * @return string
     */
    protected function getShowLink(string $route, array $params = []): string
    {
        return $this->getLink($route, 'Voir', 'btn-primary', $params );
    }

}
