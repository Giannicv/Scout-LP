<h2>
    Lo que necesitamos compartir.
</h2>
@forelse ($comunicados as $comunicado)
    <article class="panel">
        <span class="meta">
            {{ $comunicado->creado_en->format('d/m/Y') }}
        </span>
        <h3>
            {{ $comunicado->titulo }}
        </h3>
        <p class="message-text">
            {{ $comunicado->contenido }}
        </p>
        @if ($lecturas->contains($comunicado->id))
            <span class="status done">
                Leído
            </span>
        @else
            <form method="POST" action="{{ route($rolRuta.'.operacion', ['accion' => 'leer']) }}">
                @csrf
                <input type="hidden" name="comunicado_id" value="{{ $comunicado->id }}">
                <button>
                    Marcar como leído
                </button>
            </form>
        @endif
    </article>
@empty
    <p class="empty">
        Aún no tienes comunicados.
    </p>
@endforelse
