<div class="form-group">
    {!! Form::label('radioBWColor', 'B&W or Color', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <label class="radio-inline">
            {!! Form::radio('radioBWColor', 'radioBW', true, ['id' => 'radioBW']) !!} B&W
        </label>
        <label class="radio-inline">
            {!! Form::radio('radioBWColor', 'radioColor', false, ['id' => 'radioColor']) !!} Color
        </label>
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputUPC', 'UPC', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('previewInputUPC', null, ['class' => 'form-control']) !!}
            <span class="input-group-btn">
                <button id="previewODFill" class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
            </span>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputBrand', 'Brand', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputBrand', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputDesc', 'Product Description', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputDesc', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputSalePrice', 'Sale Price (for OrderDog)', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputSalePrice', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputDispPrice', 'Display Price (for Printing)', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputDispPrice', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputRegPrice', 'Regular Price', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputRegPrice', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputSavings', 'Savings', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputSavings', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputSaleCat', 'Sale Category', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputSaleCat', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_begin', 'Sale Begins', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('sale_begin', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_end', 'Sale Ends', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('sale_end', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary']) !!}
    </div>
</div>
