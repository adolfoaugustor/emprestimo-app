@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-heading"><div><div class="page-eyebrow">Acesso à equipe</div><h1>{{ $subuser->name }}</h1><p>{{ $subuser->email }}</p></div><a class="btn btn-outline-secondary" href="{{ route('subusers.index') }}">Voltar</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('temporary_password'))
        <div class="alert alert-warning"><strong>Senha provisória — copie agora:</strong> <code>{{ session('temporary_password') }}</code><br>Ela não será enviada por e-mail e só aparece nesta tela uma vez. O subusuário terá que trocá-la ao entrar.</div>
    @endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('subusers.update', $subuser) }}" class="form-panel form-panel-body subuser-form">
        @csrf @method('PUT')
        <h2 class="h5 mb-3">Permissões</h2>
        @foreach($permissionLabels as $permission => $label)
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="permission_{{ $permission }}" name="permissions[]" value="{{ $permission }}" @checked(data_get($subuser->subuserPermissions, $permission, false))>
                <label class="form-check-label" for="permission_{{ $permission }}">{{ $label }}</label>
            </div>
        @endforeach
        <div class="form-actions"><button class="btn btn-primary" type="submit">Salvar permissões</button></div>
    </form>

    <div class="subuser-security-actions">
        <div><h2 class="h5">Acesso e senha</h2><p>O subusuário deverá escolher uma senha pessoal no próximo acesso.</p></div>
        <form method="POST" action="{{ route('subusers.temporary-password', $subuser) }}">
            @csrf
            <button class="btn btn-outline-secondary" type="submit">Gerar nova senha provisória</button>
        </form>
    </div>
</div>
@endsection
