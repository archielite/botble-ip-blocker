@php
    Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
    ->addScriptsDirectly([
        'vendor/core/core/base/libraries/tagify/tagify.js',
        'vendor/core/core/base/js/tags.js',
    ]);
@endphp

{!! Form::text($name, is_array($value) ? json_encode($value) : $value, array_merge(['class' => 'tags'], $attributes)) !!}
