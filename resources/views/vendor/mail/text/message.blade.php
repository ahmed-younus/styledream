@component('mail::layout')
{{ $slot }}

@isset($subcopy)
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endisset
@endcomponent
