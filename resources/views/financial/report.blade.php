@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Relatório Financeiro</h1>

        <form action="{{ route('financial.report') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Investimentos</h5>
                        <p class="card-text">R$ {{ number_format($summary['total_investments'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Cobranças</h5>
                        <p class="card-text">R$ {{ number_format($summary['total_charges'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Pagamentos</h5>
                        <p class="card-text">R$ {{ number_format($summary['total_payments'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Atual</h5>
                        <p class="card-text">R$ {{ number_format($summary['current_balance'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($transaction->transaction_type == 'charge' ? 'Cobrança': 'investimento') }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($transaction->balance, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection