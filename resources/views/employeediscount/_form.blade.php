<div class="form-group">
    {!! Form::label('brand', 'Brand', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <select class="form-control" name="brand" id="brand">
            <option value="">&nbsp;</option>
            @foreach($brands as $brandKey => $brandValue)
                <option value="{{ $brandKey }}">{{ $brandValue }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    {!! Form::label('discount', 'Discount %', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('discount', '25', ['class' => 'form-control']) !!}
            <div class="input-group-addon">% Off</div>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_begin', 'Discount Begins', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="form-inline">
            {!! Form::text('sale_begin', '', ['class' => 'form-control']) !!}
            {!! Form::checkbox('checkNoBegin', 'checkNoBegin', true, ['id' => 'checkNoBegin']) !!} No Begin Date
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_end', 'Discount Ends', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="form-inline">
            {!! Form::text('sale_end', '', ['class' => 'form-control']) !!}
            {!! Form::checkbox('checkNoEnd', 'checkNoEnd', true, ['id' => 'checkNoEnd']) !!} No End Date
        </div>
    </div>
</div>

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        <input type="submit" name="submit" value="{{ $submitButtonText }}" class="btn btn-primary">
    </div>
</div>
