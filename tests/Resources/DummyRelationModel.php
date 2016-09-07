<?php

/**
 * Class DummyRelationModel
 */
class DummyRelationModel extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [ 'dummy_id', 'name', 'detail' ];

    /**
     * @var string
     */
    protected $table = 'dummy_relations';

    /**
     * @var array
     */
    protected $dates = [ 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dummy()
    {
        return $this->belongsTo(DummyModel::class, 'dummy_id');
    }
}