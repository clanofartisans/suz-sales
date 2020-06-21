{!! Form::open(['files' => true]) !!}

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="upmonth">Workbook Month</label>
            <select class="form-control" name="upmonth" id="upmonth">
                <option value="1" {{ $selectedMonth == 1 ? "selected" : "" }}>January</option>
                <option value="2" {{ $selectedMonth == 2 ? "selected" : "" }}>February</option>
                <option value="3" {{ $selectedMonth == 3 ? "selected" : "" }}>March</option>
                <option value="4" {{ $selectedMonth == 4 ? "selected" : "" }}>April</option>
                <option value="5" {{ $selectedMonth == 5 ? "selected" : "" }}>May</option>
                <option value="6" {{ $selectedMonth == 6 ? "selected" : "" }}>June</option>
                <option value="7" {{ $selectedMonth == 7 ? "selected" : "" }}>July</option>
                <option value="8" {{ $selectedMonth == 8 ? "selected" : "" }}>August</option>
                <option value="9" {{ $selectedMonth == 9 ? "selected" : "" }}>September</option>
                <option value="10" {{ $selectedMonth == 10 ? "selected" : "" }}>October</option>
                <option value="11" {{ $selectedMonth == 11 ? "selected" : "" }}>November</option>
                <option value="12" {{ $selectedMonth == 12 ? "selected" : "" }}>December</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="upyear">Workbook Year</label>
            <select class="form-control" name="upyear" id="upyear">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? "selected" : "" }}>{{ $year }}</option>
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
