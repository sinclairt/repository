<?php

/**
 * Class DummyModel
 */
class DummyModel extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [ 'name', 'number', 'rank' ];

    /**
     * @var string
     */
    protected $table = 'dummies';

    /**
     * @var array
     */
    public $filters = [ 'name', 'number', 'rank' ];

    /**
     * @var array
     */
    protected $dates = [ 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dummyRelations()
    {
        return $this->hasMany(DummyRelationModel::class, 'dummy_id');
    }

    /**
     * @param $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterName( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('name', $value) : $query->where('name', $value);
    }

    /**
     * @param $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterNumber( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('number', $value) : $query->where('number', $value);
    }

    /**
     * @param $query
     * @param $value
     * @param bool $trashed
     *
     * @return mixed
     */
    public function scopeFilterRank( $query, $value, $trashed = false )
    {
        $query = $trashed ? $query->withTrashed() : $query;

        return is_array($value) ? $query->whereIn('rank', $value) : $query->where('rank', $value);
    }
}