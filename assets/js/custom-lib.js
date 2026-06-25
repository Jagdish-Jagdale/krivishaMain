/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/** ******  left menu  *********************** **/
$(function () {
    // $('#sidebar-menu li ul').hide();
    // $('#sidebar-menu li').removeClass('active');

    $('#sidebar-menu li').click(function () {
        if ($(this).is('.active')) {
            // $(this).removeClass('active');
            // $('ul', this).hide();
            // $(this).removeClass('nv');
            // $(this).addClass('vn');  
			$('.right_col').removeClass('active_right');
			// $('.header_sticky').removeClass('active_right');
        } else {
            // $('#sidebar-menu li ul').hide();
            // $(this).removeClass('vn');
            // $(this).addClass('nv');
           
            // $('#sidebar-menu li').removeClass('active');
            // $(this).addClass('active');
			$('.right_col').addClass('active_right');
			// $('.header_sticky').addClass('active_right');
        }

        $('#sidebar-menu li ul').not($('ul', this)).hide();
        $('#sidebar-menu li').not(this).removeClass('active_parent');

        $('ul', this).toggle();
        $(this).toggleClass('active_parent');


        // $('.right_col').toggleClass('active_right');
    });

    $('#menu_toggle').click(function () {
        if ($('body').hasClass('nav-md')) {
            $('body').removeClass('nav-md');
            $('body').addClass('nav-sm');
            $('.left_col').removeClass('scroll-view');
            $('.left_col').removeAttr('style');
            $('.sidebar-footer').hide();

            if ($('#sidebar-menu li').hasClass('active')) {
                $('#sidebar-menu li.active').addClass('active-sm');
                $('#sidebar-menu li.active').removeClass('active');
            }
        } else {
            $('body').removeClass('nav-sm');
            $('body').addClass('nav-md');
            $('.sidebar-footer').show();

            if ($('#sidebar-menu li').hasClass('active-sm')) {
                $('#sidebar-menu li.active-sm').addClass('active');
                $('#sidebar-menu li.active-sm').removeClass('active-sm');
            }
        }
    });
});

/* Sidebar Menu active class */
/*
$(function () {
    var url = window.location;
    $('#sidebar-menu a[href="' + url + '"]').parent('li').addClass('current-page');
    $('#sidebar-menu a').filter(function () {
        return this.href == url;
    }).parent('li').addClass('current-page').parent('ul').slideDown().parent().addClass('active');
});
*/
/** ******  /left menu  *********************** **/



/** ******  tooltip  *********************** **/
$(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    /** ******  /tooltip  *********************** **/
    /** ******  progressbar  *********************** **/
if ($(".progress .progress-bar")[0]) {
    $('.progress .progress-bar').progressbar(); // bootstrap 3
}
/** ******  /progressbar  *********************** **/
/** ******  switchery  *********************** **/
if ($(".js-switch")[0]) {
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
    elems.forEach(function (html) {
        var switchery = new Switchery(html, {
            color: '#26B99A'
        });
    });
}
/** ******  /switcher  *********************** **/
/** ******  collapse panel  *********************** **/
// Close ibox function
$('.close-link').click(function () {
    var content = $(this).closest('div.x_panel');
    content.remove();
});

// Collapse ibox function
$('.collapse-link').click(function () {
    var x_panel = $(this).closest('div.x_panel');
    var button = $(this).find('i');
    var content = x_panel.find('div.x_content');
    content.slideToggle(200);
    (x_panel.hasClass('fixed_height_390') ? x_panel.toggleClass('').toggleClass('fixed_height_390') : '');
    (x_panel.hasClass('fixed_height_320') ? x_panel.toggleClass('').toggleClass('fixed_height_320') : '');
    button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
    setTimeout(function () {
        x_panel.resize();
    }, 50);
});
/** ******  /collapse panel  *********************** **/
/** ******  iswitch  *********************** **/
if ($("input.flat")[0]) {
    $(document).ready(function () {
        $('input.flat').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });
    });
}
/** ******  /iswitch  *********************** **/
/** ******  star rating  *********************** **/
// Starrr plugin (https://github.com/dobtco/starrr)
var __slice = [].slice;

