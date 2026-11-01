@extends('layouts.app')

@section('content')
    @php($accountContext = app(\App\Services\AccountContext::class))
    @php($canRecordPayments = $accountContext->canPerform(Auth::user(), 'record_payments'))
    @php($canChangeDueDates = $accountContext->canPerform(Auth::user(), 'change_due_dates'))
    <div class="container">
        <div class="page-heading"><div><div class="page-eyebrow">Empréstimos · {{ $client->name }}</div><h1>{{ __('Detalhes da cobrança') }}</h1><p>{{ __('Cobrança #') . $charge->id }}</p></div><a class="btn btn-outline-secondary" href="{{ route('home') }}">Voltar ao painel</a></div>

        <div class="card mb-4">
            <div class="card-header">{{ __('Resumo do empréstimo') }}</div>
            <div class="card-body row g-3">
                <div class="col-6 col-lg-3"><div class="summary-label">Valor total</div><div class="summary-value">R$ {{ number_format($charge->total_amount, 2, ',', '.') }}</div></div>
                <div class="col-6 col-lg-3"><div class="summary-label">Valor recebido</div><div class="summary-value">R$ {{ number_format($charge->amount_paid, 2, ',', '.') }}</div></div>
                <div class="col-6 col-lg-3"><div class="summary-label">Parcelas pagas</div><div class="summary-value">{{ $charge->installments_paid }} / {{ $charge->installments_count }}</div></div>
                <div class="col-6 col-lg-3"><div class="summary-label">Situação</div><div class="summary-value fs-6">{{ $charge->installments_paid == $charge->installments_count ? __('Finalizado') : __('Em andamento') }}</div></div>
                <div class="col-6"><div class="summary-label">Data da cobrança</div><div>{{ Carbon\Carbon::parse($charge->start_date)->format('d/m/Y') }}</div></div>
                <div class="col-6"><div class="summary-label">Data final</div><div>{{ $charge->end_date ? Carbon\Carbon::parse($charge->end_date)->format('d/m/Y') : __('Em andamento') }}</div></div>
            </div>
        </div>

        <div class="mb-4">
            <h4>{{ __('Parcelas') }}</h4>
            <div class="table-responsive"><table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Nº') }}</th>
                        <th>{{ __('Vlr Parcela') }}</th>
                        <th>{{ __('Vlr Pago') }}</th>
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
                            <td>{{ $loop->iteration }}</td>
                            <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                            <td class="{{ $installment->payment_date && $installment->amount_paid == 0.00 ? 'text-danger' : '' }}">
                                R$ {{ number_format($installment->amount_paid, 2, ',', '.') }}
                            </td>                            
                            <td>{{ Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                            <td>{{ $installment->payment_date ? 
                                Carbon\Carbon::parse($installment->payment_date)->format('d/m/Y') :
                                __('Not Paid') }}
                            </td>
                            <td>R$ {{ number_format($installment->discount, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($installment->penalty, 2, ',', '.') }}</td>
                            <td>
                                @if(!$installment->payment_date && $canRecordPayments)
                                    <a href="{{ route('installments.showPaymentForm', $installment->id) }}" class="btn btn-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                        </svg>
                                    </a>
                                @elseif($installment->payment_date)
                                    <span class="badge bg-primary">{{ __('Tá Pago') }}</span>
                                @endif
                                @if($canChangeDueDates && !$installment->payment_date)
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editDueDateModal{{ $installment->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-check" viewBox="0 0 16 16">
                                        <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($installment->dueDateChanges->isNotEmpty())
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dueDateHistoryModal{{ $installment->id }}" title="Histórico de vencimento">Histórico</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
    </div>

    <!-- Modals de Edição -->
    @if($canChangeDueDates)
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
    @endif
    @foreach($charge->installments as $installment)
        @if($installment->dueDateChanges->isNotEmpty())
        <div class="modal fade" id="dueDateHistoryModal{{ $installment->id }}" tabindex="-1" aria-labelledby="dueDateHistoryModalLabel{{ $installment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dueDateHistoryModalLabel{{ $installment->id }}">Histórico da parcela #{{ $installment->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($installment->dueDateChanges as $change)
                                <li class="py-2 border-bottom">{{ Carbon\Carbon::parse($change->old_due_date)->format('d/m/Y') }} → {{ Carbon\Carbon::parse($change->new_due_date)->format('d/m/Y') }}<br><small class="text-muted">{{ $change->changedBy?->name ?? 'Usuário removido' }} · {{ $change->created_at->format('d/m/Y H:i') }}</small></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endsection
