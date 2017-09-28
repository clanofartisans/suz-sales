<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <style type="text/css">
        @font-face {
            font-family: Rockwell;
            font-style: normal;
            font-weight: normal;
            src: url({{ asset('fonts/Rockwell.ttf') }}) format('truetype');
        }
        @font-face {
            font-family: Rockwell;
            font-style: normal;
            font-weight: bold;
            src: url({{ asset('fonts/Rockwell-Bold.ttf') }}) format('truetype');
        }
        @font-face {
            font-family: "Sketch Rockwell";
            font-style: normal;
            font-weight: normal;
            src: url({{ asset('fonts/SketchRockwell-Bold.ttf') }}) format('truetype');
        }

        .sale-wrapper {
            background-image: url('http://suz-sales.turners.pw/img/color-sale-bg.png');
            width: 600px;
            height: 900px;
            text-align: center;
            line-height: 1px;
            color: white;
            text-transform: uppercase;
        }
        .sale-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sale-fix-wrap {
            width: 550px;
            overflow: hidden;
        }
        .sale-fix-wrap-brand {
            width: 590px;
            overflow: hidden;
        }
        .sale-brand {
            font-family: "Sketch Rockwell";
            vertical-align: middle;
            line-height: 75px;
            font-size: 75px;
            padding: 0 5px;
            text-align: center;
            -webkit-text-stroke: 0.013em white;
            position: relative;
            top: -3px;
        }
        .sale-desc {
            font-family: "Rockwell";
            vertical-align: middle;
            line-height: 50px;
            font-size: 50px;
            padding: 0 25px;
            text-align: center;
            position: relative;
            top: -4px;
        }
        .sale-upc {
            font-family: "Rockwell";
            vertical-align: middle;
            font-size: 25px;
            text-align: center;
            position: relative;
            top: -6px;
        }
        .sale-price {
            font-family: "Sketch Rockwell";
            vertical-align: middle;
            line-height: 150px;
            font-size: 150px;
            position: relative;
            top: 3px;
            color: black;
            -webkit-text-stroke: 0.007em #664b36;
            text-align: center;
            position: relative;
            top: -7px;
        }
        .sale-msrp {
            font-family: "Rockwell";
            font-size: 50px;
            text-align: center;
            position: relative;
            top: -14px;
        }
        .sale-savings {
            font-family: "Rockwell";
            vertical-align: middle;
            font-weight: bold;
            font-size: 99px;
            position: relative;
            top: -1px;
            text-align: center;
            position: relative;
            top: -18px;
        }
        .sale-cat {
            font-family: "Sketch Rockwell";
            font-size: 75px;
            line-height: 66px;
            padding: 0 25px;
            color: black;
            -webkit-text-stroke: 0.002em black;
            text-align: center;
            position: relative;
            top: -23px;
        }
    </style>

    <!-- Scripts -->
    <script src="{{ mix('/js/app.js') }}"></script>
</head>
<body>
    <div class="sale-wrapper">
        <table class="sale-table">
            <tr>
                <td style="height: 20px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-brand" style="height: 155px;">
                    <div class="sale-fix-wrap-brand" style="max-height: 155px;">
                        Product Brand
                    </div>
                </td>
            </tr>
            <tr>
                <td style="height: 7px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-desc" style="height: 151px;">
                    <div class="sale-fix-wrap" style="max-height: 151px; font-family: Rockwell;">
                        Product Description Goes Here
                    </div>
                </td>
            </tr>
            <tr>
                <td style="height: 8px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-upc" style="height: 17px;">763948564453</td>
            </tr>
            <tr>
                <td style="height: 17px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-price" style="height: 150px;">$5.49!</td>
            </tr>
            <tr>
                <td style="height: 36px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-msrp" style="height: 42px;">Was $6.89</td>
            </tr>
            <tr>
                <td style="height: 28px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-savings" style="height: 89px;">Save $1.40</td>
            </tr>
            <tr>
                <td style="height: 31px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="height: 12px;">&nbsp;</td>
            </tr>
            <tr>
                <td class="sale-cat" style="height: 125px;">
                    <div class="sale-fix-wrap" style="max-height: 125px;">
                        September Savings
                    </div>
                </td>
            </tr>
            <tr>
                <td style="height: 13px;">&nbsp;</td>
            </tr>
        </table>
    </div>
</body>
</html>