(function ($, window) {
    var Starrr;

    Starrr = (function () {
        Starrr.prototype.defaults = {
            rating: void 0,
            numStars: 5,
            change: function (e, value) {}
        };

        function Starrr($el, options) {
            var i, _, _ref,
                _this = this;

            this.options = $.extend({}, this.defaults, options);
            this.$el = $el;
            _ref = this.defaults;
            for (i in _ref) {
                _ = _ref[i];
                if (this.$el.data(i) != null) {
                    this.options[i] = this.$el.data(i);
                }
            }
            this.createStars();
            this.syncRating();
            this.$el.on('mouseover.starrr', 'span', function (e) {
                return _this.syncRating(_this.$el.find('span').index(e.currentTarget) + 1);
            });
            this.$el.on('mouseout.starrr', function () {
                return _this.syncRating();
            });
            this.$el.on('click.starrr', 'span', function (e) {
                return _this.setRating(_this.$el.find('span').index(e.currentTarget) + 1);
            });
            this.$el.on('starrr:change', this.options.change);
        }

        Starrr.prototype.createStars = function () {
            var _i, _ref, _results;

            _results = [];
            for (_i = 1, _ref = this.options.numStars; 1 <= _ref ? _i <= _ref : _i >= _ref; 1 <= _ref ? _i++ : _i--) {
                _results.push(this.$el.append("<span class='glyphicon .glyphicon-star-empty'></span>"));
            }
            return _results;
        };

        Starrr.prototype.setRating = function (rating) {
            if (this.options.rating === rating) {
                rating = void 0;
            }
            this.options.rating = rating;
            this.syncRating();
            return this.$el.trigger('starrr:change', rating);
        };

        Starrr.prototype.syncRating = function (rating) {
            var i, _i, _j, _ref;

            rating || (rating = this.options.rating);
            if (rating) {
                for (i = _i = 0, _ref = rating - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; i = 0 <= _ref ? ++_i : --_i) {
                    this.$el.find('span').eq(i).removeClass('glyphicon-star-empty').addClass('glyphicon-star');
                }
            }
            if (rating && rating < 5) {
                for (i = _j = rating; rating <= 4 ? _j <= 4 : _j >= 4; i = rating <= 4 ? ++_j : --_j) {
                    this.$el.find('span').eq(i).removeClass('glyphicon-star').addClass('glyphicon-star-empty');
                }
            }
            if (!rating) {
                return this.$el.find('span').removeClass('glyphicon-star').addClass('glyphicon-star-empty');
            }
        };

        return Starrr;

    })();
    return $.fn.extend({
        starrr: function () {
            var args, option;

            option = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
            return this.each(function () {
                var data;

                data = $(this).data('star-rating');
                if (!data) {
                    $(this).data('star-rating', (data = new Starrr($(this), option)));
                }
                if (typeof option === 'string') {
                    return data[option].apply(data, args);
                }
            });
        }
    });
})(window.jQuery, window);

$(function () {
    return $(".starrr").starrr();
});

$(document).ready(function () {

    $('#stars').on('starrr:change', function (e, value) {
        $('#count').html(value);
    });


    $('#stars-existing').on('starrr:change', function (e, value) {
        $('#count-existing').html(value);
    });

});
/** ******  /star rating  *********************** **/
/** ******  table  *********************** **/
$('table input').on('ifChecked', function () {
    check_state = '';
    $(this).parent().parent().parent().addClass('selected');
    countChecked();
});
$('table input').on('ifUnchecked', function () {
    check_state = '';
    $(this).parent().parent().parent().removeClass('selected');
    countChecked();
});

var check_state = '';
$('.bulk_action input').on('ifChecked', function () {
    check_state = '';
    $(this).parent().parent().parent().addClass('selected');
    countChecked();
});
$('.bulk_action input').on('ifUnchecked', function () {
    check_state = '';
    $(this).parent().parent().parent().removeClass('selected');
    countChecked();
});
$('.bulk_action input#check-all').on('ifChecked', function () {
    check_state = 'check_all';
    countChecked();
});
$('.bulk_action input#check-all').on('ifUnchecked', function () {
    check_state = 'uncheck_all';
    countChecked();
});

function countChecked() {
        if (check_state == 'check_all') {
            $(".bulk_action input[name='table_records']").iCheck('check');
        }
        if (check_state == 'uncheck_all') {
            $(".bulk_action input[name='table_records']").iCheck('uncheck');
        }
        var n = $(".bulk_action input[name='table_records']:checked").length;
        if (n > 0) {
            $('.column-title').hide();
            $('.bulk-actions').show();
            $('.action-cnt').html(n + ' Records Selected');
        } else {
            $('.column-title').show();
            $('.bulk-actions').hide();
        }
    }
    /** ******  /table  *********************** **/
    /** ******    *********************** **/
    /** ******    *********************** **/
    /** ******    *********************** **/
    /** ******    *********************** **/
    /** ******    *********************** **/
    /** ******    *********************** **/
    /** ******  Accordion  *********************** **/

