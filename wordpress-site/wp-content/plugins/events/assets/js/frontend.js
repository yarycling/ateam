/**
 * ATEAM EVENTS PRO - Frontend JavaScript
 *
 * @package ATeam_Events_Pro
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // ===== Intersection Observer for scroll animations =====
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('ateam-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -40px 0px'
            });

            document.querySelectorAll('.ateam-event-card, .ateam-event-list-item, .ateam-sidebar-card').forEach(function (el) {
                observer.observe(el);
            });
        }

        // ===== Share: Copy link to clipboard =====
        $(document).on('click', '.ateam-share-copy', function (e) {
            e.preventDefault();
            var url = $(this).data('url');
            var $btn = $(this);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    showToast('Link copied to clipboard!');
                    pulseButton($btn);
                });
            } else {
                var textarea = document.createElement('textarea');
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast('Link copied to clipboard!');
                pulseButton($btn);
            }
        });

        // ===== Add to Calendar (Google Calendar) =====
        $(document).on('click', '.ateam-add-cal-btn', function (e) {
            e.preventDefault();

            var title = $(this).data('title') || '';
            var date = $(this).data('date') || '';
            var startTime = $(this).data('start') || '';
            var endTime = $(this).data('end') || '';
            var location = $(this).data('location') || '';
            var description = $(this).data('description') || '';

            // Format date for Google Calendar (YYYYMMDD)
            var formattedDate = date.replace(/-/g, '');

            var startDateTime, endDateTime;

            if (startTime) {
                // Format: YYYYMMDDTHHMMSS
                var st = startTime.replace(/:/g, '') + '00';
                startDateTime = formattedDate + 'T' + st;

                if (endTime) {
                    var et = endTime.replace(/:/g, '') + '00';
                    endDateTime = formattedDate + 'T' + et;
                } else {
                    // Default 1 hour duration
                    var startHour = parseInt(startTime.split(':')[0]) + 1;
                    var startMin = startTime.split(':')[1];
                    endDateTime = formattedDate + 'T' + String(startHour).padStart(2, '0') + startMin + '00';
                }
            } else {
                // All day event
                startDateTime = formattedDate;
                // Next day for end
                var nextDay = new Date(date);
                nextDay.setDate(nextDay.getDate() + 1);
                var nd = nextDay.toISOString().split('T')[0].replace(/-/g, '');
                endDateTime = nd;
            }

            var gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
                + '&text=' + encodeURIComponent(title)
                + '&dates=' + startDateTime + '/' + endDateTime
                + '&details=' + encodeURIComponent(description)
                + '&location=' + encodeURIComponent(location);

            window.open(gcalUrl, '_blank');
        });

        // ===== Filter chips active state =====
        var currentUrl = window.location.href;
        $('.ateam-filter-chip').each(function () {
            if ($(this).attr('href') === currentUrl) {
                $(this).addClass('active');
            } else if ($(this).hasClass('active') && $(this).attr('href') !== currentUrl) {
                // Only keep "All Events" active if we're on the archive page
            }
        });

        // ===== Smooth scroll for internal links =====
        $('a[href^="#ateam-"]').on('click', function (e) {
            var target = $($(this).attr('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 500, 'swing');
            }
        });

        // ===== Utility: Toast notification =====
        function showToast(message) {
            var $toast = $('<div class="ateam-toast">' + message + '</div>');

            $toast.css({
                position: 'fixed',
                bottom: '24px',
                left: '50%',
                transform: 'translateX(-50%) translateY(20px)',
                background: '#1E293B',
                color: '#fff',
                padding: '12px 24px',
                borderRadius: '8px',
                fontFamily: "'Inter', sans-serif",
                fontSize: '14px',
                fontWeight: '600',
                zIndex: 99999,
                opacity: 0,
                transition: 'all 0.3s ease',
                boxShadow: '0 8px 30px rgba(0,0,0,0.2)',
                whiteSpace: 'nowrap'
            });

            $('body').append($toast);

            setTimeout(function () {
                $toast.css({
                    opacity: 1,
                    transform: 'translateX(-50%) translateY(0)'
                });
            }, 10);

            setTimeout(function () {
                $toast.css({
                    opacity: 0,
                    transform: 'translateX(-50%) translateY(20px)'
                });
                setTimeout(function () {
                    $toast.remove();
                }, 300);
            }, 2500);
        }

        // ===== Utility: Pulse button animation =====
        function pulseButton($btn) {
            $btn.css({
                transform: 'scale(0.9)',
                transition: 'transform 0.15s ease'
            });
            setTimeout(function () {
                $btn.css({
                    transform: 'scale(1.1)'
                });
                setTimeout(function () {
                    $btn.css({
                        transform: ''
                    });
                }, 150);
            }, 150);
        }

        // ===== Parallax hero image on scroll =====
        var $hero = $('.ateam-hero-image img');
        if ($hero.length) {
            $(window).on('scroll', function () {
                var scrollTop = $(window).scrollTop();
                var heroHeight = $('.ateam-event-hero').height();

                if (scrollTop < heroHeight) {
                    var translateY = scrollTop * 0.3;
                    $hero.css('transform', 'translateY(' + translateY + 'px) scale(1.05)');
                }
            });
        }
        // ===== Hero Slider Logic =====
        $('.ateam-hero-slider-wrap').each(function () {
            var $slider = $(this);
            var $track = $slider.find('.ateam-hero-slider-track');
            var $slides = $slider.find('.ateam-hero-slide');
            var $next = $slider.find('.ateam-nav-next');
            var $prev = $slider.find('.ateam-nav-prev');
            
            var currentIndex = 0;
            var totalSlides = $slides.length;
            
            if (totalSlides <= 1) {
                $next.hide();
                $prev.hide();
                return;
            }

            function updateSlider() {
                var slideWidth = 85; 
                var peekAmount = (100 - slideWidth) / 2;
                var offset = (currentIndex * slideWidth) - peekAmount;
                $track.css('transform', 'translateX(' + (-offset) + '%)');
                
                $slides.removeClass('ateam-slide-active');
                $slides.eq(currentIndex).addClass('ateam-slide-active');

                // Update Thumbnails
                $slider.find('.ateam-hero-thumb').removeClass('active');
                $slider.find('.ateam-hero-thumb[data-index="' + currentIndex + '"]').addClass('active');
            }

            // Thumbnail Click
            $slider.find('.ateam-hero-thumb').on('click', function() {
                currentIndex = parseInt($(this).data('index'));
                updateSlider();
            });

            $next.on('click', function (e) {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            });

            $prev.on('click', function (e) {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                updateSlider();
            });

            // Auto-play (optional)
            var autoPlayInterval = setInterval(function() {
                $next.trigger('click');
            }, 6000);

            $slider.on('mouseenter', function() {
                clearInterval(autoPlayInterval);
            }).on('mouseleave', function() {
                autoPlayInterval = setInterval(function() {
                    $next.trigger('click');
                }, 6000);
            });
            
            // Initial call
            updateSlider();
        });


    });

})(jQuery);
