if (typeof Craft.Commerce === typeof undefined) {
	Craft.Commerce = {};
}

(function () {
	//$('#notesTab').appendTo($('#content'));
	/*var $tabs = $('#tabs ul li');
	var $notesTab = $tabs.last().clone();
	$notesTab
		.find('a')
		.attr('id', 'tab-' + $tabs.length)
		.attr('href', '#notesTab')
		.attr('title', 'Notes')
		.text('Notes');
	$notesTab.insertAfter($tabs.last());
	Craft.cp.initTabs();*/
	// var modal = null;
	// $('#new-note').on('click', function(e) {
	// 	e.preventDefault();
	// 	if (modal) {
	// 		modal.show();
	// 	} else {
	// 		modal = new Craft.Commerce.OrderNoteModal({});
	// 	}
	// });
})();

Craft.Commerce.OrderNotes = Garnish.Base.extend({
	init: function (settings) {
		this.setSettings(settings);

		this.$newNote = $('#new-note');
		var modal = null;

		this.addListener(this.$newNote, 'click', function (e) {
			e.preventDefault();
			if (modal) {
				modal.show();
			} else {
				modal = new Craft.Commerce.OrderNoteModal(this.settings);
			}
		});
	}
});

Craft.Commerce.OrderNoteModal = Garnish.Modal.extend({
	id: null,
	orderStatusId: null,
	originalStatus: null,
	currentStatus: null,
	originalStatusId: null,
	$statusSelect: null,
	$selectedStatus: null,
	$orderStatusIdInput: null,
	$list: null,
	$message: null,
	$error: null,
	$saveBtn: null,
	$statusMenuBtn: null,
	$cancelBtn: null,
	addIds: [],
	init: function (settings) {
		this.id = Math.floor(Math.random() * 1000000000);

		this.setSettings(settings, {
			resizable: true,
			hideOnShadeClick: false,
		});
		//console.log(this.settings)

		var self = this;

		var $form = $('<form class="modal fitted order-notes" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
		var $container = $('<div class="notes-container"></div>').appendTo($form);
		var $header = $('<div class="header"/>').appendTo($container);
		$('<h2 class="">' + Craft.t('commerce', 'Order Note') + '</h2>').appendTo($header);
		var $body = $('<div class="body"></div>').appendTo($container);
		this.$inputs = $('<div class="content"></div>').appendTo($body);

		// Build menu button
		this.$statusSelect = $('<a class="btn menubtn" href="#">Choose type...</a>').appendTo($header);
		var $menu = $('<div class="menu"/>').appendTo($header);
		this.$list = $('<ul class="padded"/>').appendTo($menu);
		var classes = '';
		// for (var i = 0; i < this.settings.noteTypes.length; i++) {
		// 		classes = '';
		// 	$('<li><a data-id="' + this.settings.noteTypes[i].id + '" data-color="' + orderStatuses[i].color + '" data-name="' + orderStatuses[i].name + '" class="' + classes + '"><span class="status ' + orderStatuses[i].color + '"></span>' + orderStatuses[i].name + '</a></li>').appendTo($list);
		// }
		$.each(this.settings.types, function (key, value) {
			$('<li><a data-id="' + value.type + '" data-name="' + value.name + '">' + value.name + '</a></li>').appendTo(self.$list);
		});

		this.$selectedStatus = $('.sel', this.$list);

		// Build message input
		this.$comments = $('<div class="field first">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Comments') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The reason for the note') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<textarea class="text fullwidth" rows="3" cols="50" name="comments"></textarea>' + '</div>' + '</div>').appendTo(this.$inputs);
		this.$value = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Value') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The amount to discount from the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="value">' + '</div>' + '</div>');
		this.$code = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Coupon Code') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The coupon code to add to the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="code">' + '</div>' + '</div>');
		this.$qty = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('commerce', 'Items') + '</label></div>' + '<div class="input ltr">' + '<table class="data fullwidth"><tbody></tbody></table>' + '</div>' + '</div>');
		$.each(this.settings.lineItems, function () {
			$('<tr><td>' + this.name + '</td><td><input type="number" class="text fullwidth" data-label="' + this.name + '" name="qty[' + this.id + ']" value="' + this.qty + '" data-value="' + this.qty + '" /></td></tr>').appendTo(self.$qty.find('tbody'));
		});
		this.$products = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('Product') + '</label>' + '</div>' + '<div class="input ltr">' + '<table class="data fullwidth"><tbody><tr><td></td><td><input class="text fullwidth" name="qty" value="" placeholder="qty" required /></td></tr></tbody></table>' + '</div>' + '</div>');
		this.$add = $('<div class="field hidden">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Product') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'Select products to add to the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<div id="productPicker" class="elementselect">' + '<div class="elements">' + '</div>' + '<div class="btn add icon dashed">Choose</div>' + '</div>' + '</div>' + '</div>').appendTo(
			this.$inputs
		);
		this.$email = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Email') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The customers email') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="email" value="' + this.settings.email + '">' + '</div>' + '</div>');

		//<div id="{{ id }}" class="elementselect"><div class="elements"></div><div class="btn add icon dashed">Choose</div></div>

		// Error notice area
		this.$error = $('<div class="error"/>').appendTo(this.$inputs);

		// Footer and buttons
		var $footer = $('<div class="footer"/>').appendTo($container);
		var $mainBtnGroup = $('<div class="buttons right"/>').appendTo($footer);
		this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('commerce', 'Cancel') + '"/>').appendTo($mainBtnGroup);
		this.$saveBtn = $('<input type="button" class="btn submit" value="' + Craft.t('commerce', 'Save') + '"/>').appendTo($mainBtnGroup);

		this.$saveBtn.addClass('disabled');

		// Listeners and
		this.addListener(this.$statusSelect, 'click', function (e) {
			e.preventDefault();
		});
		this.$statusMenuBtn = new Garnish.MenuBtn(this.$statusSelect, {
			onOptionSelect: $.proxy(this, 'onSelectStatus')
		});
		this.onSelectStatus(this.$list.find('li:first-child a'));

		this.addListener(this.$cancelBtn, 'click', function (e) {
			e.preventDefault();
			this.reset();
			this.hide();
		});
		this.addListener(this.$saveBtn, 'click', function (ev) {
			ev.preventDefault();
			if (!$(ev.target).hasClass('disabled')) {
				this.save();
			}
		});
		this.addListener(this.$comments.find('textarea'), 'mouseup', function (e) {
			self.updateSizeAndPosition();
		});
		this.base($form, this.settings);

		//init new variant modal

		new Craft.BaseElementSelectInput({
			id: 'productPicker',
			name: 'product',
			elementType: 'craft\\commerce\\elements\\Variant',
			sources: null,
			criteria: { hasStock: true, hasProduct: { 'availableForPurchase': true } },
			sourceElementId: null,
			viewMode: 'list',
			limit: null,
			editable: false,
			sortable: false,
			selectable: false,
			modalStorageKey: null,
			onSelectElements: function (e) {
				//console.log(e);
				//$('#main').addClass('loading');
				$.each(e, function (i) {
					console.log(this);

					var $el = $('#productPicker .element[data-id="' + this.id + '"]');
					var $qty = $el.find('input[name="qty[' + this.id + ']"]');
					//console.log($el)
					//console.log($qty)
					if ($qty[0]) {
						$qty.val(Number($qty.val()) + 1);
					} else {
						var $div = $('<div class="flex"></div>').appendTo($el);
						$div.append($el.find('.status'));
						$div.append($el.find('.label'));
						$div.append($('<input type="number" min="1" class="text qty" data-id="' + this.id + '" name="qty[' + this.id + ']" value="1">'));
						$div.append($el.find('.icon'));
					}
				});
				self.updateSizeAndPosition();
			}
		});
	},
	onSelectStatus: function (status) {
		this.deselectStatus();
		//console.log(this);
		this.$selectedStatus = $(status);
		var type = this.$selectedStatus.data('id');

		this.$selectedStatus.addClass('sel');

		var newHtml = '<span>' + Craft.uppercaseFirst($(status).data('name')) + '</span>';
		this.$statusSelect.html(newHtml);

		this.$saveBtn.removeClass('disabled');

		this.$comments.removeClass('hidden');
		this.$value.detach();
		this.$code.detach();
		this.$qty.detach();
		this.$email.detach();
		//this.$productSelect.detach();
		this.$add.addClass('hidden');

		var self = this;
		$.each(this.settings.types, function (key, value) {
			if (value.type == type) {
				$.each(value.props, function (k, v) {
					self['$' + v].removeClass('hidden').appendTo(self.$inputs);
				});
			}
		});

		// if (type == 'manual') {
		// 	this.$value.appendTo(this.$inputs);
		// }
		// if (type == 'code') {
		// 	this.$code.appendTo(this.$inputs);
		// }
		// if (type == 'qty') {
		// 	this.$qty.appendTo(this.$inputs);
		// }
		// if (type == 'add') {
		// 	//this.$productSelect.appendTo(this.$inputs);
		// 	this.$add.removeClass('hidden');
		// }
		// if (type == 'email') {
		// 	this.$comments.addClass('hidden');
		// 	this.$email.appendTo(this.$inputs);
		// }
		//console.log(this.updateSizeAndPosition)
		this.updateSizeAndPosition();
	},

	deselectStatus: function () {
		if (this.$selectedStatus) {
			this.$selectedStatus.removeClass('sel');
		}
	},

	reset: function () {
		//clear values
		this.$comments.find('textarea[name="comments"]').val('');
		this.$value.find('input[name="value"]').val('');
		this.$code.find('input[name="code"]').val('');
		this.addIds = [];
		//clear any errors
		this.clearErrors();
		//set back to first note type
		this.onSelectStatus(this.$list.find('li:first-child a'));
	},

	clearErrors: function () {
		this.$inputs.find('.field.has-errors').each(function () {
			var $this = $(this);
			$this.removeClass('has-errors');
			$this.find('ul.errors').remove();
			$this.find('.input').removeClass('errors');
		});
	},

	save: function () {
		var self = this;
		var data = {
			comments: this.$comments.find('textarea[name="comments"]').val(),
			type: this.$selectedStatus.data('id'),
			orderId: this.settings.orderId,
			data: {}
		};

		if (this.$value.is(':visible')) {
			data['value'] = this.$value.find('input[name="value"]').val();
		}
		if (this.$code.is(':visible')) {
			data.data.code = this.$code.find('input[name="code"]').val();
		}
		if (this.$qty.is(':visible')) {
			data.data.qty = [];
			this.$qty.find('input').each(function () {
				data.data.qty.push({
					id: '' + this.name.replace('qty[', '').replace(']', ''),
					label: this.dataset.label,
					values: { old: $(this).attr('data-value'), new: Number(this.value) }
				});
			});
		}
		if (this.$email.is(':visible')) {
			data.data.oldEmail = this.settings.email;
			data.data.email = this.$email.find('input[name="email"]').val();
			data.comments = this.settings.email + ' => ' + data.data.email;
		}
		//console.log(this.$productSelect.is(':visible'));
		if (this.$add.find('#productPicker').is(':visible')) {
			//console.log(this.addIds);
			data.data.add = [];
			$('#productPicker')
				.find('.qty')
				.each(function () {
					data.data.add.push({
						id: this.dataset.id,
						label: $(this)
							.parent()
							.find('.label')
							.text(),
						qty: Number(this.value)
					});
				});
			//data['variant'] = this.$productSelect.find('input[name="variant[]"]').val();
			//data['qty'] = this.$productSelect.find('input[name="qty"]').val();
		}
		//console.log(data);

		Craft.postActionRequest('commerce-order-notes/notes/save', data, function (response) {
			if (response.success) {
				document.location.reload(true);
			} else {
				Craft.cp.displayError("Couldn't save note");
				self.clearErrors();
				$.each(response.errors, function (key) {
					var $field = self['$' + key];
					$field.addClass('has-errors');
					$field.find('.input').addClass('errors');
					$field.append($('<ul class="errors"><li>' + this + '</li></ul>'));
				});

				self.updateSizeAndPosition();
			}
		});

		//this.settings.onSubmit(data);
	},
	defaults: {
		onSubmit: $.noop
	}
});
