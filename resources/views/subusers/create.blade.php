@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-heading"><div><div class="page-eyebrow">Acesso à equipe</div><h1>Novo subusuário</h1><p>O novo acesso será vinculado à sua carteira.</p></div></div>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('subusers.store') }}" class="form-panel form-panel-body subuser-form">
        @csrf
        <div class="mb-3"><label for="name" class="form-label">Nome</label><input id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255"></div>
        <div class="mb-3"><label for="email" class="form-label">E-mail</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required maxlength="255" autocomplete="off"></div>
        <div class="alert alert-info">As quatro permissões começam habilitadas. Você pode alterá-las depois. A senha provisória será exibida uma única vez após salvar.</div>
        <div class="form-actions"><a href="{{ route('subusers.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary" type="submit">Criar subusuário</button></div>
    </form>
</div>
@endsection
