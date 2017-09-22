<div class="sale-preview-wrapper-bw" style="border: 1px solid black;">
    <table class="sale-preview-table-bw">
        <tr>
            <td style="height: 10px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-brand-bw" style="height: 78px;">
                <div id="previewDispBrand" class="sale-preview-fix-wrap-bw" style="max-height: 78px;">
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
            <td class="sale-preview-desc-bw" style="height: 75px;">
                <div id="previewDispDesc" class="sale-preview-fix-wrap-bw" style="max-height: 75px; font-family: Rockwell;"></div>
            </td>
        </tr>
        <tr>
            <td style="height: 4px;">&nbsp;</td>
        </tr>
        <tr>
            <td id="previewDispUPC" style="height: 8px;"></td>
        </tr>
        <tr>
            <td style="height: 8px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-price-bw" style="height: 75px;"><span id="previewDispSalePrice"></span>!</td>
        </tr>
        <tr>
            <td style="height: 18px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-msrp-bw" style="height: 21px;">Was $<span id="previewDispRegPrice"></span></td>
        </tr>
        <tr>
            <td style="height: 14px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-savings-bw" style="height: 44px;">Save $<span id="previewDispSavings"></span></td>
        </tr>
        <tr>
            <td style="height: 15px;">&nbsp;</td>
        </tr>
        <tr>
            <td class="sale-preview-cat-bw" style="height: 75px;">
                <div id="previewDispSaleCat" class="sale-preview-fix-wrap-bw" style="max-height: 75px;">
                    @isset($data['sale_cat'])
                        {{ $data['sale_cat'] }}
                    @endisset
                </div>
            </td>
        </tr>
    </table>
</div>
