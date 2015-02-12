<?php

namespace Quotebot\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use \Exception;

/*
* APIRepository
*/
abstract class Repository
{

    // must define this
    protected $model_type = ''; // e.g. Quotebot\Models\User


    public function findByID($id) {
        return call_user_func([$this->model_type, 'find'], $id);
    }

    public function update(Model $model, $attributes) {
        return $model->update($attributes);
    }

    public function delete(Model $model) {
        return $model->delete();
    }


    public function create($attributes) {
        $attributes = $this->modifyAttributesBeforeCreate($attributes);
        return call_user_func([$this->model_type, 'create'], $attributes);
    }

    public function saveModel(Model $model) {
        $model->save();
        return $model;
    }

    public function findAll() {
        return call_user_func([$this->model_type, 'all']);
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Modify
    
    protected function modifyAttributesBeforeCreate($attributes) {
        return $attributes;
    }

}
