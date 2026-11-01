@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card auth-panel">
        <div class="card-body">
            <div class="auth-brand">Segurança da conta</div>
            <h1 class="auth-title">Crie sua senha pessoal</h1>
            <p class="auth-intro">A senha provisória precisa ser trocada antes de continuar.</p>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('account.temporary-password.update') }}" class="login-form">
                @csrf @method('PUT')
                <div class="mb-3"><label for="current_password" class="form-label">Senha provisória atual</label><input class="form-control" id="current_password" type="password" name="current_password" required autocomplete="current-password"></div>
                <div class="mb-3"><label for="password" class="form-label">Nova senha</label><input class="form-control" id="password" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
                <div class="mb-3"><label for="password_confirmation" class="form-label">Confirme a nova senha</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></div>
                <button class="btn btn-primary" type="submit">Salvar nova senha</button>
            </form>
        </div>
    </div>
</div>
@endsection
