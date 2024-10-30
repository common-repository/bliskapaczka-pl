function Bliskapaczka()
{
}

Bliskapaczka.showMap = function (operators, googleMapApiKey, testMode, codOnly = false) {

    if (!!event.pageX === false) {
    return false;
    }
    bpWidget = document.getElementById('bpWidget');

    myModal = document.getElementById('bliskapaczka-modal');
    bpWidget.classList.add('bliskapaczka-modal-content');
    bpWidget.style.display = 'block';
    myModal.classList.add('bliskapaczka-modal');
    myModal.style.display = 'block';

    var nodes = document.querySelectorAll('input.bliskapaczka-point-code');
    let posCode = nodes[nodes.length- 1].value;

    var lnodes = document.querySelectorAll('input.bliskapaczka-point-operator');
    let posOperator = lnodes[lnodes.length- 1].value;


    if (posCode === "") {
        document.querySelector('input.bliskapaczka-point-code').value = '';
        document.querySelector('input.bliskapaczka-point-operator').value = '';
    }

    var clickableElement = document.querySelector('input[value="bliskapaczka"]');
    if (clickableElement){
    clickableElement.click();
    }

    BPWidget.init(
        bpWidget,
        {
            googleMapApiKey: googleMapApiKey,
            callback: function (data) {

                posCode = data.code;
                posOperator = data.operator;

                document.querySelector('input.bliskapaczka-point-code').value = posCode;
                document.querySelector('input.bliskapaczka-point-operator').value = posOperator;

                Bliskapaczka.pointSelected(data, operators);
            },
            operators: operators,
            posType: 'DELIVERY',
            testMode: testMode,
            codOnly: codOnly,
            showCod: false,
            selectedPos: {
            	code: posCode,
            	operator: posOperator
            }
        }
    );
}

Bliskapaczka.pointSelected = function (data, operators) {
    var modal = document.getElementById("bliskapaczka-modal");
    if (modal) {
        modal.style.display = "none";
        document.body.dispatchEvent(new Event("update_checkout"));

    }
}

/**
 * Show loader spinner on element.
 *
 * ex. Bliskapaczka.loadBlock('div.my_class');
 *
 * @param {String} selector jQuery element selector string
 */
Bliskapaczka.loadBlock = function( selector ) {
	jQuery( selector ).addClass( 'processing' ).block( {
		message: null,
		overlayCSS: {
			background: '#fff',
			opacity: 0.6
		}
	});
}

/**
 * Hide loader spinner on element
 *
 * ex. Bliskapaczka.loadUnblock('div.my_class');
 *
 * @param {String} selector jQuery element selector
 */
Bliskapaczka.loadUnblock = function( selector ) {
	jQuery( selector ).removeClass( 'processing' ).unblock();
};

document.addEventListener("DOMContentLoaded", function () {


    //Close modal on click outside
    var modalContent = document.querySelector('#bliskapaczka-modal');

    if (modalContent) {
        modalContent.addEventListener('click', function (e) {
            if (document.getElementById('bpWidget').contains(e.target)) {
            } else {
                modalContent.style.display = "none";
            }
        });
    }


    var formCheckout = document.querySelector('form.checkout');
    if (formCheckout) {
        formCheckout.addEventListener('change', function () {
            document.body.dispatchEvent(new Event("update_checkout"));
        });
    }

     document.querySelector('body').addEventListener('updated_checkout', function(){
         console.log("Updated checkout");
         if (document.querySelector('input[value="bliskapaczka"]:checked') == true) {
            var a = document.querySelector('a[href="#bpWidget_wrapper"]');
            var arguments = a.attr('onclick');
            eval(arguments);
        }
    });

    /**
     * Remember choosed courier and show new total order price on cart page
     */
    jQuery('body').on('click', '.bliskapaczka_courier_item_wrapper', function () {

    	 // loader
    	 Bliskapaczka.loadBlock('div.cart_totals');

    	 const previousCourier = document.querySelector('.bliskapaczka_courier_item_wrapper.checked').getAttribute('data-operator');
    	 const currentCourier = this.getAttribute('data-operator');

    	 // if data no changed then return
    	 if (previousCourier === currentCourier) {
    		 Bliskapaczka.loadUnblock('div.cart_totals');
    		 return;
    	 }

        var classRemover = document.querySelectorAll('.bliskapaczka_courier_item_wrapper');
        for (let i = 0; i < classRemover.length; i++) {
            classRemover[i].classList.remove('checked');
        }

        this.classList.add('checked');

    	 // remember selected courier
    	 var data = {
	        action: 'bliskapaczka_delivery_to_door_switch_courier', //the function in php functions to call
	        bliskapaczka_door_operator: currentCourier,
	        security: BliskapaczkaAjax.security,
    	 };

    	 jQuery
	    	 .post(BliskapaczkaAjax.ajax_url, data, function( response ) {
	    		 if (typeof response !== 'undefined' && response.order_total_html !== 'undefined') {
                     document.querySelector('.order-total td').innerHTML = response.order_total_html;
	    		 }

	    		 // if the shipping method is not checked, we update it
                    if (!document.querySelector('input[value="bliskapaczka-courier"]:checked') == true) {
                        var simClick = document.querySelector('input[value="bliskapaczka-courier"]');
                        if (simClick){
                        simClick.click();
                        }
	        	 }

	    	 }, 'json')
	    	 .always(function() {
	    		 Bliskapaczka.loadUnblock('div.cart_totals');
	    	 });
    });

});

