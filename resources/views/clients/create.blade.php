@extends('layouts.app')

@section('content')
    <div class="container">
        
        <div class="col-md-12">
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
                <h2 class="mb-4 text-center">{{ __('Cadastrar Cliente') }}</h2>

                <form action="{{ route('clients.store') }}" method="POST">
                    @csrf
                    <div class="mb-3 row">
                        <div class="col-6">
                            <label for="name" class="form-label">{{ __('Nome do Cliente')}}</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name')}}" required />
                        </div>
                        <div class="col-6">
                            <label for="document" class="form-label">{{ __('Documento: CPF')}}</label>
                            <input type="text" class="form-control" id="document" name="document" value="{{ old('document') }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-4">
                            <label for="email" class="form-label">{{ __('E-mail')}}</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="col-4">
                            <label for="phone1" class="form-label">{{ __('Telefone principal')}}</label>
                            <input type="text" class="form-control" id="phone1" name="phone1" value="{{ old('phone1') }}">
                        </div>
                        <div class="col-4">
                            <label for="phone2" class="form-label">{{ __('Telefone secundário')}}</label>
                            <input type="text" class="form-control" id="phone2" name="phone2" value="{{ old('phone2') }}">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-4">
                            <label for="address" class="form-label">{{ __('Endereço:  ex: Rua/AV sitio tome, n° 500')}}</label>
                            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="col-4">
                            <label for="district" class="form-label">{{ __('Bairro')}}</label>
                            <input type="text" class="form-control" id="district" name="district" value="{{ old('district') }}">
                        </div>
                        <div class="col-4">
                            <label for="city" class="form-label">{{ __('Cidade')}}</label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-6">
                            <label for="birth_date" class="form-label">{{ __('Data de Nascimento: mes / dia / ano')}}</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                        </div>
                        <div class="col">
                            <label for="description" class="form-label">{{ __('Descrição / Observação')}}</label>
                            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
@endsection