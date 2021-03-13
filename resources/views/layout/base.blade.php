<style>
    body {font-family: 'Nunito';}
    div.editable{
        display: flex;
        align-items: center;
    }
    .edit-target{
        padding: 10px;
    }
</style>

@stack('inplace.component.style')
@include('inplace::styles')

    @yield('inplace.content')

@include('inplace::scripts')
@stack('inplace.component.script')
