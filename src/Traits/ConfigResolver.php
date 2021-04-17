<?php

namespace devsrv\inplace\Traits;
use Illuminate\Database\Eloquent\Model;
use devsrv\inplace\Exceptions\ConfigException;
use devsrv\inplace\InplaceConfig;

trait ConfigResolver
{
    public static function getConfig($type, $id)
    {
        try {
            $config = app()->make(InplaceConfig::class);
        } catch (BindingResolutionException $th) {
            throw ConfigException::notFound();
        }

        $getter = [
            'inline' => 'getInlineEditor',
            'relation' => 'getRelationEditor'
        ];

        $inplaceEditor = $config::{$getter[$type]}($id);
        throw_if(is_null($inplaceEditor), ConfigException::missing($id));

        return $inplaceEditor;
    }
}
