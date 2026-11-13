@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if (session('status'))
                <div class="col-12">
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="col-12"><div class="alert alert-danger" role="alert">{{ $errors->first() }}</div></div>
            @endif
        </div>
        <div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <div class="page-eyebrow">Resumo da conta</div>
                <h1>Visão geral</h1>
                <p class="text-muted mb-0">Acompanhe sua carteira e os recebimentos em aberto.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($canCreateCharges)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newLoanSearchModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                        Abrir Novo Empréstimo
                    </button>
                @endif
                @if($canCreateClients)
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">+ Novo cliente</a>
                @endif
            </div>
        </div>
        <div class="row home-summary">
            <div class="{{ $isAccountOwner ? 'col-sm-12 col-lg-3 col-md-3' : 'col-12' }}">
                <div class="card">
                    <div class="card-body">
                        @if($canCreateClients)
                        <a href="{{ route('clients.create') }}" class="link-primary" style="text-decoration: blink;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                            </svg>
                        </a>
                        @endif
                        <br>
                        <div class="summary-label">Clientes na carteira</div>
                        <div class="summary-value">{{ $totalClients }}</div>
                        <small class="text-muted d-block mt-1">
                            <span class="badge bg-primary text-white">{{ $activeClientsCount }} na lista ativa</span>
                            <span class="badge bg-secondary text-white">{{ $settledClientsCount }} quitados</span>
                        </small>
                    </div>
                </div>
            </div>
            @if($isAccountOwner)
            <div class="col-sm-12 col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="green" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z"/>
                        </svg>
                        <br>
                        <div class="summary-label">Total emprestado</div>
                        <div class="summary-value">R$ {{ number_format((float) $charger, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="blue" class="bi bi-cash-coin" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                            <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                            <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                            <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
                        </svg>
                        <br>
                        <div class="summary-label">A receber neste mês</div>
                        <div class="summary-value">R$ {{ number_format($totalReceivable, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="purple" class="bi bi-piggy-bank-fill" viewBox="0 0 16 16">
                            <path d="M7.964 1.527c-2.977 0-5.571 1.704-6.32 4.125h-.55A1 1 0 0 0 .11 6.824l.254 1.46a1.5 1.5 0 0 0 1.478 1.243h.263c.3.513.688.978 1.145 1.382l-.729 2.477a.5.5 0 0 0 .48.641h2a.5.5 0 0 0 .471-.332l.482-1.351c.635.173 1.31.267 2.011.267.707 0 1.388-.095 2.028-.272l.543 1.372a.5.5 0 0 0 .465.316h2a.5.5 0 0 0 .478-.645l-.761-2.506C13.81 9.895 14.5 8.559 14.5 7.069q0-.218-.02-.431c.261-.11.508-.266.705-.444.315.306.815.306.815-.417 0 .223-.5.223-.461-.026a1 1 0 0 0 .09-.255.7.7 0 0 0-.202-.645.58.58 0 0 0-.707-.098.74.74 0 0 0-.375.562c-.024.243.082.48.32.654a2 2 0 0 1-.259.153c-.534-2.664-3.284-4.595-6.442-4.595m7.173 3.876a.6.6 0 0 1-.098.21l-.044-.025c-.146-.09-.157-.175-.152-.223a.24.24 0 0 1 .117-.173c.049-.027.08-.021.113.012a.2.2 0 0 1 .064.199m-8.999-.65a.5.5 0 1 1-.276-.96A7.6 7.6 0 0 1 7.964 3.5c.763 0 1.497.11 2.18.315a.5.5 0 1 1-.287.958A6.6 6.6 0 0 0 7.964 4.5c-.64 0-1.255.09-1.826.254ZM5 6.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0"/>
                        </svg>
                        <br>
                        <div class="summary-label">Recebido no total</div>
                        <div class="summary-value">R$ {{ number_format($totalPaid, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @if($isAccountOwner)
        <div class="row">
            <div class="col-12 text-end mt-3">
                <!-- Botão para abrir o modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#investmentModal">
                    Investir
                </button>
            </div>
        </div>
        @endif
        <div class="row mt-4">
            <div class="col-12">
                <h2 class="mb-3 text-center">{{ __('Próximos vencimentos e pagamentos pendentes') }}</h2>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Vencimento') }}</th>
                                <th>{{ __('Valor') }}</th>
                                <th>{{ __('Situação') }}</th>
                                <th>{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingInstallments as $installment)
                                <tr>
                                    <td>{{ $installment->charge->client->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                                    <td>
                                        @if(\Carbon\Carbon::parse($installment->due_date)->lt(today()))
                                            <span class="badge bg-danger">{{ __('Atrasado') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('Pendente') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($canRecordPayments)
                                            <a class="btn btn-success btn-sm" href="{{ route('installments.showPaymentForm', $installment->id) }}">{{ __('Registrar pagamento') }}</a>
                                        @else
                                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('charges.show', $installment->charge->client_id) }}">{{ __('Ver parcela') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">{{ __('Não há pagamentos pendentes.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h2 class="mb-1">{{ __('Lista de Clientes') }}</h2>
                        <p class="text-muted small mb-0">
                            Por padrão, clientes com empréstimos quitados ficam ocultos da lista ativa. Use as abas ou a busca para localizá-los.
                        </p>
                    </div>
                    @if($canCreateCharges)
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#newLoanSearchModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                            Buscar cliente para novo empréstimo
                        </button>
                    @endif
                </div>

                <!-- Filtros e Barra de Busca da Tabela -->
                <div class="card mb-3 border-0 shadow-sm bg-light">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="btn-group" role="group" id="clientFilterGroup" aria-label="Filtro de clientes">
                                    <button type="button" class="btn btn-sm btn-primary active" data-filter="active">
                                        Empréstimos Ativos <span class="badge bg-light text-dark ms-1">{{ $activeClientsCount }}</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="settled">
                                        Quitados (Ocultos) <span class="badge bg-secondary ms-1">{{ $settledClientsCount }}</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="all">
                                        Todos os Clientes <span class="badge bg-dark ms-1">{{ $totalClients }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                        </svg>
                                    </span>
                                    <input type="text" id="clientTableSearch" class="form-control" placeholder="Filtrar por nome, documento ou cidade..." autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="clearTableSearchBtn" title="Limpar busca">×</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="clientsTable">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Documento') }}</th>
                                <th>{{ __('Endereço') }}</th>
                                <th>{{ __('Situação Empréstimo') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                @php
                                    $hasActive = $client->hasActiveCharge();
                                    $isSettled = $client->isSettled();
                                    $isNew = $client->isNewClient();
                                    $filterGroup = $hasActive || $isNew ? 'active' : 'settled';
                                    $searchData = mb_strtolower($client->name . ' ' . $client->document . ' ' . preg_replace('/\D/', '', $client->document) . ' ' . $client->phone1 . ' ' . $client->city . ' ' . $client->district . ' ' . $client->address);
                                @endphp
                                <tr class="client-row" data-group="{{ $filterGroup }}" data-search="{{ $searchData }}" style="{{ $filterGroup === 'settled' ? 'display: none;' : '' }}">
                                    <td>{{ $client->id }}</td>
                                    <td>
                                        <strong>{{ $client->name }}</strong>
                                        @if($isNew)
                                            <span class="badge bg-info text-dark ms-1" style="font-size: 0.7rem;">Novo</span>
                                        @endif
                                        @if($client->phone1)
                                            <div class="small text-muted">{{ $client->phone1 }}</div>
                                        @endif
                                    </td>
                                    <td>{{ maskMiddleCharacters($client->document) }}</td>
                                    <td>{{ $client->address ?: ($client->city ?: '-') }}</td>
                                    <td>
                                        @if($hasActive)
                                            <span class="badge bg-primary">Empréstimo Ativo</span>
                                        @elseif($isSettled)
                                            <span class="badge bg-secondary">Quitado</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Sem Empréstimo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($client->status == 1)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Inadimplente') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($isAccountOwner)
                                                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-secondary btn-sm" title="{{ __('Editar Cliente') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                                                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($canCreateCharges && !$hasActive)
                                                <a href="{{ route('charges.create', ['client_id' => $client->id]) }}" class="btn btn-success btn-sm" title="{{ __('Abrir Novo Empréstimo') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-cash-coin me-1" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                                                        <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                                                        <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                                                        <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
                                                    </svg>
                                                    Abrir Empréstimo
                                                </a>
                                            @endif

                                            @if($client->charges->isNotEmpty())
                                                <a href="{{ route('charges.show', ['client_id' => $client->id]) }}" class="btn btn-info btn-sm text-white" title="{{ $hasActive ? 'Visualizar Cobrança Ativa' : 'Histórico de Cobranças' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-receipt" viewBox="0 0 16 16">
                                                        <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
                                                        <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($isAccountOwner)
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $client->id }}" title="{{ __('Excluir Cliente') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                                    </svg>
                                                </button>
                                                @if($transferOwners->isNotEmpty())
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#transferClientModal{{ $client->id }}" title="Transferir cliente">↗</button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="emptyClientTableRow" style="display: none;">
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <div class="py-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-inbox text-muted mb-2" viewBox="0 0 16 16">
                                            <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438zM3.809 3.563A1.5 1.5 0 0 1 4.981 3h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a1 1 0 0 1 .109 1.077l-.84 6.72A1.5 1.5 0 0 1 13.666 17H2.334a1.5 1.5 0 0 1-1.492-1.315l-.84-6.72a1 1 0 0 1 .109-1.077z"/>
                                        </svg>
                                        <p class="mb-0">Nenhum cliente encontrado para este filtro ou termo de busca.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($isAccountOwner)
    <!-- Modals de Confirmação de Exclusão -->
    @foreach($clients as $client)
    <div class="modal fade" id="confirmDeleteModal{{ $client->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $client->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel{{ $client->id }}">{{ __('Confirmar Exclusão') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($client->charges->isNotEmpty())
                        <div class="alert alert-warning mb-2">
                            <strong>Atenção:</strong> Este cliente possui histórico financeiro vinculado ({{ $client->charges->count() }} cobrança(s)).
                        </div>
                        <p class="text-muted mb-0 small">
                            Para garantir a integridade do caixa e relatórios contábeis, clientes com cobranças não podem ser excluídos fisicamente do banco de dados.
                            Assim que todas as parcelas são pagas, o cliente é <strong>ocultado automaticamente</strong> da listagem diária de ativos.
                        </p>
                    @else
                        {{ __('Você deseja realmente excluir esse cliente? Esta ação não tem mais volta!') }}
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Fechar') }}</button>
                    @if($client->charges->isEmpty())
                        <form action="{{ route('clients.destroy', $client->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">{{ __('Sim, Excluir') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($transferOwners->isNotEmpty())
    <div class="modal fade" id="transferClientModal{{ $client->id }}" tabindex="-1" aria-labelledby="transferClientModalLabel{{ $client->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('clients.transfer', $client->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferClientModalLabel{{ $client->id }}">Transferir {{ $client->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <label for="target_account_owner_id_{{ $client->id }}" class="form-label">Conta titular de destino</label>
                        <select id="target_account_owner_id_{{ $client->id }}" name="target_account_owner_id" class="form-select" required>
                            <option value="">Selecione</option>
                            @foreach($transferOwners as $targetOwner)
                                <option value="{{ $targetOwner->id }}">{{ $targetOwner->name }}</option>
                            @endforeach
                        </select>
                        <p class="small text-muted mt-3 mb-0">As cobranças e os pagamentos vinculados ao cliente serão transferidos junto com ele.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Transferir cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach
    @endif

    @if($isAccountOwner)
    <!-- Modal -->
    <div class="modal fade" id="investmentModal" tabindex="-1" role="dialog" aria-labelledby="investmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="investmentModalLabel">Adicionar Investimento</h5>
                    <button type="button" class="text-end close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('add.investment') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="amount">Valor do Investimento</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descrição</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($canCreateCharges)
    <!-- Modal de Busca Rápida para Novo Empréstimo -->
    <div class="modal fade" id="newLoanSearchModal" tabindex="-1" aria-labelledby="newLoanSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <div>
                        <h5 class="modal-title fw-bold" id="newLoanSearchModalLabel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cash-coin text-success me-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                                <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                                <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                                <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
                            </svg>
                            Abrir Novo Empréstimo
                        </h5>
                        <small class="text-muted">Busque qualquer cliente (ativo, quitado ou novo) para iniciar uma nova cobrança.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="newLoanSearchInput" class="form-control" placeholder="Digite o nome ou CPF do cliente..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearNewLoanSearchBtn" title="Limpar">×</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                            <small class="text-muted" id="newLoanSearchCount">Mostrando todos os {{ $totalClients }} clientes</small>
                            <span class="badge bg-light text-secondary border">Dica: Clientes quitados (ocultos da lista) aparecem aqui</span>
                        </div>
                    </div>

                    <div class="list-group" id="newLoanClientsList" style="max-height: 400px; overflow-y: auto;">
                        @foreach($clients as $client)
                            @php
                                $hasActive = $client->hasActiveCharge();
                                $isSettled = $client->isSettled();
                                $isNew = $client->isNewClient();
                                $searchData = mb_strtolower($client->name . ' ' . $client->document . ' ' . preg_replace('/\D/', '', $client->document) . ' ' . $client->phone1 . ' ' . $client->city);
                            @endphp
                            <div class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 new-loan-item" data-search="{{ $searchData }}">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold">{{ $client->name }}</h6>
                                        @if($hasActive)
                                            <span class="badge bg-primary">Cobrança Ativa</span>
                                        @elseif($isSettled)
                                            <span class="badge bg-secondary">Quitado (Oculto)</span>
                                        @else
                                            <span class="badge bg-info text-dark">Novo Cadastro</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <span>CPF/Doc: {{ maskMiddleCharacters($client->document) }}</span>
                                        @if($client->phone1)
                                            <span class="ms-2">• Tel: {{ $client->phone1 }}</span>
                                        @endif
                                        @if($client->city)
                                            <span class="ms-2">• {{ $client->city }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($hasActive)
                                        <a href="{{ route('charges.show', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary" title="Já possui empréstimo ativo">
                                            Ver Cobrança Ativa
                                        </a>
                                    @else
                                        <a href="{{ route('charges.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-success fw-bold px-3">
                                            + Iniciar Empréstimo
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div id="newLoanEmptyMessage" class="text-center py-4 text-muted" style="display: none;">
                            <p class="mb-2">Nenhum cliente encontrado com esse termo.</p>
                            @if($canCreateClients)
                                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">+ Cadastrar novo cliente</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <div>
                        @if($canCreateClients)
                            <a href="{{ route('clients.create') }}" class="btn btn-outline-primary btn-sm">+ Cadastrar Novo Cliente</a>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Filtros e Busca da Tabela de Clientes ---
        const tableSearchInput = document.getElementById('clientTableSearch');
        const clearTableSearchBtn = document.getElementById('clearTableSearchBtn');
        const filterButtons = document.querySelectorAll('#clientFilterGroup button');
        const tableRows = document.querySelectorAll('#clientsTable tbody tr.client-row');
        const emptyTableRow = document.getElementById('emptyClientTableRow');

        let currentFilter = 'active'; // padrão: ativos e novos (quitados ocultos)

        function updateTableVisibility() {
            const query = (tableSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            tableRows.forEach(row => {
                const group = row.getAttribute('data-group');
                const searchContent = row.getAttribute('data-search') || '';

                const matchesFilter = (currentFilter === 'all') || (currentFilter === group);
                const matchesSearch = !query || searchContent.includes(query);

                if (matchesFilter && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (emptyTableRow) {
                emptyTableRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                filterButtons.forEach(b => {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.add('active', 'btn-primary');
                this.classList.remove('btn-outline-secondary');

                currentFilter = this.getAttribute('data-filter');
                updateTableVisibility();
            });
        });

        tableSearchInput?.addEventListener('input', updateTableVisibility);
        clearTableSearchBtn?.addEventListener('click', function () {
            if (tableSearchInput) {
                tableSearchInput.value = '';
                updateTableVisibility();
                tableSearchInput.focus();
            }
        });

        updateTableVisibility();

        // --- 2. Busca Rápida no Modal de Novo Empréstimo ---
        const newLoanModal = document.getElementById('newLoanSearchModal');
        const newLoanSearchInput = document.getElementById('newLoanSearchInput');
        const clearNewLoanSearchBtn = document.getElementById('clearNewLoanSearchBtn');
        const newLoanItems = document.querySelectorAll('.new-loan-item');
        const newLoanEmptyMsg = document.getElementById('newLoanEmptyMessage');
        const newLoanCountEl = document.getElementById('newLoanSearchCount');

        function filterNewLoanClients() {
            const query = (newLoanSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            newLoanItems.forEach(item => {
                const searchContent = item.getAttribute('data-search') || '';
                if (!query || searchContent.includes(query)) {
                    item.style.setProperty('display', 'flex', 'important');
                    visibleCount++;
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });

            if (newLoanEmptyMsg) {
                newLoanEmptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            if (newLoanCountEl) {
                if (query) {
                    newLoanCountEl.textContent = `${visibleCount} cliente(s) encontrado(s)`;
                } else {
                    newLoanCountEl.textContent = `Mostrando todos os ${newLoanItems.length} clientes`;
                }
            }
        }

        newLoanSearchInput?.addEventListener('input', filterNewLoanClients);
        clearNewLoanSearchBtn?.addEventListener('click', function () {
            if (newLoanSearchInput) {
                newLoanSearchInput.value = '';
                filterNewLoanClients();
                newLoanSearchInput.focus();
            }
        });

        if (newLoanModal && newLoanSearchInput) {
            newLoanModal.addEventListener('shown.bs.modal', function () {
                newLoanSearchInput.focus();
                newLoanSearchInput.select();
            });
        }
    });
    </script>
@endsection
