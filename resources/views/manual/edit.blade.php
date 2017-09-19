<div class="form-group">
    {!! Form::label('tamms', 'Tamms ID', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        @if (!empty(Form::getValueAttribute('tamms')))
            {!! Form::text('tamms', null, ['class' => 'form-control']) !!}
        @else
            {!! Form::text('tamms', 'T-VIN-', ['class' => 'form-control']) !!}
        @endif
    </div>
</div>

<div class="form-group">
    {!! Form::label('name', 'Name', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary']) !!}
    </div>
</div>
