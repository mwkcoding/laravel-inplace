<?php

namespace devsrv\inplace\Traits;
use Illuminate\Database\Eloquent\Model;
use devsrv\inplace\Exceptions\ModelException;

trait ResolveModel
{
    public function resolveModel($model)
    {
        if($model instanceof Model) {
            $modelClass = get_class($model);
            $primaryKeyValue = $model->getKey();
        }
        else {
            try {
                [$modelClass, $primaryKeyValue] = explode(':', $model);
            } catch (\Exception $th) {
                throw ModelException::badFormat('namespace\Model:key or Model object');
            }
            
            if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);
        }

        return $modelClass.':'.$primaryKeyValue;
    }
}