$(function () {
    $(".expand").on("click", function () {
        $(this).next().slideToggle(200);
        $expand = $(this).find(">:first-child");

        if ($expand.text() == "+") {
            $expand.text("-");
        } else {
            $expand.text("+");
        }
    });
});

/** ******  Accordion  *********************** **/
/** ******  scrollview  *********************** **/
$(document).ready(function () {
  
            // $(".scroll-view").niceScroll({
                // touchbehavior: true,
                // cursorcolor: "rgba(42, 63, 84, 0.35)"
            // });

});
/** ******  /scrollview  *********************** **/

/**
 * Prevent double form submit (multiple inserts) by disabling submit buttons
 * and blocking subsequent submissions while the first one is in progress.
 *
 * - Works with native HTML5 validation and jQuery Validate (if used).
 * - Applies to all forms in admin pages because this file is loaded globally.
 */
(function ($, window) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    var SUBMIT_LOCK_KEY = 'ciSubmitting';
    var isNavigatingAway = false;

    // If a real navigation starts, keep the lock in place.
    window.addEventListener('beforeunload', function () {
        isNavigatingAway = true;
    });

    function isFormValid($form) {
        var formEl = $form.get(0);

        // If jQuery Validate is attached, use it.
        if ($.fn && $.fn.validate && $form.data('validator')) {
            return $form.valid();
        }

        // Native HTML5 validation.
        if (formEl && typeof formEl.checkValidity === 'function') {
            return formEl.checkValidity();
        }

        return true;
    }

    function disableSubmitControls($form) {
        $form.find('button[type="submit"], input[type="submit"]').each(function () {
            var $btn = $(this);
            $btn.prop('disabled', true);
            $btn.attr('data-double-submit-disabled', '1');
        });
    }

    function enableSubmitControls($form) {
        $form.find('[data-double-submit-disabled="1"]').each(function () {
            var $btn = $(this);
            $btn.prop('disabled', false);
            $btn.removeAttr('data-double-submit-disabled');
        });
    }

    // Patch programmatic submits: many pages use `submitHandler` + `form.submit()`.
    // Note: calling `HTMLFormElement.submit()` does NOT fire the `submit` event.
    (function patchNativeFormSubmit() {
        try {
            if (!window.HTMLFormElement || !window.HTMLFormElement.prototype) {
                return;
            }
            if (window.HTMLFormElement.prototype.__doubleSubmitPatched) {
                return;
            }
            var originalSubmit = window.HTMLFormElement.prototype.submit;
            if (typeof originalSubmit !== 'function') {
                return;
            }

            window.HTMLFormElement.prototype.submit = function () {
                try {
                    var $form = $(this);
                    if ($form.length && !$form.data(SUBMIT_LOCK_KEY)) {
                        $form.data(SUBMIT_LOCK_KEY, true);
                        disableSubmitControls($form);
                    }
                } catch (e) {
                    // ignore
                }
                return originalSubmit.apply(this, arguments);
            };

            window.HTMLFormElement.prototype.__doubleSubmitPatched = true;
        } catch (e) {
            // ignore
        }
    })();

    // Use capturing so we run before other submit handlers.
    document.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!form || form.tagName !== 'FORM') {
            return;
        }

        var $form = $(form);

        if ($form.hasClass('no-global-disable')) {
            return;
        }

        // If already submitting, block.
        if ($form.data(SUBMIT_LOCK_KEY)) {
            ev.preventDefault();
            ev.stopPropagation();
            return false;
        }

        // Only lock if the form is actually valid; otherwise don't disable buttons.
        if (!isFormValid($form)) {
            return;
        }

        $form.data(SUBMIT_LOCK_KEY, true);
        disableSubmitControls($form);

        // If some other handler cancels/prevents the submit (e.g. confirm() cancelled
        // inside jQuery Validate submitHandler), re-enable shortly after.
        window.setTimeout(function () {
            if (isNavigatingAway) {
                return;
            }
            if ($form.data(SUBMIT_LOCK_KEY)) {
                $form.removeData(SUBMIT_LOCK_KEY);
                enableSubmitControls($form);
            }
        }, 800);
    }, true);

    // If user navigates back and the page is restored from bfcache,
    // re-enable buttons so the form is usable again.
    window.addEventListener('pageshow', function () {
        $('form').each(function () {
            var $form = $(this);
            if ($form.data(SUBMIT_LOCK_KEY)) {
                $form.removeData(SUBMIT_LOCK_KEY);
            }
            enableSubmitControls($form);
        });
    });
})(window.jQuery, window);