/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
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
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/app-footer.js":
/*!************************************!*\
  !*** ./resources/js/app-footer.js ***!
  \************************************/
/*! no static exports found */
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
window.setInterval(function () {
  $.ajax({
    dataType: "json",
    url: '/ajax/jobCounts',
    success: function success(result) {
      $('#job-count-processing').html(result.processing);
      $('#job-count-imaging').html(result.imaging);

      if (result.processing + result.imaging > 0) {
        $('#job-count-loader').show();
      } else {
        $('#job-count-loader').hide();
      }
    }
  });
}, 2000);
window.setInterval(function () {
  $.ajax({
    dataType: "json",
    url: '/ajax/queueCountInfra',
    success: function success(result) {
      $('#queue-count-infra').html(result.infra);
    }
  });
}, 10000);
window.setInterval(function () {
  $.ajax({
    dataType: "json",
    url: '/ajax/queueCountsManual',
    success: function success(result) {
      $('#queue-count-bw').html(result.bw);
      $('#queue-count-color').html(result.color);
    }
  });
}, 10000); // Manual Sale Tag Preview Scripts

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
  $('#previewInputPercentOff').val('');
});
$('#previewInputDispPrice').keyup(function () {
  $('#previewDispSalePrice').html($(this).val());
});
$('#previewInputRegPrice').keyup(function () {
  var savings = ($(this).val() - $('#previewInputSalePrice').val()).toFixed(2);
  $('#previewDispRegPrice').html($(this).val());
  $('#previewInputSavings').val(savings);
  $('#previewDispSavings').html(savings);
  $('#previewInputPercentOff').val('');
});
$('#previewInputSavings').keyup(function () {
  $('#previewDispSavings').html($(this).val());
});
$('#previewInputPercentOff').keyup(function () {
  var savings = ($('#previewInputRegPrice').val() * ($(this).val() / 100.00)).toFixed(2);
  var salePrice = ($('#previewInputRegPrice').val() - savings).toFixed(2);
  $('#previewInputSalePrice').val(salePrice);
  $('#previewInputDispPrice').val('$' + salePrice);
  $('#previewDispSalePrice').html('$' + salePrice);
  $('#previewInputSavings').val(savings);
  $('#previewDispSavings').html(savings);
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
$('#previewPOSFill').click(function () {
  $.ajax({
    dataType: "json",
    url: '/manual/preview/posquery/' + $('#previewInputUPC').val(),
    success: function success(result) {
      if (result) {
        $('#posNotFound').hide();
        $('#previewInputBrand').val(result.brand);
        $('#previewDispBrand').html(result.brand);
        $('#previewInputDesc').val(result.desc);
        $('#previewDispDesc').html(result.desc);
        $('#previewInputRegPrice').val(result.price);
        $('#previewDispRegPrice').html(result.price);

        if ($('#previewInputPercentOff').val() != '') {
          var savings = (result.price * ($('#previewInputPercentOff').val() / 100.00)).toFixed(2);
          var salePrice = (result.price - savings).toFixed(2);
          $('#previewInputSalePrice').val(salePrice);
          $('#previewInputDispPrice').val('$' + salePrice);
          $('#previewDispSalePrice').html('$' + salePrice);
          $('#previewInputSavings').val(savings);
          $('#previewDispSavings').html(savings);
        }
      } else {
        $('#posNotFound').show();
      }
    }
  });
  return false;
});
$(function () {
  $("#sale_begin").datepicker();
});
$(function () {
  $("#sale_end").datepicker();
});
$('#sale_begin').change(function () {
  $('#checkNoBegin').prop('checked', false);
});
$('#checkNoBegin').click(function () {
  if ($('#checkNoBegin').is(':checked')) {
    $('#sale_begin').val(null);
  }
});
$('#sale_end').change(function () {
  $('#checkNoEnd').prop('checked', false);
});
$('#checkNoEnd').click(function () {
  if ($('#checkNoEnd').is(':checked')) {
    $('#sale_end').val(null);
  }
});

/***/ }),

/***/ 1:
/*!******************************************!*\
  !*** multi ./resources/js/app-footer.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\Projects\Projects\suz-sales\resources\js\app-footer.js */"./resources/js/app-footer.js");


/***/ })

/******/ });