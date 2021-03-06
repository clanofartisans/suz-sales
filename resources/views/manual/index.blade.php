@extends('layouts.app-loader')

@section('content')
    {!! Form::open() !!}
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 col-sm-2 col-xs-2">
                <ul class="nav nav-pills nav-stacked" style="float: left; position: fixed;">
                    <li><button type="submit" class="btn btn-success btn-block" name="process" value="add"><i class="fa fa-plus-circle"></i> Add Sales</button></li>
                    <li><button type="submit" class="btn btn-info btn-block" name="process" value="queue"><i class="fa fa-print"></i> Queue Selected</button></li>
                    <li><button type="submit" class="btn btn-info btn-block" name="process" value="printbwqueue"><i class="fa fa-print"></i> Print B&amp;W Queue</button></li>
                    <li><button type="submit" class="btn btn-info btn-block" name="process" value="printcolorqueue"><i class="fa fa-print"></i> Print Color Queue</button></li>
                    <li><button type="submit" class="btn btn-warning btn-block" name="process" value="reprocess"><i class="fa fa-refresh"></i> Reprocess Selected</button></li>
                    <li><button type="submit" class="btn btn-danger btn-block" name="process" value="delete"><i class="fa fa-trash-o"></i> Delete Selected</button></li>
                    <li>&nbsp;</li>
                    <li><input type="radio" name="filter" id="f_unprinted" value="f_unprinted" {{ $filter === 'f_unprinted' ? 'checked="checked"' : '' }}> Show unprinted items<br>
                    <input type="radio" name="filter" id="f_all" value="f_all" {{ $filter === 'f_all' ? 'checked="checked"' : '' }}> Show all items<br>
                    <input type="radio" name="filter" id="f_processed" value="f_processed" {{ $filter === 'f_processed' ? 'checked="checked"' : '' }}> Show only processed<br>
                    <input type="radio" name="filter" id="f_queued" value="f_queued" {{ $filter === 'f_queued' ? 'checked="checked"' : '' }}> Show queued for printing<br>
                    <input type="radio" name="filter" id="f_img_bw" value="f_img_bw" {{ $filter === 'f_img_bw' ? 'checked="checked"' : '' }}> Show B&amp;W tags<br>
                    <input type="radio" name="filter" id="f_img_color" value="f_img_color" {{ $filter === 'f_img_color' ? 'checked="checked"' : '' }}> Show color tags<br>
                    <input type="radio" name="filter" id="f_printed" value="f_printed" {{ $filter === 'f_printed' ? 'checked="checked"' : '' }}> Show only printed<br>
                    <input type="radio" name="filter" id="f_flagged" value="f_flagged" {{ $filter === 'f_flagged' ? 'checked="checked"' : '' }}> Show only flagged<br>
                    <input type="radio" name="filter" id="f_flagged_discounted" value="f_flagged_discounted" {{ $filter === 'f_flagged_discounted' ? 'checked="checked"' : '' }}> &bull; Already discounted<br>
                    <input type="radio" name="filter" id="f_flagged_lowprice" value="f_flagged_lowprice" {{ $filter === 'f_flagged_lowprice' ? 'checked="checked"' : '' }}> &bull; Price &lt; sale<br>
                    <input type="radio" name="filter" id="f_flagged_notfound" value="f_flagged_notfound" {{ $filter === 'f_flagged_notfound' ? 'checked="checked"' : '' }}> &bull; Not in {{ config('pos.shortname') }}<br>
                    <input type="radio" name="filter" id="f_expired" value="f_expired" {{ $filter === 'f_expired' ? 'checked="checked"' : '' }}> Show only expired</li>
                    <li>&nbsp;</li>
                    <li><span id="queue-count-bw">{{ $queueCounts['bw'] }}</span> B&amp;W tags in print queue</li>
                    <li><span id="queue-count-color">{{ $queueCounts['color'] }}</span> color tags in print queue</li>
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
            <div class="col-md-10 col-sm-9 col-xs-9 col-md-offset-0 col-sm-offset-1 col-xs-offset-1">
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
                                <?php $brandHeader = ''; $first = true; ?>
                                @foreach ($items as $item)
                                    @if ($item->brand_uc != strtoupper($brandHeader))
                                        @if (!$first)
                                            </tbody>
                                        @else
                                            <?php $first = false; ?>
                                        @endif
                                        <tbody>
                                        <?php $brandHeader = $item->brand; ?>
                                        <tr>
                                            <th><input type="checkbox" class="checkAll" /></th>
                                            <th style="white-space: nowrap"
                                                @if(empty(Request::get('debug')))
                                                    colspan="2"
                                                @else
                                                    colspan="3"
                                                @endif
                                            >{{ $brandHeader }}</th>
                                            <th class="text-nowrap">Description</th>
                                            <th class="text-nowrap text-center">Sale $</th>
                                            <th class="text-nowrap text-center">MSRP</th>
                                            <th class="text-nowrap text-center">Sale %</th>
                                            <th class="text-nowrap text-center">Begin</th>
                                            <th class="text-nowrap text-center">End</th>
                                            <th class="text-nowrap text-center">Category</th>
                                            <th class="text-center"><i class="fa fa-share" title="Processed" aria-hidden="true"></i></th>
                                            <th class="text-center"><i class="fa fa-file-image-o" title="Ready to Print" aria-hidden="true"></i></th>
                                            <th class="text-center"><i class="fa fa-print" title="Printed" aria-hidden="true"></i></th>
                                            <th class="text-center"><i class="fa fa-flag" title="Flags" aria-hidden="true"></i></th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><input type="checkbox" name="checked[]" value="{{ $item->id }}" /></td>
                                        @if(!empty(Request::get('debug')))
                                            <td class="text-nowrap"><strong>SMMS{{ $item->id }}</strong></td>
                                        @endif
                                        <td class="text-nowrap">{{ $item->upc }}</td>
                                        <td class="text-nowrap">{{ $item->brand }}</td>
                                        <td>{{ $item->desc }}</td>
                                        <td class="text-nowrap text-center">{{ $item->disp_sale_price }}</td>
                                        <td class="text-nowrap text-center">${{ number_format($item->reg_price, 2) }}</td>
                                        <td class="text-nowrap text-center">{{ number_format($item->percent_off, 0) }}%</td>
                                        <td class="text-nowrap text-center">
                                            @if (is_null($item->sale_begin))
                                                &mdash;
                                            @else
                                                {{ $item->sale_begin->toFormattedDateString() }}
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-center">
                                            @if($item->no_end)
                                                Forever
                                            @else
                                                @if (is_null($item->sale_end))
                                                    &mdash;
                                                @else
                                                    {{ $item->sale_end->toFormattedDateString() }}
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-center">{{ $item->sale_cat }}</td>
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
                                            @elseif ($item->queued)
                                                <button type="button" class="btn btn-primary btn-xs" disabled="disabled" title="Queued">
                                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
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
