@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">Manually Add a Sale</div>
                    <div class="panel-body">
                        @include('errors._list')

                        {!! Form::open(['method' => 'POST',
                                        'route'  => 'manual.store',
                                        'class'  => 'form-horizontal',
                                        'role'   => 'form']) !!}
                        @include('manual._form', ['submitButtonText' => 'Add and Return', 'submitContinueButtonText' => 'Add and Continue'])
                        {!! Form::close() !!}
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel-default -->
            </div>
            <!-- /.col-md-6 -->
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Preview</div>
                    <div id="preview-container" class="panel-body">
                        @if($data['color'])
                            @include('saletags.previewcolor')
                        @else
                            @include('saletags.previewbw')
                        @endif
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel-default -->
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container -->
@endsection
