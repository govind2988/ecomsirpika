$(document).ready(function () {
    $("#mainSlider").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        nav: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>',
        ],
        dots: false,
    });


    $("#productSlider").owlCarousel({
        items: 3,
        loop: true,
        margin:20,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        nav: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>',
        ],
        dots: false,
        responsive:{
            0:{
                items:1,
                nav:true
            },
            600:{
                items:2,
                nav:false
            },
            1100:{
                items:3,
                nav:true,
                loop:false
            }
        }
    });



    
    $("#instaSlider").owlCarousel({
        items: 6,
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        nav: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>',
        ],
        dots: false,
        responsive:{
            0:{
                items:2,
                nav:true
            },
            600:{
                items:4,
                nav:false
            },
            1000:{
                items:6,
                nav:true,
                loop:false
            }
        }
    });


    new WOW().init();


    $('#suc_message').delay(5000).fadeOut('slow');




});