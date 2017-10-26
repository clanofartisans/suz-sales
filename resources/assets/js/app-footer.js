$('div.alert').not('.alert-important').delay(3000).slideUp(300);

jQuery(document).ready(function($) {
    $(".clickable").click(function() {
        window.document.location = $(this).data("url");
    });
});

$('#delete-confirm-submit').click(function() {
    $('#delete-form').submit();
});

$('.checkAll').on('click',function(){
    if($(this).is(':checked')) {
        $(this).closest('tbody').find('input[type="checkbox"]').prop('checked','checked');
    } else {
        $(this).closest('tbody').find('input[type="checkbox"]').prop('checked','');
    }
});

$('.checkAllManual').on('click',function(){
    if($(this).is(':checked')) {
        $(this).closest('table').children('tbody') .find('input[type="checkbox"]').prop('checked','checked');
    } else {
        $(this).closest('table').children('tbody') .find('input[type="checkbox"]').prop('checked','');
    }
});

$(document).ready(function() {
    $('input[name=filter]').change(function(){
        $('form').submit();
    });
});

$("#upsubmit").on("click", function(event){
    $.LoadingOverlay("show");
});

window.setInterval(function(){
	$.ajax({dataType: "json", url: '/ajax/jobCounts', success: function(result){
        $('#job-count-processing').html(result.processing);
		$('#job-count-imaging').html(result.imaging);

		if((result.processing + result.imaging) > 0) {
            $('#job-count-loader').show();
        } else {
            $('#job-count-loader').hide();
        }
	}});
}, 2000);

window.setInterval(function(){
	$.ajax({dataType: "json", url: '/ajax/queueCountInfra', success: function(result){
        $('#queue-count-infra').html(result.infra);
	}});
}, 10000);

window.setInterval(function(){
	$.ajax({dataType: "json", url: '/ajax/queueCountsManual', success: function(result){
        $('#queue-count-bw').html(result.bw);
		$('#queue-count-color').html(result.color);
	}});
}, 10000);

// Manual Sale Tag Preview Scripts

$('#previewInputUPC').keyup(function() {
    $('#previewDispUPC').html($(this).val());
});

$('#previewInputBrand').keyup(function() {
    $('#previewDispBrand').html($(this).val());
});

$('#previewInputDesc').keyup(function() {
    $('#previewDispDesc').html($(this).val());
});

$('#previewInputSalePrice').keyup(function() {
    var savings = (($('#previewInputRegPrice').val() - $(this).val()).toFixed(2));
    $('#previewInputDispPrice').val('$' + $(this).val());
    $('#previewDispSalePrice').html('$' + $(this).val());
    $('#previewInputSavings').val(savings);
    $('#previewDispSavings').html(savings);
    $('#previewInputPercentOff').val('');
});

$('#previewInputDispPrice').keyup(function() {
    $('#previewDispSalePrice').html($(this).val());
});

$('#previewInputRegPrice').keyup(function() {
    var savings = (($(this).val() - $('#previewInputSalePrice').val()).toFixed(2));
    $('#previewDispRegPrice').html($(this).val());
    $('#previewInputSavings').val(savings);
    $('#previewDispSavings').html(savings);
    $('#previewInputPercentOff').val('');
});

$('#previewInputSavings').keyup(function() {
    $('#previewDispSavings').html($(this).val());
});

$('#previewInputPercentOff').keyup(function() {
    var savings = (($('#previewInputRegPrice').val() * ($(this).val() / 100.00)).toFixed(2));
    var salePrice = (($('#previewInputRegPrice').val() - savings).toFixed(2));
    $('#previewInputSalePrice').val(salePrice);
    $('#previewInputDispPrice').val('$' + salePrice);
    $('#previewDispSalePrice').html('$' + salePrice);
    $('#previewInputSavings').val(savings);
    $('#previewDispSavings').html(savings);
});

$('#previewInputSaleCat').keyup(function() {
    $('#previewDispSaleCat').html($(this).val());
});

$('#radioBW').click(function() {
    if($('#radioBW').is(':checked')) {
        $('#preview-container').load('/manual/preview/bw', function() {
            $('#previewDispUPC').html($('#previewInputUPC').val());
            $('#previewDispBrand').html($('#previewInputBrand').val());
            $('#previewDispDesc').html($('#previewInputDesc').val());
            $('#previewDispSalePrice').html($('#previewInputDispPrice').val());
            $('#previewDispRegPrice').html($('#previewInputRegPrice').val());
            $('#previewDispSavings').html($('#previewInputSavings').val());
            $('#previewDispSaleCat').html($('#previewInputSaleCat').val());
        });
    }
});

$('#radioColor').click(function() {
    if($('#radioColor').is(':checked')) {
        $('#preview-container').load('/manual/preview/color', function() {
            $('#previewDispUPC').html($('#previewInputUPC').val());
            $('#previewDispBrand').html($('#previewInputBrand').val());
            $('#previewDispDesc').html($('#previewInputDesc').val());
            $('#previewDispSalePrice').html($('#previewInputDispPrice').val());
            $('#previewDispRegPrice').html($('#previewInputRegPrice').val());
            $('#previewDispSavings').html($('#previewInputSavings').val());
            $('#previewDispSaleCat').html($('#previewInputSaleCat').val());
        });
    }
});

$('#previewODFill').click(function(){
	$.ajax({dataType: "json", url: ('/manual/preview/odquery/' + ($('#previewInputUPC').val())), success: function(result){
	    if(result) {
            $('#odNotFound').hide();
            $('#previewInputBrand').val(result.brand);
            $('#previewDispBrand').html(result.brand);
            $('#previewInputDesc').val(result.desc);
            $('#previewDispDesc').html(result.desc);
            $('#previewInputRegPrice').val(result.price);
            $('#previewDispRegPrice').html(result.price);
            if($('#previewInputPercentOff').val() != '') {
                var savings = ((result.price * ($('#previewInputPercentOff').val() / 100.00)).toFixed(2));
                var salePrice = ((result.price - savings).toFixed(2));
                $('#previewInputSalePrice').val(salePrice);
                $('#previewInputDispPrice').val('$' + salePrice);
                $('#previewDispSalePrice').html('$' + salePrice);
                $('#previewInputSavings').val(savings);
                $('#previewDispSavings').html(savings);
            }
        } else {
            $('#odNotFound').show();
        }
	}});
	return false;
});

$(function() {
    $( "#sale_begin" ).datepicker();
});

$(function() {
    $( "#sale_end" ).datepicker();
});
