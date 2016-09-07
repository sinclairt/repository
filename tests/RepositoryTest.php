<?php

require_once 'DbTestCase.php';

/**
 * Class TraitTest
 */
class RepositoryTest extends DbTestCase
{
    /**
     * @var
     */
    private $faker;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/../vendor/laravel/laravel/database/migrations');

        $this->migrate(__DIR__ . '/Resources/migrations');

        $this->faker = Faker\Factory::create();

        \Artisan::call('vendor:publish', [ '--tag' => 'config' ]);
    }

    function append( $item )
    {
        return $item = 'hello_' . $item;
    }

    public function test_i_can_apply_a_sort_to_an_existing_query_with_a_column_and_direction()
    {
        $repository = $this->makeRepository();

        $model = $repository->getModel();

        $query = $model->latest();

        $repository->sort($query, 'name', 'asc');

        $orderClauses = $query->getQuery()->orders;

        $this->assertEquals(2, sizeof($orderClauses));

        $this->assertArraySubset([
            'column'    => 'created_at',
            'direction' => 'desc',

        ], $orderClauses[ 0 ]);

        $this->assertArraySubset([
            'column'    => 'name',
            'direction' => 'asc',

        ], $orderClauses[ 1 ]);
    }

    public function test_i_can_apply_a_sort_to_an_existing_query_with_a_relations_column_and_direction()
    {
        $repository = $this->makeRepository();

        $model = $repository->getModel();

        $query = $model->latest();

        $repository->sort($query, 'dummyRelations.name', 'asc');

        $orderClauses = $query->getQuery()->orders;

        $this->assertEquals(2, sizeof($orderClauses));

        $this->assertArraySubset([
            'column'    => 'created_at',
            'direction' => 'desc',

        ], $orderClauses[ 0 ]);

        $this->assertArraySubset([
            'column'    => 'related.name',
            'direction' => 'asc',

        ], $orderClauses[ 1 ]);
    }

    public function test_i_can_get_an_object_by_its_id()
    {
        $expected = $this->makeDummies(1);

        $actual = $this->makeRepository()
                       ->getById($expected->id);

        $this->assertArraySubset($expected->toArray(), $actual->toArray());
    }

    public function test_i_can_get_all_models()
    {
        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getAll();

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset($dummy->toArray(), $actual->get($key)
                                                               ->toArray());
    }

    public function test_i_can_get_all_models_with_only_the_name_attribute()
    {
        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getAll([ 'name' ]);

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset(array_only($dummy->toArray(), 'name'), $actual->get($key)
                                                                                   ->toArray());
    }

    public function test_i_can_get_all_models_and_specify_an_order()
    {
        $expected = $this->makeDummies()
                         ->sortByDesc('rank')
                         ->values();

        $actual = $this->makeRepository()
                       ->getAll([ '*' ], 'rank', 'desc');

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset($dummy->toArray(), $actual->get($key)
                                                               ->toArray());
    }

    public function test_i_can_get_all_models_retrieving_only_the_name()
    {
        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getAll([ 'name' ]);

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset(array_only($dummy->toArray(), 'name'), $actual->get($key)
                                                                                   ->toArray());
    }

    public function test_i_can_get_all_models_retrieving_only_the_name_and_sorting_by_the_rank_ascending()
    {
        $expected = $this->makeDummies()
                         ->sortBy('rank')
                         ->values();

        $actual = $this->makeRepository()
                       ->getAll([ 'name' ], 'rank');

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset(array_only($dummy->toArray(), 'name'), $actual->get($key)
                                                                                   ->toArray());
    }

    public function test_i_can_get_all_models_paginated()
    {
        $expected = $this->makeDummies()
                         ->slice(0, 15);

        $actual = $this->makeRepository()
                       ->getAllPaginated();

        foreach ( $actual as $key => $item )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $item->toArray());

        $this->assertEquals(15, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_pages_of_10_models()
    {
        $expected = $this->makeDummies()
                         ->slice(0, 10);

        $actual = $this->makeRepository()
                       ->getAllPaginated(10);

        foreach ( $actual as $key => $item )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_set_the_order_of_the_paginated_models()
    {
        $expected = $this->makeDummies()
                         ->sortByDesc('rank')
                         ->slice(0, 10)
                         ->values();

        $actual = $this->makeRepository()
                       ->getAllPaginated(10, 'rank', 'desc');

        foreach ( $actual as $key => $item )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_10_models_per_page_of_names_sorted_by_rank()
    {
        $expected = $this->makeDummies()
                         ->sortByDesc('rank')
                         ->pluck('name')
                         ->slice(0, 10)
                         ->values();

        $actual = $this->makeRepository()
                       ->getAllPaginated(10, 'rank', 'desc', [ 'name' ]);

        foreach ( $actual as $key => $item )
            $this->assertArraySubset([ 'name' => $expected->get($key) ], $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_10_models_per_page_of_names_sorted_by_rank_with_a_page_name_of_foo()
    {
        $expected = $this->makeDummies()
                         ->sortByDesc('rank')
                         ->pluck('name')
                         ->slice(0, 10)
                         ->values();

        $actual = $this->makeRepository()
                       ->getAllPaginated(10, 'rank', 'desc', [ 'name' ], 'foo');

        foreach ( $actual as $key => $item )
            $this->assertArraySubset([ 'name' => $expected->get($key) ], $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);

        $this->assertEquals('foo', $actual->getPageName());
    }

    public function test_i_can_add_a_new_model()
    {
        $attributes = $this->makeDummyAttributes();

        $model = $this->makeRepository()
                      ->add($attributes);

        $this->assertArraySubset($attributes, $model->toArray());
    }

    public function test_i_can_create_a_model_with_additional_parameters_which_will_be_ignored()
    {
        $attributes = array_replace($this->makeDummyAttributes(), [ 'foo' => 'bar' ]);

        $model = $this->makeRepository()
                      ->add($attributes);

        $this->assertNull($model->foo);
    }

    public function test_i_can_get_a_model_by_its_name()
    {
        $expected = $this->makeDummies();

        $dummy = $expected->first();

        $actual = $this->makeRepository()
                       ->getByName($dummy->name);

        $this->assertArraySubset($dummy->toArray(), $actual->toArray());
    }

    public function test_i_can_update_a_model()
    {
        $dummy = $this->makeDummies(1);

        $name = $this->faker->word;

        $actual = $this->makeRepository()
                       ->update(compact('name'), $dummy);

        $this->assertEquals($name, $actual->name);
    }

    public function test_i_can_destroy_a_model()
    {
        $dummy = $this->makeDummies(1);

        $this->assertTrue($this->makeRepository()
                               ->destroy($dummy));

        $dummy = DummyModel::withTrashed()
                           ->find($dummy->id);

        $this->assertTrue($dummy->trashed());
    }

    public function test_i_can_save_a_model()
    {
        $this->test_i_can_update_a_model();
    }

    public function test_i_can_create_a_model_if_it_does_not_exist()
    {
        $attributes = $this->makeDummyAttributes();

        $query = $this->makeRepository()
                      ->getModel()
                      ->newQuery();

        foreach ( $attributes as $key => $value )
            $query->where($key, $value);

        $this->assertEquals(0, $query->count());

        $this->makeRepository()
             ->firstOrCreate($attributes);

        $query = $this->makeRepository()
                      ->getModel()
                      ->newQuery();

        foreach ( $attributes as $key => $value )
            $query->where($key, $value);

        $this->assertEquals(1, $query->count());
    }

    public function test_i_can_find_the_first_model_with_a_set_of_attributes()
    {
        $attributes = [ 'rank' => $this->faker->word, 'number' => $this->faker->ean13 ];

        $expected = $this->makeDummies(20, $attributes)
                         ->first();

        $actual = $this->makeRepository()
                       ->firstOrCreate($attributes);

        $this->assertArraySubset($expected->toArray(), $actual->toArray());
    }

    public function test_i_can_get_an_array_for_select()
    {
        $expected = $this->makeDummies()
                         ->pluck('name', 'id')
                         ->map('ucwords')
                         ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelect();

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_the_key_and_value()
    {
        $expected = $this->makeDummies()
                         ->pluck('rank', 'id')
                         ->map('ucwords')
                         ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_callback()
    {
        $expected = $this->makeDummies()
                         ->pluck('rank', 'id')
                         ->toArray();

        array_walk($expected, function ( &$item )
        {
            $item = strtolower($item);
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id', 'strtolower');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_custom_callback()
    {
        $expected = $this->makeDummies()
                         ->pluck('rank', 'id')
                         ->toArray();

        array_walk($expected, function ( &$item )
        {
            return $item = 'hello_' . $item;
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id', [ $this, 'append' ]);

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_for_select_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies
            ->pluck('name', 'id')
            ->map('ucwords')
            ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelectWithTrashed();

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_the_key_and_value_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->pluck('rank', 'id')
                            ->map('ucwords')
                            ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelectWithTrashed('rank', 'id');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_callback_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->pluck('rank', 'id')
                            ->toArray();

        array_walk($expected, function ( &$item )
        {
            $item = strtolower($item);
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelectWithTrashed('rank', 'id', 'strtolower');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_custom_callback_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->pluck('rank', 'id')
                            ->toArray();

        array_walk($expected, function ( &$item )
        {
            return $item = 'hello_' . $item;
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelectWithTrashed('rank', 'id', [ $this, 'append' ]);

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_for_select_without_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->slice(10)
                            ->pluck('name', 'id')
                            ->map('ucwords')
                            ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelect();

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_the_key_and_value_without_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->slice(10)
                            ->pluck('rank', 'id')
                            ->map('ucwords')
                            ->toArray();

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_callback_without_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->slice(10)
                            ->pluck('rank', 'id')
                            ->toArray();

        array_walk($expected, function ( &$item )
        {
            $item = strtolower($item);
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id', 'strtolower');

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_an_array_specifying_a_custom_callback_without_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $expected = $dummies->slice(10)
                            ->pluck('rank', 'id')
                            ->toArray();

        array_walk($expected, function ( &$item )
        {
            return $item = 'hello_' . $item;
        });

        $actual = $this->makeRepository()
                       ->getArrayForSelect('rank', 'id', [ $this, 'append' ]);

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_filter_supplied_attributes_to_fillable_only()
    {
        $expected = $this->makeDummyAttributes();

        $repository = $this->makeRepository();

        $actual = $repository->onlyFillable(array_replace($expected, [ 'foo' => 'bar' ]), $repository->getModel());

        $this->assertEquals($expected, $actual);
    }

    public function test_i_can_get_a_model_by_its_id_including_deleted_items_in_the_search()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 10)
                ->each(function ( $item )
                {
                    $item->delete();
                });

        $expected = $dummies->first();

        $actual = $this->makeRepository()
                       ->getByIdWithTrashed($expected->id);

        $this->assertArraySubset(array_except($expected->toArray(), 'deleted_at'), $actual->toArray());
    }

    public function test_i_can_get_all_models_including_deleted_models()
    {
        $expected = $this->makeDummies();

        $expected->slice(0, 10)
                 ->each(function ( $item )
                 {
                     $item->delete();
                 });

        $actual = $this->makeRepository()
                       ->getAllWithTrashed();

        foreach ( $expected as $key => $value )
            $this->assertArraySubset($expected->toArray(), $actual->toArray());
    }

    public function test_i_can_get_all_models_with_only_the_name_attribute_including_deleted_models()
    {
        $expected = $this->makeDummies();

        $expected->slice(0, 10)
                 ->each(function ( $item )
                 {
                     $item->delete();
                 });

        $actual = $this->makeRepository()
                       ->getAllWithTrashed([ 'name' ]);

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset(array_only($dummy->toArray(), 'name'), $actual->get($key)
                                                                                   ->toArray());
    }

    public function test_i_can_get_all_models_and_specify_an_order_including_deleted_models()
    {
        $expected = $this->makeDummies()
                         ->sortByDesc('rank')
                         ->values();

        $expected->slice(0, 10)
                 ->each(function ( $item )
                 {
                     $item->delete();
                 });

        $actual = $this->makeRepository()
                       ->getAllWithTrashed([ '*' ], 'rank', 'desc');

        foreach ( $expected as $key => $dummy )
            $this->assertArraySubset(array_except($dummy->toArray(), 'deleted_at'), $actual->get($key)
                                                                                           ->toArray());
    }

    public function test_i_can_get_all_models_paginated_including_deleted_models()
    {
        $expected = $this->makeDummies()
                         ->slice(0, 15);

        $expected->slice(0, 5)
                 ->each(function ( $dummy )
                 {
                     $dummy->delete();
                 });

        $actual = $this->makeRepository()
                       ->getAllPaginatedWithTrashed();

        foreach ( $actual as $key => $item )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $item->toArray());

        $this->assertEquals(15, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_pages_of_10_models_including_deleted_models()
    {
        $expected = $this->makeDummies()
                         ->slice(0, 10);

        $expected->slice(0, 5)
                 ->each(function ( $dummy )
                 {
                     $dummy->delete();
                 });

        $actual = $this->makeRepository()
                       ->getAllPaginatedWithTrashed(10);

        foreach ( $actual as $key => $item )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_set_the_order_of_the_paginated_models_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $expected = $dummies
            ->sortByDesc('rank')
            ->slice(0, 10)
            ->values();

        $dummies->slice(0, 5)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $actual = $this->makeRepository()
                       ->getAllPaginatedWithTrashed(10, 'rank', 'desc');

        foreach ( $actual as $key => $item )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_10_models_per_page_of_names_sorted_by_rank_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $expected = $dummies->sortByDesc('rank')
                            ->pluck('name')
                            ->slice(0, 10)
                            ->values();

        $dummies->slice(0, 5)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $actual = $this->makeRepository()
                       ->getAllPaginatedWithTrashed(10, 'rank', 'desc', [ 'name' ]);

        foreach ( $actual as $key => $item )
            $this->assertArraySubset([ 'name' => $expected->get($key) ], $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);
    }

    public function test_i_can_get_10_models_per_page_of_names_sorted_by_rank_with_a_page_name_of_foo_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $expected = $dummies->sortByDesc('rank')
                            ->pluck('name')
                            ->slice(0, 10)
                            ->values();

        $dummies->slice(0, 5)
                ->each(function ( $dummy )
                {
                    $dummy->delete();
                });

        $actual = $this->makeRepository()
                       ->getAllPaginatedWithTrashed(10, 'rank', 'desc', [ 'name' ], 'foo');

        foreach ( $actual as $key => $item )
            $this->assertArraySubset([ 'name' => $expected->get($key) ], $item->toArray());

        $this->assertEquals(10, sizeof($actual->items()));

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $actual);

        $this->assertEquals('foo', $actual->getPageName());
    }

    public function test_i_can_restore_a_model()
    {
        $expected = $this->makeDummies(1);

        $expected->delete();

        $actual = $this->makeRepository()
                       ->restore($expected);

        $this->assertInstanceOf(DummyModel::class, $actual);

        $this->assertFalse($actual->trashed());
    }

    public function test_i_can_search_for_a_model()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->slice(0, 15);

        $actual = $this->makeRepository()
                       ->search($term);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
    }

    public function test_i_can_search_for_a_model_including_deleted_models()
    {
        $dummies = $this->makeDummies();

        $dummies->slice(0, 5)
                ->each(function ( $item )
                {
                    $item->delete();
                });

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->slice(0, 15);

        $actual = $this->makeRepository()
                       ->searchWithTrashed($term);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());
    }

    public function test_i_can_search_for_a_model_without_deleted_models()
    {
        $dummies = $this->makeDummies();

        $deleted = $dummies->slice(0, 5)
                           ->each(function ( $item )
                           {
                               $item->delete();
                           });

        $dummies = $dummies->slice(5);

        $term = $dummies->first()->number;

        $deleted = $deleted->each(function ( $item ) use ( $term )
        {
            if ( $item->number == $term )
            {
                $item->number = $this->faker->ean13;

                $item->save();
            }
        });

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->slice(0, 15)
                             ->values();

        $actual = $this->makeRepository()
                       ->search($term);

        foreach ( $actual as $key => $value )
        {
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
            foreach ( $deleted as $model )
                $this->assertNotEquals(4, sizeof(array_intersect_assoc(array_except($model->toArray(), $model->getDates()), $value->toArray())));
        }
    }

    public function test_i_can_filter_models()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

//        $expected = $expected->slice(0, 15);

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filter($request);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
    }

    public function test_i_can_filter_models_and_sort_them()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->sortByDesc('rank')
//                             ->slice(0, 15)
                             ->values();

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filter($request, 'rank', 'desc');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
    }

    public function test_i_can_filter_models_returning_only_the_name_column()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

//        $expected = $expected->slice(0, 15);

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filter($request, null, 'asc', [ 'name' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());
    }

    public function test_i_can_turn_off_the_search_when_filtering_results()
    {
        $expected = DummyModel::create([
            'name'   => 'foo',
            'number' => $this->faker->ean13,
            'rank'   => $this->faker->unique()->word
        ]);

        $notExpected = DummyModel::create([
            'name'   => $this->faker->word,
            'number' => $this->faker->ean13,
            'rank'   => 'foo'
        ]);

        $request = request();

        $request->offsetSet('name', 'foo');

        $actual = $this->makeRepository()
                       ->filter($request, null, 'asc', [ '*' ], false);

        $this->assertEquals(1, sizeof($actual));

        foreach ( $actual as $key => $value )
        {
            $this->assertArraySubset($expected->toArray(), $value->toArray());

            $this->assertNotEquals(4, sizeof(array_intersect_assoc($notExpected->toArray(), $value->toArray())));
        }
    }

    public function test_i_can_filter_models_and_paginate_them()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->slice(0, 15);

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filterPaginated($request);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
    }

    public function test_i_can_filter_models_and_sort_them_and_paginate_them()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->sortByDesc('rank')
                             ->slice(0, 15)
                             ->values();

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filterPaginated($request, 15, 'rank', 'desc');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());
    }

    public function test_i_can_filter_models_returning_only_the_name_column_and_paginate_them()
    {
        $dummies = $this->makeDummies();

        $term = $dummies->first()->number;

        $dummies = $dummies->merge($this->makeDummies(10, [ 'number' => $term ]));

        $expected = collect();

        foreach ( $dummies as $dummy )
            if ( in_array($term, $dummy->toArray()) )
                $expected->push($dummy);

        $expected = $expected->slice(0, 15);

        $request = request();

        $request->offsetSet('number', $term);

        $actual = $this->makeRepository()
                       ->filterPaginated($request, 15, null, 'asc', [ 'name' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());
    }

    public function test_i_can_turn_off_the_search_when_filtering_results_and_paginate_them()
    {
        $expected = DummyModel::create([
            'name'   => 'foo',
            'number' => $this->faker->ean13,
            'rank'   => $this->faker->unique()->word
        ]);

        $notExpected = DummyModel::create([
            'name'   => $this->faker->word,
            'number' => $this->faker->ean13,
            'rank'   => 'foo'
        ]);

        $request = request();

        $request->offsetSet('name', 'foo');

        $actual = $this->makeRepository()
                       ->filterPaginated($request, 15, null, 'asc', [ '*' ], false);

        $this->assertEquals(1, sizeof($actual));

        foreach ( $actual as $key => $value )
        {
            $this->assertArraySubset($expected->toArray(), $value->toArray());

            $this->assertNotEquals(4, sizeof(array_intersect_assoc($notExpected->toArray(), $value->toArray())));
        }
    }

    public function test_i_can_pages_of_10_filtered_results()
    {
        $expected = $this->makeDummies(20, [ 'name' => $name = $this->faker->word ])
                         ->slice(0, 10)
                         ->values();

        $request = request();

        $request->offsetSet('name', $name);

        $actual = $this->makeRepository()
                       ->filterPaginated($request, 10);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());

        $this->assertEquals(10, sizeof($actual));
    }

    public function test_i_can_get_a_paginated_set_of_filtered_results_with_a_page_name_of_foo()
    {
        $this->makeDummies(20, [ 'name' => 'foo' ]);

        $request = request();

        $request->offsetSet('name', 'foo');

        $actual = $this->makeRepository()
                       ->filterPaginated($request, 15, null, 'asc', [ '*' ], 'foo');

        $this->assertTrue($actual->getPageName() == 'foo');
    }

    public function test_i_can_set_the_query_object()
    {
        $this->makeRepository()
             ->setQuery($this->makeDummies(1)
                             ->newQuery());
    }

    public function test_i_can_get_the_query_object()
    {
        $dummyRepository = $this->makeRepository();

        $dummyRepository->setQuery($dummyRepository->getModel()
                                                   ->newQuery());

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $dummyRepository->getQuery());
    }

    public function test_i_can_set_the_model_object()
    {
        $this->makeRepository()
             ->setModel($this->makeDummies(1));
    }

    public function test_i_can_get_the_model_object()
    {
        $repository = $this->makeRepository();

        $expected = $this->makeDummies(1);

        $repository->setModel($expected);

        $this->assertEquals($expected, $repository->getModel());
    }

    public function test_i_can_get_models_with_a_date_between_two_supplied_values()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();
        $to = \Carbon\Carbon::now()
                            ->subDay();
        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween($from, $to);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_created_in_the_last_24_hours()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween();

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_updated_in_the_last_24_hours()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween(null, null, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_updated_between_two_dates()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween($from, $to, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_the_name_attribute_of_models_between_two_dates()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween($from, $to, 'updated_at', [ 'name' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_order_models_between_two_dates_by_the_rank_attribute()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->sortByDesc('rank')
                         ->values();

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetween($from, $to, 'created_at', [ '*' ], 'rank', 'desc');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_with_a_date_between_two_supplied_values_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();
        $to = \Carbon\Carbon::now()
                            ->subDay();
        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->slice(0, 15);

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated($from, $to);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_models_created_in_the_last_24_hours_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies()
                         ->slice(0, 15);

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated();

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_models_updated_in_the_last_24_hours_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated(null, null, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_models_updated_between_two_dates_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->slice(0, 15);

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated($from, $to, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_the_name_attribute_of_models_between_two_dates_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);

                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();

                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();

                             $dummy->save();
                         })
                         ->slice(0, 15);

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated($from, $to, 'updated_at', 15, null, 'asc', [ 'name' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_order_models_between_two_dates_by_the_rank_attribute_and_paginated()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->sortByDesc('rank')
                         ->slice(0, 15)
                         ->values();

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated($from, $to, 'created_at', 15, 'rank', 'desc', [ '*' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_back_models_between_two_dates_in_pages_of_10()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->slice(0, 10)
                         ->values();

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated($from, $to, 'created_at', 10);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset($expected->get($key)
                                              ->toArray(), $value->toArray());

        $this->assertEquals(20, $actual->total());
    }

    public function test_i_can_get_back_models_between_two_dates_swith_a_page_name_of_foo()
    {
        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenPaginated(null, null, 'created_at', 15, null, 'asc', [ '*' ], 'foo');

        $this->assertEquals('foo', $actual->getPageName());
    }

    public function test_i_can_get_models_with_a_date_between_two_supplied_values_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();
        $to = \Carbon\Carbon::now()
                            ->subDay();
        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed($from, $to);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_created_in_the_last_24_hours_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies();

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed();

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_updated_in_the_last_24_hours_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $this->makeDummies()
             ->each(function ( &$dummy ) use ( $from, $to )
             {
                 $dt = $this->faker->dateTimeBetween($from, $to);
                 $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                    ->toDateTimeString();
                 $dummy->save();
             });

        $expected = $this->makeDummies();

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed(null, null, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_updated_between_two_dates_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed($from, $to, 'updated_at');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_the_name_attribute_of_models_between_two_dates_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         });

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed($from, $to, 'updated_at', [ 'name' ]);

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_only($expected->get($key)
                                                         ->toArray(), 'name'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_order_models_between_two_dates_by_the_rank_attribute_including_deleted_models()
    {
        $from = \Carbon\Carbon::now()
                              ->subWeek();

        $to = \Carbon\Carbon::now()
                            ->subDay();

        $expected = $this->makeDummies()
                         ->each(function ( &$dummy ) use ( $from, $to )
                         {
                             $dt = $this->faker->dateTimeBetween($from, $to);
                             $dummy->created_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->updated_at = \Carbon\Carbon::instance($dt)
                                                                ->toDateTimeString();
                             $dummy->save();
                         })
                         ->sortByDesc('rank')
                         ->values();

        $expected->slice(5, 5)
                 ->each(function ( &$item )
                 {
                     $item->delete();
                 });

        $this->makeDummies();

        $actual = $this->makeRepository()
                       ->getDateBetweenWithTrashed($from, $to, 'created_at', [ '*' ], 'rank', 'desc');

        foreach ( $actual as $key => $value )
            $this->assertArraySubset(array_except($expected->get($key)
                                                           ->toArray(), 'deleted_at'), $value->toArray());

        $this->assertEquals(20, sizeof($actual));
    }

    public function test_i_can_get_models_with_a_date_between_two_supplied_values_including_deleted_models_paginated()
    {

    }

    protected function makeRepository()
    {
        return new DummyRepository(new DummyModel());
    }

    protected function makeDummies( $count = 20, $attributes = [] )
    {
        $dummies = collect();

        for ( $i = 0; $i < $count; $i++ )
            $dummies->push(DummyModel::create(array_replace($this->makeDummyAttributes(), $attributes)));

        return $count == 1 ? $dummies->first() : $dummies;
    }

    /**
     * @return array
     */
    protected function makeDummyAttributes()
    {
        return [
            'name'   => $this->faker->word,
            'number' => $this->faker->ean13,
            'rank'   => $this->faker->unique()->word
        ];
    }
}