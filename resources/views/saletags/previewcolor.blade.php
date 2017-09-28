<div class="sale-preview-wrapper-color">
    <table class="sale-preview-table-color">
        <tr>
            <td style="height: 10px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-brand-color" style="height: 77px;">
                <div id="previewDispBrand" class="sale-preview-fix-wrap-color-brand" style="max-height: 77px;">
                    @isset($data['brand'])
                        {{ $data['brand'] }}
                    @endisset
                </div>
            </td>
        </tr>
        <tr>
            <td style="height: 3px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-desc-color" style="height: 75px;">
                <div id="previewDispDesc" class="sale-preview-fix-wrap-color" style="max-height: 75px; font-family: Rockwell;"></div>
            </td>
        </tr>
        <tr>
            <td style="height: 4px;">&nbsp;</td>
        </tr>
        <tr>
            <td id="previewDispUPC" class="sale-preview-upc-color" style="height: 8px;"></td>
        </tr>
        <tr>
            <td style="height: 8px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-price-color" style="height: 75px;"><span id="previewDispSalePrice"></span>!</td>
        </tr>
        <tr>
            <td style="height: 18px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-msrp-color" style="height: 21px;">Was $<span id="previewDispRegPrice"></span></td>
        </tr>
        <tr>
            <td style="height: 14px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-savings-color" style="height: 44px;">Save $<span id="previewDispSavings"></span></td>
        </tr>
        <tr>
            <td style="height: 15px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="height: 6px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-cat-color" style="height: 62px;">
                <div id="previewDispSaleCat" class="sale-preview-fix-wrap-color" style="max-height: 62px;">
                    @isset($data['sale_cat'])
                        {{ $data['sale_cat'] }}
                    @endisset
                </div>
            </td>
        </tr>
        <tr>
            <td style="height: 6px;">&nbsp;</td>
        </tr>
    </table>
</div>
