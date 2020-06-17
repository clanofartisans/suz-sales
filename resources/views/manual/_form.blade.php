<div class="form-group">
    {!! Form::label('radioPOSUpdate', 'Update '.config('pos.name').'?', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <label class="radio-inline">
            {!! Form::radio('radioPOSUpdate', 'radioPOSYes', $data['POSUpdate'], ['id' => 'radioPOSYes']) !!} Yes
        </label>
        <label class="radio-inline">
            {!! Form::radio('radioPOSUpdate', 'radioPOSNo', !$data['POSUpdate'], ['id' => 'radioPOSNo']) !!} No
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
    {!! Form::label('upc', 'UPC', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('upc', null, ['class' => 'form-control', 'autofocus' => 'autofocus']) !!}
            <span class="input-group-btn">
                <input type="submit" id="previewPOSFill" class="btn btn-default fa-input" value="&#xf002;" />
            </span>
        </div>
    </div>
    <div class="col-md-2">
        <small id="posNotFound" class="text-danger" style="display: none;">
            Not found
        </small>
    </div>
</div>

<div class="form-group">
    {!! Form::label('brand', 'Brand', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('brand', $data['brand'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('description', 'Product Description', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('description', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('size', 'Size', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('size', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('real_sale_price', 'Sale Price (for '.config('pos.name', 'POS System').')', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('real_sale_price', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('display_sale_price', 'Display Price (for Printing)', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('display_sale_price', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('regular_price', 'Regular Price', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('regular_price', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('savings_amount', 'Savings', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('savings_amount', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('discount_percent', 'Discount % (optional)', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="input-group">
            {!! Form::text('discount_percent', $data['discount_percent'], ['class' => 'form-control']) !!}
            <div class="input-group-addon">% Off</div>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_category', 'Sale Category', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('sale_category', $data['sale_category'], ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_begin', 'Sale Begins', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="form-inline">
            {!! Form::text('sale_begin', $data['sale_begin'], ['class' => 'form-control']) !!}
            {!! Form::checkbox('checkNoBegin', 'checkNoBegin', $data['no_begin'], ['id' => 'checkNoBegin']) !!} No Begin Date
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('sale_end', 'Sale Ends', ['class' => 'col-md-4 control-label']) !!}
    <div class="col-md-6">
        <div class="form-inline">
            {!! Form::text('sale_end', $data['sale_end'], ['class' => 'form-control']) !!}
            {!! Form::checkbox('checkNoEnd', 'checkNoEnd', $data['no_end'], ['id' => 'checkNoEnd']) !!} No End Date
        </div>
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
