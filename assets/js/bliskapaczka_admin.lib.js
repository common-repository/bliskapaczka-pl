/*
 * Wordpress BliskaPaczka Admin Library
 */
(function ($) {
   
    bliskapaczkaInitDownloadButtonsOnList = function () {

        const btnReportPickup = $("<button>")
            .addClass('button').addClass("bliskapaczka-btn-batch").addClass("bliskapaczka-btn-batch-pickup")
            .attr('title', bliskapaczka_report_pickup_multi_label)
            .html(bliskapaczka_report_pickup_multi_label)
            .bliskapaczkaListOrdersBatchOperation({
                url: bliskapaczka_report_pickup_multi_url
            });

        const btnWaybill = $("<button>")
            .addClass('button').addClass("bliskapaczka-btn-batch").addClass("bliskapaczka-btn-batch-waybill")
            .attr('title', bliskapaczka_waybill_multi_label)
            .html(bliskapaczka_waybill_multi_label)
            .bliskapaczkaListOrdersBatchOperation({
                url: bliskapaczka_waybill_multi_url
            });

        $(".bulkactions")
            .append(btnWaybill)
            .append(btnReportPickup);
    }; // .bliskapaczkaInitDownloadButtonsOnList

    $.fn.bliskapaczkaListOrdersBatchOperation = function (options) {

        var settings = $.extend({
            type: 'post',
            url: '',
            width: 600,
            height: 400
        }, options);

        this.each(function () {
            var el = $(this);

            el.on('click', function (event) {
                event.preventDefault();

                // verify operation in progress
                if ($(el).prop('disabled')) {
                    return;
                }

                //mark operation as in progress
                $(el).prop('disabled', true);

                // verify if orders are selected
                let ids = $('.check-column input[name = "post[]"]:checked');
                if (ids.length === 0) {
                    alert(bliskapaczka_msg_choose_orders_bulk);
                    $(el).removeProp('disabled', true);
                    return;
                }

                // process operation
                let txt = el.html();
                $(el).html(txt + ' <i class="fas fa-spinner fa-spin"></i>');

                var ordersIds = [];

                for (let i = 0, max = ids.length; i < max; i++) {
                    ordersIds.push($(ids[i]).val());
                }

                $.ajax({
                    url: settings.url,
                    type: settings.type,
                    dataType: 'json',
                    data: { orders: ordersIds },
                    success: function (data) {
                        $('#bliskapaczka-modal-body').html(data.content);
                        tb_show($(el).attr('title'), '#TB_inline?inlineId=bliskapaczka-modal'); // &width=' + settings.width + '&height=' + settings.height
                    },
                    error: function(data) {
                        alert("Wystąpił problem podczas wykonywania operacji.");
                    },
                    complete: function () {
                        $(el).html(txt);
                        $(el).removeProp('disabled');
                    }
                });
                
                return false;
            }); // .on

        }); // .each


        return this;
    }; // .bliskapaczkaListOrdersBatchOperation

} (jQuery));
