@extends('layouts.master')

@section('heading', 'Commands')

@section('content')
<section class="content" id="commands">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Custom Commands</h3>
                </div><!-- .box-header -->

                <div class="box-body">

                    <p>
                        <select class="form-control pull-right" v-model="itemsPerPage" style="width: 130px; display: inline-block;">
                            <option value="10" selected="selected">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>

                        <input type="search" class="form-control pull-right" v-model="searchKeyword" placeholder="search commands" style="width: 180px; display: inline-block; margin-right: 15px;">
                        <br><br>
                    </p>

                    <table class="table table-bordered table-striped" id="custom-commands-table">
                        <thead>
                            <th stlye="width: 30%">Command</th>
                            <th style="width: 10%" class="hidden-sm hidden-xs">Level</th>
                            <th style="width: 60%" class="hidden-sm hidden-xs">Response</th>
                        </thead>

                        <tbody class="hide" v-el:loop>
                            <tr v-for="command in commands | filterBy 'custom' in 'type' | searchCommands searchKeyword | limitBy itemsPerPage itemsIndex" :class="{ 'command-disabled': command.disabled }">
                                <td>@{{ command.command }}</td>
                                <td class="hidden-sm hidden-xs">@{{ command.level.capitalize() }}</td>
                                <td class="hidden-sm hidden-xs">@{{ command.response.substring(0, 100) }}<span v-if="command.response.length > 100">...</span></td>
                            </tr>
                            <tr v-if="customCommands.length === 0 || noSearchResults">
                                <td colspan="3">No records found.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="3" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

                    <div class="text-center">
                        <paginator :items-index.sync="itemsIndex" :items-per-page.sync="itemsPerPage" :data.sync="customCommands"></paginator>
                    </div>
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">System Commands</h3>
                </div><!-- .box-header -->

                <div class="box-body">
                    <table class="table table-bordered" v-if="loading2">
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="box box-solid system-commands-box hide" v-for="name in commandGroupNames">
                        <div class="box-header">
                            <strong>@{{ name.capitalize() }}</strong>
                        </div>

                        <div class="box-body">
                            <table class="table table-bordered table-striped system-commands-table">
                                <thead>
                                    <th stlye="width: 30%">Command</th>
                                    <th style="width: 10%" class="hidden-sm hidden-xs">Level</th>
                                    <th style="width: 60%" class="hidden-sm hidden-xs">Description</th>
                                </thead>

                                <tbody>
                                    <tr v-for="command in commandGroups[name]">
                                        <td>@{{ command.usage }}</td>
                                        <td class="hidden-sm hidden-xs">@{{ command.level.capitalize() }}</td>
                                        <td class="hidden-sm hidden-xs">@{{{ command.description }}}</td>
                                    </tr>
                                </tbody>
                            </table><!-- .table -->
                        </div><!-- .box-body -->
                    </div>


                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->

</section>
@endsection

@section('after-js')
    <script>
        var options = {
            api: {
                root: '{{ makeDomain(config('app.api_domain'), '//') }}/{{ $channel->slug }}'
            },
            csrf_token: '{{ csrf_token() }}',
            pusher: {
                key: '{{ config('services.pusher.key') }}'
            },
            channel: '{{ $channel->slug }}'
        };
    </script>

    <script src="{{ elixir('assets/js/public.js') }}"></script>
@endsection
