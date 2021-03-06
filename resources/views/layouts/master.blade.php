<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    @yield('custom-meta')
    <title>{{ $channel->getSetting('title') }}</title>

    <!-- Bootstrap -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ elixir('assets/css/style.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ elixir('assets/css/vendor.css') }}">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-purple sidebar-mini">
    <div class="wrapper">

        <header class="main-header">
            <a href="{{ route('home_path', [$channel->slug]) }}" class="logo">
                <?php $title = $channel->getSetting('title') ?>
                <span class="logo-mini"><strong>{{ $title[0] }}</strong></span>
                <span class="logo-lg"><strong>{{ $title }}</strong></span>
            </a><!-- .logo -->

            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>

                <div class="navbar-custom-menu" id="header-nav">
                    <ul class="nav navbar-nav">
                        @can('access-page', [$channel, 'bot'])
                        <li class="navbar-text" v-if="loading"><img src="/assets/img/loader-white.svg" width="24" height="24" alt="Loading..."></li>
                        <li v-el:unavailable class="hide"><a v-if="status === 'unavailable'">Bot is unavailable &nbsp; <i class="fa fa-fw fa-toggle-off"></i></a></li>
                        <li v-el:join class="hide"><a href="javascript:void(0)" @click="joinChannel()" v-if="status === 'not_in_channel'">Join Channel &nbsp; <i class="fa fa-fw fa-toggle-off"></i></a></li>
                        <li v-el:leave class="hide"><a href="javascript:void(0)" @click="leaveChannel()" v-if="status === 'in_channel'">Leave Channel &nbsp; <i class="fa fa-fw fa-toggle-on"></i></a></li>
                        @endcan

                        @if ($user)
                        <li><a href="{{ route('logout_path', $channel->slug) }}">Logout</a></li>
                        @endif
                    </ul>
                </div><!-- .navbar-custom-menu -->
            </nav><!-- .navbar -->
        </header><!-- .main-header -->

        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu">
                    @if ($channel->hasFeature('currency'))
                        <li{!! Route::currentRouteName() === 'scoreboard_path' ? ' class="active"' : '' !!}><a href="{!! route('scoreboard_path', [$channel->slug]) !!}"><i class="fa fa-money"></i> <span>Scoreboard</span></a></li>
                    @endif

                    @if ($channel->hasFeature('commands'))
                        <li{!! Route::currentRouteName() === 'commands_path' ? ' class="active"' : '' !!}><a href="{!! route('commands_path', [$channel->slug]) !!}"><i class="fa fa-list"></i> <span>Commands</span></a></li>
                    @endif

                    @if ($channel->hasFeature('quotes'))
                        <li{!! Route::currentRouteName() === 'quotes_path' ? ' class="active"' : '' !!}><a href="{!! route('quotes_path', [$channel->slug]) !!}"><i class="fa fa-quote-left"></i> <span>Quotes</span></a></li>
                    @endif

                    @if ($user)
                        @if ($channel->hasFeature('queue'))
                            <li{!! Route::currentRouteName() === 'queue_path' ? ' class="active"' : '' !!}><a href="{!! route('queue_path', [$channel->slug]) !!}"><i class="fa fa-bars"></i> <span>Queue</span></a></li>
                        @endif

                        @if ($channel->hasFeature('giveaway'))
                            <li{!! Route::currentRouteName() === 'giveaway_path' ? ' class="active"' : '' !!}><a href="{!! route('giveaway_path', [$channel->slug]) !!}"><i class="fa fa-gift"></i> <span>Giveaways</span></a></li>
                        @endif

                        @if ($channel->hasFeature('timers'))
                            <li{!! Route::currentRouteName() === 'timers_path' ? ' class="active"' : '' !!}><a href="{!! route('timers_path', [$channel->slug]) !!}"><i class="fa fa-clock-o"></i> <span>Timers</span></a></li>
                        @endcan

                        @if ($channel->hasFeature('chat-logs'))
                        <li{!! Route::currentRouteName() === 'chat_logs_path' ? ' class="active"' : '' !!}><a href="{!! route('chat_logs_path', [$channel->slug]) !!}"><i class="fa fa-book"></i> <span>Chat Logs</span></a></li>
                        @endcan
                    @endif

                </ul>
            </section><!-- .sidebar -->
        </aside><!-- .main-sidebar -->

        <div class="content-wrapper">
            <section class="content-header">
                <h1>@yield('heading', 'Dashboard')</h1>
                {{--<ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Dashboard</li>
                </ol>--}}
            </section><!-- .content-header -->

            @yield('content')
        </div><!-- .content-wrapper -->

        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} Created by <a href="https://twitter.com/mikemcmullan">Mike McMullan</a> a.k.a <a href="https://twitch.tv/mcsmike">MCSMike</a></strong> All rights reserved.
        </footer>

        <div class="control-sidebar-bg"></div>

    </div><!-- .wrapper -->

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script>
        var screenSizes = {
            xs: 480,
            sm: 768,
            md: 992,
            lg: 1200
        };

        var body = $('body');

        $('.sidebar-toggle').on('click', function (e) {
            e.preventDefault();

            //Enable sidebar push menu
            if ($(window).width() > (screenSizes.sm - 1)) {
                if (body.hasClass('sidebar-collapse')) {
                    body.removeClass('sidebar-collapse');
                } else {
                    body.addClass('sidebar-collapse');
                }
            }
            //Handle sidebar push menu for small screens
            else {
                if (body.hasClass('sidebar-open')) {
                    body.removeClass('sidebar-open').removeClass('sidebar-collapse');
                } else {
                    body.addClass('sidebar-open');
                }
            }
        });

        $(".content-wrapper").click(function () {
            //Enable hide menu when clicking on the content-wrapper on small screens
            if ($(window).width() <= (screenSizes.sm - 1) && $("body").hasClass("sidebar-open")) {
                body.removeClass('sidebar-open');
            }
        });
    </script>

    @yield('after-js')
</body>
</html>
