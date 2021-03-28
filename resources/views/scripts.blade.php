@production
<script src="https://cdn.jsdelivr.net/gh/alpine-collective/alpine-magic-helpers@1.0.0/dist/index.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.1/dist/alpine.min.js" defer></script>
@else
<script src="https://cdn.jsdelivr.net/gh/alpine-collective/alpine-magic-helpers@1.0.x/dist/index.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
@endproduction

@stack('inplace.component.script')