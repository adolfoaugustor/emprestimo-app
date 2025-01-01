@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">{{ __('Criar cobrança para ') . $client->name }}</h2>

    <div class="col-md-12">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger" role="alert">
                    {{ $error }}
                </div>
            @endforeach
        @endif
    </div>

    <form action="{{ route('charges.store', ['client_id' => $client->id]) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="total_amount" class="form-label">{{ __('Total da cobrança') }}</label>
            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required
            placeholder="R$ 0,00">
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">{{ __('Data de Cadastro') }}</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required
            placeholder="dd/mm/aaaa">
        </div>

        <div class="mb-3">
            <label for="installments_count" class="form-label">{{ __('Número de parcelas') }}</label>
            <input type="number" class="form-control" name="installments" value="20" disabled>
            <input type="hidden" name="installments_count" value="20">
        </div>

        <button type="submit" class="btn btn-primary">{{ __('Cadastrar Cobrança') }}</button>
    </form>
</div>
@endsection