$(document).ready(function () {
    // etsy variables
    var url = 'https://openapi.etsy.com/v2/listings/active.js?includes=MainImage',
        key = 's5moesajqivedfa25fmys7bn';
        // key = '94yrdvn9gaqoi9fz6oyxt6w6';
        
    // pagination variables
    var set,
        page,
        prev,
        next,
        limit = 15;
        
    // get data
    function getData(page) {
        $.ajax({
            url: url,
            data: {
                api_key: key,
                limit: limit,
                offset: page
            },
            dataType: 'jsonp',
            success: function (data) {
                if (data.ok) {
                    if (data.count > 0) {
                        // if hidden listings exist... *
                        if ($('.item.hidden').length > 0) {
                            // * remove hidden listings
                            $('.item.hidden').remove();
                        }
                        
                        // first page
                        if (typeof page === 'undefined' || page < limit) {
                            $('.prev').hide();
                        } else {
                            $('.prev').show();
                        }
                        
                        // pagination
                        set = data.pagination.effective_page;
                        prev = data.pagination.next_offset - limit - limit;
                        next = data.pagination.next_offset;
                        
                        // for each listing... *
                        for (var i = 0; i < data.results.length; i++) {
                            // * assign listing variables
                            var item = data.results[i];
                            var url = item.url;
                            var image = item.MainImage.url_570xN;
                            var title = item.title;
                            var price = item.price;
                            var quantity = item.quantity;
                            
                            // * indicate quantity level
                            var level;
                            if (quantity <= 10) {
                                level = 'low';
                            } else if (quantity > 10 && quantity <= 20) {
                                level = 'medium';
                            } else if (quantity > 20) {
                                level = 'high';
                            }
                            
                            // * check for title character length and add ellipses
                            if (title.length > 50) {
                                title = title.substring(0, 50) + '...';
                            }
                            
                            // * build html structure for each listing
                            var listing = $('<div class="item">\
                                <div class="item-image">\
                                    <a href="' + url + '"><img src="' + image + '"/></a>\
                                </div>\
                                <div class="item-details">\
                                    <p class="item-title">' + title + '</p>\
                                    <p class="item-price">$' + price + '</p>\
                                    <p class="item-quantity level-' + level + '">' + quantity + ' left</p>\
                                </div>\
                            </div>');
                            
                            // * append listings and hide them
                            $('.item-list').append(listing).css({
                                'opacity': '0',
                                'transition': 'none'
                            });
                            
                            // * load images
                            loadImages();
                        }
                    } else {
                        console.log('no results');
                    }
                } else {
                    console.log(data.error);
                }
            }
        });
    }
    
    // load images
    function loadImages() {
        // assign image variables
        var images = $('.item img');
        var count = images.length;
        
        // initialize dynamic image loading
        $(images).imagesLoaded().always(function (instance) {
            // assign set
            $('.set').text(set);
        }).done(function (instance) {
            // hide loading spinner
            $('.loading').hide();
            // fade in listings
            $('.item-list').css({
                'opacity': '1',
                'transition': 'opacity 300ms ease'
            });
        }).fail(function () {
            console.log('all images loaded, at least one is broken');
        }).progress(function (instance, image) {
            // triggered after each image has been loaded
            if (image.isLoaded) {
                $(image.img).addClass('loaded').delay(600).css('opacity', '1');
                var loaded = $('.item-list img.loaded').length;
                var width = 100 * (loaded / count);
                $('.progress').css('width', width + '%');
                $('.loading').show();
            }
        });
    }
    
    // pagination
    $('.page a').on('click', function (e) {
        // prevent default action
        e.preventDefault();
        
        // fade out and remove listings
        $('.item-list').css({
            'opacity': '0',
            'transition': 'opacity 300ms ease'
        }).delay(300).queue(function () {
            $('.item').addClass('hidden');
            $(this).dequeue();
        });
        
        // show loading spinner
        $('.loading').show();
        
        // reset progress bar width
        $('.progress').css('width', '0');
        
        // assign pagination variable
        if ($(this).hasClass('prev')) {
            page = prev;
        } else if ($(this).hasClass('next')) {
            page = next;
        }
        
        // get data
        getData(page);
    });
    
    // get data on first load
    getData(page);
});
