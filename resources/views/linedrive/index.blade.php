@extends('layouts.app-loader')

@section('content')
    {!! Form::open() !!}
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 col-sm-2 col-xs-2">
                @if(config('pos.driver') == 'orderdog')
                    &nbsp;
                @else
                    <ul class="nav nav-pills nav-stacked" style="float: left; position: fixed;">
                        <li><button type="submit" class="btn btn-success btn-block" name="process" value="add"><i class="fa fa-plus-circle"></i> Add Line Drive</button></li>
                        <li><button type="submit" class="btn btn-danger btn-block" name="process" value="delete"><i class="fa fa-trash-o"></i> Delete Selected</button></li>
                        <li>&nbsp;</li>
                        <li><input type="radio" name="filter" id="f_all" value="f_all" {{ $filter === 'f_all' ? 'checked="checked"' : '' }}> Show all items<br>
                        <input type="radio" name="filter" id="f_processed" value="f_processed" {{ $filter === 'f_processed' ? 'checked="checked"' : '' }}> Show only processed<br>
                        <input type="radio" name="filter" id="f_flagged" value="f_flagged" {{ $filter === 'f_flagged' ? 'checked="checked"' : '' }}> Show only flagged<br>
                        <input type="radio" name="filter" id="f_expired" value="f_expired" {{ $filter === 'f_expired' ? 'checked="checked"' : '' }}> Show only expired</li>
                        <li>&nbsp;</li>
                        <li><span id="job-count-processing">{{ $jobCounts['processing'] }}</span> sales being processed</li>
                        <li>&nbsp;</li>
                        <li><img id="job-count-loader" src="{{ asset('img/ajax-loader.gif') }}"
                            @if (($jobCounts['processing']) == 0)
                                style="display: none"
                            @endif
                        >
                        </li>
                    </ul>
                @endif
            </nav>
            <div class="col-md-10 col-sm-9 col-xs-9 col-md-offset-0 col-sm-offset-1 col-xs-offset-1">
                <!-- /.panel-default -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Line Drives ({{ number_format($items->count()) }} items)
                    </div>
                    <div class="panel-body">
                        @if(config('pos.driver') == 'orderdog')
                            Line Drives are not supported by the OrderDog driver
                        @else
                            @include('flash::message')
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" class="checkAllManual" /></th>
                                            <th class="text-nowrap">Brand</th>
                                            <th class="text-nowrap">Discount</th>
                                            <th class="text-center"><i class="fa fa-share" title="Processed" aria-hidden="true"></i></th>
                                            <th class="text-center"><i class="fa fa-flag" title="Flags" aria-hidden="true"></i></th>
                                        </tr>
                                    </thead>
                                    <?php $datesHeader = ''; $first = true; ?>
                                    @foreach ($items as $item)
                                        @if ($item->from_to != $datesHeader)
                                            @if (!$first)
                                                </tbody>
                                            @else
                                                <?php $first = false; ?>
                                            @endif
                                            <tbody>
                                            <?php $datesHeader = $item->from_to; ?>
                                            <tr>
                                                <th style="white-space: nowrap" colspan="5">
                                                    {{ $datesHeader }}
                                                </th>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><input type="checkbox" name="checked[]" value="{{ $item->id }}" /></td>
                                            <td class="text-nowrap">{{ $item->brand }}</td>
                                            <td class="text-nowrap text-center">{{ $item->discount }}%</td>
                                            <td class="text-nowrap text-center">
                                                @if ($item->processed)
                                                    <button type="button" class="btn btn-info btn-xs" disabled="disabled" title="Processed">
                                                        <i class="fa fa-share" aria-hidden="true"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="Not Processed">
                                                        <i class="fa fa-share" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="text-nowrap text-center">
                                                @if (!empty($item->flags))
                                                    <button type="button" class="btn btn-danger btn-xs" disabled="disabled" title="{{ $item->flags }}">
                                                        <i class="fa fa-flag" aria-hidden="true"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="No Flags">
                                                        <i class="fa fa-flag" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        @endif
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
    {!! Form::close() !!}
@endsection
