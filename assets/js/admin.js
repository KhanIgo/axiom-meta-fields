/**
 * Axiom Meta Fields - Admin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initFields();
        initMetaBoxes();
        initNotices();
    });

    function initFields() {
        initColorPickers();
        initDatePickers();
        initTimePickers();
        initSliders();
        initGalleries();
        initImages();
        initFiles();
        initRepeater();
        initGroups();
        initTabs();
        initRelationships();
        initMaps();
    }

    function initColorPickers() {
        if ($.fn.wpColorPicker) {
            $('.amf-color-picker').wpColorPicker();
        }
    }

    function initDatePickers() {
        if ($.fn.datepicker) {
            $('.amf-date-picker').each(function() {
                var $this = $(this);
                var format = $this.data('format') || 'yy-mm-dd';
                var minDate = $this.data('min-date') || null;
                var maxDate = $this.data('max-date') || null;

                $this.datepicker({
                    dateFormat: format,
                    minDate: minDate,
                    maxDate: maxDate,
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+100',
                });
            });

            // Date range pickers
            $('.amf-date-range-start, .amf-date-range-end').each(function() {
                var $this = $(this);
                var format = $this.data('format') || 'yy-mm-dd';

                $this.datepicker({
                    dateFormat: format,
                    changeMonth: true,
                    changeYear: true,
                    numberOfMonths: 1,
                    onClose: function(selectedDate) {
                        var option = $this.hasClass('amf-date-range-start') ? 'minDate' : 'maxDate';
                        var $other = $this.hasClass('amf-date-range-start')
                            ? $this.closest('.amf-date-range-wrapper').find('.amf-date-range-end')
                            : $this.closest('.amf-date-range-wrapper').find('.amf-date-range-start');
                        $other.datepicker('option', option, selectedDate);
                    }
                });
            });
        }
    }

    function initTimePickers() {
        if ($.fn.timepicker) {
            $('.amf-time-picker').each(function() {
                var $this = $(this);
                var format = $this.data('format') || 'HH:mm';
                var step = $this.data('step') || 5;
                var timeFormat = $this.data('24hr') === '1' ? 'HH:mm' : 'h:mm TT';

                $this.timepicker({
                    timeFormat: timeFormat,
                    step: step,
                });
            });
        }
    }

    function initSliders() {
        if ($.fn.slider) {
            $('.amf-slider-wrapper').each(function() {
                var $wrapper = $(this);
                var $input = $wrapper.find('.amf-slider-input');
                var $slider = $wrapper.find('.amf-slider');
                var min = parseInt($input.data('min')) || 0;
                var max = parseInt($input.data('max')) || 100;
                var step = parseInt($input.data('step')) || 1;
                var value = parseInt($input.val()) || min;

                $slider.slider({
                    min: min,
                    max: max,
                    step: step,
                    value: value,
                    slide: function(event, ui) {
                        $input.val(ui.value);
                    }
                });

                $input.on('change', function() {
                    $slider.slider('value', parseInt($(this).val()) || min);
                });
            });
        }
    }

    function initImages() {
        var mediaUploader;

        $(document).on('click', '.amf-image-upload', function(e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.amf-image-wrapper');
            var $input = $wrapper.find('.amf-image-id');
            var $preview = $wrapper.find('.amf-image-preview');
            var $removeBtn = $wrapper.find('.amf-image-remove');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: amfAdmin.strings.selectImage || 'Select Image',
                button: {
                    text: amfAdmin.strings.selectImage || 'Select Image'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="" class="amf-image-thumbnail" />');
                $removeBtn.removeClass('hidden');
            });

            mediaUploader.open();
        });

        $(document).on('click', '.amf-image-remove', function(e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.amf-image-wrapper');
            $wrapper.find('.amf-image-id').val('');
            $wrapper.find('.amf-image-preview').html('<div class="amf-image-placeholder">' + (amfAdmin.strings.noImage || 'No image selected') + '</div>');
            $(this).addClass('hidden');
        });
    }

    function initGalleries() {
        var mediaUploader;

        $(document).on('click', '.amf-gallery-add', function(e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.amf-gallery-wrapper');
            var $input = $wrapper.find('.amf-gallery-ids');
            var $list = $wrapper.find('.amf-gallery-list');
            var existingIds = $input.val().split(',').filter(Boolean);

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: amfAdmin.strings.addToGallery || 'Add to Gallery',
                button: {
                    text: amfAdmin.strings.addToGallery || 'Add to Gallery'
                },
                library: {
                    type: 'image'
                },
                multiple: true
            });

            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').toJSON();
                var ids = existingIds;

                attachments.forEach(function(attachment) {
                    if (!ids.includes(attachment.id.toString())) {
                        ids.push(attachment.id.toString());
                        $list.append(
                            '<li class="amf-gallery-item" data-id="' + attachment.id + '">' +
                            '<img src="' + attachment.sizes.thumbnail.url + '" alt="" />' +
                            '<button type="button" class="amf-gallery-remove dashicons dashicons-no"></button>' +
                            '</li>'
                        );
                    }
                });

                $input.val(ids.join(','));
            });

            mediaUploader.open();
        });

        $(document).on('click', '.amf-gallery-remove', function(e) {
            e.preventDefault();

            var $item = $(this).closest('.amf-gallery-item');
            var id = $item.data('id');
            var $wrapper = $(this).closest('.amf-gallery-wrapper');
            var $input = $wrapper.find('.amf-gallery-ids');

            $item.remove();

            var ids = $input.val().split(',').filter(function(i) {
                return i !== id.toString();
            });
            $input.val(ids.join(','));
        });

        // Sortable galleries
        if ($.fn.sortable) {
            $('.amf-gallery-list').sortable({
                placeholder: 'amf-gallery-item ui-state-highlight',
                update: function() {
                    var $wrapper = $(this).closest('.amf-gallery-wrapper');
                    var $input = $wrapper.find('.amf-gallery-ids');
                    var ids = [];

                    $(this).find('.amf-gallery-item').each(function() {
                        ids.push($(this).data('id'));
                    });

                    $input.val(ids.join(','));
                }
            });
        }
    }

    function initFiles() {
        var mediaUploader;

        $(document).on('click', '.amf-file-upload', function(e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.amf-file-wrapper');
            var $input = $wrapper.find('.amf-file-id');
            var $preview = $wrapper.find('.amf-file-preview');
            var $removeBtn = $wrapper.find('.amf-file-remove');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: amfAdmin.strings.selectFile || 'Select File',
                button: {
                    text: amfAdmin.strings.selectFile || 'Select File'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html(
                    '<div class="amf-file-info">' +
                    '<span class="amf-file-name">' + attachment.filename + '</span>' +
                    '<a href="' + attachment.url + '" target="_blank" class="amf-file-download">' + (amfAdmin.strings.download || 'Download') + '</a>' +
                    '</div>'
                );
                $removeBtn.removeClass('hidden');
            });

            mediaUploader.open();
        });

        $(document).on('click', '.amf-file-remove', function(e) {
            e.preventDefault();

            var $wrapper = $(this).closest('.amf-file-wrapper');
            $wrapper.find('.amf-file-id').val('');
            $wrapper.find('.amf-file-preview').html('<span class="amf-file-placeholder">' + (amfAdmin.strings.noFile || 'No file selected') + '</span>');
            $(this).addClass('hidden');
        });
    }

    function initRepeater() {
        $(document).on('click', '.amf-repeater-add', function(e) {
            e.preventDefault();

            var $repeater = $(this).closest('.amf-repeater');
            var $rows = $repeater.find('.amf-repeater-rows');
            var $firstRow = $rows.find('.amf-repeater-row:first');
            var min = parseInt($repeater.data('min')) || 0;
            var max = parseInt($repeater.data('max')) || 0;
            var currentCount = $rows.find('.amf-repeater-row').length;

            if (max > 0 && currentCount >= max) {
                return;
            }

            var newIndex = currentCount;
            var $newRow = $firstRow.clone();

            // Update indices in names and IDs
            $newRow.find('[name], [id]').each(function() {
                var $el = $(this);
                var name = $el.attr('name');
                var id = $el.attr('id');

                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + newIndex + ']');
                    $el.attr('name', name);
                }

                if (id) {
                    id = id.replace(/-\d+-/, '-' + newIndex + '-');
                    $el.attr('id', id);
                }
            });

            $newRow.find('.amf-repeater-row-title').text(amfAdmin.strings.row + ' ' + (newIndex + 1));
            $newRow.find('input, textarea, select').val('');

            $rows.append($newRow);
            updateRepeaterIndices($repeater);
        });

        $(document).on('click', '.amf-repeater-remove', function(e) {
            e.preventDefault();

            var $repeater = $(this).closest('.amf-repeater');
            var $row = $(this).closest('.amf-repeater-row');
            var min = parseInt($repeater.data('min')) || 0;
            var currentCount = $repeater.find('.amf-repeater-rows .amf-repeater-row').length;

            if (currentCount <= min) {
                return;
            }

            $row.remove();
            updateRepeaterIndices($repeater);
        });

        // Sortable rows
        if ($.fn.sortable) {
            $('.amf-repeater-rows').sortable({
                handle: '.amf-repeater-sort',
                placeholder: 'amf-repeater-row ui-state-highlight',
                update: function() {
                    var $repeater = $(this).closest('.amf-repeater');
                    updateRepeaterIndices($repeater);
                }
            });
        }
    }

    function updateRepeaterIndices($repeater) {
        $repeater.find('.amf-repeater-row').each(function(index) {
            var $row = $(this);
            $row.find('.amf-repeater-row-title').text(amfAdmin.strings.row + ' ' + (index + 1));

            $row.find('[name], [id]').each(function() {
                var $el = $(this);
                var name = $el.attr('name');
                var id = $el.attr('id');

                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + index + ']');
                    $el.attr('name', name);
                }

                if (id) {
                    $el.attr('id', id);
                }
            });
        });
    }

    function initGroups() {
        // Collapsible groups
        $(document).on('click', '.amf-group-toggle', function() {
            var $header = $(this).closest('.amf-group-header');
            var $content = $header.next('.amf-group-content');
            var $toggle = $(this);

            $content.slideToggle(200);
            $toggle.toggleClass('dashicons-arrow-down dashicons-arrow-up');
        });

        // Cloneable groups
        $(document).on('click', '.amf-clone-add', function(e) {
            e.preventDefault();

            var $group = $(this).closest('.amf-group-cloneable');
            var $clones = $group.find('.amf-group-clones');
            var $firstClone = $clones.find('.amf-group-clone:first');
            var min = parseInt($group.data('min')) || 0;
            var max = parseInt($group.data('max')) || 0;
            var currentCount = $clones.find('.amf-group-clone').length;

            if (max > 0 && currentCount >= max) {
                return;
            }

            var $newClone = $firstClone.clone();
            $newClone.find('input, textarea, select').val('');
            $clones.append($newClone);
        });

        $(document).on('click', '.amf-clone-remove', function(e) {
            e.preventDefault();

            var $group = $(this).closest('.amf-group-cloneable');
            var $clone = $(this).closest('.amf-group-clone');
            var min = parseInt($group.data('min')) || 0;
            var currentCount = $group.find('.amf-group-clones .amf-group-clone').length;

            if (currentCount <= min) {
                return;
            }

            $clone.remove();
        });
    }

    function initTabs() {
        $(document).on('click', '.amf-tab-link', function(e) {
            e.preventDefault();

            var $link = $(this);
            var $tabs = $link.closest('.amf-tabs');
            var $nav = $tabs.find('.amf-tab-nav');
            var $panels = $tabs.find('.amf-tab-panels');
            var tabId = $link.attr('href');
            var tabIndex = $link.data('tab');

            // Update nav
            $nav.find('.amf-tab-item').removeClass('active');
            $link.closest('.amf-tab-item').addClass('active');

            // Update panels
            $panels.find('.amf-tab-panel').removeClass('active');
            $panels.find(tabId).addClass('active');

            // Store active tab
            $tabs.data('active', tabIndex);
        });
    }

    function initRelationships() {
        $(document).on('click', '.amf-relationship-add', function(e) {
            e.preventDefault();

            var $item = $(this).closest('.amf-relationship-item');
            var id = $item.data('id');
            var title = $item.find('.amf-relationship-item-title').text() || $item.contents().first().text().trim();
            var $wrapper = $(this).closest('.amf-relationship-wrapper');
            var $selected = $wrapper.find('.amf-relationship-selected');
            var $input = $wrapper.find('.amf-relationship-value');

            // Add to selected
            $selected.append(
                '<li data-id="' + id + '">' +
                title +
                '<button type="button" class="amf-relationship-remove dashicons dashicons-no"></button>' +
                '</li>'
            );

            // Remove from available
            $item.remove();

            // Update hidden input
            updateRelationshipValues($wrapper);
        });

        $(document).on('click', '.amf-relationship-remove', function(e) {
            e.preventDefault();

            var $item = $(this).closest('li');
            var id = $item.data('id');
            var $wrapper = $(this).closest('.amf-relationship-wrapper');

            $item.remove();
            updateRelationshipValues($wrapper);
        });
    }

    function updateRelationshipValues($wrapper) {
        var ids = [];
        $wrapper.find('.amf-relationship-selected li').each(function() {
            ids.push($(this).data('id'));
        });
        $wrapper.find('.amf-relationship-value').val(ids.join(','));
    }

    function initMaps() {
        // TODO: map init
    }

    function initMetaBoxes() {
    }

    function initNotices() {
        // Handle dismissible notices
        $(document).on('click', '.is-dismissible .notice-dismiss, [data-notice-id] .notice-dismiss', function() {
            var $notice = $(this).closest('.notice');
            var noticeId = $notice.data('notice-id');

            if (noticeId && amfAdmin && amfAdmin.ajaxUrl) {
                $.post(amfAdmin.ajaxUrl, {
                    action: 'amf_dismiss_notice',
                    notice_id: noticeId,
                    nonce: amfAdmin.nonce
                });
            }
        });
    }

})(jQuery);
