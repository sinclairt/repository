# Sterling Repository

Common queries wrapped up into a class to be extended.

### Installation

Add the following repository to your ``` composer.json ```. You will have access as long as you belong to the Sterling team on Bitbucket.

``` sh
  "repositories": [
    {
      "type": "composer",
      "url": "http://satis.sterling-design.co.uk"
    }
  ]
```

``` composer require sterling/repository```

``` composer install ```

### Usage

All you need to do is create a repository that extends ``` Sterling\Repository\Repositories\Repository ``` and implements the ``` Sterling\Repository\Contracts\Repository ``` and off you go you have access to all the goodness of the repository!

Alternatively, you can use either of the two traits ``` Sterling\Repository\Traits\EloquentRepository ``` or ``` Sterling\Repository\Traits\EloquentSoftDeleteRepository ```, add a protected ``` $model ``` variable in your class and set it to your model class and away you go!

F.Y.I All of the queries use Laravel's query builder methods, so be sure that your model class has access to these!