$(document)
    .on('change', 'form.autosave', function () {
        $(this).submit();
    })
    .on('click', '[data-clear]', function (e) {
        e.preventDefault();

        var $this = $(this),
            target = $this.data('clear'),
            $target = $(target),
            $inputs = $target.find(':input');

        $inputs.val('').prop('checked', false);

        if ($this.attr('type') === 'submit') {
            $target.parents('form').submit();
        }
    })
    .on('input', 'input.slug', function () {
        var $input = $(this),
            slug = $input.val().toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'');

        $input.val(slug);
    })
    .on('focus', 'input.slug[data-slug-from]', function () {
        var $input = $(this),
            from = $input.data('slug-from'),
            $from = $(from).first();

        if (! $input.val() && $from.length === 1) {
            var slug = $from.val().toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'');

            $input.val(slug).trigger('input');
        }
    })
    .on('click', '[data-confirm]', function () {
        return window.confirm($(this).data('confirm'));
    })
    .on('mason:lockable:init', '.is-lockable', function (e) {
        var $this = $(this),
            $input = $this.find('.input'),
            $lock = $this.find('.lock'),
            $unlock = $this.find('.unlock');

        if ($this.hasClass('is-locked') || $input.prop('disabled')) {
            $this.trigger('mason:lockable:lock');
        } else {
            $this.trigger('mason:lockable:unlock');
        }

        $lock.on('click', function (e) {
            e.preventDefault();

            $this.trigger('mason:lockable:lock');
        });

        $unlock.on('click', function (e) {
            e.preventDefault();

            $this.trigger('mason:lockable:unlock');
        });
    })
    .on('mason:lockable:lock', '.is-lockable', function () {
        var $this = $(this),
            $input = $this.find('.input'),
            $lock = $this.find('.lock'),
            $unlock = $this.find('.unlock');

        $this.addClass('is-locked');
        $input.prop('disabled', true);
        $lock.addClass('is-hidden');
        $unlock.removeClass('is-hidden');
    })
    .on('mason:lockable:unlock', '.is-lockable', function () {
        var $this = $(this),
            $input = $this.find('.input'),
            $lock = $this.find('.lock'),
            $unlock = $this.find('.unlock');

        $this.removeClass('is-locked');
        $input.prop('disabled', false);
        $unlock.addClass('is-hidden');
        $lock.removeClass('is-hidden');
    })
    .on('ready DOMSubtreeModified', function () {
        $('.is-lockable').trigger('mason:lockable:init');
    })
    .on('click', '[rel="expand"]', function (e) {
        e.preventDefault();

        var $this = $(this),
            href = $this.attr('href'),
            $href = $(href);

        $href.removeClass('is-hidden');
    })
    .on('click', '[rel="collapse"]', function (e) {
        e.preventDefault();

        var $this = $(this),
            href = $this.attr('href'),
            $href = $(href);

        $href.addClass('is-hidden');
    })
    .on('click', '[rel="toggle"]', function (e) {
        e.preventDefault();

        var $this = $(this),
            href = $this.attr('href'),
            $href = $(href);

        $href.toggleClass('is-hidden');
    });
