<?php

namespace devsrv\inplace\Contracts;

interface Assemble {
    public function resolveFromComponentAttribute();

    public function resolveFromFieldMaker();

    public function getValues();
}