@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Add an Employee Discount</div>
                    <div class="panel-body">
                        @include('errors._list')

                        {!! Form::open(['method' => 'POST',
                                        'route'  => 'employeediscount.store',
                                        'class'  => 'form-horizontal',
                                        'role'   => 'form']) !!}
                        @include('employeediscount._form', ['submitButtonText' => 'Add Employee Discount'])
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
