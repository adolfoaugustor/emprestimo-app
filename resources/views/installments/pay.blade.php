@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">{{ __('Pagamento da parcela #') . $installment->id }}</h2>

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

    <form action="{{ route('installments.pay', $installment->id) }}" method="POST">
        @csrf
        <div class="mb-3 row">
            <div class="col-4">
                <label for="amount" class="form-label">{{ __('Quantia') }}</label>
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
</div>
@endsection