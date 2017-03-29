# Repository

Common queries wrapped up into a class to be extended.

### Installation

``` composer require sinclairt/repository```

``` composer install ```

### Usage

All you need to do is create a repository that extends ``` Sinclair\Repository\Repositories\Repository ``` and implements the ``` Sinclair\Repository\Contracts\Repository ``` and off you go you have access to all the goodness of the repository!

This is a list of all the methods available to you

    sort
    getById
    getAll
    getAllPaginate
    add
    getByName
    update
    destroy
    save
    firstOrCreate
    getArrayForSelect
    getArrayForSelectWithTrashed
    onlyFillable
    getByIdWithTrashed
    getAllWithTrashed
    getAllPaginatedWithTrashed
    restore
    search
    searchWithTrashed
    filter
    filterPaginated
    setQuery
    getQuery

F.Y.I All of the queries use Laravel's query builder methods, so be sure that your model class has access to these!
