@extends('layouts.app-loader')

@section('content')
    {!! Form::open() !!}
    <nav class="col-sm-1">
      <ul class="nav nav-pills nav-stacked" style="float: left;" data-spy="affix">
        <li><button type="submit" class="btn btn-success btn-block" name="process" value="add"><i class="fa fa-plus-circle"></i> Add Sales</button></li>
        <li><button type="submit" class="btn btn-info btn-block" name="process" value="print"><i class="fa fa-print"></i> Print Selected</button></li>
        <li><button type="submit" class="btn btn-info btn-block" name="process" value="printallbw"><i class="fa fa-print"></i> Print All B&W</button></li>
        <li><button type="submit" class="btn btn-info btn-block" name="process" value="printallcolor"><i class="fa fa-print"></i> Print All Color</button></li>
        <li><button type="submit" class="btn btn-warning btn-block" name="process" value="reprocess"><i class="fa fa-refresh"></i> Reprocess Selected</button></li>
        <li><button type="submit" class="btn btn-danger btn-block" name="process" value="delete"><i class="fa fa-trash-o"></i> Delete Selected</button></li>
        <li>&nbsp;</li>
        <li><input type="radio" name="filter" id="f_all" value="f_all" {{ $filter === 'f_all' ? 'checked="checked"' : '' }}> Show all items<br>
            <input type="radio" name="filter" id="f_processed" value="f_processed" {{ $filter === 'f_processed' ? 'checked="checked"' : '' }}> Show only processed<br>
            <input type="radio" name="filter" id="f_ready_to_print" value="f_ready_to_print" {{ $filter === 'f_ready_to_print' ? 'checked="checked"' : '' }}> Show ready to print<br>
            <input type="radio" name="filter" id="f_img_bw" value="f_img_bw" {{ $filter === 'f_img_bw' ? 'checked="checked"' : '' }}> Show B&amp;W tags<br>
            <input type="radio" name="filter" id="f_img_color" value="f_img_color" {{ $filter === 'f_img_color' ? 'checked="checked"' : '' }}> Show color tags<br>
            <input type="radio" name="filter" id="f_printed" value="f_printed" {{ $filter === 'f_printed' ? 'checked="checked"' : '' }}> Show only printed<br>
            <input type="radio" name="filter" id="f_flagged" value="f_flagged" {{ $filter === 'f_flagged' ? 'checked="checked"' : '' }}> Show only flagged</li>
            <input type="radio" name="filter" id="f_expired" value="f_expired" {{ $filter === 'f_expired' ? 'checked="checked"' : '' }}> Show only expired</li>
        <li>&nbsp;</li>
        <li><span id="job-count-processing">{{ $jobCounts['processing'] }}</span> sales being processed</li>
        <li><span id="job-count-imaging">{{ $jobCounts['imaging'] }}</span> tags being generated</li>
        <li>&nbsp;</li>
        <li><img id="job-count-loader" src="{{ asset('img/ajax-loader.gif') }}"
                 @if (($jobCounts['processing'] + $jobCounts['imaging']) == 0)
                    style="display: none"
                 @endif
            >
        </li>
      </ul>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- /.panel-default -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Manually Created Sales ({{ number_format($items->total()) }} items)
                        <div class="pull-right">{{ $items->links() }}</div>
                    </div>
                    <div class="panel-body">
                        @include('flash::message')
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="checkAllManual" /></th>
                                        <th class="text-nowrap">UPC</th>
                                        <th class="text-nowrap">Brand</th>
                                        <th class="text-nowrap">Description</th>
                                        <th class="text-nowrap">Reg. $</th>
                                        <th class="text-nowrap">Sale $</th>
                                        <th class="text-nowrap">Begin</th>
                                        <th class="text-nowrap">End</th>
                                        <th class="text-center"><i class="fa fa-share" title="Processed" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-file-image-o" title="Ready to Print" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-print" title="Printed" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-flag" title="Flags" aria-hidden="true"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td><input type="checkbox" name="checked[]" value="{{ $item->id }}" /></td>
                                        <td class="text-nowrap">{{ $item->upc }}</td>
                                        <td class="text-nowrap">{{ $item->brand }}</td>
                                        <td>{{ $item->desc }}</td>
                                        <td class="text-nowrap text-center">{{ $item->reg_price }}</td>
                                        <td class="text-nowrap text-center">{{ $item->disp_sale_price }}</td>
                                        <td class="text-nowrap text-center">{{ $item->sale_begin->toFormattedDateString() }}</td>
                                        <td class="text-nowrap text-center">{{ $item->sale_end->toFormattedDateString() }}</td>
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
                                            @if ($item->imaged)
                                                <button type="button" class="btn btn-xs @if($item->color) {{ 'print-color' }} @else {{ 'print-bw' }} @endif" disabled="disabled" title="Ready to Print">
                                                    <i class="fa fa-file-image-o" aria-hidden="true"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="Not Ready to Print">
                                                    <i class="fa fa-file-image-o" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-center">
                                            @if ($item->printed)
                                                <button type="button" class="btn btn-primary btn-xs" disabled="disabled" title="Printed">
                                                    <i class="fa fa-print" aria-hidden="true"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="Not Printed">
                                                    <i class="fa fa-print" aria-hidden="true"></i>
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
                    </div>
                    <!-- /.panel-body -->
                    <div class="panel-footer">&nbsp;<div class="pull-right">{{ $items->links() }}</div></div>
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
