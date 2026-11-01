@extends('layouts.app')

@section('content')
@php($canRecordPayment = app(\App\Services\AccountContext::class)->canPerform(Auth::user(), 'record_payments'))
<div class="container">
    <div class="page-heading"><div><div class="page-eyebrow">Pagamento · {{ $installment->charge->client->name }}</div><h1>{{ __('Registrar pagamento') }}</h1><p>{{ __('Parcela #') . $installment->id }} · Vencimento {{ Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</p></div></div>

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

    @if($canRecordPayment && !$installment->payment_date)
    <form action="{{ route('installments.pay', $installment->id) }}" method="POST" class="form-panel form-panel-body">
        @csrf
        <div class="mb-3 row">
            <div class="col-4">
                <label for="amount" class="form-label">{{ __('Valor recebido') }} <span class="required-mark">*</span></label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount', $installment->amount) }}" required>
            </div>
            <div class="col-4">
                <label for="discount" class="form-label">{{ __('Disconto?') }}</label>
                <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="{{ old('discount', $installment->discount) }}">
            </div>
    
            <div class="col-4">
                <label for="penalty" class="form-label">{{ __('Juros?') }}</label>
                <input type="number" step="0.01" class="form-control" id="penalty" name="penalty" value="{{ old('penalty', $installment->penalty) }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Confirmar Pagamento?') }}</button>
    </form>
    @else
        <div class="alert alert-info">
            @if($installment->payment_date)
                Esta parcela já foi paga.
            @else
                Você tem acesso para consultar esta parcela, mas não para registrar o pagamento.
            @endif
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('charges.show', $installment->charge->client_id) }}">Voltar ao empréstimo</a>
    @endif
</div>
@endsection
