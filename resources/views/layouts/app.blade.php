<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    @include('layouts.meta')

    @include('layouts.favicon')

    @include('layouts.include_css')
</head>

<body class="home" >

    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
        your browser</a> to improve your experience.</p>
    <![endif]-->

    {{-- Ziggy directive --}}
    @routes

    {{-- Main wrapper --}}
    <div class="lgx-container">

        @include('layouts.header')

        @php
            $no_breadcrumb = [
                'eventmie.welcome',
                'eventmie.events_show',
                'eventmie.login',
                'eventmie.register',
                'eventmie.register_show',
                'eventmie.password.request',
                'eventmie.password.reset',
            ]
        @endphp
        @if (!in_array(Route::currentRouteName(), $no_breadcrumb))
            @include('layouts.breadcrumb')
        @endif

        <section class="main-wrapper" id="eventmie_app">

            {{-- page content --}}
            @yield('content')

            {{-- set progress bar --}}
            <vue-progress-bar></vue-progress-bar>
        </section>

        @include('layouts.footer')

    </div>
    <!--Main wrapper end-->

    @include('layouts.include_js')

    {{-- Page specific javascript --}}
    @yield('javascript')

</body>
</html>
