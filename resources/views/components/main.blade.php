@extends('inplace::layout.base')

@section('inplace.content')
    
<livewire:editable
    model="users:1"
    :inline="$inline"
    :value="$value"
    :validation="$validation"
/>

@endsection