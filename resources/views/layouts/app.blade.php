<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Empréstimo App') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        @auth
            @php($accountContext = app(\App\Services\AccountContext::class))
            <aside class="app-sidebar" id="appSidebar" aria-label="Menu lateral">
                <a class="app-brand" href="{{ route('home') }}">
                    <span class="app-brand-mark">E</span>
                    <span><strong>Empréstimo</strong><small>Gestão financeira</small></span>
                </a>

                <div class="sidebar-section-label">Menu principal</div>
                <nav class="sidebar-nav" aria-label="Navegação principal">
                    <a class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><span class="sidebar-icon">⌂</span>Visão geral</a>
                    @if($accountContext->canPerform(Auth::user(), 'create_clients'))
                        <a class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.create') }}"><span class="sidebar-icon">♙</span>Novo cliente</a>
                    @endif
                    <a class="sidebar-link {{ request()->routeIs('charges.*', 'installments.*') ? 'active' : '' }}" href="{{ route('home') }}"><span class="sidebar-icon">▤</span>Empréstimos e parcelas</a>
                    @if($accountContext->isOwner(Auth::user()))
                        <a class="sidebar-link {{ request()->routeIs('financial.report') ? 'active' : '' }}" href="{{ route('financial.report') }}"><span class="sidebar-icon">▥</span>Relatório financeiro</a>
                        <a class="sidebar-link {{ request()->routeIs('subusers.*') ? 'active' : '' }}" href="{{ route('subusers.index') }}"><span class="sidebar-icon">♧</span>Subusuários</a>
                    @endif
                </nav>

                <div class="sidebar-bottom">Área de gestão<br><span>Carteira pessoal</span></div>
            </aside>
            <button class="sidebar-backdrop" id="appSidebarBackdrop" type="button" aria-label="Fechar menu" tabindex="-1" hidden></button>
        @endauth

        <div class="app-main">
            <header class="app-topbar">
                @auth
                    <button class="sidebar-toggle" id="appSidebarToggle" type="button" aria-label="Abrir menu" aria-controls="appSidebar" aria-expanded="false"><span aria-hidden="true">☰</span></button>
                @else
                    <a class="guest-brand" href="{{ url('/') }}"><span class="app-brand-mark">E</span>Empréstimo</a>
                @endauth

                <div class="topbar-spacer"></div>
                @auth
                    <div class="dropdown">
                        <button class="user-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-avatar">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="dropdown-caret" aria-hidden="true">⌄</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                            <span class="dropdown-heading">Conta conectada</span>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item" type="submit">Sair da conta</button>
                            </form>
                        </div>
                    </div>
                @else
                    @if (Route::has('login'))
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('login') }}">Entrar</a>
                    @endif
                @endauth
            </header>

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
