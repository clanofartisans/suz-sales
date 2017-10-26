{!! Form::open(['files' => true]) !!}

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="upmonth">Workbook Month</label>
            <select class="form-control" name="upmonth" id="upmonth">
                <option value="1" {{ $nextMonth == 1 ? "selected" : "" }}>January</option>
                <option value="2" {{ $nextMonth == 2 ? "selected" : "" }}>February</option>
                <option value="3" {{ $nextMonth == 3 ? "selected" : "" }}>March</option>
                <option value="4" {{ $nextMonth == 4 ? "selected" : "" }}>April</option>
                <option value="5" {{ $nextMonth == 5 ? "selected" : "" }}>May</option>
                <option value="6" {{ $nextMonth == 6 ? "selected" : "" }}>June</option>
                <option value="7" {{ $nextMonth == 7 ? "selected" : "" }}>July</option>
                <option value="8" {{ $nextMonth == 8 ? "selected" : "" }}>August</option>
                <option value="9" {{ $nextMonth == 9 ? "selected" : "" }}>September</option>
                <option value="10" {{ $nextMonth == 10 ? "selected" : "" }}>October</option>
                <option value="11" {{ $nextMonth == 11 ? "selected" : "" }}>November</option>
                <option value="12" {{ $nextMonth == 12 ? "selected" : "" }}>December</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="upyear">Workbook Year</label>
            <select class="form-control" name="upyear" id="upyear">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $nextYear == $year ? "selected" : "" }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="upworkbook">Workbook File</label>
            <input type="file" id="upworkbook" name="upworkbook">
        </div>
        <button type="submit" class="btn btn-primary" name="upsubmit" id="upsubmit"><i class="fa fa-cloud-upload"></i> Upload</button>
    </div>
</div>

{!! Form::close() !!}
