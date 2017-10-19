/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 37);
/******/ })
/************************************************************************/
/******/ ({

/***/ 37:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(8);


/***/ }),

/***/ 8:
/***/ (function(module, exports) {

$('div.alert').not('.alert-important').delay(3000).slideUp(300);

jQuery(document).ready(function ($) {
    $(".clickable").click(function () {
        window.document.location = $(this).data("url");
    });
});

$('#delete-confirm-submit').click(function () {
    $('#delete-form').submit();
});

$('.checkAll').on('click', function () {
    if ($(this).is(':checked')) {
        $(this).closest('tbody').find('input[type="checkbox"]').prop('checked', 'checked');
    } else {
        $(this).closest('tbody').find('input[type="checkbox"]').prop('checked', '');
    }
});

$('.checkAllManual').on('click', function () {
    if ($(this).is(':checked')) {
        $(this).closest('table').children('tbody').find('input[type="checkbox"]').prop('checked', 'checked');
    } else {
        $(this).closest('table').children('tbody').find('input[type="checkbox"]').prop('checked', '');
    }
});

$(document).ready(function () {
    $('input[name=filter]').change(function () {
        $('form').submit();
    });
});

$("#upsubmit").on("click", function (event) {
    $.LoadingOverlay("show");
});
/*
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
*/
// Manual Sale Tag Preview Scripts

$('#previewInputUPC').keyup(function () {
    $('#previewDispUPC').html($(this).val());
});

$('#previewInputBrand').keyup(function () {
    $('#previewDispBrand').html($(this).val());
});

$('#previewInputDesc').keyup(function () {
    $('#previewDispDesc').html($(this).val());
});

$('#previewInputSalePrice').keyup(function () {
    var savings = ($('#previewInputRegPrice').val() - $(this).val()).toFixed(2);
    $('#previewInputDispPrice').val('$' + $(this).val());
    $('#previewDispSalePrice').html('$' + $(this).val());
    $('#previewInputSavings').val(savings);
    $('#previewDispSavings').html(savings);
});

$('#previewInputDispPrice').keyup(function () {
    $('#previewDispSalePrice').html($(this).val());
});

$('#previewInputRegPrice').keyup(function () {
    var savings = ($(this).val() - $('#previewInputSalePrice').val()).toFixed(2);
    $('#previewDispRegPrice').html($(this).val());
    $('#previewInputSavings').val(savings);
    $('#previewDispSavings').html(savings);
});

$('#previewInputSavings').keyup(function () {
    $('#previewDispSavings').html($(this).val());
});

$('#previewInputSaleCat').keyup(function () {
    $('#previewDispSaleCat').html($(this).val());
});

$('#radioBW').click(function () {
    if ($('#radioBW').is(':checked')) {
        $('#preview-container').load('/manual/preview/bw', function () {
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

$('#radioColor').click(function () {
    if ($('#radioColor').is(':checked')) {
        $('#preview-container').load('/manual/preview/color', function () {
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

$('#previewODFill').click(function () {
    $.ajax({ dataType: "json", url: '/manual/preview/odquery/' + $('#previewInputUPC').val(), success: function success(result) {
            $('#previewInputBrand').val(result.brand);
            $('#previewDispBrand').html(result.brand);
            $('#previewInputDesc').val(result.desc);
            $('#previewDispDesc').html(result.desc);
            $('#previewInputRegPrice').val(result.price);
            $('#previewDispRegPrice').html(result.price);
        } });
});

$(function () {
    $("#sale_begin").datepicker();
});

$(function () {
    $("#sale_end").datepicker();
});

/***/ })

/******/ });