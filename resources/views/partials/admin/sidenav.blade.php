<div id="sidenav-6" class="sidenav sidenav-sm sidenav-right" data-mdb-accordion="true" data-mdb-hidden="false"
    data-mdb-mode="side" role="navigation" data-mdb-right="false" data-mdb-color="light" style="background-color: #2d2c2c">

    <a class="ripple d-flex justify-content-center py-4 mb-3" style="padding-top: 4rem !important;"
        href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}" data-mdb-ripple-color="primary">
        <img id="YassinERP-Logo" width="200" src="{{ asset('assets/mdb/marta-szymanska/images/logo-brand.png') }}"
            alt="YassinERP-Logo" draggable="false" />
    </a>

    @php
        $locale = app()->getLocale() === 'en' ? true : false;
    @endphp

    <ul class="sidenav-menu px-2 pb-5">

        <li class="sidenav-item">
            <a class="sidenav-link" href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}">
                <i class="fas fa-tachometer-alt fa-fw {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Overview') }}</span>
            </a>
        </li>
        <hr />
        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="far fa-list-alt {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Users') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-plus fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Users') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="far fa-building {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Companies') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.companies.list', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-plus fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Companies') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidenav-item">
            <a class="sidenav-link">
                <i class="fas fa-calculator {{ $locale ? 'me-3' : 'me-3' }}"></i>
                <span>{{ __('Financial Accounts') }}</span>
            </a>

            <ul class="sidenav-collapse">
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.financial-accounts.currencies.list', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-money-bill-wave fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Currencies') }}</span>
                    </a>
                </li>
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.financial-accounts.exchange-rates.list', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-exchange-alt fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Exchange Rates') }}</span>
                    </a>
                </li>
                <li class="sidenav-item">
                    <a class="sidenav-link"
                        href="{{ route('admin.panel.financial-accounts.fiscal-years.list', ['lang' => app()->getLocale()]) }}">
                        <i class="fas fa-calendar-alt fa-fw {{ $locale ? 'me-2' : 'me-2' }}"></i>
                        <span>{{ __('Fiscal Years') }}</span>
                    </a>
                </li>
            </ul>
        </li>


    </ul>

</div>
