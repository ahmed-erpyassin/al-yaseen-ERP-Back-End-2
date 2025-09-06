<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />

    <title>{{ __($title) ?? 'Page Title' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon_io/favicon-32x32.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Google Font Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- MDB CSS -->
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/rtl/css/mdb.rtl.min.css') }}">
    @endif

    @if (app()->getLocale() === 'en')
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/ltr/css/mdb.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/mdb/marta-szymanska/ltr/css/new-prism.css') }}" />
    @endif

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles()

    <!-- Custom Font Setup -->
    <style>
        /* @media (min-width: 1400px) {

            main,
            header,
            #main-navbar {
                padding-left: 240px;
            }
        } */

        body {
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            line-height: 1.7;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
        }

        h3 {
            font-size: 24px;
            font-weight: 600;
        }

        h4 {
            font-size: 20px;
            font-weight: 600;
        }

        h5 {
            font-size: 18px;
            font-weight: 500;
        }

        h6 {
            font-size: 16px;
            font-weight: 500;
        }

        table,
        th,
        td {
            font-size: 16px;
        }

        button,
        input,
        select,
        textarea {
            font-size: 16px;
        }
    </style>
</head>

<body>
    <!-- Header: Sidenav + Navbar -->
    <header>
        @include('partials.admin.sidenav')
        @include('partials.admin.navbar')
    </header>

    <!-- Main Content -->
    <main id="main-screen" class="container">
        {{ $slot }}
    </main>

    @include('partials.admin.footer')

    <!-- MDB JS -->

    @if (app()->getLocale() === 'ar')
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/rtl/js/mdb.min.js') }}"></script>
    @endif

    @if (app()->getLocale() === 'en')
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/ltr/js/new-prism.js') }}"></script>
        <script type="text/javascript" src="{{ asset('assets/mdb/marta-szymanska/ltr/js/mdb.min.js') }}"></script>
    @endif


    <script type="text/javascript" src="{{ asset('assets/mdb/js/jquery-3.4.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/mdb/js/popper.min.js') }}"></script>

    <!-- Sidenav Responsive Script -->
    <script type="text/javascript">
        $(document).ready(function() {
            const sidenav = document.getElementById('sidenav-6'); // ID في HTML
            const sidenavInstance = mdb.Sidenav.getInstance(sidenav);

            let innerWidth = null;

            const setMode = () => {
                if (window.innerWidth === innerWidth) return;
                innerWidth = window.innerWidth;

                if (window.innerWidth < 660) {
                    sidenavInstance.changeMode('over');
                    sidenavInstance.show();
                } else {
                    sidenavInstance.changeMode('side');
                    sidenavInstance.show();
                }
            };

            setMode();
            window.addEventListener('resize', setMode);
        });
    </script>

    @livewireScripts()
</body>

</html>
