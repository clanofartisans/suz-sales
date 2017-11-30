<div class="form-group">
    {!! Form::label('radioODUpdate', 'Update OrderDog?', ['class' => 'col-md-4 control-label']) !!} {{-- ODREF --}}
    <div class="col-md-6">
        <label class="radio-inline">
            {!! Form::radio('radioODUpdate', 'radioODYes', $data['ODUpdate'], ['id' => 'radioODYes']) !!} Yes {{-- ODREF --}}
        </label>
        <label class="radio-inline">
            {!! Form::radio('radioODUpdate', 'radioODNo', !$data['ODUpdate'], ['id' => 'radioODNo']) !!} No {{-- ODREF --}}
        </label>
    </div>
</div>

<div class="form-group">
    {!! Form::label('radioBWColor', 'B&W or Color', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <label class="radio-inline">
            {!! Form::radio('radioBWColor', 'radioBW', !$data['color'], ['id' => 'radioBW']) !!} B&W
        </label>
        <label class="radio-inline">
            {!! Form::radio('radioBWColor', 'radioColor', $data['color'], ['id' => 'radioColor']) !!} Color
        </label>
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputUPC', 'UPC', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('previewInputUPC', null, ['class' => 'form-control']) !!}
            <span class="input-group-btn">
                <input type="submit" id="previewODFill" class="btn btn-default fa-input" value="&#xf002;" /> {{-- ODREF --}}
            </span>
        </div>
    </div>
    <div class="col-md-2">
        <small id="odNotFound" class="text-danger" style="display: none;"> {{-- ODREF --}}
            Not found
        </small>
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputBrand', 'Brand', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputBrand', $data['brand'], ['class' => 'form-control']) !!}
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
    {!! Form::label('previewInputPercentOff', 'Discount % (optional)', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('previewInputPercentOff', $data['percent'], ['class' => 'form-control']) !!}
            <div class="input-group-addon">% Off</div>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('previewInputSaleCat', 'Sale Category', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('previewInputSaleCat', $data['sale_cat'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_begin', 'Sale Begins', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('sale_begin', $data['begin'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_end', 'Sale Ends', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('sale_end', $data['end'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        @if(!empty($submitContinueButtonText))
            <input type="submit" name="submitContinue" value="{{ $submitContinueButtonText }}" class="btn btn-success">
        @endif
        <input type="submit" name="submitReturn" value="{{ $submitButtonText }}" class="btn btn-primary">
    </div>
</div>
