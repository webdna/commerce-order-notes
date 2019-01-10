if (typeof Craft.Commerce === typeof undefined) {
	Craft.Commerce = {};
}

(function() {
	$('#notesTab').appendTo($('#content'));
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
	init: function(settings) {
		this.setSettings(settings);

		this.$newNote = $('#new-note');
		var modal = null;

		this.addListener(this.$newNote, 'click', function(e) {
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
	init: function(settings) {
		this.id = Math.floor(Math.random() * 1000000000);

		this.setSettings(settings, {
			resizable: false
		});

		var self = this;

		var $form = $('<form class="modal fitted order-notes" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
		var $body = $('<div class="body"></div>').appendTo($form);
		this.$inputs = $('<div class="content">' + '<h2 class="first">' + Craft.t('commerce', 'Order Note') + '</h2>' + '</div>').appendTo($body);

		// Build menu button
		this.$statusSelect = $('<a class="btn menubtn" href="#">Choose type...</a>').appendTo(this.$inputs);
		var $menu = $('<div class="menu"/>').appendTo(this.$inputs);
		this.$list = $('<ul class="padded"/>').appendTo($menu);
		var classes = '';
		// for (var i = 0; i < this.settings.noteTypes.length; i++) {
		// 		classes = '';
		// 	$('<li><a data-id="' + this.settings.noteTypes[i].id + '" data-color="' + orderStatuses[i].color + '" data-name="' + orderStatuses[i].name + '" class="' + classes + '"><span class="status ' + orderStatuses[i].color + '"></span>' + orderStatuses[i].name + '</a></li>').appendTo($list);
		// }
		$.each(this.settings.types, function(key, value) {
			$('<li><a data-id="' + key + '" data-name="' + value + '">' + value + '</a></li>').appendTo(self.$list);
		});

		this.$selectedStatus = $('.sel', this.$list);

		// Build message input
		this.$comments = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Comments') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The reason for the note') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<textarea class="text fullwidth" rows="2" cols="50" name="comments"></textarea>' + '</div>' + '</div>').appendTo(this.$inputs);
		this.$value = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Value') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The amount to discount from the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="value">' + '</div>' + '</div>');
		this.$code = $('<div class="field">' + '<div class="heading">' + '<label class="required">' + Craft.t('commerce', 'Coupon Code') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The coupon code to add to the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="data[coupon]">' + '</div>' + '</div>');
		this.$qty = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('commerce', 'Items') + '</label></div>' + '<div class="input ltr">' + '<table class="data fullwidth"><tbody></tbody></table>' + '</div>' + '</div>');
		$.each(this.settings.lineItems, function() {
			$('<tr><td>' + this.name + '</td><td><input class="text fullwidth" name="qty[' + this.id + ']" value="' + this.qty + '" /></td></tr>').appendTo(self.$qty.find('tbody'));
		});
		this.$products = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('Product') + '</label>' + '</div>' + '<div class="input ltr">' + '<table class="data fullwidth"><tbody><tr><td></td><td><input class="text fullwidth" name="qty" value="" placeholder="qty" required /></td></tr></tbody></table>' + '</div>' + '</div>');
		this.$productSelect = $('<div id="addProduct" class="elementselect hidden">' + '<div class="elements">' + '</div>' + '<div class="btn add icon dashed">Choose</div>' + '</div>').appendTo(this.$inputs);

		//<div id="{{ id }}" class="elementselect"><div class="elements"></div><div class="btn add icon dashed">Choose</div></div>

		// Error notice area
		this.$error = $('<div class="error"/>').appendTo(this.$inputs);

		// Footer and buttons
		var $footer = $('<div class="footer"/>').appendTo($form);
		var $mainBtnGroup = $('<div class="btngroup right"/>').appendTo($footer);
		this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('commerce', 'Cancel') + '"/>').appendTo($mainBtnGroup);
		this.$saveBtn = $('<input type="button" class="btn submit" value="' + Craft.t('commerce', 'Save') + '"/>').appendTo($mainBtnGroup);

		this.$saveBtn.addClass('disabled');

		// Listeners and
		this.addListener(this.$statusSelect, 'click', function(e) {
			e.preventDefault();
		});
		this.$statusMenuBtn = new Garnish.MenuBtn(this.$statusSelect, {
			onOptionSelect: $.proxy(this, 'onSelectStatus')
		});
		this.onSelectStatus(this.$list.find('li:first-child a'));

		this.addListener(this.$cancelBtn, 'click', function(e) {
			e.preventDefault();
			this.reset();
			this.hide();
		});
		this.addListener(this.$saveBtn, 'click', function(ev) {
			ev.preventDefault();
			if (!$(ev.target).hasClass('disabled')) {
				this.save();
			}
		});
		this.base($form, settings);

		//init new variant modal
		/*
{% set jsSettings = {
    id: id|namespaceInputId,
    name: name|namespaceInputName,
    elementType: elementType,
    sources: sources,
    criteria: criteria,
    sourceElementId: sourceElementId,
    viewMode: viewMode,
    limit: limit ?? null,
    showSiteMenu: showSiteMenu ?? false,
    modalStorageKey: storageKey,
    sortable: sortable
} %}

		new Craft.VariantElementSelectInput({
			id: 'addProduct',
			name: 'variant',
			elementType: 'Commerce_Variant',
			baseElementType: 'Commerce_Product',
			sources: null,
			sourceElementId: null,
			viewMode: 'list',
			limit: 1,
			modalStorageKey: null
		});
		new Craft.BaseElementSelectInput({"id":"fields-product","name":"fields[product]","elementType":"craft\\commerce\\elements\\Variant","sources":"*","criteria":{"enabledForSite":null,"siteId":1},"sourceElementId":"28","viewMode":"list","limit":"","showSiteMenu":false,"modalStorageKey":"field.2","sortable":true});
		*/
	},
	onSelectStatus: function(status) {
		this.deselectStatus();
		//console.log(status);
		this.$selectedStatus = $(status);
		var type = this.$selectedStatus.data('id');

		this.$selectedStatus.addClass('sel');

		var newHtml = '<span>' + Craft.uppercaseFirst($(status).data('name')) + '</span>';
		this.$statusSelect.html(newHtml);

		this.$saveBtn.removeClass('disabled');

		this.$value.detach();
		this.$code.detach();
		this.$qty.detach();
		this.$products.detach();

		if (type == 'manual') {
			this.$value.appendTo(this.$inputs);
		}
		if (type == 'code') {
			this.$code.appendTo(this.$inputs);
		}
		if (type == 'qty') {
			this.$qty.appendTo(this.$inputs);
		}
		if (type == 'add') {
			this.$products.appendTo(this.$inputs);
		}

		this.updateSizeAndPosition();
	},

	deselectStatus: function() {
		if (this.$selectedStatus) {
			this.$selectedStatus.removeClass('sel');
		}
	},

	reset: function() {
		//clear values
		this.$comments.find('textarea[name="comments"]').val('');
		this.$value.find('input[name="value"]').val('');
		this.$code.find('input[name="data[coupon]"]').val('');
		//clear any errors
		this.clearErrors();
		//set back to first note type
		this.onSelectStatus(this.$list.find('li:first-child a'));
	},

	clearErrors: function() {
		this.$inputs.find('.field.has-errors').each(function() {
			var $this = $(this);
			$this.removeClass('has-errors');
			$this.find('ul.errors').remove();
			$this.find('.input').removeClass('errors');
		});
	},

	save: function() {
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
			data.data['code'] = this.$code.find('input[name="data[coupon]"]').val();
		}
		/*
		if (this.$manualDiscount.is(':visible')) {
			data['amount'] = this.$manualDiscount.find('input[name="amount"]').val();
		}
		if (this.$discountCode.is(':visible')) {
			data['code'] = this.$discountCode.find('input[name="code"]').val();
		}
		if (this.$qtyAdjustment.is(':visible')) {
			data['qty'] = {};
			this.$qtyAdjustment.find('input').each(function() {
				data['qty'][this.name.replace('qty[', '').replace(']', '')] = this.value;
			});
			//data['qty'] = this.$qtyAdjustment.find('input[name="qty"]').val();
		}
		if (this.$addProduct.is(':visible')) {
			data['variant'] = this.$addProduct.find('input[name="variant[]"]').val();
			data['qty'] = this.$addProduct.find('input[name="qty"]').val();
		}*/

		Craft.postActionRequest('order-notes/notes/save', data, function(response) {
			if (response.success) {
				document.location.reload(true);
			} else {
				Craft.cp.displayError("Couldn't save note");
				self.clearErrors();
				$.each(response.errors, function(key) {
					console.log(this, key);
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
