@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="page-heading">
        <div><div class="page-eyebrow">Acesso à equipe</div><h1>Subusuários</h1><p>Gerencie quem pode operar a carteira da sua conta.</p></div>
        <a href="{{ route('subusers.create') }}" class="btn btn-primary">Criar subusuário</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Permissões</th><th>Estado</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse($subusers as $subuser)
                    <tr>
                        <td>{{ $subuser->name }}</td>
                        <td>{{ $subuser->email }}</td>
                        <td>
                            @if($subuser->subuserPermissions)
                                {{ collect(['create_clients' => 'Clientes', 'create_charges' => 'Empréstimos', 'record_payments' => 'Pagamentos', 'change_due_dates' => 'Vencimentos'])->filter(fn ($label, $key) => $subuser->subuserPermissions->{$key})->implode(', ') ?: 'Sem permissões' }}
                            @else
                                Sem permissões configuradas
                            @endif
                        </td>
                        <td><span class="badge {{ $subuser->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $subuser->is_active ? 'Ativo' : 'Inativo' }}</span></td>
                        <td class="subuser-actions">
                            <a href="{{ route('subusers.edit', $subuser) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                            <form method="POST" action="{{ route('subusers.status', $subuser) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $subuser->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $subuser->is_active ? 'Desativar' : 'Ativar' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Você ainda não criou subusuários.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
