@extends('layouts.app')

@section('content')
    <div class="container">
        <section class="welcome-panel">
            <div class="page-eyebrow">Gestão de empréstimos</div>
            <h1>Uma visão clara da sua carteira.</h1>
            <p>Organize clientes, acompanhe parcelas e registre pagamentos em um só lugar.</p>
            <a class="btn btn-primary" href="{{ route('login') }}">Entrar na sua conta</a>
        </section>
    </div>
@endsection
