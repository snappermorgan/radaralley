var wpzlbWidgetSaveCallbacks = [];

(function($){

	var $rootElem = $('#wpzlb'),
	    postid = parseInt($('form#post input#post_ID').val(), 10),
	    _sortableOptions = {},
	    _coreWpWidgetsInit = window.wpWidgets.init,
	    saveTimeout,
	    loadLayoutAnimateTimeout,
	    imageUploadFrame,
	    validBorderStyles = ['none', 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'inset', 'outset'],
	    groupRowSortableOptions = {
	      axis: 'y',
	      cursor: 'move',
	      forceHelperSize: true,
	      forcePlaceholderSize: true,
	      opacity: 0.8,
	      placeholder: 'sortable-placeholder',
	      revert: true,
	      tolerance: 'pointer',
				start: function(e,ui){ ui.helper.height( ui.item.height() ); ui.placeholder.height( ui.item.height() ); },
	      stop: function(e,ui){ removeSortableWrapHeight(e,ui); ui.item.removeAttr('style'); },
	      update: sortablesSaveLayout
	    },
	    colResizableOptions = {
	      liveDrag: true,
	      minWidth: 100,
	      gripInnerHtml: '<div class="grip"></div>',
	      draggingClass: 'dragging',
	      onDrag: gripDragUpdateWidthDisplays,
	      onResize: wpWidgetsSaveOrder
	    },
	    groupSettingsColorPickerOptions = {
	      change: updateLinkedColorInputs,
	      clear: updateLinkedColorInputs
	    },
	    rowColumnsSelectSpinnersOptions = {
	      min: 1,
	      max: wpzlbL10n.maxColumns,
	      step: 1,
	      incremental: false,
	      change: function(){ changeColumnAmount($(this).closest('.wpzlb-row'), $(this).val()); },
	      stop: function(){ $(this).blur(); }
	    };

	function wpWidgetsInit() {
		_coreWpWidgetsInit();
		$rootElem.find('#widgets-left #available-widgets .sidebar-name').on('click', function(){ if ( $(this).closest('.widgets-holder-wrap').hasClass('closed') ) $.cookie('wpzlb_available_widgets_collapsed', true); else $.removeCookie('wpzlb_available_widgets_collapsed'); });
		$rootElem.find('#widgets-left #available-widgets .widget .widget-title').off('click.widgets-chooser');
		var origOverFunc = $('#available-widgets').droppable('option', 'over'),
		    origOutFunc = $('#available-widgets').droppable('option', 'out');
		$('#available-widgets').droppable('option', {
			accept: function(o){ return $(o).parent().hasClass('widgets-sortables'); },
			over: function(e,ui){ origOverFunc.call(this, e, ui); ui.draggable.closest('tr').find('div.widgets-sortables').removeClass('ui-sortable-over'); },
			out: function(e,ui){ origOutFunc.call(this, e, ui); ui.draggable.closest('div.widgets-sortables').addClass('ui-sortable-over'); }
		});
		var origStopFunc = $rootElem.find('div.widgets-sortables').sortable('option', 'stop');
		$rootElem.find('div.widgets-sortables').sortable('option', {
			containment: false,
			receive: $.noop,
			start: function(e,ui){ ui.item.removeClass('open').css('height', ''); ui.item.children('.widget-inside').hide(); ui.item.closest('div.widgets-sortables').addClass('ui-sortable-over'); },
			stop: function(e,ui){ ui.item.closest('div.widgets-sortables').removeClass('ui-sortable-over'); origStopFunc.call(this, e, ui); },
			over: function(){ $(this).closest('tr').find('div.widgets-sortables').removeClass('ui-sortable-over'); $(this).addClass('ui-sortable-over'); },
			out: function(e,ui){ ui.sender.removeClass('ui-sortable-over'); }
		});
	}

	function wpWidgetsRefresh() {
		var $sortables = $('div.widgets-sortables');
		$sortables.each(function(){ if ( $(this).hasClass('ui-sortable') ) $(this).sortable('destroy'); });
		$sortables.sortable(_sortableOptions).sortable('refresh');
	}

	function wpWidgetsSave( widget, del, animate, order, synchronous ) {
		var data = widget.find('> .widget-inside :input').serialize(), a;
		widget = $(widget);
		$('.spinner', widget).show();

		a = {
			action: 'save-widget',
			savewidgets: $('#_wpnonce_widgets').val(),
			sidebar: '_wpzlb-page-' + postid + '-widgets'
		};

		if ( del )
			a.delete_widget = 1;

		data += '&' + $.param(a);

		$.ajax({
			url: ajaxurl,
			type: 'post',
			async: ( synchronous !== true ),
			data: data,
			complete: function(xhr){
				var r = xhr.responseText, id;

				if ( del ) {
					if ( !$('input.widget_number', widget).val() ) {
						id = $('input.widget-id', widget).val();
						$('#available-widgets').find('input.widget-id').each(function(){
							if ( $(this).val() == id )
								$(this).closest('div.widget').show();
						});
					}

					if ( animate ) {
						order = 0;
						widget.slideUp('fast', function(){
							$(this).remove();
							window.wpWidgets.saveOrder();
						});
					} else {
						widget.remove();
						window.wpWidgets.resize();
					}
				} else {
					$('.spinner').hide();
					if ( r && r.length > 2 ) {
						$('div.widget-content', widget).html(r);
						window.wpWidgets.appendTitle(widget);
					}
				}

				if ( wpzlbWidgetSaveCallbacks.length > 0 ) $.each(wpzlbWidgetSaveCallbacks, function(i,v){ if ( typeof(v) == 'function' ) v.call(widget); });

				if ( order )
					window.wpWidgets.saveOrder();
			}
		});
	}

	function wpWidgetsSaveOrder( refresh ) {
		refresh = refresh === true ? true : false;

		var a = {
			action: 'wpzlb-widgets-order',
			savewidgets: $('#_wpnonce_widgets').val(),
			sidebar: '_wpzlb-page-' + postid + '-widgets',
			widgets: '',
			layout: getLayoutAsJsonString()
		};

		var widgets = [];
		$rootElem.find('div.widgets-sortables > .widget').each(function(){ widgets.push( $(this).attr('id') ); });
		a.widgets = widgets.join(',');

		$.post( ajaxurl, a, function(){
			$('.spinner').hide();

			if ( refresh ) {
				$.cookie('wpzlb_load_saved_layout_window_scroll_pos', $(window).scrollTop());
				window.location.href = wpzlbL10n.adminPostEditUrl;
				window.location.reload(true);
			}
		});
	}

	function throttleSaveOrder() {
		clearTimeout(saveTimeout);
		saveTimeout = setTimeout(wpWidgetsSaveOrder, 500);
	}

	function sortablesSaveLayout( e, ui ) {
		if ( ui.sender !== null ) return;
		wpWidgetsSaveOrder();
	}

	function colResizableRefresh( $row ) {
		var $table = $row.find('table.wpzlb-columns');
		if ( $table.length < 1 ) return false;
		if ( $table.hasClass('CRZ') ) $table.colResizable({ disable: true });
		$table.find('td.wpzlb-column').removeAttr('style');
		$table.colResizable(colResizableOptions);
	}

	function scrollToElementIfNotInView( $element ) {
		if ( ( $element.offset().top - $(window).scrollTop() ) > window.innerHeight ) {
			$('html, body').animate({ scrollTop: $element.offset().top - 50 }, 1000);
			return false;
		}

		return true;
	}

	function getTableCellPercentWidth( $cell ) {
		var cellsWidth = 0;
		$cell.closest('table').find('> tbody > tr > td:visible').each(function(){ cellsWidth += $(this).width(); });
		return ( ( $cell.width() / cellsWidth ) * 100 ).toFixed(1);
	}

	function updateColumnWidthDisplays( $table ) {
		$table.find('td.wpzlb-column:visible').each(function(){
			var $currWidth = $(this).find('> .wpzlb-column-wrap > div.wpzlb-column-width span span'),
			    newWidth = getTableCellPercentWidth( $(this) ) + '%';
			if ( $currWidth.text() != newWidth ) $currWidth.text(newWidth);
		});
	}

	function updateAllColumnWidthDisplays() {
		$rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-group .wpzlb-row table.wpzlb-columns').each(function(){
			updateColumnWidthDisplays( $(this) );
		});
	}

	function gripDragUpdateWidthDisplays( event ) {
		document.activeElement.blur();
		updateColumnWidthDisplays( $(event.currentTarget) );
	}

	function setupGroupAndRowSortables() {
		var $wrapper = $rootElem.find('#widgets-right .widgets-holder-wrap'), groupSortableOptions, rowSortableOptions;

		groupSortableOptions = rowSortableOptions = groupRowSortableOptions;

		groupSortableOptions.cancel = '.wpzlb-group.show-settings';
		groupSortableOptions.handle = '.wpzlb-group-controls .wpzlb-group-controls-move:not(.disabled)';
		groupSortableOptions.items = '> .wpzlb-group';
		$wrapper.sortable(groupSortableOptions);

		rowSortableOptions.connectWith = '#widgets-right .widgets-holder-wrap > .wpzlb-group > .wpzlb-rows';
		rowSortableOptions.handle = '.wpzlb-row-controls .wpzlb-row-controls-move:not(.disabled)';
		rowSortableOptions.items = '> .wpzlb-row';
		rowSortableOptions.receive = sortablesReceiveRow;
		$wrapper.find('> .wpzlb-group > .wpzlb-rows').sortable(rowSortableOptions);

		$wrapper.on('mousedown', '> .wpzlb-group > .wpzlb-group-controls .wpzlb-group-controls-move:not(.disabled), > .wpzlb-group > .wpzlb-rows > .wpzlb-row > .wpzlb-row-controls .wpzlb-row-controls-move:not(.disabled)', updateSortableWrapHeight);
	}

	function setupRowColumnsSelectSpinners() {
		$rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-group .wpzlb-row .wpzlb-row-controls .wpzlb-row-controls-type .wpzlb-row-controls-type-input')
			.spinner(rowColumnsSelectSpinnersOptions);
	}

	function newRowColumnsSelectSpinnersUpdate( $row ) {
		$row.find('.wpzlb-row-controls .wpzlb-row-controls-type .wpzlb-row-controls-type-input')
			.spinner(rowColumnsSelectSpinnersOptions);
	}

	function sortablesReceiveRow( e, ui ) {
		ui.item.siblings('.wpzlb-row').addBack().find('.wpzlb-row-controls a.wpzlb-row-controls-remove').removeClass('disabled');

		ui.sender.css('min-height', '');

		if ( ui.sender.find('> .wpzlb-row').length < 1 ) {
			var $newRow = insertNewRow( ui.sender.closest('.wpzlb-group') );

			$newRow.find('.wpzlb-row-controls a.wpzlb-row-controls-remove').addClass('disabled');

			wpWidgetsRefresh();
			colResizableRefresh($newRow);
			wpWidgetsSaveOrder();
		}
	}

	function newGroupSortablesUpdate( $group ) {
		var rowSortableOptions = groupRowSortableOptions;
		rowSortableOptions.connectWith = '#widgets-right .widgets-holder-wrap > .wpzlb-group > .wpzlb-rows';
		rowSortableOptions.handle = '.wpzlb-row-controls .wpzlb-row-controls-move:not(.disabled)';
		rowSortableOptions.items = '> .wpzlb-row';
		rowSortableOptions.receive = sortablesReceiveRow;
		$group.find('> .wpzlb-rows').sortable(rowSortableOptions);
	}

	function newGroupSettingsUpdate( $group ) {
		var $settings = $group.find('.wpzlb-group-settings');
		$group.find('.wpzlb-group-name .wpzlb-group-name-input').tipsy({ fade: true, gravity: 's', trigger: 'manual' });
		$settings.find('.wpzlb-group-settings-inner .reset > span').tipsy({ fade: true, gravity: 'e', trigger: 'manual' });
	}

	function updateSortableWrapHeight() {
		var $wrap = $(this).closest('.wpzlb-rows').length > 0 ? $(this).closest('.wpzlb-rows') : $(this).closest('.widgets-holder-wrap'),
		    wrapHeight = $wrap.height();
		if ( parseInt($wrap.css('min-height'), 10) != wrapHeight ) $wrap.css('min-height', wrapHeight);
	}

	function removeSortableWrapHeight( e, ui ) {
		ui.item.parent().css('min-height', '');
	}

	function setupGroupSettingsInputs( $group ) {
		var $groupSettings = $group.find('.wpzlb-group-settings');

		$groupSettings.find('input.fontcolor:not(.wp-color-picker), input.bgcolor:not(.wp-color-picker)').wpColorPicker(groupSettingsColorPickerOptions);
		$groupSettings.find('input.bordercolor:not(.wp-color-picker)').wpzColorPicker(groupSettingsColorPickerOptions);
		$groupSettings.find('input.padding:not(.ui-spinner-input), input.borderwidth:not(.ui-spinner-input), input.borderradius:not(.ui-spinner-input), input.margin:not(.ui-spinner-input)').each(setupGroupSettingsSpinners);
		setupLinkedInputs($groupSettings);
	}

	function setupGroupSettingsSpinners() {
		var $input = $(this);

		$input.spinner({ min: 0, max: 100 });

		if ( $input.hasClass('padding') || $input.hasClass('margin') || $input.hasClass('borderradius') ) {
			var tooltip;

			if ( $input.hasClass('padding') || $input.hasClass('margin') ) {
				tooltip = $input.hasClass('postop') ? wpzlbL10n.tooltipGroupSettingsPositionTop : ( $input.hasClass('posleft') ? wpzlbL10n.tooltipGroupSettingsPositionLeft : ( $input.hasClass('posright') ? wpzlbL10n.tooltipGroupSettingsPositionRight : wpzlbL10n.tooltipGroupSettingsPositionBottom ) );
			} else {
				tooltip = $input.hasClass('postopleft') ? wpzlbL10n.tooltipGroupSettingsPositionTopLeft : ( $input.hasClass('postopright') ? wpzlbL10n.tooltipGroupSettingsPositionTopRight : ( $input.hasClass('posbottomleft') ? wpzlbL10n.tooltipGroupSettingsPositionBottomLeft : wpzlbL10n.tooltipGroupSettingsPositionBottomRight ) );
			}
			$input.parent('.ui-spinner').attr('title', tooltip);
		}

		$('<span class="pxsuffix code">px</span>').click(function(){ $(this).prev('input[type="text"]').focus(); }).insertAfter($input);
	}

	function setupLinkedInputs( $groupSettings ) {
		$groupSettings.find('.inputlink').each(function(){
			if ( $(this).hasClass('checked') ) {
				if ( $(this).hasClass('paddinglink') || $(this).hasClass('marginlink') ) {
					$(this).closest('tr').find('td').slice(1).find('input.ui-spinner-input').spinner('disable');
				} else if ( $(this).hasClass('borderwidthlink') || $(this).hasClass('borderradiuslink') ) {
					$(this).closest('table').find('tbody tr td:nth-child(' + ($(this).closest('th').index()+1) + ')').slice(1).find('input.ui-spinner-input').spinner('disable');
				} else if ( $(this).hasClass('bordercolorlink') ) {
					$(this).closest('table').find('tbody td .wp-picker-container').slice(1).addClass('disabled');
				} else if ( $(this).hasClass('borderstylelink') ) {
					$(this).closest('table').find('tbody td select.borderstyle').slice(1).attr('disabled', true);
				}
			}
		});
	}

	function linkedInputToggle( event ) {
		event.preventDefault();

		var $allInputs = $(this).hasClass('paddinglink') || $(this).hasClass('marginlink') ? $(this).closest('tr').find('input.ui-spinner-input') : $(this).closest('table').find('> tbody > tr > td:nth-child(' + ($(this).closest('th').index()+1) + ')').find('input.ui-spinner-input, input.bordercolor, select.borderstyle'),
		    $inputs = $allInputs.slice(1),
		    inputVal = $allInputs.first().val();

		if ( $(this).hasClass('checked') ) {
			$(this).removeClass('checked');

			if ( $inputs.hasClass('ui-spinner-input') ) {
				$inputs.spinner('enable');
			} else if ( $inputs.hasClass('bordercolor') ) {
				$inputs.closest('.wp-picker-container').removeClass('disabled');
			} else if ( $inputs.hasClass('borderstyle') ) {
				$inputs.attr('disabled', false);
			}
		} else {
			$(this).addClass('checked');
			$inputs.val(inputVal);

			if ( $inputs.hasClass('ui-spinner-input') ) {
				$inputs.spinner('disable');
			} else if ( $inputs.hasClass('bordercolor') ) {
				$inputs.wpzColorPicker('color', inputVal).closest('.wp-picker-container').addClass('disabled');
			} else if ( $inputs.hasClass('borderstyle') ) {
				$inputs.attr('disabled', true);
			}

			throttleSaveOrder();
		}
	}

	function updateLinkedInputs( event ) {
		var $this = $(event.currentTarget),
		    linked = $this.hasClass('padding') || $this.hasClass('margin') ? $this.closest('tr').find('> th .inputlink.checked').length > 0 : $this.closest('table').find('> thead > tr > th:nth-child(' + ($this.closest('td').index()+1) + ') .inputlink.checked').length > 0;

		if ( $this.hasClass('ui-spinner-input') ) {
			$this.val( Math.min( Math.max( parseInt( $this.val().replace(/[^0-9]/g, '') || 0, 10 ), 0 ), 100 ) );
		}

		if ( linked ) {
			var $wrappers = $this.hasClass('padding') || $this.hasClass('margin') ? $this.closest('tr').find('> td').slice(1) : $this.closest('table').find('> tbody > tr > td:nth-child(' + ($this.closest('td').index()+1) + ')').slice(1);

			if ( $this.hasClass('ui-spinner-input') ) {
				$wrappers.find('input.ui-spinner-input').val($this.val());
			} else if ( $this.hasClass('bordercolor') ) {
				$wrappers.find('input.bordercolor').wpzColorPicker('color', $this.val());
			} else if ( $this.hasClass('borderstyle') ) {
				$wrappers.find('select.borderstyle').val($this.val());
			}
		}

		throttleSaveOrder();
	}

	function updateLinkedColorInputs( event ) {
		var index = $(this).closest('td').index() + 1,
		    linked = $(this).closest('table').find('> thead > tr > th:nth-child(' + index + ') .inputlink.checked').length > 0;

		if ( linked && $(this).closest('tr').is(':first-child') ) {
			var color = $(this).closest('table').find('> tbody > tr:first-child > td:nth-child(' + index + ') input.bordercolor').wpzColorPicker('color'),
			    $inputs = $(this).closest('table').find('> tbody > tr > td:nth-child(' + index + ') input.bordercolor').slice(1);

			if ( $(event.currentTarget).hasClass('wp-picker-clear') ) {
				$inputs.val('');
				$inputs.closest('.wp-picker-container').find('a.wp-color-result').css('backgroundColor', '');
			} else {
				$inputs.wpzColorPicker('color', color);
			}
		}

		throttleSaveOrder();
	}

	function setupGroupSettingsBgImg() {
		imageUploadFrame = wp.media({
			title: wpzlbL10n.backgroundImageModalTitle,
			multiple: false,
			library: { type: 'image' },
			button: { text: wpzlbL10n.backgroundImageModalButtonLabel }
		});

		imageUploadFrame.on('open', function(){
			imageUploadFrame.reset();
			var attachment = wp.media.attachment( parseInt( $(imageUploadFrame.currImageUrlField).prev('input.bgimgid').val(), 10 ) );
			attachment.fetch();
			if ( typeof attachment.id != 'undefined' && !isNaN( attachment.id ) ) imageUploadFrame.state().get('selection').add( attachment );
		});

		imageUploadFrame.on('select', function(){
			var attachment = imageUploadFrame.state().get('selection').first();
			$(imageUploadFrame.currImageUrlField).val(attachment.get('url')).prev('input.bgimgid').val(attachment.get('id'));
		});

		imageUploadFrame.on('close', function(){
			$(imageUploadFrame.currImageUrlField).focus();
		});
	}

	function backgroundImageSelectClick( event ) {
		event.preventDefault();

		imageUploadFrame.currImageUrlField = $(this).prev('input.bgimgurl');
		imageUploadFrame.open();

		return;
	}

	function backgroundImageClearClick( event ) {
		event.preventDefault();
		$(this).siblings('input.bgimgurl, input.bgimgid').val('');
		return;
	}

	function newGroupClick( event ) {
		if ( event !== false ) event.preventDefault();

		var colsTpl = '';
		for ( var i = 1; i <= wpzlbL10n.maxColumns; i++ ) {
			colsTpl += '<td class="wpzlb-column wpzlb-column-' + i + '"><div class="wpzlb-column-wrap"><div class="widgets-sortables"></div><div class="wpzlb-column-width"><span><span>' + ( i == 1 ? '100' : '0' ) + '%</span></span></div><div class="wpzlb-column-focusable" tabindex="-1"></div></div></td>';
		}

		var $groups = $rootElem.find('#widgets-right .widgets-holder-wrap > .wpzlb-group'),
		    newGroupNum = $groups.length + 1,
		    $newGroup = $('<div class="wpzlb-group" style="display:none"> \
		                     <div class="wpzlb-group-controls"> \
		                       <a class="wpzlb-group-controls-move" title="' + wpzlbL10n.tooltipGroupRearrange + '"></a> \
		                       <a class="wpzlb-group-controls-settings" title="' + wpzlbL10n.tooltipGroupChangeSettings + '"></a> \
		                       <a class="wpzlb-group-controls-remove" title="' + wpzlbL10n.tooltipGroupRemove + '"></a> \
		                       <div class="clear"></div> \
		                     </div> \
		                     <div class="wpzlb-group-name"><input type="text" class="wpzlb-group-name-input" size="35" placeholder="' + wpzlbL10n.inputPlaceholderGroupName + '" autocomplete="off" title="' + wpzlbL10n.tooltipGroupName + '" /></div> \
		                     <div class="clear"></div> \
		                     <div class="wpzlb-rows"> \
		                       <div class="wpzlb-row wpzlb-row-type-1"> \
		                         <div class="wpzlb-row-controls"> \
		                           <a class="wpzlb-row-controls-move" title="' + wpzlbL10n.tooltipRowRearrange + '"></a> \
		                           <span class="wpzlb-row-controls-type" title="' + wpzlbL10n.tooltipRowType + '"><input type="text" value="1" size="1" maxlength="2" autocomplete="off" class="wpzlb-row-controls-type-input code" /></span> \
		                           <a class="wpzlb-row-controls-remove disabled" title="' + wpzlbL10n.tooltipRowRemove + '"></a> \
		                           <div class="clear"></div> \
		                         </div> \
		                         <div class="wpzlb-columns-wrap"><table class="wpzlb-columns"><tbody><tr>' + colsTpl + '</tr></tbody></table></div> \
		                       </div> \
		                     </div> \
		                   	 <div class="clear"></div> \
		                   	 <span class="wpzlb-group-addrow button"><i class="fa fa-plus"></i> ' + wpzlbL10n.buttonLabelAddRow + '</span> \
		                   	 <span class="wpzlb-group-adddivider button"><i class="fa fa-sort"></i> ' + wpzlbL10n.buttonLabelAddDivider + '</span> \
		                     <div class="wpzlb-group-settings"> \
		                       <div class="wpzlb-group-settings-inner"> \
		                         <div class="reset" title="' + wpzlbL10n.tooltipGroupSettingsReset + '"><span title="' + wpzlbL10n.tooltipGroupSettingsDidReset + '"></span></div> \
		                         <div class="close" title="' + wpzlbL10n.tooltipGroupSettingsClose + '"></div> \
		                         <table class="altspacing"> \
		                           <tbody> \
		                             <tr valign="top"> \
		                               <th scope="row">' + wpzlbL10n.inputLabelFontColor + '</th> \
		                               <td><input type="text" class="fontcolor code" size="7" autocomplete="off" /></td> \
		                             </tr> \
		                           </tbody> \
		                         </table> \
		                         <fieldset> \
		                           <legend>' + wpzlbL10n.inputLabelBackground + '</legend> \
		                           <table> \
		                             <tbody> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBackgroundColor + '</th> \
		                                 <td><input type="text" class="bgcolor code" size="7" autocomplete="off" /></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBackgroundImage + '</th> \
		                                 <td><input type="hidden" class="bgimgid" /> <input type="text" class="bgimgurl code" size="22" autocomplete="off" /> <span class="select-image button">' + wpzlbL10n.inputLabelBackgroundImageButtonSelect + '</span> <span class="clear-image button">' + wpzlbL10n.inputLabelBackgroundImageButtonClear + '</span></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBackgroundPosition + '</th> \
		                                 <td><label><input type="radio" name="bgimgpos' + newGroupNum + '" class="bgimgpos" value="left" /> <span class="code">' + wpzlbL10n.inputLabelBackgroundPositionLeft + '</span></label> <label><input type="radio" name="bgimgpos' + newGroupNum + '" class="bgimgpos" value="center" checked /> <span class="code">' + wpzlbL10n.inputLabelBackgroundPositionCenter + '</span></label> <label><input type="radio" name="bgimgpos' + newGroupNum + '" class="bgimgpos" value="right" /> <span class="code">' + wpzlbL10n.inputLabelBackgroundPositionRight + '</span></label></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBackgroundRepeat + '</th> \
		                                 <td><select class="bgimgrepeat code"><option value="norepeat">' + wpzlbL10n.inputLabelBackgroundRepeatNo + '</option><option value="tile" selected>' + wpzlbL10n.inputLabelBackgroundRepeatTile + '</option><option value="tileh">' + wpzlbL10n.inputLabelBackgroundRepeatTileHorizontally + '</option><option value="tilev">' + wpzlbL10n.inputLabelBackgroundRepeatTileVertically + '</option></select></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBackgroundAttachment + '</th> \
		                                 <td><label><input type="radio" name="bgimgattach' + newGroupNum + '" class="bgimgattach" value="scroll" checked /> <span class="code">' + wpzlbL10n.inputLabelBackgroundAttachmentScroll + '</span></label> <label><input type="radio" name="bgimgattach' + newGroupNum + '" class="bgimgattach" value="fixed" /> <span class="code">' + wpzlbL10n.inputLabelBackgroundAttachmentFixed + '</span></label></td> \
		                               </tr> \
		                             </tbody> \
		                           </table> \
		                         </fieldset> \
		                         <table class="paddmarg"> \
		                           <tbody> \
		                             <tr valign="top"> \
		                               <th scope="row">' + wpzlbL10n.inputLabelPadding + ' <span class="inputlink paddinglink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                               <td><input type="text" class="padding paddingtop postop code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="padding paddingleft posleft code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="padding paddingright posright code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="padding paddingbottom posbottom code" size="3" value="0" autocomplete="off" /></td> \
		                             </tr> \
		                             <tr valign="top"> \
		                               <th scope="row">' + wpzlbL10n.inputLabelMargin + ' <span class="inputlink marginlink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                               <td><input type="text" class="margin margintop postop code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="margin marginleft posleft code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="margin marginright posright code" size="3" value="0" autocomplete="off" /></td> \
		                               <td><input type="text" class="margin marginbottom posbottom code" size="3" value="0" autocomplete="off" /></td> \
		                             </tr> \
		                           </tbody> \
		                         </table> \
		                         <fieldset> \
		                           <legend>' + wpzlbL10n.inputLabelBorder + '</legend> \
		                           <table> \
		                             <thead> \
		                               <tr> \
		                                 <th>&nbsp;</th> \
		                                 <th>' + wpzlbL10n.inputLabelBorderWidth + ' <span class="inputlink borderwidthlink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                                 <th>' + wpzlbL10n.inputLabelBorderColor + ' <span class="inputlink bordercolorlink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                                 <th>' + wpzlbL10n.inputLabelBorderStyle + ' <span class="inputlink borderstylelink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                                 <th>' + wpzlbL10n.inputLabelBorderRadius + ' <span class="inputlink borderradiuslink checked" title="' + wpzlbL10n.tooltipGroupSettingsLockInputs + '"></span></th> \
		                               </tr> \
		                             </thead> \
		                             <tbody> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBorderTop + '</th> \
		                                 <td><input type="text" class="borderwidth borderwidthtop code" size="3" value="0" autocomplete="off" /></td> \
		                                 <td><input type="text" class="bordercolor bordercolortop code" size="7" autocomplete="off" /></td> \
		                                 <td><select class="borderstyle borderstyletop code"><option value="none" selected>' + wpzlbL10n.inputLabelBorderStyleNone + '</option><option value="solid">' + wpzlbL10n.inputLabelBorderStyleSolid + '</option><option value="dotted">' + wpzlbL10n.inputLabelBorderStyleDotted + '</option><option value="dashed">' + wpzlbL10n.inputLabelBorderStyleDashed + '</option><option value="double">' + wpzlbL10n.inputLabelBorderStyleDouble + '</option><option value="groove">' + wpzlbL10n.inputLabelBorderStyleGroove + '</option><option value="ridge">' + wpzlbL10n.inputLabelBorderStyleRidge + '</option><option value="inset">' + wpzlbL10n.inputLabelBorderStyleInset + '</option><option value="outset">' + wpzlbL10n.inputLabelBorderStyleOutset + '</option></select></td> \
		                                 <td class="borderradius"><input type="text" class="borderradius borderradiustopright postopright code" size="3" value="0" autocomplete="off" /></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBorderLeft + '</th> \
		                                 <td><input type="text" class="borderwidth borderwidthleft code" size="3" value="0" autocomplete="off" /></td> \
		                                 <td><input type="text" class="bordercolor bordercolorleft code" size="7" autocomplete="off" /></td> \
		                                 <td><select class="borderstyle borderstyleleft code"><option value="none" selected>' + wpzlbL10n.inputLabelBorderStyleNone + '</option><option value="solid">' + wpzlbL10n.inputLabelBorderStyleSolid + '</option><option value="dotted">' + wpzlbL10n.inputLabelBorderStyleDotted + '</option><option value="dashed">' + wpzlbL10n.inputLabelBorderStyleDashed + '</option><option value="double">' + wpzlbL10n.inputLabelBorderStyleDouble + '</option><option value="groove">' + wpzlbL10n.inputLabelBorderStyleGroove + '</option><option value="ridge">' + wpzlbL10n.inputLabelBorderStyleRidge + '</option><option value="inset">' + wpzlbL10n.inputLabelBorderStyleInset + '</option><option value="outset">' + wpzlbL10n.inputLabelBorderStyleOutset + '</option></select></td> \
		                                 <td class="borderradius"><input type="text" class="borderradius borderradiustopleft postopleft code" size="3" value="0" autocomplete="off" /></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBorderRight + '</th> \
		                                 <td><input type="text" class="borderwidth borderwidthright code" size="3" value="0" autocomplete="off" /></td> \
		                                 <td><input type="text" class="bordercolor bordercolorright code" size="7" autocomplete="off" /></td> \
		                                 <td><select class="borderstyle borderstyleright code"><option value="none" selected>' + wpzlbL10n.inputLabelBorderStyleNone + '</option><option value="solid">' + wpzlbL10n.inputLabelBorderStyleSolid + '</option><option value="dotted">' + wpzlbL10n.inputLabelBorderStyleDotted + '</option><option value="dashed">' + wpzlbL10n.inputLabelBorderStyleDashed + '</option><option value="double">' + wpzlbL10n.inputLabelBorderStyleDouble + '</option><option value="groove">' + wpzlbL10n.inputLabelBorderStyleGroove + '</option><option value="ridge">' + wpzlbL10n.inputLabelBorderStyleRidge + '</option><option value="inset">' + wpzlbL10n.inputLabelBorderStyleInset + '</option><option value="outset">' + wpzlbL10n.inputLabelBorderStyleOutset + '</option></select></td> \
		                                 <td class="borderradius"><input type="text" class="borderradius borderradiusbottomright posbottomright code" size="3" value="0" autocomplete="off" /></td> \
		                               </tr> \
		                               <tr valign="top"> \
		                                 <th scope="row">' + wpzlbL10n.inputLabelBorderBottom + '</th> \
		                                 <td><input type="text" class="borderwidth borderwidthbottom code" size="3" value="0" autocomplete="off" /></td> \
		                                 <td><input type="text" class="bordercolor bordercolorbottom code" size="7" autocomplete="off" /></td> \
		                                 <td><select class="borderstyle borderstylebottom code"><option value="none" selected>' + wpzlbL10n.inputLabelBorderStyleNone + '</option><option value="solid">' + wpzlbL10n.inputLabelBorderStyleSolid + '</option><option value="dotted">' + wpzlbL10n.inputLabelBorderStyleDotted + '</option><option value="dashed">' + wpzlbL10n.inputLabelBorderStyleDashed + '</option><option value="double">' + wpzlbL10n.inputLabelBorderStyleDouble + '</option><option value="groove">' + wpzlbL10n.inputLabelBorderStyleGroove + '</option><option value="ridge">' + wpzlbL10n.inputLabelBorderStyleRidge + '</option><option value="inset">' + wpzlbL10n.inputLabelBorderStyleInset + '</option><option value="outset">' + wpzlbL10n.inputLabelBorderStyleOutset + '</option></select></td> \
		                                 <td class="borderradius"><input type="text" class="borderradius borderradiusbottomleft posbottomleft code" size="3" value="0" autocomplete="off" /></td> \
		                               </tr> \
		                             </tbody> \
		                           </table> \
		                         </fieldset> \
		                       </div> \
		                     </div> \
		                     <div class="new"></div> \
		                   </div>').insertAfter($groups.last()).animate({ opacity: 'show', height: 'show' }, ( event !== false ? 800 : 0 ), function(){
												 $(this).find('> .new').fadeOut(( event !== false ? 800 : 0 ), function(){ $(this).remove(); });
		                   });

		$groups.find('.wpzlb-group-controls a, .wpzlb-rows .wpzlb-row-controls a.wpzlb-row-controls-move').removeClass('disabled');

		if ( event !== false ) scrollToElementIfNotInView($newGroup);

		newGroupSortablesUpdate($newGroup);
		newGroupSettingsUpdate($newGroup);
		newRowColumnsSelectSpinnersUpdate($newGroup.find('> .wpzlb-rows > .wpzlb-row').first());

		wpWidgetsRefresh();
		colResizableRefresh($newGroup.find('> .wpzlb-rows > .wpzlb-row').first());
		wpWidgetsSaveOrder();

		return $newGroup;
	}

	function newRowClick( event ) {
		event.preventDefault();

		var $thisGroup = $(this).closest('.wpzlb-group'),
		    $newRow = insertNewRow( $thisGroup, $(this).hasClass('wpzlb-group-adddivider') );

		scrollToElementIfNotInView($newRow);

		$thisGroup.find('> .wpzlb-rows > .wpzlb-row .wpzlb-row-controls a').removeClass('disabled');

		newRowColumnsSelectSpinnersUpdate($newRow);

		wpWidgetsRefresh();
		colResizableRefresh($newRow);
		wpWidgetsSaveOrder();

	}

	function groupSettingsToggleClick( $group ) {
		var $groupSettings = $group.find('> .wpzlb-group-settings'),
		    $groupSettingsInner = $groupSettings.find('> .wpzlb-group-settings-inner'),
		    doHideAfterHeight = false;

		if ( $groupSettings.is(':hidden') ) {
			doHideAfterHeight = true;
			$groupSettings.show();
		}
		var groupSettingsInnerHeight = $groupSettingsInner.outerHeight(true);
		if ( doHideAfterHeight ) $groupSettings.hide();

		if ( $group.hasClass('show-settings') ) {
			$group.removeClass('show-settings');
			$groupSettingsInner.find('.wp-color-result.wp-picker-open').click();
			$groupSettings.animate({ opacity: 'hide' }, 500);
			$group.delay(300).animate({ minHeight: '' }, 500);
		} else {
			$group.addClass('show-settings');
			$group.find('.wpzlb-rows .wpzlb-columns .widgets-sortables .widget .widget-inside:visible').slideUp('fast', function(){ $(this).closest('.widget').attr('style', ''); });
			$group.animate({ minHeight: ( groupSettingsInnerHeight + 22 ) + 'px' }, 500);
			$groupSettings.delay(300).animate({ opacity: 'show' }, 500);
		}
	}

	function resetAllGroupSettings() {
		if ( !confirm(wpzlbL10n.confirmGroupSettingsReset) ) return;

		var $settingsWrap = $(this).closest('.wpzlb-group-settings-inner');

		$settingsWrap.find('input.wp-color-picker, input.bgimgurl, input.bgimgid').val('');
		$settingsWrap.find('.wp-picker-container a.wp-color-result').css('backgroundColor', '');
		$settingsWrap.find('input.ui-spinner-input').val('0');
		$settingsWrap.find('input.bgimgpos[value="center"], input.bgimgattach[value="scroll"]').prop('checked', true);
		$settingsWrap.find('select.bgimgrepeat option[value="tile"], select.borderstyle option[value="none"]').prop('selected', true);

		$settingsWrap.find('.inputlink').addClass('checked');
		$settingsWrap.find('table:not(.paddmarg) tr:nth-child(n+2) input.ui-spinner-input, table.paddmarg td:nth-child(n+3) input.ui-spinner-input').spinner('disable');
		$settingsWrap.find('table:not(.paddmarg) tr:nth-child(n+2) .wp-picker-container').addClass('disabled');
		$settingsWrap.find('table:not(.paddmarg) tr:nth-child(n+2) select.borderstyle').attr('disabled', true);

		$(this).find('> span').tipsy('show').animate({ opacity: 1 }, 3000, function(){ $(this).tipsy('hide'); });

		wpWidgetsSaveOrder();
	}

	function hideAllGroupSettings() {
		if ( $rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-group.show-settings').length < 1 ) return;

		$rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-group.show-settings').each(function(){
			$(this).removeClass('show-settings');

			var $groupSettings = $(this).find('> .wpzlb-group-settings'),
		      $groupSettingsInner = $groupSettings.find('> .wpzlb-group-settings-inner');

			$groupSettingsInner.find('.wp-color-result.wp-picker-open').click();

			$groupSettings.animate({ opacity: 'hide' }, 500);
			$(this).delay(300).animate({ minHeight: '' }, 500);
		});
	}

	function groupOrRowDelete( $this, animate ) {
		animate = animate === false ? false : true;
		var $widgets = $this.find('td.wpzlb-column div.widgets-sortables > .widget');
		if ( $widgets.length > 0 ) $widgets.each(function(){ wpWidgetsSave( $(this), 1, 0, 0 ); });

		$this.css('position', 'relative');
		$('<div class="removing" style="display:none"/>').appendTo($this).fadeIn(200, function(){
			$this.animate({ opacity: 'hide', height: 'hide' }, ( animate ? 'slow' : 0 ), function(){
				var $siblings = $(this).siblings('.wpzlb-group, .wpzlb-row');

				$(this).remove();

				if ( $siblings.length < 2 )
					$siblings.find('> .wpzlb-group-controls a.wpzlb-group-controls-move, > .wpzlb-group-controls a.wpzlb-group-controls-remove, > .wpzlb-row-controls a.wpzlb-row-controls-move, > .wpzlb-row-controls a.wpzlb-row-controls-remove').addClass('disabled');

				if ( animate ) {
					wpWidgetsRefresh();
					wpWidgetsSaveOrder();
				}
			});
		});
	}

	function insertNewRow( $group, divider ) {
		var colsTpl = '';
		for ( var i = 1; i <= wpzlbL10n.maxColumns; i++ ) {
			colsTpl += '<td class="wpzlb-column wpzlb-column-' + i + '"><div class="wpzlb-column-wrap"><div class="widgets-sortables"></div><div class="wpzlb-column-width"><span><span>' + ( i == 1 ? '100' : '0' ) + '%</span></span></div><div class="wpzlb-column-focusable" tabindex="-1"></div></div></td>';
		}

		return $('<div class="wpzlb-row wpzlb-row-type-' + ( divider ? 'divider' : '1' ) + '" style="display:none"> \
		            <div class="wpzlb-row-controls"> \
		              <a class="wpzlb-row-controls-move" title="' + wpzlbL10n.tooltipRowRearrange + '"></a>' +
		              ( divider ? '' : '<span class="wpzlb-row-controls-type" title="' + wpzlbL10n.tooltipRowType + '"><input type="text" value="1" size="1" maxlength="2" autocomplete="off" class="wpzlb-row-controls-type-input code" /></span>' ) +
		              '<a class="wpzlb-row-controls-remove" title="' + ( divider ? wpzlbL10n.tooltipDividerRemove : wpzlbL10n.tooltipRowRemove ) + '"></a> \
		              <div class="clear"></div> \
		            </div>' +
		            ( divider ? '<p class="wpzlb-divider-label"><span>' + wpzlbL10n.rowDividerLabel + '</span></p><div class="clear"></div>' : '<div class="wpzlb-columns-wrap"><table class="wpzlb-columns"><tbody><tr>' + colsTpl + '</tr></tbody></table></div>' ) +
		            '<div class="new"></div> \
		          </div>').appendTo($group.find('> .wpzlb-rows')).animate({ opacity: 'show', height: 'show' }, 800, function(){
		            $(this).find('> .new').fadeOut(800, function(){ $(this).remove(); });
		          });
	}

	function changeColumnAmount( row, newColAmount ) {
		var $thisRow = $(row),
		    newAmount = Math.max(1, Math.min(parseInt(newColAmount, 10), wpzlbL10n.maxColumns)),
		    oldAmount = (m = $thisRow.attr('class').match(/wpzlb-row-type-([0-9]+)/i)) && typeof(m[1]) !== 'undefined' ? parseInt(m[1], 10) : 1,
		    $widgetsToRemove = $thisRow.find('table.wpzlb-columns td.wpzlb-column:gt(' + (newAmount - 1) + ') div.widgets-sortables > .widget');

		if ( oldAmount == newAmount || ( newAmount < oldAmount && $widgetsToRemove.length > 0 && !confirm(wpzlbL10n.confirmColumnRemove) ) ) {
			$thisRow.find('.wpzlb-row-controls .wpzlb-row-controls-type input.wpzlb-row-controls-type-input').val('' + oldAmount);
			return;
		}

		$widgetsToRemove.each(function(){ wpWidgetsSave( $(this), 1, 0, 0 ); $(this).remove(); });

		$thisRow.attr('class', $thisRow.attr('class').replace(/wpzlb-row-type-[0-9]+/i, 'wpzlb-row-type-' + newAmount));
		$thisRow.find('table.wpzlb-columns td.wpzlb-column').removeAttr('style');

		updateColumnWidthDisplays( $thisRow.find('table.wpzlb-columns') );
		wpWidgetsRefresh();
		colResizableRefresh($thisRow);
		throttleSaveOrder();
	}

	function groupActionsClick( event ) {
		event.preventDefault();

		var $thisGroup = $(this).closest('.wpzlb-group');

		if ( $(this).hasClass('wpzlb-group-controls-settings') ) {

			event.stopPropagation();
			hideAllGroupSettings();
			setupGroupSettingsInputs($thisGroup);
			groupSettingsToggleClick($thisGroup);

		} else if ( $(this).hasClass('wpzlb-group-controls-remove') ) {

			var optionsChanged = false;
			$thisGroup.find('.wpzlb-group-settings input').each(function(){
				if ( $(this).val() != $(this).prop('defaultValue') ) {
					optionsChanged = true;
					return false;
				}
			});
			if ( ( optionsChanged || $thisGroup.find('.wpzlb-rows .wpzlb-row').length > 1 || !$thisGroup.find('.wpzlb-rows .wpzlb-row').first().hasClass('wpzlb-row-type-1') || $thisGroup.find('.wpzlb-rows .wpzlb-row .wpzlb-columns .widget').length > 0 ) &&
					 !confirm(wpzlbL10n.confirmGroupRemove) )
				return;

			groupOrRowDelete($thisGroup);

		}

		return;
	}

	function rowActionsClick( event ) {
		event.preventDefault();

		if ( $(this).hasClass('wpzlb-row-controls-remove') ) {
			var $thisRow = $(this).closest('.wpzlb-row');

			if ( $thisRow.find('table.wpzlb-columns td.wpzlb-column div.widgets-sortables > .widget').length > 0 && !confirm(wpzlbL10n.confirmRowRemove) )
				return;

			groupOrRowDelete($thisRow);
		}

		return;
	}

	function showWidgetSelector( elem ) {
		var $selectedColumn = $(elem),
		    $selectedColumnLastChild = $selectedColumn.find('> .wpzlb-column-wrap > .widgets-sortables > .widget').last(),
		    $widgetSelector = $rootElem.find('#wpzlb-widget-picker'),
		    widgetSelectorHidden = $widgetSelector.is(':hidden');

		$widgetSelector.css('pointer-events', '');

		if ( widgetSelectorHidden ) $widgetSelector.show();

		$widgetSelector.find('.wpzlb-widget-picker-inner-wrap').scrollTop(0);
		$widgetSelector.position({
			my: 'top',
			at: ( $selectedColumnLastChild.length > 0 ? 'bottom' : 'top' ),
			of: ( $selectedColumnLastChild.length > 0 ? $selectedColumnLastChild : $selectedColumn ),
			collision: 'none',
			using: function(p,e){ e.element.element.css({ top: ( p.top + 62 ), left: p.left }); },
			within: $rootElem.find('#widgets-right .widgets-holder-wrap')
		});

		if ( widgetSelectorHidden ) $widgetSelector.hide();

		$widgetSelector.stop(true, true).fadeIn(300);
	}

	function hideWidgetSelector() {
		$rootElem.find('#wpzlb-widget-picker').stop(true, true).fadeOut(300);
	}

	function widgetSelectorResizeUpdate() {
		var $selectedColumn = $rootElem.find('#widgets-right > .widgets-holder-wrap > .wpzlb-group > .wpzlb-rows > .wpzlb-row > .wpzlb-columns-wrap > table.wpzlb-columns > tbody > tr > td.selected');

		if ( $selectedColumn.length < 1 ) return;

		var $selectedColumnLastChild = $selectedColumn.find('> .wpzlb-column-wrap > .widgets-sortables > .widget').last(),
		    $widgetSelector = $rootElem.find('#wpzlb-widget-picker');

		$widgetSelector.position({
			my: 'top',
			at: ( $selectedColumnLastChild.length > 0 ? 'bottom' : 'top' ),
			of: ( $selectedColumnLastChild.length > 0 ? $selectedColumnLastChild : $selectedColumn ),
			collision: 'none',
			using: function(p,e){ e.element.element.css({ top: ( p.top + 62 ), left: p.left }); },
			within: $rootElem.find('#widgets-right .widgets-holder-wrap')
		});
	}

	function widgetSelectorAddClick( event ) {
		event.preventDefault();

		var id = $(this).attr('id').substring( $(this).attr('id').indexOf('_') + 1 ),
		    $toClone = $rootElem.find('#widgets-left #widget-list .widget[id$="' + id + '"]').first();

		if ( $toClone.length < 1 ) return false;

		$rootElem.find('#wpzlb-widget-picker').css('pointer-events', 'none');

		var $clone = $toClone.clone();

		$rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-row table.wpzlb-columns > tbody > tr > td.selected').first().find('.widgets-sortables').append($clone);

		var cloneProps = { top: $clone.position().top, left: $clone.position().left, width: $clone.width() };

		$clone
			.css({ position: 'absolute', zIndex: 1000, width: $(this).width() })
			.position({ my: 'left top', at: 'left top', of: $(this), collision: 'none' })
			.animate({ top: cloneProps.top, left: cloneProps.left, width: cloneProps.width }, 600, function(){
				$(this).removeAttr('style');
				hideWidgetSelector();

				var add = $clone.find('input.add_new').val(),
				    n = $clone.find('input.multi_number').val(),
				    id = $clone.attr('id');

				$clone.css({ margin: '', width: '' });

				if ( add ) {
					if ( 'multi' == add ) {
						$clone.html( $clone.html().replace(/<[^<>]+>/g, function(m){ return m.replace(/__i__|%i%/g, n); }) );
						$clone.attr( 'id', id.replace('__i__', n) );
						n++;
						$('div#' + id).find('input.multi_number').val(n);
					} else if ( 'single' == add ) {
						$clone.attr( 'id', 'new-' + id );
					}
					window.wpWidgets.save( $clone, 0, 0, 1 );
					$clone.find('input.add_new').val('');
					$clone.find('a.widget-action').click();
					return;
				}

				window.wpWidgets.saveOrder();
			});

		return false;
	}

	function widgetCloneLinkClick( event ) {
		event.preventDefault();

		var $widget = $(this).closest('.widget'),
		    $clone = $widget.clone(),
		    idBase = $clone.find('.widget-inside input.id_base').val(),
		    newNum = 1;

		$rootElem.find('#widgets-left #widget-list .widget').each(function(){
			var regex = new RegExp('^widget-[0-9]+_' + idBase + '-__i__$', 'i');
			if ( regex.test($(this).attr('id')) ) {
				var multi_number = $(this).find('.widget-inside input.multi_number');
				newNum = parseInt(multi_number.val(), 10);
				multi_number.val(newNum + 1);
				return false;
			}
		});

		$clone.attr('id', $clone.attr('id').replace(/^widget-([0-9]+)_(.+)-[0-9]+$/i, function(m,p1,p2){ return 'widget-' + (parseInt(p1,10)+1) + '_' + p2 + '-' + newNum; }));
		$clone.find('.widget-inside input.widget-id').val($clone.find('.widget-inside input.widget-id').val().replace(/-[0-9]+$/i, '-' + newNum));
		$clone.find('.widget-inside input.widget_number').val(newNum);
		$clone.find('.widget-inside input.add_new').val('multi');
		$clone.find('.widget-inside .widget-content input[name^="widget-"], .widget-inside .widget-content select[name^="widget-"], .widget-inside .widget-content textarea[name^="widget-"]').each(function(){
			$(this).attr('name', $(this).attr('name').replace(/^widget-(.+)\[[0-9]+\]/i, 'widget-$1[' + newNum + ']'));
		});
		$clone.hide().append('<div class="cloned"/>');

		$widget.after($clone).children('.widget-inside').slideUp('fast', function(){
			$(this).closest('.widget').css({ width: '', maxWidth: '', margin: '' });
		});
		$clone.animate({ opacity: 'show', height: 'show' }, 600, function(){ $(this).children('.cloned').fadeOut(300); });

		window.wpWidgets.save( $clone, 0, 0, 1 );
		$clone.find('.widget-inside input.add_new').val('');
	}

	function sanitizeNumber( number ) { return Math.min( Math.max( parseInt( number.replace(/[^0-9]/g, ''), 10 ), 0 ), 100 ); }
	function sanitizeHexColor( color ) { color = $.trim(color); return (/^#[a-f0-9]{6}$/i).test(color) ? color : ''; }
	function sanitizeBorderStyle( style ) { style = $.trim(style); return $.inArray(style, validBorderStyles) > -1 ? style : 'none'; }

	function getLayoutAsJsonString() {
		var layout = [];

		$rootElem.find('#widgets-right > .widgets-holder-wrap > .wpzlb-group').each(function(i){

			layout[i] = {};
			layout[i].settings = {};

			var groupname = $.trim( $(this).find('.wpzlb-group-name .wpzlb-group-name-input').val() ),
			    $settings = $(this).find('.wpzlb-group-settings'),
			    fontcolor = $.trim( $settings.find('input.fontcolor').val() ),
			    bgcolor = $.trim( $settings.find('input.bgcolor').val() ),
			    bgimgid = $.trim( $settings.find('input.bgimgid').val() ),
					bgimgurl = $.trim( $settings.find('input.bgimgurl').val() ),
			    bgimgpos = $.trim( $settings.find('input.bgimgpos:checked').val() ),
			    bgimgrepeat = $.trim( $settings.find('select.bgimgrepeat').val() ),
			    bgimgattach = $.trim( $settings.find('input.bgimgattach:checked').val() );

			layout[i].groupname = groupname !== '' ? groupname : '';

			if ( fontcolor !== '' ) layout[i].settings.font = fontcolor;

			if ( bgcolor !== '' || bgimgid !== '' || bgimgurl !== '' || bgimgpos !== '' || bgimgrepeat !== '' || bgimgattach !== '' ) {
				layout[i].settings.background = {};
				if ( bgcolor !== '' ) layout[i].settings.background.color = bgcolor;
				if ( bgimgid !== '' || bgimgurl !== '' || bgimgpos !== '' || bgimgrepeat !== '' || bgimgattach !== '' ) {
					layout[i].settings.background.image = {};
					if ( bgimgid !== '' && bgimgurl !== '' ) layout[i].settings.background.image.id = bgimgid;
					if ( bgimgurl !== '' ) layout[i].settings.background.image.url = bgimgurl;
					if ( bgimgpos !== '' ) layout[i].settings.background.image.position = bgimgpos;
					if ( bgimgrepeat !== '' ) layout[i].settings.background.image.repeat = bgimgrepeat;
					if ( bgimgattach !== '' ) layout[i].settings.background.image.attachment = bgimgattach;
				}
			}

			layout[i].settings.padding = {};
			layout[i].settings.padding.top = sanitizeNumber( $settings.find('input.paddingtop').val() );
			layout[i].settings.padding.left = sanitizeNumber( $settings.find('input.paddingleft').val() );
			layout[i].settings.padding.right = sanitizeNumber( $settings.find('input.paddingright').val() );
			layout[i].settings.padding.bottom = sanitizeNumber( $settings.find('input.paddingbottom').val() );

			layout[i].settings.border = {};
			layout[i].settings.border.top = {};
			layout[i].settings.border.top.width = sanitizeNumber( $settings.find('input.borderwidthtop').val() );
			layout[i].settings.border.top.color = sanitizeHexColor( $settings.find('input.bordercolortop').val() );
			layout[i].settings.border.top.style = sanitizeBorderStyle( $settings.find('select.borderstyletop').val() );
			layout[i].settings.border.top.radius = sanitizeNumber( $settings.find('input.borderradiustopright').val() );
			layout[i].settings.border.left = {};
			layout[i].settings.border.left.width = sanitizeNumber( $settings.find('input.borderwidthleft').val() );
			layout[i].settings.border.left.color = sanitizeHexColor( $settings.find('input.bordercolorleft').val() );
			layout[i].settings.border.left.style = sanitizeBorderStyle( $settings.find('select.borderstyleleft').val() );
			layout[i].settings.border.left.radius = sanitizeNumber( $settings.find('input.borderradiustopleft').val() );
			layout[i].settings.border.right = {};
			layout[i].settings.border.right.width = sanitizeNumber( $settings.find('input.borderwidthright').val() );
			layout[i].settings.border.right.color = sanitizeHexColor( $settings.find('input.bordercolorright').val() );
			layout[i].settings.border.right.style = sanitizeBorderStyle( $settings.find('select.borderstyleright').val() );
			layout[i].settings.border.right.radius = sanitizeNumber( $settings.find('input.borderradiusbottomright').val() );
			layout[i].settings.border.bottom = {};
			layout[i].settings.border.bottom.width = sanitizeNumber( $settings.find('input.borderwidthbottom').val() );
			layout[i].settings.border.bottom.color = sanitizeHexColor( $settings.find('input.bordercolorbottom').val() );
			layout[i].settings.border.bottom.style = sanitizeBorderStyle( $settings.find('select.borderstylebottom').val() );
			layout[i].settings.border.bottom.radius = sanitizeNumber( $settings.find('input.borderradiusbottomleft').val() );

			layout[i].settings.margin = {};
			layout[i].settings.margin.top = sanitizeNumber( $settings.find('input.margintop').val() );
			layout[i].settings.margin.left = sanitizeNumber( $settings.find('input.marginleft').val() );
			layout[i].settings.margin.right = sanitizeNumber( $settings.find('input.marginright').val() );
			layout[i].settings.margin.bottom = sanitizeNumber( $settings.find('input.marginbottom').val() );

			layout[i].rows = [];

			$(this).find('> .wpzlb-rows > .wpzlb-row').each(function(i2){

				layout[i].rows[i2] = {};

				var thisType = $(this).hasClass('wpzlb-row-type-divider') ? 'divider' : ( ( m = $(this).attr('class').match(/wpzlb-row-type-([0-9]+)/i) ) && typeof( m[1] ) !== 'undefined' ? parseInt( m[1], 10 ) : 1 );
				layout[i].rows[i2].type = thisType;
				if ( thisType == 'divider' ) return;

				var colCount = thisType;
				layout[i].rows[i2].columns = {};

				for ( var i3 = 0; i3 < colCount; i3++ ) {

					layout[i].rows[i2].columns[i3] = {};
					var i3p = (i3 + 1);
					if ( $(this).find('.wpzlb-column-' + i3p).width() > 0 ) layout[i].rows[i2].columns[i3].width = getTableCellPercentWidth( $(this).find('.wpzlb-column-' + i3p) );
					layout[i].rows[i2].columns[i3].widgets = $(this).find('.wpzlb-column-' + i3p + ' div.widgets-sortables > .widget').length > 0 ? $(this).find('.wpzlb-column-' + i3p + ' div.widgets-sortables > .widget').map(function(){return this.id;}).get() : [];

				}

			});

		});

		return JSON.stringify( layout );
	}

	function loadLayoutFailed() {
		var $loadBtn = $rootElem.find('#widgets-right .loadsave-layout #wpzlb-loadlayout');

		$loadBtn.next('#wpzlb-savelayout').addBack().removeClass('disabled');

		$loadBtn
			.addClass('failed')
			.attr('title', wpzlbL10n.tooltipSavedLayoutsLoadFailed)
			.tipsy('show')
			.find('> span > i.fa-spinner')
				.removeClass('fa-spinner fa-spin')
				.addClass('fa-times');

		loadLayoutFailedTimeout();
	}

	function loadLayoutFailedTimeout() {
		loadLayoutAnimateTimeout = setTimeout(function(){
			$rootElem.find('#widgets-right .loadsave-layout #wpzlb-loadlayout')
				.removeClass('success failed')
				.attr('title', wpzlbL10n.tooltipSavedLayoutsLoad)
				.tipsy('hide')
				.find('> span > i.fa-times')
					.removeClass('fa-times')
					.addClass('fa-folder-open');
		}, 6000);
	}

	function loadLayoutWidgets( layoutid ) {
		var $loadBtn = $rootElem.find('#widgets-right .loadsave-layout #wpzlb-loadlayout');

		$loadBtn.find('> span > i.fa-folder-open').removeClass('fa-folder-open').addClass('fa-spinner fa-spin');
		$loadBtn.find('> ul').animate({ height: 'hide', width: 'hide' }, 300, function(){
			$loadBtn.removeClass('active').next('#wpzlb-savelayout').addBack().addClass('disabled');

			$.post(ajaxurl, { action: 'wpzlb-load-layout-widgets', loadlayoutwidgets: $('#_wpzlbnonce_load_layout_widgets').val(), layoutid: layoutid, postid: parseInt($('input#post_ID').val(), 10) }, function(r){
				if ( r != '-1' && (typeof r == 'object') ) {
					loadLayout(layoutid, r);
				} else {
					loadLayoutFailed();
				}
			});
		});
	}

	function loadLayout( layoutid, ids ) {
		ids = (typeof ids == 'object') ? ids : {};

		var data = { action: 'wpzlb-get-saved-layout-controls', layoutid: layoutid, postid: parseInt($('input#post_ID').val(), 10) };
		data.newids = JSON.stringify(ids);

		$.post(ajaxurl, data, function(r){
			if ( r == '-1' ) {
				loadLayoutFailed();
				return;
			}

			$rootElem.find('#widgets-right .widgets-holder-wrap > .wpzlb-group').each(function(){ groupOrRowDelete($(this), false); });

			$rootElem.find('#widgets-right .widgets-holder-wrap').html(r).find('> .wpzlb-group .wpzlb-rows .wpzlb-row div.widgets-sortables .widget').each(function(){
				wpWidgetsSave( $(this), 0, 0, 0 );
			});

			$rootElem.find('#widgets-right .loadsave-layout #wpzlb-loadlayout')
				.addClass('success')
				.attr('title', wpzlbL10n.tooltipSavedLayoutsLoadSuccess)
				.tipsy('show')
				.find('> span > i.fa-spinner')
					.removeClass('fa-spinner fa-spin')
					.addClass('fa-check');

			wpWidgetsSaveOrder(true);
		});
	}

	function loadLayoutCookieScroll() {
		var window_scroll_pos_cookie = $.cookie('wpzlb_load_saved_layout_window_scroll_pos');
		if ( typeof window_scroll_pos_cookie !== 'undefined' && parseInt( window_scroll_pos_cookie, 10 ) > 0 ) {
			$(window).scrollTop( parseInt( window_scroll_pos_cookie, 10 ) );
			$.removeCookie('wpzlb_load_saved_layout_window_scroll_pos');
		}
	}

	function saveCurrentLayoutClick( event ) {
		event.preventDefault();

		if ( $(this).hasClass('button-disabled') ) return;

		var $dialog = $('#wpzlb-savelayout-dialog');

		if ( !$dialog.hasClass('ui-dialog-content') ) {
			$dialog.dialog({
				autoOpen: false,
				buttons: [
					{ 'text': wpzlbL10n.savedLayoutsSaveDialogButtonSave, 'class': 'button-primary', 'disabled': 'disabled', 'click': function(){
						var $dialog = $(this),
						    nameVal = $.trim($('input#wpzlb-savelayout-name').val());

						if ( !nameVal ) return alert(wpzlbL10n.savedLayoutsSaveDialogNameEmptyAlert);
						if ( $.grep(wpzlbL10n.savedLayoutsSaveDialogLayouts, function(e){ return e.value == nameVal; }).length > 0 && !confirm(wpzlbL10n.savedLayoutsSaveDialogOverwriteConfirm) ) return;

						$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset button.button-primary').attr('disabled', 'disabled');
						$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset .spinner').show();

						$.post(ajaxurl, { action: 'wpzlb-update-saved-layouts', updatelayouts: $('#_wpzlbnonce_saved_layouts').val(), name: nameVal, layout: getLayoutAsJsonString() }, function(r){
							if ( $.isArray(r) ) {
								wpzlbL10n.savedLayoutsSaveDialogLayouts = r;
								$('input#wpzlb-savelayout-name').autocomplete('option', 'source', r);

								$('#wpzlb-loadlayout > ul > li:not(.wpzlb-loadlayout-predefined)').remove();
								for ( i = 0; i < r.length; i++ ) {
									$('#wpzlb-loadlayout > ul > li.wpzlb-loadlayout-predefined').before('<li data-saved-layout-id="' + r[i].id + '" title="' + r[i].label + '">' + r[i].label + '</li>');
								}

								$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset .spinner').animate({ opacity: 'hide', width: 'hide' }, 'slow');
								$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset .success').animate({ opacity: 'show' }, 'slow', function(){ $(this).animate({opacity:1}, 1500, function(){ $dialog.dialog('close'); }); });
							} else {
								$('input#wpzlb-savelayout-name').addClass('error');
								$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset .spinner').animate({ opacity: 'hide', width: 'hide' }, 'slow');
								$dialog.closest('.ui-dialog').find('div.ui-dialog-buttonset .failed').animate({ opacity: 'show' }, 'slow');
							}
						}, 'json');
					} },
					{ 'text': wpzlbL10n.savedLayoutsSaveDialogButtonCancel, 'class': 'button', 'click': function(){$(this).dialog('close');} }
				],
				close: function(){
					var $uidialog = $(this).closest('.ui-dialog');
					$uidialog.find('.ui-dialog-content input#wpzlb-savelayout-name').removeClass('error').val('');
					$uidialog.find('div.ui-dialog-buttonset .success, div.ui-dialog-buttonset .failed, div.ui-dialog-buttonset .spinner').removeAttr('style').hide();
					$uidialog.find('div.ui-dialog-buttonset button.button-primary').attr('disabled', 'disabled');
				},
				dialogClass: 'wp-dialog',
				draggable: false,
				modal: true,
				resizable: false,
				title: wpzlbL10n.savedLayoutsSaveDialogTitle,
				width: 480
			});

			$('input#wpzlb-savelayout-name').on('input', function(){
				var $savebtn = $(this).closest('.ui-dialog').find('div.ui-dialog-buttonset button.button-primary');
				if ( $.trim($(this).val()) === '' ) {
					$savebtn.attr('disabled', 'disabled');
				} else {
					$savebtn.removeAttr('disabled');
				}
			}).autocomplete({
				source: wpzlbL10n.savedLayoutsSaveDialogLayouts
			});
		}

		var $buttonset = $dialog.siblings('div.ui-dialog-buttonpane').find('div.ui-dialog-buttonset');
		if ( $buttonset.find('span.success').length < 1 ) {
			$buttonset.prepend('<span class="success" style="display:none">' + wpzlbL10n.savedLayoutsSaveDialogSuccess + '</span><span class="failed" style="display:none">' + wpzlbL10n.savedLayoutsSaveDialogFailed + '</span><span class="spinner" style="display:none"></span>');
		}

		$dialog.dialog('open');
	}

	function setupBinds() {
		$rootElem.on('mouseenter', '.widget > .widget-top > .widget-title > h4', function(){
			if ( this.offsetWidth < this.scrollWidth && !$(this).attr('title') ) {
				$(this).attr('title', $(this).text());
			} else if ( this.offsetWidth >= this.scrollWidth && $(this).attr('title') ) {
				$(this).removeAttr('title');
			}
		});

		$rootElem.find('#wpzlb-widget-picker')
			.on('mousedown', function(e){ e.preventDefault(); })
			.find('.widget')
				.on('click', widgetSelectorAddClick);

		$rootElem.find('#wpzlb-addgroup').on('click', newGroupClick);

		$(window).on('resize', widgetSelectorResizeUpdate);
		$('html').on('click', hideAllGroupSettings);
		$('body').on('click', 'div.media-modal, div.media-modal-backdrop', function(e){ e.stopPropagation(); });
		$('#collapse-menu').on('click.collapse-menu', function(){ $rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-row .wpzlb-columns').trigger('resize'); });

		$rootElem.find('#widgets-right .widgets-holder-wrap')
			.on('input', '.wpzlb-group .wpzlb-group-name .wpzlb-group-name-input', throttleSaveOrder)
			.on('focus mouseenter', '.wpzlb-group .wpzlb-group-name .wpzlb-group-name-input', function(){ if ( !$(this).hasClass('showing-tipsy') ) $(this).addClass('showing-tipsy').tipsy('show'); })
			.on('blur mouseleave', '.wpzlb-group .wpzlb-group-name .wpzlb-group-name-input', function(){ if ( $(this).hasClass('showing-tipsy') && !$(this).is(':focus') ) $(this).removeClass('showing-tipsy').tipsy('hide'); })
			.on('click', '.wpzlb-group:not(.show-settings) .wpzlb-group-controls > a:not(.wpzlb-group-controls-move,.disabled)', groupActionsClick)
			.on('click', '.wpzlb-group:not(.show-settings) .wpzlb-group-addrow, .wpzlb-group:not(.show-settings) .wpzlb-group-adddivider', newRowClick)
			.on('click', '.wpzlb-group .wpzlb-group-settings .wpzlb-group-settings-inner', function(e){ e.stopPropagation(); $(this).find('.wp-color-result.wp-picker-open').click(); })
			.on('click', '.wpzlb-group .wpzlb-group-settings .wpzlb-group-settings-inner .reset', resetAllGroupSettings)
			.on('click', '.wpzlb-group .wpzlb-group-settings .wpzlb-group-settings-inner .close', hideAllGroupSettings)
			.on('click', '.wpzlb-group .wpzlb-group-settings .wpzlb-group-settings-inner .wp-picker-container', function(e){ e.stopPropagation(); })
			.on('click', '.wpzlb-group .wpzlb-group-settings span.select-image', backgroundImageSelectClick)
			.on('click', '.wpzlb-group .wpzlb-group-settings span.clear-image', backgroundImageClearClick)
			.on('change', '.wpzlb-group .wpzlb-group-settings input.bgimgpos, .wpzlb-group .wpzlb-group-settings select.bgimgrepeat, .wpzlb-group .wpzlb-group-settings input.bgimgattach', wpWidgetsSaveOrder)
			.on('click', '.wpzlb-group .wpzlb-group-settings .inputlink', linkedInputToggle)
			.on('input', '.wpzlb-group .wpzlb-group-settings input.ui-spinner-input', updateLinkedInputs)
			.on('focus', '.wpzlb-group .wpzlb-group-settings input.ui-spinner-input', function(){ if ( !$(this).closest('.ui-spinner').hasClass('focus') ) $(this).closest('.ui-spinner').addClass('focus'); })
			.on('blur', '.wpzlb-group .wpzlb-group-settings input.ui-spinner-input', function(){ $(this).closest('.ui-spinner').removeClass('focus'); })
			.on('spinstop', '.wpzlb-group .wpzlb-group-settings input.ui-spinner-input', updateLinkedInputs)
			.on('change', '.wpzlb-group .wpzlb-group-settings select.borderstyle', updateLinkedInputs)
			.on('click', '.wpzlb-row .wpzlb-row-controls > a:not(.wpzlb-row-controls-move,.current,.disabled)', rowActionsClick)
			.on('focus', '.wpzlb-row .wpzlb-row-controls .wpzlb-row-controls-type .wpzlb-row-controls-type-input', function(){ $(this).closest('.wpzlb-row-controls-type').addClass('focused'); })
			.on('blur', '.wpzlb-row .wpzlb-row-controls .wpzlb-row-controls-type .wpzlb-row-controls-type-input', function(){ $(this).closest('.wpzlb-row-controls-type').removeClass('focused'); })
			.on('input', '.wpzlb-row .wpzlb-row-controls .wpzlb-row-controls-type .wpzlb-row-controls-type-input', function(){ var cv = parseInt( $(this).val(), 10 ); if ( cv < 1 ) $(this).val('1'); else if ( cv > wpzlbL10n.maxColumns ) $(this).val('' + wpzlbL10n.maxColumns); })
			.on('focus', '.wpzlb-row table.wpzlb-columns td.wpzlb-column .wpzlb-column-wrap > .wpzlb-column-focusable', function(){ var $col = $(this).closest('td.wpzlb-column'); $col.addClass('selected'); showWidgetSelector($col); })
			.on('blur', '.wpzlb-row table.wpzlb-columns td.wpzlb-column .wpzlb-column-wrap > .wpzlb-column-focusable', function(){ $(this).closest('td.wpzlb-column').removeClass('selected'); hideWidgetSelector(); })
			.on('click', '.wpzlb-row table.wpzlb-columns .widget', function(){ if ( $(document.activeElement).is('td.wpzlb-column .wpzlb-column-wrap > .wpzlb-column-focusable') ) document.activeElement.blur(); })
			.on('click', '.wpzlb-row table.wpzlb-columns .widget .widget-inside .widget-control-actions .widget-control-clone', widgetCloneLinkClick);

		$('#wpzlb-loadlayout').on('click', function(e){
			e.preventDefault();
			e.stopPropagation();

			if ( $(this).hasClass('disabled') || $(e.target).parent().addBack().hasClass('wpzlb-loadlayout-predefined') || $(e.target).parent().addBack().hasClass('none') ) return;

			if ( $(this).is('.success, .failed') ) {
				clearTimeout(loadLayoutAnimateTimeout);

				$(this)
					.removeClass('success failed')
					.attr('title', wpzlbL10n.tooltipSavedLayoutsLoad)
					.tipsy('hide')
					.find('> span i.fa-check, > span i.fa-times')
						.removeClass('fa-check fa-times')
						.addClass('fa-folder-open');
			}

			if ( $(this).hasClass('active') ) {
				$(this).find('> ul').animate({ height: 'hide', width: 'hide' }, 300, function(){ $(this).parent().removeClass('active'); });
				$(document).off('click.wpzlbLoadlayoutClickOutside focus.wpzlbLoadlayoutFocusOutside');
			} else {
				$(this).find('> ul').css({ minWidth: $(this).width() }).animate({ height: 'show', width: 'show' }, 300);
				$(this).addClass('active');
				$(document).one('click.wpzlbLoadlayoutClickOutside focus.wpzlbLoadlayoutFocusOutside', function(){ if ( $('#wpzlb-loadlayout').hasClass('active') ) $('#wpzlb-loadlayout > ul').animate({ height: 'hide', width: 'hide' }, 300, function(){ $(this).parent().removeClass('active'); }); });
			}
		});
		$('#wpzlb-loadlayout > ul').on('click', 'li', function(e){
			if ( $(this).hasClass('wpzlb-loadlayout-predefined') || !$(this).is('[data-saved-layout-id]') || $.trim( $(this).data('savedLayoutId') ) == '' ) return;

			if ( confirm(wpzlbL10n.savedLayoutsLoadConfirm) ) {
				loadLayoutWidgets( $(this).data('savedLayoutId') );
			}
		});

		$('#wpzlb-savelayout').on('click', saveCurrentLayoutClick);

		$('.postbox#wpzoom_layout_builder').one('mouseenter', function(){
			$('#wpzlb-loadlayout, #wpzlb-savelayout').tipsy({ fade: true, gravity: 's' });

			var $groups = $rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-group');
			$groups.find('.wpzlb-group-name .wpzlb-group-name-input').tipsy({ fade: true, gravity: 's', offset: 2, trigger: 'manual' })
			$groups.find('.wpzlb-group-controls a:not(.disabled), .wpzlb-row .wpzlb-row-controls a:not(.disabled), .wpzlb-row .wpzlb-row-controls .wpzlb-row-controls-type').tipsy({ fade: true, gravity: 's', offset: 2, live: true });
			$groups.find('.wpzlb-group-settings .inputlink, .wpzlb-group-settings .paddmarg .ui-spinner, .wpzlb-group-settings .borderradius .ui-spinner').tipsy({ fade: true, gravity: 's', live: true });
			$groups.find('.wpzlb-group-settings .wpzlb-group-settings-inner .reset > span').tipsy({ fade: true, gravity: 'e', trigger: 'manual' });
		});
	}

	function afterDomReadyDelay() {
		setTimeout(function(){
			_sortableOptions = $rootElem.find('div.widgets-sortables').sortable('option');

			setupGroupAndRowSortables();

			setupRowColumnsSelectSpinners();

			$rootElem.find('#widgets-right .widgets-holder-wrap .wpzlb-row .wpzlb-columns')
				.colResizable(colResizableOptions)
				.trigger('resize');

			updateAllColumnWidthDisplays();

			setupGroupSettingsBgImg();

			setupBinds();
		}, 300);
	}

	window.wpWidgets.init = wpWidgetsInit;
	window.wpWidgets.save = wpWidgetsSave;
	window.wpWidgets.saveOrder = wpWidgetsSaveOrder;

	$rootElem.find('#widgets-right .widgets-holder-wrap').on('click.widgets-toggle', '.wpzlb-row .wpzlb-columns td div.widgets-sortables > .widget > .widget-top', function(e){
		e.preventDefault();

		var widget = $(this).closest('div.widget'),
		    w = parseInt( widget.find('input.widget-width').val(), 10 );

		if ( widget.children('.widget-inside').is(':hidden') ) {
			if ( w > 250 ) widget.css('max-width', w + 30 + 'px');
			if ( !widget.hasClass('open') ) widget.addClass('open');
		} else {
			widget.css('max-width', '');
			widget.removeClass('open');
		}
	});

	$.widget('wp.wpzColorPicker', $.wp.wpColorPicker, {
		_create: function(){
			$.wp.wpColorPicker.prototype._create.apply(this);

			var $pickerInputWrap = this.element.parent('.wp-picker-input-wrap').css({ display: 'block', marginBottom: '10px' }),
			    $irisWrap = this.element.closest('.wp-picker-container').find('.wp-picker-holder .iris-picker');

			$pickerInputWrap.prependTo($irisWrap.find('.iris-picker-inner'));
			$pickerInputWrap.find('input.wp-picker-clear').css('margin-left', '5px');
			$irisWrap.height($irisWrap.outerHeight() + $pickerInputWrap.outerHeight() + 10);
		}
	});

	$(function(){
		afterDomReadyDelay();
		loadLayoutCookieScroll();
	});

})(jQuery);