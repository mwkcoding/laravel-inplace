@extends('inplace::layout.base')

@section('inplace.content')
    
<livewire:editable
    model="users:1"
    :inline="true"
    value="Lorem Ipsum is simply"
    validation="required|min:10"
/>

@endsection