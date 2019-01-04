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
	$message: null,
	$error: null,
	$updateBtn: null,
	$statusMenuBtn: null,
	$cancelBtn: null,
	init: function(settings) {
		this.id = Math.floor(Math.random() * 1000000000);

		this.setSettings(settings, {
			resizable: false
		});

		var self = this;

		var $form = $('<form class="modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
		var $body = $('<div class="body"></div>').appendTo($form);
		this.$inputs = $('<div class="content">' + '<h2 class="first">' + Craft.t('commerce', 'Order Note') + '</h2>' + '</div>').appendTo($body);

		// Build menu button
		this.$statusSelect = $('<a class="btn menubtn" href="#">Choose type...</a>').appendTo(this.$inputs);
		var $menu = $('<div class="menu"/>').appendTo(this.$inputs);
		var $list = $('<ul class="padded"/>').appendTo($menu);
		var classes = '';
		// for (var i = 0; i < this.settings.noteTypes.length; i++) {
		// 		classes = '';
		// 	$('<li><a data-id="' + this.settings.noteTypes[i].id + '" data-color="' + orderStatuses[i].color + '" data-name="' + orderStatuses[i].name + '" class="' + classes + '"><span class="status ' + orderStatuses[i].color + '"></span>' + orderStatuses[i].name + '</a></li>').appendTo($list);
		// }
		$.each(this.settings.types, function(key, value) {
			$('<li><a data-id="' + key + '" data-name="' + value + '">' + value + '</a></li>').appendTo($list);
		});

		this.$selectedStatus = $('.sel', $list);

		// Build message input
		this.$comment = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('commerce', 'Comment') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The reason for the note') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<textarea class="text fullwidth" rows="2" cols="50" name="comment"></textarea>' + '</div>' + '</div>').appendTo(this.$inputs);
		this.$amount = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('commerce', 'Amount') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The amount to add to the order, can be negative!') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="amount">' + '</div>' + '</div>');
		this.$code = $('<div class="field">' + '<div class="heading">' + '<label>' + Craft.t('commerce', 'Coupon Code') + '</label>' + '<div class="instructions"><p>' + Craft.t('commerce', 'The coupon code to add to the order') + '.</p>' + '</div>' + '</div>' + '<div class="input ltr">' + '<input type="text" class="text fullwidth" name="coupon">' + '</div>' + '</div>');
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
		this.$updateBtn = $('<input type="button" class="btn submit" value="' + Craft.t('commerce', 'Update') + '"/>').appendTo($mainBtnGroup);

		this.$updateBtn.addClass('disabled');

		// Listeners and
		this.$statusMenuBtn = new Garnish.MenuBtn(this.$statusSelect, {
			onOptionSelect: $.proxy(this, 'onSelectStatus')
		});
		this.onSelectStatus($list.find('li:first-child a'));

		this.addListener(this.$cancelBtn, 'click', 'hide');
		this.addListener(this.$updateBtn, 'click', function(ev) {
			ev.preventDefault();
			if (!$(ev.target).hasClass('disabled')) {
				this.updateStatus();
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

		this.$updateBtn.removeClass('disabled');

		this.$amount.detach();
		this.$code.detach();
		this.$qty.detach();
		this.$products.detach();

		if (type == 'manual') {
			this.$amount.appendTo(this.$inputs);
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

	updateStatus: function() {
		var data = {
			orderStatusId: this.currentStatus.id,
			message: this.$message.find('textarea[name="message"]').val(),
			color: this.currentStatus.color,
			name: this.currentStatus.name
		};

		this.settings.onSubmit(data);
	},
	defaults: {
		onSubmit: $.noop
	}
});
