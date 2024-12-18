@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4 text-center">{{ __('Detalhes da cobrança para ') . $client->name }}</h2>

        <div class="mb-3">
            <h4>{{ __('Informações de cobrança') }}</h4>
            <ul>
                <li><strong>{{ __('Valor Total:') }}</strong> R$ {{ number_format($charge->total_amount, 2) }}</li>
                <li><strong>{{ __('Valor Pago:') }}</strong> R$ {{ number_format($charge->amount_paid, 2) }}</li>
                <li><strong>{{ __('Data da Cobrança:') }}</strong> {{ Carbon\Carbon::parse($charge->start_date)->format('d/m/Y') }}</li>
                <li><strong>{{ __('Data Final:') }}</strong> {{ $charge->end_date ? Carbon\Carbon::parse($charge->start_date)->format('d/m/Y') : __('N/A') }}</li>
                <li><strong>{{ __('Parcelas Pagas:') }}</strong> {{ $charge->installments_paid }} / {{ $charge->installments_count }}</li>
                <li><strong>{{ __('Status:') }}</strong> {{ $charge->installments_paid == $charge->installments_count ? __('Finalizado') : __('Pendente') }}</li>
            </ul>
        </div>

        <div class="mb-4">
            <h4>{{ __('Parcelas') }}</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Quantia') }}</th>
                        <th>{{ __('Data de Vencimento') }}</th>
                        <th>{{ __('Data de Pagamento') }}</th>
                        <th>{{ __('Disconto') }}</th>
                        <th>{{ __('Penalidade') }}</th>
                        <th>{{ __('ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($charge->installments as $installment)
                        <tr>
                            <td>{{ $installment->id }}</td>
                            <td>R$ {{ number_format($installment->amount, 2) }}</td>
                            <td>{{ Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                            <td>{{ $installment->payment_date ? 
                                Carbon\Carbon::parse($installment->payment_date)->format('d/m/Y') :
                                __('Not Paid') }}</td>
                            <td>R$ {{ number_format($installment->discount, 2) }}</td>
                            <td>R$ {{ number_format($installment->penalty, 2) }}</td>
                            <td>
                                @if(!$installment->payment_date)
                                    <a href="{{ route('installments.showPaymentForm', $installment->id) }}" class="btn btn-success">{{ __('Pagar') }}</a>
                                @else
                                    <span class="badge bg-primary">{{ __('Tá Pago') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection