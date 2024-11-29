@extends('layouts.app')

@section('content')

<div class="container">
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
    <div class="col-md-12">
        <div class="my-2 p-3 bg-body card shadow rounded-0">
            <h2 class="mb-4 text-center">{{ __('Editar Cliente') }}</h2>

            <form action="{{ route('clients.update', $client->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3 row">
                    <div class="col-6">
                        <label for="name" class="form-label">{{ __('Nome do Cliente')}}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name) }}" required />
                    </div>
                    <div class="col-6">
                        <label for="document" class="form-label">{{ __('Documento: CPF')}}</label>
                        <input type="text" class="form-control" id="document" name="document" value="{{ old('document', $client->document) }}" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-4">
                        <label for="email" class="form-label">{{ __('E-mail')}}</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}" required>
                    </div>
                    <div class="col-4">
                        <label for="phone1" class="form-label">{{ __('Telefone principal')}}</label>
                        <input type="text" class="form-control" id="phone1" name="phone1" value="{{ old('phone1', $client->phone1) }}">
                    </div>
                    <div class="col-4">
                        <label for="phone2" class="form-label">{{ __('Telefone secundário')}}</label>
                        <input type="text" class="form-control" id="phone2" name="phone2" value="{{ old('phone2', $client->phone2) }}">
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <div class="col-4">
                        <label for="address" class="form-label">{{ __('Endereço: ex: Rua/AV sitio tome, n° 500 - Bairro - CEP')}}</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $client->address) }}">
                    </div>
                    <div class="col-4">
                        <label for="district" class="form-label">{{ __('Bairro')}}</label>
                        <input type="text" class="form-control" id="district" name="district" value="{{ old('district', $client->district) }}">
                    </div>
                    <div class="col-4">
                        <label for="city" class="form-label">{{ __('Cidade')}}</label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $client->city) }}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col">
                        <label for="birth_date" class="form-label">{{ __('Data de Nascimento: mes / dia / ano')}}</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date', $client->birth_date) }}">
                    </div>
                    <div class="col">
                        <label for="status" class="form-label">{{ __('Status do cliente')}}</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Inadimplente/Bloqueado</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="description" class="form-label">{{ __('Descrição / Observação')}}</label>
                        <textarea class="form-control" id="description" name="description">{{ old('description', $client->description) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </form>
        </div>
    </div>
</div>

@endsection