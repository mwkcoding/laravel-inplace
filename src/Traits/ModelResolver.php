<?php

namespace devsrv\inplace\Traits;
use Illuminate\Database\Eloquent\Model;
use devsrv\inplace\Exceptions\ModelException;
use devsrv\inplace\Helper;

trait ModelResolver
{
    public function resolveModel($model)
    {
        if($model instanceof Model) {
            $modelClass = get_class($model);
            $primaryKeyValue = $model->getKey();
        }
        else {
            throw_unless(is_string($model), ModelException::badFormat('namespace\Model:key or Model object'));

            try {
                [$modelClass, $primaryKeyValue] = explode(':', $model);
            } catch (\Exception $th) {
                throw ModelException::badFormat('namespace\Model:key or Model object');
            }
            
            if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);
        }

        return $modelClass.':'.$primaryKeyValue;
    }

    public function decryptModel($encrypted) : Model
    {
        $model = Helper::decrypt($encrypted);

        try {
            [$modelClass, $primaryKeyValue] = explode(':', $model);
        } catch (\Exception $th) {
            throw ModelException::badFormat('namespace\Model:key');
        }
        
        if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);

        return $modelClass::findOrFail($primaryKeyValue);
    }
}
