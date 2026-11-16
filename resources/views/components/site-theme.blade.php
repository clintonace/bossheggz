@props(['theme'])

@if ($fontUrl = $theme->fontStylesheetUrl())
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="{{ $fontUrl }}">
@endif
<style id="site-theme">{!! $theme->css() !!}</style>
