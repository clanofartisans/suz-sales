@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        New INFRA Workbook
                    </div>
                    <div class="panel-body">

                        @include('flash::message')

                        @include('infra.uploadform')
                    </div>
                    <!-- /.panel-body -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-2">
                <!-- /.panel-default -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        INFRA Workbooks
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>Workbook Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($infrasheets as $infrasheet)
                                    <tr class="clickable" data-url="{{ route('infra.show', [$infrasheet->id]) }}">
                                        <td><a href="{{ route('infra.show', [$infrasheet->id]) }}">
                                            {{ $infrasheet->month }} {{ $infrasheet->year }}
                                            </a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.table-responsive -->
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel-default -->
            </div>
            <!-- /.col-md-8 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container -->
@endsection
