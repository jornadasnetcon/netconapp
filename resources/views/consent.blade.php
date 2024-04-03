@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading"><h1>¿Eres mayor de edad?</h1></div>

                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <p>Para participar en las {{ config('app.name', 'Laravel') }} necesitas ser mayor de edad. Si eres menor de edad por
                            favor,
                            contacta con la organización en <a href="mailto:netconcerouno@gmail.com">netconcerouno@gmail.com</a>
                            y te explicaremos cómo proceder para poder disfrutar de las jornadas {{ config('app.name', 'Laravel') }}
                        </p>

                        {!! Form::open(['url' => route('consent_store')]) !!}
                        {!! Form::submit('Soy mayor de edad', ['class' => 'btn btn-primary']) !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
