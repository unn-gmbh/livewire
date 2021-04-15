<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $page = 1;
    public $paginators = [];

    public $queryStringPropertyMap = [
        'foo' => 'paginators.foo',
    ];

    public function getQueryString()
    {
        foreach ($this->paginators as $key => $value) {
            $this->$key = $value;
        }

        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        foreach ($this->paginators as $key => $value) {
            $queryString[$key] = ['except' => 1];
        }

        return array_merge(['page' => ['except' => 1]], $queryString);
    }

    public function initializeWithPagination()
    {
        $this->page = $this->resolvePage();

        Paginator::currentPageResolver(function ($pageName) {
            if (! isset($this->paginators[$pageName])) {
                return $this->paginators[$pageName] = request()->query($pageName, 1);
            }

            return $this->paginators[$pageName];
        });

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
    }

    public function paginationView()
    {
        return 'livewire::' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function paginationSimpleView()
    {
        return 'livewire::simple-' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function previousPage($pageName = 'page')
    {
        $this->setPage(max($this->page - 1, 1), $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->page + 1, $pageName);
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->setPage($page, $pageName);
    }

    public function resetPage($pageName = 'page')
    {
        $this->setPage(1, $pageName);
    }

    public function setPage($page, $pageName = 'page')
    {
        if ($pageName === 'page') {
            $this->page = $page;
        }

        $this->paginators[$pageName] = $page;
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return (int) request()->query('page', $this->page);
    }
}
