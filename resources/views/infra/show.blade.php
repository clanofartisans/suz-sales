@extends('layouts.app-loader')

@section('content')
    {!! Form::open() !!}
    <input type="hidden" name="infrasheet" value="{{ $infrasheet->id }}">
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 col-sm-2 col-xs-2">
                <ul class="nav nav-pills nav-stacked" style="float: left;">
                    <li><button type="submit" class="btn btn-success btn-block" name="process" value="approve"><i class="fa fa-thumbs-o-up"></i> Approve</button></li>
                    <li><button type="submit" class="btn btn-success btn-block" name="process" value="approveall"><i class="fa fa-thumbs-o-up"></i> Approve All</button></li>
                    <li><button type="submit" class="btn btn-info btn-block" name="process" value="queue"><i class="fa fa-print"></i> Queue Selected</button></li>
                    <li><button type="submit" class="btn btn-info btn-block" name="process" value="print"><i class="fa fa-print"></i> Print Queued Items</button></li>
                    <li>&nbsp;</li>
                    <li><input type="radio" name="filter" id="f_all" value="f_all" {{ $filter === 'f_all' ? 'checked="checked"' : '' }}> Show all items<br>
                    <input type="radio" name="filter" id="f_approved" value="f_approved" {{ $filter === 'f_approved' ? 'checked="checked"' : '' }}> Show only approved<br>
                    <input type="radio" name="filter" id="f_processed" value="f_processed" {{ $filter === 'f_processed' ? 'checked="checked"' : '' }}> Show only processed<br>
                    <input type="radio" name="filter" id="f_queued" value="f_queued" {{ $filter === 'f_queued' ? 'checked="checked"' : '' }}> Show queued for printing<br>
                    <input type="radio" name="filter" id="f_printed" value="f_printed" {{ $filter === 'f_printed' ? 'checked="checked"' : '' }}> Show only printed<br>
                    <input type="radio" name="filter" id="f_flagged" value="f_flagged" {{ $filter === 'f_flagged' ? 'checked="checked"' : '' }}> Show only flagged<br>
                    <input type="radio" name="filter" id="f_flagged_discounted" value="f_flagged_discounted" {{ $filter === 'f_flagged_discounted' ? 'checked="checked"' : '' }}> &bull; Already discounted<br>
                    <input type="radio" name="filter" id="f_flagged_lowprice" value="f_flagged_lowprice" {{ $filter === 'f_flagged_lowprice' ? 'checked="checked"' : '' }}> &bull; Price &lt; sale<br>
                    <input type="radio" name="filter" id="f_flagged_notfound" value="f_flagged_notfound" {{ $filter === 'f_flagged_notfound' ? 'checked="checked"' : '' }}> &bull; Not in {{ config('pos.shortname') }}</li>
                    <li>&nbsp;</li>
                    <li><span id="queue-count-infra">{{ $queueCount }}</span> tags in print queue</li>
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
                        {{ $infrasheet->month }} {{ $infrasheet->year }} Items ({{ number_format($items->total()) }} items)
                        <div class="pull-right">{{ $items->links() }}</div>
                    </div>
                    <div class="panel-body">
                        @include('flash::message')
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th class="text-nowrap">UPC</th>
                                        <th class="text-nowrap">Brand</th>
                                        <th class="text-nowrap">Description</th>
                                        <th class="text-nowrap">Size</th>
                                        <th class="text-nowrap">INFRA $</th>
                                        <th class="text-nowrap">Real $</th>
                                        <th class="text-center"><i class="fa fa-thumbs-up" title="Approved" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-share" title="Processed" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-file-image-o" title="Ready to Print" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-print" title="Printed" aria-hidden="true"></i></th>
                                        <th class="text-center"><i class="fa fa-flag" title="Flags" aria-hidden="true"></i></th>
                                    </tr>
                                </thead>
                                <?php $brandHeader = ''; $first = true; ?>
                                @foreach ($items as $item)
                                    @if ($item->brand != $brandHeader)
                                        @if (!$first)
                                            </tbody>
                                        @else
                                            <?php $first = false; ?>
                                        @endif
                                        <tbody>
                                        <?php $brandHeader = $item->brand; ?>
                                        <tr>
                                            <th><input type="checkbox" class="checkAll" /></th>
                                            <th style="white-space: nowrap" colspan="11">
                                                {{ $brandHeader }}
                                            </th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><input type="checkbox" name="checked[]" value="{{ $item->id }}" /></td>
                                        <td class="text-nowrap">{{ $item->upc }}</td>
                                        <td class="text-nowrap">{{ $item->brand }}</td>
                                        <td>{{ $item->desc }}</td>
                                        <td class="text-nowrap">{{ $item->size }}</td>
                                        <td class="text-nowrap text-center">{{ $item->list_price }}</td>
                                        <td class="text-nowrap text-center">{{ $item->list_price_calc }}</td>
                                        <td class="text-nowrap text-center">
                                            @if ($item->approved)
                                                <button type="button" class="btn btn-success btn-xs" disabled="disabled" title="Approved">
                                                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="Not Approved">
                                                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </td>
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
                                                <button type="button" class="btn btn-xs print-bw" disabled="disabled" title="Ready to Print">
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
