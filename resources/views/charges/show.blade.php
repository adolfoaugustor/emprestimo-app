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
                                    <a href="{{ route('installments.showPaymentForm', $installment->id) }}" class="btn btn-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="badge bg-primary">{{ __('Tá Pago') }}</span>
                                @endif
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editDueDateModal{{ $installment->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-check" viewBox="0 0 16 16">
                                        <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals de Edição -->
    @foreach($charge->installments as $installment)
    <div class="modal fade" id="editDueDateModal{{ $installment->id }}" tabindex="-1" aria-labelledby="editDueDateModalLabel{{ $installment->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('installments.updateDueDate', $installment->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDueDateModalLabel{{ $installment->id }}">{{ __('Editar vencimento da parcela #') . $installment->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="due_date" class="form-label">{{ __('Nova data de vencimento') }}</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $installment->due_date }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection