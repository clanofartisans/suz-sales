@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Add a Line Drive</div>
                    <div class="panel-body">
                        @include('errors._list')

                        {!! Form::open(['method' => 'POST',
                                        'route'  => 'linedrive.store',
                                        'class'  => 'form-horizontal',
                                        'role'   => 'form']) !!}
                        @include('linedrive._form', ['submitButtonText' => 'Add Line Drive'])
                        {!! Form::close() !!}
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
