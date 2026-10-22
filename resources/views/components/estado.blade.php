@props(['valor'])
<span class="status {{ $valor }}">{{ ucfirst(str_replace('_', ' ', $valor)) }}</span>
