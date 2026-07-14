<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
    <h1>HELLO WORLD! (Now from a blade view 🗡️)</h1>

    @if( $nome == 'Francesco')
        <p>Ciao {{ $nome }}</p>
    @endif

    @foreach($colori as $colore)
        <p>{{ $colore }}</p>
    @endforeach

    @foreach($ruoli as $persona => $ruolo)
        <p>Utente gestionale: {{$persona}}, ruolo: {{$ruolo}}</p>
    @endforeach
</div>
