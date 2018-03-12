
$('.input-date').inputmask('dd/mm/yyyy');


function debug_out(txt){
    if(location.href.match(/debug_out/))
        alert(txt);
}

$(document).ready(function(){

    $('#home .slider').cycle('fade');

	$(document).scroll(function() {
		var HomePos 		= $(document).scrollTop();
		var clearingHeight	= 200;//Math.ceil( ($(window).height() /3)*2);

		if (HomePos > clearingHeight){
			$('header').addClass('scrolled');
		}else{
			$('header').removeClass('scrolled');
		}
	}).scroll();

	$(document.body).on('click','header .nav-icon',function(){
		if( $('header').hasClass('mobile-active') ){
			$('header').removeClass('mobile-active')
		}else{
			$('header').addClass('mobile-active');
		}
	});
	$(document.body).on('click','header ul li a',function(){
		if( $('header').hasClass('mobile-active') ){
			$('header').removeClass('mobile-active')
		}else{
			$('header').addClass('mobile-active');
		}
	});
	$(document.body).on('click','.row .payment-info',function(){
        var row = $(this).parent().parent();
        var close = row.is('.show-info');

		$('.payments-options .body .row').removeClass('show-info');
        if(!close)
		    row.addClass('show-info');
	});

    $('.gallery img').each(function(){
        $(this).attr('class', ' ');
        var a = $('<a/>').addClass('gal-item').attr('href', $(this).attr('src'));
        $(this).wrap(a);
    });

    $('.gallery').lightGallery({
        selector: '.gal-item'
    });

    $(document.body).on("click","header .filter-icon", function(){
    	if( $('.filters').is(':visible') ){
    		$('.filters').hide();
    	}else{
    		$('.filters').show();
    	}
    });

    // smooth scrolling and anchor fix

	var doc_w = $(window).width();
	// Select all links with hashes
    $('a[href*="#"]')
        // Remove links that don't actually link to anything
        .not('[href="#"]')
        .not('[href="#0"]')
        .click(function (event) {
            debug_out('click');

            debug_out(location.pathname);

            debug_out(this.pathname);

            debug_out(location.hostname);

            debug_out(this.hostname);
            // On-page links
            if (
                location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
                &&
                location.hostname == this.hostname
            ) {
                // Figure out element to scroll to
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

                debug_out(this.hash.slice(1));
                // Does a scroll target exist?
                if (target.length) {
                    debug_out('ok');

                    // Only prevent default if animation is actually gonna happen
                    event.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 90
                    }, 1000, function () {
                        // Callback after animation
                        // Must change focus!
                        var $target = $(target);
                        $target.focus();
                        if ($target.is(":focus")) { // Checking if the target was focused
                            return false;
                        } else {
                            $target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
                            $target.focus(); // Set focus again
                        }
                        ;
                    });
                }
            }
        });


});
