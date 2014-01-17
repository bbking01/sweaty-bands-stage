if (typeof(Itoris) == 'undefined') {
	Itoris = {};
}
//using prototype.js  DCS:
Itoris.QuickBuy = Class.create();


Itoris.QuickBuy.prototype = {
    products : [],
	loadedProductsConfig : [],
    totalRows : 0,
	totalPages : 1,
	currentPage : 1,
    currencySymbol : '&#36;', //code of the symbol $
	grid : null,
	firstRowNum : 0,
	limit : 5,
	limits : [5, 10, 20, 50],
	order : 'asc',
	orderBy : 'product_name',
	translates : {},
	limitDropdownCreated : false,
	hoverDropdown : false,
	selectedProducts : [],
	cartList : {},
	responder : null,
	responderRegistered : false,
	activeRequestCount : 0,
	alertAjaxSuccess : false,
	alertAjaxSuccessText : '',
	alertValidateFail : false,
	validationError : false,
	productCount : 0,
	urls : {},
    initialize : function(urls, translates, products, selectedProducts, config) {
		this.urls = urls;
		this.config = config;
		if (this.isCacheEngine()) {
			this.createCache();
		}
		this.currencySymbol = config.currency;
		this.currencyRate = parseFloat(config.currency_rate);
		this.translates = translates;
		this.cartList = new Itoris.QuickBuy.CartList(this, products);
		this.grid = $$('#qb .grid .content')[0];
		if (selectedProducts) {
			//this.selectedProducts = selectedProducts.evalJSON();
		}
		var qb = this;
		Event.observe($$('#qb .search-block .button-search')[0], 'click', function() {
	
			qb.allowDefaultProductsAction = false;
			qb.setProducts(true);
		});
		Event.observe($$('#qb .search-block .text')[0], 'keypress', function(event) {
		
			if (event.keyCode == 13) {
				qb.allowDefaultProductsAction = false;
				qb.setProducts(true);
						
			}
		});
	
	/*
	 * DCS:
	 * add listbox with categories
	 * Event.observe(document, eventName, handler)
	 * Use the double dollar sign to get elements by css.
	 * dblclick
	 *  */
	
		var qb2 = this;
	      Event.observe($$('#qb .search-block .select')[0], 'click', function(event) {
		//Event.observe($$('#qb .search-block .select')[0], 'dblclick', function(event) {
		//clear '#qb .search-block .text'	
		/*
		 * $$('#qb .search-block .text')[0], 'keypress', function(event)
		 * 
		 */	
		$$('#qb .search-block .text')[0].clear();	
		qb2.allowDefaultProductsAction = false;
		qb2.setProducts(true);
	
		});		
/*
 * Here’s a simple code that lets you click everywhere on the page and, 
 * if you click directly on paragraphs,hides them.
 * DCS:
 

Event.observe(document.body, 'click', function(event) {
var element = Event.element(event);
if ('P' == element.tagName)
element.hide();
});
 */

	 	

		this.addSortAction('qb-grid-product', 'product_name');
		this.addSortAction('qb-grid-category', 'category_name');
		this.addSortAction('qb-grid-sku', 'sku');
		this.addSortAction('qb-grid-price', 'price');
		this.createLoader();
		$('qb-search').hide();
		if (this.config.default_products.length) {
			this.addDefaultProducts();
		}
		setInterval(this.saveInSession.bind(this), 300000);
	},
	isCacheEngine : function() {
		return this.config.search_engine == 'cache';
	},
	addSortAction : function(elementId, orderBy) {
		Event.observe($(elementId), 'click', this.sort.bind(this, elementId, orderBy));
	},
	sort : function(elementId, orderBy) {
		if (this.orderBy != orderBy) {
			this.orderBy = orderBy;
			this.order = 'asc';
		} else {
			this.changeOrder();
		}
		this.changeOrderImage(this.order);
		var orderImage = $('qb-order-image');
		$$('#' + elementId + ' .rborder')[0].insert({'after' : orderImage});
		this.setProducts();
	},
	changeOrder : function() {
		if (this.order == 'asc') {
			this.order = 'desc';
		} else {
			this.order = 'asc';
		}
	},
	changeOrderImage : function(order) {
		var orderImage = $('qb-order-image');
		if (order == 'asc') {
			if (orderImage.hasClassName('order-desc')) {
				orderImage.removeClassName('order-desc');
			}
			orderImage.addClassName('order-asc');
		} else {
			if (orderImage.hasClassName('order-asc')) {
				orderImage.removeClassName('order-asc');
			}
			orderImage.addClassName('order-desc');
		}
	},
	setProducts : function(firstPage) {
		var text = $$('#qb .search-block .text')[0].value;
		
		if (text || this.allowDefaultProductsAction) {
			if (firstPage) {
				this.firstRowNum = 0;
			}
			this.getData(text, this.limit, this.firstRowNum, this.order, this.orderBy);
		}
/*****************************
 * DCS:
 * add if 'select'
 ****************************/     
        else{   	
 		var select  = $$('#qb .search-block .select')[0].value;
 	//	alert ("select: "+select);
 		var selectlabel  = $$('#qb .search-block .select')[0].label;
 	//	alert ("select label: "+selectlabel); 		
		if (select || this.allowDefaultProductsAction) {
			if (firstPage) {
				this.firstRowNum = 0;
			}
			this.getData(select, this.limit, this.firstRowNum, this.order, this.orderBy);
		} 	
	}
/*****************************/
		
	},
	getData : function(text, limit, limitFrom, order, orderBy) {
		var qb = this;
		var selectedProducts = '';
		qb.showLoader();
		var url = this.urls.loadProducts;

		var params = {
			't' : text,
			'selectedProducts' : selectedProducts,
			'limit' : limit,
			'limitFrom' : limitFrom,
			'order' : order,
			'orderBy' : orderBy,
			rndm : Math.random()
		};
		if (this.isCacheEngine() && !this.allowDefaultProductsAction) {
			params.sid = this.config.sid;
			params.base_path = this.config.base_path;
			params.store_id = this.config.store_id;
			params.callback = 'qbForm.loadProductAfterJsonp';
			this.sendJsonp(params, this.urls.loadProducts);
			return;
		}
		new Ajax.Request(this.allowDefaultProductsAction ? this.urls.loadDefaultProducts : this.urls.loadProducts, {
  			method : 'post',
			parameters : params,
  			onComplete: function(transport) {
				var resObj = transport.responseText.evalJSON();
				if (resObj.redirect) {
					setLocation(resObj.redirect);
				} else {
					qb.hideLoader();
					qb.setData(resObj);
				}
			}
		});
	},
	loadProductAfterJsonp : function(data) {
		if (data.error) {
			console.log(data.error);
		} else {
			if (data.redirect) {
				setLocation(data.redirect);
			} else {
				this.hideLoader();
				this.setData(data);
			}
		}
	},
	createCache : function() {
		var	parameters = {
			type : 'cache',
			rndm : Math.random(),
			sid : this.config.sid,
			base_path : this.config.base_path,
			store_id : this.config.store_id
		};
		this.sendJsonp(parameters, this.urls.loadProducts)
	},
	sendJsonp : function(params, url) {
		url += (url.include('?') ? '&' : '?') + Hash.toQueryString(params);
		JSONP_QB(url, function () {});
	},
	setData : function(data) {
		var message = $('qb-no-products');
		if (data && data.products.length) {
			this.products = data.products;
			this.totalRows = data.totalRows;
			this.firstRowNum = Number(data.limitFrom);
			this.limit = data.limit;
			this.order = data.order;
			this.orderBy = data.orderBy;
			this.addProducts();
			message.update();
		} else {
			message.update(this.getText('noProducts'));
			$('qb-search').hide();
		}
	},
	loadProductConfig : function(id) {
		if (!this.loadedProductsConfig[id] || !this.loadedProductsConfig[id].isLoading) {
			var qb = this;
			this.loadedProductsConfig[id] = {isLoading : true, loaded : false};
			new Ajax.Request(this.urls.loadProductConfig, {
				method : 'get',
				parameters : {
					'id' : id
				},
				onComplete: function(transport) {
					var res = transport.responseText.evalJSON();
					if (res.error) {
						qb.loadedProductsConfig[id] = {isLoading : false};
						console.log(res.error);
					} else {
						qb.loadedProductsConfig[id] = {isLoading : false, loaded : true, config : res.product};
					}
				}
			});
		}
	},
	addProducts : function() {
		$('qb-search').show();
		this.grid.update();
		var rowsCount = this.products.length;
		this.totalPages = Math.ceil(this.totalRows / this.limit);
		this.currentPage = Math.ceil((this.firstRowNum + 1) / this.limit);
		for (var i = 0; i < rowsCount; i++) {
			this.addRow(this.products[i], (i + 1));
		}
		$$('#qb .paging .count')[0].update(this.getText('items') + ' ' + (this.firstRowNum + 1) + ' ' + this.getText('to')
								   + ' ' + (this.firstRowNum + rowsCount) + ' ' + this.getText('of') + ' '
								   + this.totalRows + ' ' + this.getText('total')
		);
		this.addPager();
		if (!this.limitDropdownCreated) {
			this.addLimits();
		}
	},
	addDefaultProducts : function() {
		this.products = this.config.default_products;
		this.totalRows = this.config.default_products_total;
		$('qb-no-products').update();
		this.allowDefaultProductsAction = true;
		for (var i = 0; i < this.products.length; i++) {
			this.loadedProductsConfig[this.products[i].entity_id] = {isLoading : false, loaded : true, config : this.products[i]};
		}
		this.addProducts();
	},
	addRow : function(data, num) {
		var row = document.createElement('tr');
		Element.extend(row);
		if (num % 2) {
			row.addClassName('odd');
		} else {
			row.addClassName('even');
		}
		row.appendChild(this.createSelectCell(num - 1, data));
		row.appendChild(this.createProductCell(data));
		row.appendChild(this.createDivCell(data.category));
		row.appendChild(this.createDivCell(data.sku));
		var priceText = this.preparePrice(data)
		if (data.out_of_stock) {
			priceText += '<br/><span class="green">'+ this.translates.outOfStock +'</span>';
		}
		row.appendChild(this.createDivCell(priceText));
		row.appendChild(this.createImageCell(data.image_url, data.product));
		this.grid.appendChild(row);
	},
	convertPrice : function(price) {
		return this.toFixed(parseNumber(parseFloat(price) * this.currencyRate));
	},
	preparePrice : function(obj) {
		var price = 0;
		if (obj.price && parseNumber(obj.price)) {
			price = parseNumber(obj.price);
			price = price.toFixed(2);
		}
		var minPrice = 0;
		if (obj.min_price) {
			minPrice = parseNumber(obj.min_price).toFixed(2);
		}
		var maxPrice = 0;
		if (obj.max_price) {
			maxPrice = parseNumber(obj.max_price).toFixed(2);
		}
		var tierPrice = 0;
		if (obj.tier_price) {
			tierPrice = parseNumber(obj.tier_price).toFixed(2);
		}
		var outputPrice = '';
		if (!price) {
			outputPrice += this.getText('priceFrom') + ': ' + this.currencySymbol + this.convertPrice(minPrice) + '<br/>'
					 + (maxPrice ? this.getText('priceTo') + ': ' + this.currencySymbol + this.convertPrice(maxPrice) : '');
		} else {
			outputPrice += this.currencySymbol + this.convertPrice(price);
		}
		if (parseNumber(tierPrice)) {
			outputPrice += '<br/>' + this.getText('asLowAs') + ': <br/>'
					       + this.currencySymbol + this.convertPrice(tierPrice);
		}
		return outputPrice;
	},
	createSelectCell : function(productNum, product) {
		var cell = this.createCell();
		cell.addClassName('gb-grid-th-first');
		var div = this.createDiv();
		if (!product.out_of_stock) {
			var icon = document.createElement('div');
			Element.extend(icon);
			icon.addClassName('icon-add-to-list');
			Event.observe(icon, 'click', this.addToList.bind(this, cell, productNum));
			div.appendChild(icon);
		}
		cell.appendChild(div);
		return cell;
	},
	createDivCell : function(text) {
		var cell = this.createCell();
		var div = this.createDiv();
		var textDiv = this.createDiv();
		textDiv.addClassName('text');
		textDiv.update(text);
		div.appendChild(textDiv);
		cell.appendChild(div);
		return cell;
	},
	createImageCell : function(url, title) {
		var cell = this.createCell();
		cell.addClassName('gb-grid-th-last')
		var div = this.createDiv();
		div.appendChild(this.createImage(url, title));
		cell.appendChild(div);
		return cell;
	},
	createProductCell : function(config) {
		var cell = this.createCell();
		var div = this.createDiv();
		var textDiv = this.createDiv();
		textDiv.addClassName('text');
		textDiv.update(config.product);
		if (parseInt(config.visibility) != 1) {
			var productLink = document.createElement('a');
			Element.extend(productLink);
			productLink.href = config.product_url;
			productLink.target = '__blank';
			productLink.update('(' + this.getText('viewDetails') + ')');
			textDiv.appendChild(productLink);
		}
		div.appendChild(textDiv);
		if (config.min_qty > 1) {
			var messageMinQty = this.createElm('span');
			messageMinQty.update(this.translates.minQtyInCart.replace('%d', config.min_qty) + '<br/>');
			messageMinQty.addClassName('qb-notify-message');
			div.appendChild(messageMinQty);
		}
		var incrementQty = this.getIncrementQty(config);
		if (incrementQty > 1) {
			var messageIncQty = this.createElm('span');
			messageIncQty.update(this.translates.availableIncrements.replace('%d', incrementQty));
			messageIncQty.addClassName('qb-notify-message');
			div.appendChild(messageIncQty);
		}
		cell.appendChild(div);
		return cell;
	},
	getIncrementQty : function(config) {
		if (config.use_config_enable_qty_inc) {
			if (this.config.global_increment) {
				return this.config.global_increment_qty;
			}
		} else if (config.enable_qty_increments) {
			if (config.use_config_qty_increments) {
				return this.config.global_increment_qty;
			}
			return config.qty_increments;
		}

		return 0;
	},
	createCell : function() {
		var cell = document.createElement('th');
		Element.extend(cell);
		return cell;
	},
	createDiv : function() {
		return this.createElm('div');
	},
	createElm : function(type) {
		var elm = document.createElement(type);
		Element.extend(elm);
		return elm;
	},
	createImage : function(url, title) {
		var img = document.createElement('img');
		Element.extend(img);
		img.src = url;
		img.alt = title;
		img.title = title;
		return img;
	},
	addToList : function(cell, productNum) {
		var row = cell.up();
		var cols = row.childElements();
		var widths = [];
		for (var i = 0; i < cols.length; i++) {
			cols[i].width = cols[i].getWidth() + 'px';
		}
		var offset = row.positionedOffset();
		var parentElement = row.up();
		var rowElm = row.cloneNode(true);
		parentElement.appendChild(rowElm);
		rowElm.absolutize();
		rowElm.setStyle({'top' : offset.top + 'px', 'left' : offset.left + 'px'});
		var topDestination = this.grid.positionedOffset().top - 100;
		new Effect.Parallel([
			new Effect.Morph(rowElm, {
				style: 'top:' + topDestination + 'px;'//, // CSS Properties
				//duration: 0.8 // Core Effect properties
			}),
			new Effect.Opacity(rowElm, { sync: false, from: 1, to: 0 })
		], {
		  duration: 0.5
		});
		var product = this.products[productNum];
		//this.selectedProducts.push(product.product_id);
		setTimeout(this.cartList.addProduct.bind(this.cartList, product, null), 500);
		setTimeout(function() {rowElm.remove(); }, 500);
		//this.setProducts(false);
	},
	addPager : function() {
		var pager = $$('#qb .paging .pager')[0];
		pager.update();
		var qb = this;
		var w = 0;
		if (this.totalPages != 1) {
			var link;
			if (this.currentPage != this.totalPages) {
				link = document.createElement('div');
				Element.extend(link);
				link.addClassName('page-next');
				Event.observe(link, 'click', function() {
					qb.firstRowNum = qb.limit * (qb.currentPage);
					qb.setProducts();
				});
				pager.appendChild(link);
				w += link.getWidth();
			}
			link = document.createElement('span');
			Element.extend(link);
			link.update(this.getText('page') + ' ');
			pager.appendChild(link);
			w += link.getWidth();
			for (var i = 1; i <= this.totalPages; i++) {
				link = document.createElement('span');
				Element.extend(link);
				link.update(' ' + i + ' ');
				if (i != qb.currentPage) {
					link.addClassName('qb-page-link');
					(	function(num) {
							return Event.observe(link, 'click', function() {
								qb.firstRowNum = qb.limit * (num - 1);
								qb.setProducts();
							});
						}
					)(i);
				} else {
					link.addClassName('qb-page-link-active');
				}
				pager.appendChild(link);
				w += link.getWidth();
			}
		}
		w += 10;
		var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
		if (ie7) {
			pager.setStyle({'width': w + 'px'});
		}
	},
	addLimits : function() {
		var options = $$('#qb .paging .limit .dropdown .options')[0];
		var option;
		var qb = this;
		var valueDiv = $$('#qb .paging .limit .dropdown .value')[0];
		for (var i = 0; i < this.limits.length; i++) {
			option = document.createElement('div');
			Element.extend(option);
			option.addClassName('option');
			option.update(this.limits[i]);
			( function(num) {
				return Event.observe(option, 'click',  function() {
							var value = qb.limits[num];
							var lowerValue = num ? qb.limits[num] : value;
							qb.limit = value;
							qb.firstRowNum = 0;
							valueDiv.update(value);
							qb.setProducts();
						});
			 }
			)(i);
			Event.observe(option, 'mouseover', function(){
				qb.hoverDropdown = true;
			});
			Event.observe(option, 'mouseout', function(){
				qb.hoverDropdown = false;
			});
			options.appendChild(option);
		}
		qb.showDropdown();
		Event.observe($$('#qb .paging .limit .dropdown')[0], 'click', function() {
			qb.showDropdown();
		});
		Event.observe(options, 'mouseout', function(){
			setTimeout(qb.hideDropdown.bind(qb), 1000);
		});
		Event.observe($$('#qb .paging .limit .dropdown')[0], 'mouseout', function(){
			setTimeout(qb.hideDropdown.bind(qb), 1000);
		});
		qb.limitDropdownCreated = true;
	},
	showDropdown : function() {
		var dropdown = $$('#qb .paging .limit .dropdown .options-box')[0];
		if (dropdown.visible()) {
			dropdown.hide();
		} else {
			dropdown.show();
		}
	},
	hideDropdown : function() {
		if (!this.hoverDropdown) {
			$$('#qb .paging .limit .dropdown .options-box')[0].hide();
		}
	},
	createLoader : function() {
		var loaderBack = document.createElement('div');
		Element.extend(loaderBack);
		loaderBack.addClassName('qb-loader-back');
		var loaderImage = document.createElement('div');
		Element.extend(loaderImage);
		loaderImage.addClassName('qb-loader-image');
		loaderBack.appendChild(loaderImage);
		var loaderText = this.createDiv();
		loaderText.addClassName('qb-loader-title');
		var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
		if (ie7) {
			loaderText.setStyle({'marginLeft' : '-50%'});
		}
		loaderText.update(this.getText('loading'));
		loaderBack.appendChild(loaderText);
		var body = document.getElementsByTagName('body')[0];
		Element.extend(body);
		body.appendChild(loaderBack);
		this.hideLoader();
	},
	showLoader : function() {
		this.getLoader().show();
	},
	hideLoader : function() {
		this.getLoader().hide();
	},
	getLoader : function() {
		return $$('.qb-loader-back')[0];
	},
	getText : function(text) {
		return this.translates[text];
	},
	formatPrice : function(price, qty, taxClassId, inclTax, asNumber) {
		price = parseNumber(this.toFixed(price || 0, 2));
		qty = parseNumber(qty || 1);
		if (this.config.price_includes_tax) {
			var taxRate = this.getTaxRate(taxClassId);
			var taxValue = inclTax ? 0 : -1 * parseNumber(this.toFixed((price / (1 + taxRate / 100)) * taxRate / 100, 2)) * qty;
		} else {
			var taxValue = inclTax ? parseNumber(this.toFixed(price * this.getTaxRate(taxClassId) / 100, 2)) * qty : 0;
		}
		price = price * qty + taxValue;
		price = this.convertPrice(price);
		return asNumber ? parseNumber(price) : this.currencySymbol + price;
	},
	toFixed : function(value, decimalPoints) {
		decimalPoints = decimalPoints || 2;
		var correction = Math.pow(10, decimalPoints);
		return (Math.round(parseNumber(value) * correction) / correction).toFixed(decimalPoints)
	},
	getTaxRate : function(taxClassId) {
		if (typeof this.config.taxes['value_' + taxClassId] != 'undefined') {
			return parseFloat(this.config.taxes['value_' + taxClassId]);
		}
		return 0;
	},
	increasePriceByPercentage : function(price, percent) {
		price = parseNumber(price);
		return price + price * parseNumber(percent) / 100;
	},
	getPriceStr : function(priceStr, qty, taxClassId) {
		if (this.config.display_price_incl_tax) {
			return this.formatPrice(priceStr, qty, taxClassId, true);
		}
		var result = this.formatPrice(priceStr, qty, taxClassId, false);
		if (this.config.display_both_price) {
			result += '<span>(' + this.translates.exclTax + ')</span>';
			result += this.formatPrice(priceStr, qty, taxClassId, true);
			result += '<span>(' + this.translates.inclTax + ')</span>';
		}

		return result;
	},
	canShowInclTaxPrice : function() {
		return this.config.display_price_incl_tax || this.config.display_both_price;
	},
	saveInSession : function() {
		var qb = this;
		var preparedParams = {};
		var i = 0;
		if (this.cartList.products.length) {
			this.cartList.products.each(function(product) {
				if (product && product.isLoaded) {
					preparedParams['products[' + i + '][qty]'] = product.qty;
					preparedParams['products[' + i + '][groupedProducts]'] = $A(product.groupedProducts).toJSON();
					preparedParams['products[' + i + '][links]'] = $A(product.links).toJSON();
					preparedParams['products[' + i + '][options]'] = Object.toJSON(qb.arrayToObject(product.options));
					preparedParams['products[' + i + '][optionsPrices]'] = Object.toJSON(qb.arrayToObject(product.optionsPrices));
					preparedParams['products[' + i + '][bundleOptions]'] = Object.toJSON(qb.arrayToObject(product.bundleOptions));
					preparedParams['products[' + i + '][bundleOptionsQty]'] = Object.toJSON(qb.arrayToObject(product.bundleOptionsQty));
					preparedParams['products[' + i + '][bundleOptionsPrices]'] = Object.toJSON(qb.arrayToObject(product.bundleOptionsPrices));
					preparedParams['products[' + i + '][superAttributes]'] = Object.toJSON(qb.arrayToObject(product.superAttributes));
					preparedParams['products[' + i + '][superAttributesPrices]'] = Object.toJSON(qb.arrayToObject(product.superAttributesPrices));
					if (Prototype.Version.include('1.7')) {
						preparedParams['products[' + i + '][config]'] = qb.objToJSON(product.config);
					} else {
						preparedParams['products[' + i + '][config]'] = Object.toJSON(product.config);
					}
					//preparedParams['products[' + i + '][files]'] = $A(product.files).toJSON();
					preparedParams['products[' + i + '][error]'] = product.error;
					preparedParams['products[' + i + '][finalPrice]'] = product.finalPrice;
					i++;
				}
			});
			preparedParams['selected_products'] = $A(this.selectedProducts).toJSON();
			preparedParams['type'] = 'save_in_session';
			new Ajax.Request(qb.urls.saveInSession, {
				method : 'post',
				parameters : preparedParams
			});
		}
	},
	arrayToObject : function(input) {
		var output = {};
		var qb = this;
		input.forEach(function(value, key){
			if (value instanceof Array) {
				output[key] = qb.arrayToObject(value);
			} else {
				output[key] = value;
			}
		});
		return $H(output);
	},
	objToJSON : function(object) {
    var type = typeof object;
    switch (type) {
      case 'undefined':
      case 'function':
      case 'unknown': return;
      case 'boolean': return object.toString();
    }

    if (object === null) return 'null';
	if (object instanceof String) {
		return Object.toJSON(object);
	}
	if (object instanceof Number) {
		return object;
	}
    var results = [];
		for (var property in object) {
		  if (object[property] instanceof Array) {
			  var arrResults = [];
		  	for (var i = 0; i < object[property].length; i++) {
				arrResults.push(this.objToJSON(object[property][i]));
			}
			  results.push('"' + property + '"' + ':' + '[' + arrResults.join(', ') + ']');
		} else {
			var value = Object.toJSON(object[property]);
		  	if (!Object.isUndefined(value))
				results.push('"' + property + '"' + ': ' + value);
			}
		}

    return '{' + results.join(', ') + '}';
  }
}

Itoris.QuickBuy.Product = Class.create();
Itoris.QuickBuy.Product.prototype = {
	quickbuy : null,
	initialize : function(config, block, arrow, quickbuy, category, price, preSelected) {
		this.quickbuy = quickbuy;
		if (this.quickbuy.config.search_engine == 'cache' && !preSelected && (!this.quickbuy.loadedProductsConfig[config.product_id] || this.quickbuy.loadedProductsConfig[config.product_id].isLoading)) {
			this.quickbuy.loadProductConfig(config.product_id);
			if (!this.ex) {
				this.isLoaded = false;
				this.ex = new PeriodicalExecuter(this.initialize.bind(this, config, block, arrow, quickbuy, category, price, preSelected), 1);
				var mask = document.createElement('div');
				Element.extend(mask);
				mask.addClassName('quickbuy-product-mask');
				block.appendChild(mask);
			}
			return;
		}
		this.isLoaded = true;
		if (this.quickbuy.config.search_engine == 'cache') {
			if (this.ex) {
				this.ex.stop();
			}
			if (this.quickbuy.loadedProductsConfig[config.product_id]) {
				for (var key in this.quickbuy.loadedProductsConfig[config.product_id].config) {
					config[key] = this.quickbuy.loadedProductsConfig[config.product_id].config[key];
				}
			}
			if (block.select('.quickbuy-product-mask').length) {
				block.select('.quickbuy-product-mask')[0].remove();
			}
		}
		this.optionsBlock = null;
		this.optionHeight = 0;
		this.defaultHeight = 20;
		this.zIndex = 1000;
		this.taxClassId = config.tax_class_id;
		this.qty = preSelected ? parseInt(preSelected.qty) : 1;
		this.groupedProducts = preSelected ? preSelected.groupedProducts.evalJSON() : [];
		if (preSelected) {
			for (var i = 0; i < this.groupedProducts.length; i++) {
				this.groupedProducts[i].tier_prices = this.groupedProducts[i].tier_prices.evalJSON();
			}
		}
		this.finalPriceBlock = price;
		this.options = [];
		this.optionsPrices = [];
		this.bundleOptions = [];
		this.bundleOptionsQty = [];
		this.bundleOptionsPrices = [];
		this.superAttributes = [];
		this.superAttributesPrices = [];
		this.links = preSelected ? preSelected.links.evalJSON() : [];
		if (!parseFloat(config.price)) {
			if (config.type == 'bundle') {
				this.finalPrice = 0;
			} else {
				this.finalPrice = parseNumber(config.min_price).toFixed(2);
			}
		} else {
			this.finalPrice = parseNumber(config.price, true).toFixed(2);
		}
		if (preSelected) {
			this.fromObjectToArray(preSelected.bundleOptions.evalJSON(), this.bundleOptions);
			this.fromObjectToArray(preSelected.bundleOptionsQty.evalJSON(), this.bundleOptionsQty);
			this.fromObjectToArray(preSelected.bundleOptionsPrices.evalJSON(), this.bundleOptionsPrices);
			this.fromObjectToArray(preSelected.superAttributes.evalJSON(), this.superAttributes);
			this.fromObjectToArray(preSelected.superAttributesPrices.evalJSON(), this.superAttributesPrices);
			this.fromObjectToArray(preSelected.options.evalJSON(), this.options);
			this.fromObjectToArray(preSelected.optionsPrices.evalJSON(), this.optionsPrices);
			this.finalPrice = parseFloat(preSelected.finalPrice);
		}
		this.config = config;
		this.block = block;
		this.arrow = arrow;
		this.productCount = ++this.quickbuy.productCount;
		this.category = category;
		this.files = /*preSelected ? preSelected.files.evalJSON() :*/ [];
		this.error = false;
		if (config.type != 'grouped') {
			this.finalPriceBlock.update(this.quickbuy.getPriceStr(this.finalPrice, this.qty, config.tax_class_id));
		}
		this.createOptions();
	},
	fromObjectToArray : function(inputObject, outputArray) {
		for (var key in inputObject) {
			if (typeof(inputObject[key]) == 'object') {
				outputArray[key] = [];
				this.fromObjectToArray(inputObject[key], outputArray[key]);
			} else {
				outputArray[key] = inputObject[key];
			}
		}
	},
	createOptions : function() {
		var options = document.createElement('div');
		Element.extend(options);
		options.addClassName('options');
		this.block.appendChild(options);
		var img = this.quickbuy.createImage(this.config.image_url, this.config.product);
		img.addClassName('preview');
		options.appendChild(img);
		var config = document.createElement('div');
		Element.extend(config);
		options.appendChild(config);
		if (this.config.super_group) {
			this.addOptionToBlock(this.createAssociatedProducts(), config, options);
		}
		if (this.config.bundle_options) {
			this.addOptionToBlock(this.createBundleOptions(), config, options);
		}
		if (this.config.super_attribute) {
			this.addOptionToBlock(this.createConfigurableOptions(), config, options);
		}
		if (this.config.links) {
			this.addOptionToBlock(this.createDownloadableOption(), config, options);
		}
		if (this.config.options) {
			this.addOptionToBlock(this.createSimpleOptions(), config, options);
		}
		if (config.childElements().length) {
			config.addClassName('config');
		}
		var height = 85;
		if ((options.getHeight() < 85)) {
			options.setStyle({height : '85px'});
		} else {
			height = options.getHeight();
		}
		this.optionHeight = height;
		options.hide();
		this.optionsBlock = options;
	},
	addOptionToBlock : function(option, block, container) {
		block.appendChild(option);
		var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
		if (ie7) {
			var height = parseInt((option.getHeight()/100) * 25);
			height += option.getHeight();
			if (container.getHeight() < height) {
				container.setStyle({'height': height + 'px'});
			}
		}
	},
	createSimpleOptions : function() {
		var block = this.quickbuy.createDiv();
		block.addClassName('type');
		if (parseInt(this.config.required_options)) {
			block.appendChild(this.createRequiredText().addClassName('required-options'));
		}
		var label = this.quickbuy.createDiv();
		label.addClassName('label');
		label.update(this.quickbuy.getText('customOptions') + ':');
		block.appendChild(label);
		var optionBlock = null;
		for (var i = 0; i < this.config.options.length; i++) {
			switch (this.config.options[i].type) {
				case 'drop_down':
				case 'radio':
					optionBlock = this.createRadioOption(this.config.options[i]);
					break;
				case 'date':
					optionBlock = this.createDateOption(this.config.options[i]);
					break;
				case 'date_time':
					optionBlock = this.createDateTimeOption(this.config.options[i]);
					break;
				case 'time':
					optionBlock = this.createTimeOption(this.config.options[i]);
					break;
				case 'field':
					optionBlock = this.createFieldOption(this.config.options[i]);
					break;
				case 'area':
					optionBlock = this.createAreaOption(this.config.options[i]);
					break;
				case 'checkbox':
					optionBlock = this.createCheckboxOption(this.config.options[i]);
					break;
				case 'multiple':
					optionBlock = this.createMultipleOption(this.config.options[i]);
					break;
				case 'file':
					optionBlock = this.createFileOption(this.config.options[i]);
					break;
				default:
					optionBlock = this.quickbuy.createDiv();
			}
			block.appendChild(optionBlock);
		}
		return block;
	},
	createRadioOption : function(option) {
		var block = this.quickbuy.createDiv();
		block.addClassName('option');
		block.update(option.title);
		if (parseInt(option.required)) {
			block.appendChild(this.createRequireOptionText());
		}
		var radio = null;
		var title = null;
		var br = null;
		var additionalPrice = null;
		var values = option.values;
		if (!parseInt(option.required)) {
			if (values[0] && values[0].id) {
				values.unshift({
					'id' : 	0,
					'price' : '0.0000',
					'title' : this.quickbuy.getText('none')
				});
			}
		}
		var name = '';
		for (var i = 0; i < values.length; i++) {
			br = document.createElement('br');
			block.appendChild(br);
			name = this.productCount + '_' + this.config.product_id + '_' + option.id;
			var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
			if (ie7) {
				radio = document.createElement('<input type="radio" name="'+ name +'" value="' + values[i].id + '"/>');
			} else {
				radio = document.createElement('input');
			}
			Element.extend(radio);
			radio.addClassName('radio');
			if (!ie7) {
				radio.type = 'radio';
				radio.name = name;
				radio.value = values[i].id;
			}
			Event.observe(radio, 'click', this.addOption.bind(this, option, i));
			if (this.options[option.id] && this.options[option.id] == values[i].id) {
				radio.checked = true;
			}
			title = document.createElement('span');
			Element.extend(title);
			title.update(values[i].title);
			additionalPrice = document.createElement('span');
			Element.extend(additionalPrice);
			additionalPrice.addClassName('add-price');
			additionalPrice.update('+' + this.quickbuy.formatPrice(values[i].price));
			block.appendChild(radio);
			block.appendChild(title);
			block.appendChild(additionalPrice);
		}
		return block;
	},
	createFieldOption : function(option) {
		var block = this.createOptionContainer(option);
		var field = document.createElement('input');
		Element.extend(field);
		field.addClassName('inputs');
		field.type = 'text';
		if (this.options[option.id]) {
			field.value = this.options[option.id];
		}
		Event.observe(field, 'change', this.addOption.bind(this, option, -1, field));
		block.appendChild(field);
		return block;
	},
	createSpanAddPrice : function(price) {
		var span = document.createElement('span');
		Element.extend(span);
		span.addClassName('add-price');
		span.update(' +' + this.quickbuy.formatPrice(price));
		return span;
	},
	createAreaOption : function(option) {
		var block = this.createOptionContainer(option);
		var field = document.createElement('textarea');
		Element.extend(field);
		field.addClassName('inputs');
		field.addClassName('textarea');
		Event.observe(field, 'change', this.addOption.bind(this, option, -1, field));
		if (this.options[option.id]) {
			field.update(this.options[option.id]);
		}
		block.appendChild(field);
		return block;
	},
	createOptionContainer : function(option) {
		var block = this.quickbuy.createDiv();
		block.addClassName('option');
		block.update(option.title);
		if (parseInt(option.required)) {
			block.appendChild(this.createRequireOptionText());
		}
		block.appendChild(this.createSpanAddPrice(option.price));
		return block;
	},
	createDateOption : function(option) {
		var block = this.createOptionContainer(option);
		block.appendChild(this.createDateBlock(option));
		return block;
	},
	createTimeOption : function(option) {
		var block = this.createOptionContainer(option);
		block.appendChild(this.createTimeBlock(option));
		return block;
	},
	createDateTimeOption : function(option) {
		var block = this.createOptionContainer(option);
		block.appendChild(this.createDateBlock(option));
		block.appendChild(document.createElement('br'));
		block.appendChild(this.createTimeBlock(option));
		return block;
	},
	createDateBlock : function (option) {
		var block = this.quickbuy.createDiv();
		var months = [];
		for (var i = 1; i <= 12; i++) {
			months.push(i);
		}
		block.appendChild(this.createDateDropdown(months, 'two-digits', option, 'month'));
		var days = [];
		for (i = 1; i <= 31; i++) {
			days.push(i);
		}
		block.appendChild(this.createDateDropdown(days, 'two-digits', option, 'day'));
		var years = [];
		var currentDate = new Date();
		years.push(currentDate.getFullYear());
		for (var i = parseInt(years[0]) + 1; i < parseInt(years[0]) + 11; i++) {
			years.push(i);
		}
		block.appendChild(this.createDateDropdown(years, 'four-digits', option, 'year'));
		return block;
	},
	createTimeBlock : function (option) {
		var block = this.quickbuy.createDiv();
		var hours = [];
		for (var i = 1; i <= 12; i++) {
			hours.push(i);
		}
		block.appendChild(this.createDateDropdown(hours, 'two-digits', option, 'hour'));
		var minutes = [];
		for (i = 1; i <= 59; i++) {
			minutes.push(i);
		}
		block.appendChild(this.createDateDropdown(minutes, 'two-digits', option, 'minute'));
		var dayParts = ['am', 'pm'];
		block.appendChild(this.createDateDropdown(dayParts, 'two-digits', option, 'day_part'));
		return block;
	},
	createDateDropdown : function(values, className, option, type) {
		var label = (this.options[option.id] && this.options[option.id][type]) ? this.options[option.id][type] : '-';
		var dropdown = this.createOptionsDropdown(label);
		dropdown.addClassName(className);
		var dropdownOptions = this.createDropdownOptions();
		dropdownOptions.addClassName(className);
		dropdownOptions.addClassName('z-select');
		var dropdownOption = null;
		if (values) {
			values.unshift('-');
		}
		for (var i = 0; i < values.length; i++) {
			dropdownOption = this.createDropdownOption(values[i]);
			Event.observe(dropdownOption, 'click', this.selectDateOption.bind(this, dropdown, values[i], option, type));
			Event.observe(dropdownOption, 'mouseover', function(){
				dropdownOptions.hoverDropdown = true;
			});
			Event.observe(dropdownOption, 'mouseout', function(){
				dropdownOptions.hoverDropdown = false;
			});
			dropdownOptions.appendChild(dropdownOption);
		}
		dropdown.appendChild(dropdownOptions);
		setTimeout(function() {dropdownOptions.absolutize()}, 100);
		dropdownOptions.hide();
		Event.observe(dropdown, 'click', this.showDropdownOptions.bind(this, dropdownOptions, dropdown));
		var qb = this;
		Event.observe(dropdownOptions, 'mouseout', function(){
			setTimeout(qb.hideDropdownOptions.bind(qb, dropdownOptions), 1000);
		});
		if (this.options[option.id] && this.options[option.id][type]) {
			dropdown.select('.selected-option-text')[0].update(this.options[option.id][type]);
		}
		return dropdown;
	},
	selectDateOption : function(dropdown, value, option, type) {
		dropdown.select('.selected-option-text')[0].update(value);
		if (!this.options[option.id]) {
			this.options[option.id] = [];
			var beforeValue = this.optionsPrices[option.id] || 0;
			var afterValue = parseFloat(option.price);
			this.optionsPrices[option.id] = afterValue;
			this.calculateFinalPriceWithOptions(beforeValue, afterValue);
		}
		if (parseInt(value) || value == 'am' || value == 'pm') {
			this.options[option.id][type] = value;
		} else {
			if (this.options[option.id][type]) {
				delete this.options[option.id][type];
			}
			var isDate = this.options[option.id]['year'] || this.options[option.id]['month'] || this.options[option.id]['day']
						|| this.options[option.id]['hour'] || this.options[option.id]['minute'] || this.options[option.id]['dat_part'];
			if (!isDate) {
				var beforePrice = this.optionsPrices[option.id] || 0;
				this.calculateFinalPriceWithOptions(beforePrice, 0);
				this.optionsPrices[option.id] = 0;
				delete this.options[option.id];
			}
		}
	},
	createFileOption : function(option) {
		var block = this.quickbuy.createDiv();
		block.addClassName('option');
		block.update(option.title);
		if (parseInt(option.required)) {
			block.appendChild(this.createRequireOptionText());
		}
		block.appendChild(this.createSpanAddPrice(option.price));
		var form = document.createElement('form');
		Element.extend(form);
		var field = document.createElement('input');
		Element.extend(field);
		field.addClassName('inputs');
		field.type = 'file';
		field.name = 'options_' + option.id + '_file';
		form.appendChild(field);
		var fieldOptionName = document.createElement('input');
		Element.extend(fieldOptionName);
		fieldOptionName.type = 'hidden';
		fieldOptionName.name = 'option';
		fieldOptionName.value = 'options_' + option.id + '_file';
		form.appendChild(fieldOptionName);
		block.appendChild(form);
		Event.observe(field, 'change', this.submitFileOption.bind(this, option, field));
		var additionalRequirementsHtml = document.createElement('span');
		Element.extend(additionalRequirementsHtml);
		var additionalRequirementsText = '';
		if (option.file_extension) {
			additionalRequirementsText += '<span>' + this.quickbuy.translates.allowedFileExtensions + ':</span><b> ' + option.file_extension + '</b><br/>';
		}
		if (parseInt(option.image_size_x)) {
			additionalRequirementsText += '<span>' + this.quickbuy.translates.maxImageWidth + ':</span><b> ' + option.image_size_x + ' px</b><br/>';
		}
		if (parseInt(option.image_size_y)) {
			additionalRequirementsText += '<span>' + this.quickbuy.translates.maxImageHeight + ':</span><b> ' + option.image_size_y + ' px</b><br/>';
		}
		additionalRequirementsHtml.update(additionalRequirementsText);
		block.appendChild(additionalRequirementsHtml);
		return block;
	},
	createCheckboxOption : function(option) {
		var block = this.quickbuy.createDiv();
		block.addClassName('option');
		block.update(option.title);
		if (parseInt(option.required)) {
			block.appendChild(this.createRequireOptionText());
		}
		var checkbox = null;
		var title = null;
		var br = null;
		var additionalPrice = null;
		for (var i = 0; i < option.values.length; i++) {
			br = document.createElement('br');
			block.appendChild(br);
			checkbox = document.createElement('input');
			Element.extend(checkbox);
			checkbox.addClassName('radio');
			checkbox.type = 'checkbox';
			checkbox.value = option.values[i].id;
			checkbox.name = this.config.product_id + '_' + option.id;
			Event.observe(checkbox, 'click', this.addMultiOption.bind(this, option, i));
			if (this.options[option.id] && this.options[option.id].indexOf(option.values[i].id) != -1) {
				checkbox.checked = true;
			}
			title = document.createElement('span');
			Element.extend(title);
			title.update(option.values[i].title);
			additionalPrice = document.createElement('span');
			Element.extend(additionalPrice);
			additionalPrice.addClassName('add-price');
			additionalPrice.update('+' + this.quickbuy.formatPrice(option.values[i].price));
			block.appendChild(checkbox);
			block.appendChild(title);
			block.appendChild(additionalPrice);
		}
		return block;
	},
	createMultipleOption : function(option) {
		var box = this.quickbuy.createDiv();
		box.update(option.title);
		if (parseInt(option.required)) {
			box.appendChild(this.createRequireOptionText());
		}
		var block = document.createElement('select');
		Element.extend(block);
		block.multiple = true;
		block.addClassName('option');
		block.addClassName('inputs');
		block.addClassName('textarea');
		var optionRow = null;
		var title = null;
		var br = null;
		var additionalPrice = null;
		for (var i = 0; i < option.values.length; i++) {
			br = document.createElement('br');
			block.appendChild(br);
			optionRow = document.createElement('option');
			Element.extend(optionRow);
			optionRow.value = option.values[i].id;
			optionRow.name = this.config.product_id + '_' + option.id;
			//Event.observe(optionRow, 'click', this.addMultiOption.bind(this, option, i));
			if (this.options[option.id] && this.options[option.id].indexOf(option.values[i].id) != -1) {
				optionRow.selected = true;
			}
			optionRow.update(option.values[i].title + ' +' + this.quickbuy.formatPrice(option.values[i].price));
			block.appendChild(optionRow);
		}
		var _o = this;
		Event.observe(block, 'change', function() { _o.addMultiOptionByMultiselect(block, option); });
		box.appendChild(block);
		return box;
	},
	addOption : function(option, valueNum, textField) {
		var beforeValue = this.optionsPrices[option.id] || 0;
		var afterValue = 0;
		var value = 0;
		if (valueNum >= 0) {
			if (valueNum >= 0) {
				value = option.values[valueNum].id;
				afterValue = parseFloat(option.values[valueNum].price);
			} else {
				value = 0;
				afterValue = 0;
			}
		} else {
			value = textField.value;
			afterValue = parseFloat(option.price);
		}
		this.options[option.id] = value;
		this.optionsPrices[option.id] = afterValue;
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	calculateFinalPriceWithOptions : function(subtractValue, addValue) {
		if (this.config.type && this.config.type == 'bundle') {
			this.finalPrice = this.getPercentTierPrice(true);
		}
		this.finalPrice -= subtractValue;
		this.finalPrice += addValue;
		if (this.config.type && this.config.type == 'bundle') {
			this.finalPrice = this.getPercentTierPrice();
		}
		this.finalPriceBlock.update(this.quickbuy.getPriceStr(this.finalPrice, this.qty, this.taxClassId));
	},
	getPercentTierPrice : function(returnBeforeValue) {
		for (var i = this.qty; i >= 1; i--) {
			if (this.config.tier_prices[i]) {
				if (returnBeforeValue) {
					return this.finalPrice / (1 - parseNumber(this.config.tier_prices[i]) / 100);
				} else {
					return this.finalPrice * (1 - parseNumber(this.config.tier_prices[i]) / 100);
				}
			}
		}

		return this.finalPrice;
	},
	submitFileOption : function(option, field) {
		//form.submit();
		this.options[option.id] = true;
		this.files[option.id] = field;
		var beforeValue = this.optionsPrices[option.id] || 0;
		var afterValue = parseFloat(option.price);
		this.optionsPrices[option.id] = afterValue;
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	addMultiOptionByMultiselect : function(elm, option) {
		for (var i = 0; i < elm.length; i++) {
			if ((elm[i].selected && (!this.options[option.id] || this.options[option.id].indexOf(elm[i].value) == -1))
				|| (!elm[i].selected && this.options[option.id] && this.options[option.id].indexOf(elm[i].value) != -1)
			) {
				var valueNum = this.getOptionValueNumByValueId(option, elm[i].value);
				this.addMultiOption(option, valueNum);
			}
		}

	},
	getOptionValueNumByValueId : function(option, valueId) {
		for (var i = 0; i < option.values.length; i++) {
			if (option.values[i] && option.values[i].id == valueId) {
				return i;
			}
		}
	},
	addMultiOption : function(option, valueNum) {
		var value = option.values[valueNum].id;
		var beforeValue = 0;
		var afterValue = 0;
		if (!this.options[option.id]) {
			this.options[option.id] = [];
			this.optionsPrices[option.id] = [];
		} else {
			if (this.optionsPrices[option.id]) {
				beforeValue = this.optionsPrices[option.id][valueNum] ? this.optionsPrices[option.id][valueNum] : 0;
			}
		}
		afterValue = parseFloat(option.values[valueNum].price);
		if (this.optionsPrices[option.id]) {
			this.optionsPrices[option.id][valueNum] = afterValue;
		}
		if (this.options[option.id].indexOf(value) > -1) {
			this.options[option.id] = this.options[option.id].without(value);
			if (this.optionsPrices[option.id]) {
				this.optionsPrices[option.id][valueNum] = 0;
			}
			afterValue = 0;
		} else {
			this.options[option.id].push(value);
		}
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	createRequiredText : function() {
		var required = this.quickbuy.createDiv();
		required.update('* ' + this.quickbuy.getText('requiredField'));
		required.addClassName('required');
		return required;
	},
	createRequireOptionText : function() {
		var required = document.createElement('span');
		Element.extend(required)
		required.update(' *');
		required.addClassName('required-text');
		return required;
	},
	createDownloadableOption : function() {
		var block = this.quickbuy.createDiv();
		block.addClassName('type');
		var label = this.quickbuy.createDiv();
		label.addClassName('label');
		label.update(this.config.links_title);
		block.appendChild(label);
		if (parseInt(this.config.required_options)) {
			block.appendChild(this.createRequiredText());
			label.appendChild(this.createRequireOptionText());
		}
		var br = null;
		for (var i = 0; i < this.config.links.length; i++) {
			block.appendChild(this.createLinkOption(this.config.links[i]));
		}
		return block;
	},
	createLinkOption : function(link) {
		var block = this.quickbuy.createDiv();
		var checkbox = document.createElement('input');
		Element.extend(checkbox);
		checkbox.addClassName('radio');
		checkbox.type = 'checkbox';
		checkbox.value = link.id;
		Event.observe(checkbox, 'click', this.addLinkOption.bind(this, link));
		if (this.links.indexOf(link.id) != -1) {
			checkbox.checked = true;
		}
		var title = document.createElement('span');
		Element.extend(title);
		title.update(link.title);
		block.appendChild(checkbox);
		block.appendChild(title);
		if (parseFloat(link.price)) {
			var additionalPrice = document.createElement('span');
			Element.extend(additionalPrice);
			additionalPrice.addClassName('add-price');
			additionalPrice.update('+' + this.quickbuy.formatPrice(link.price));
			block.appendChild(additionalPrice);
		}
		return block;
	},
	addLinkOption : function(link) {
		var beforeValue = 0;
		var afterValue = 0;
		if (this.links.indexOf(link.id) != -1) {
			this.links = this.links.without(link.id);
			beforeValue = parseFloat(link.price);
		} else {
			this.links.push(link.id);
			afterValue = parseFloat(link.price);
		}
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	createConfigurableOptions : function() {
		var block = this.quickbuy.createDiv();
		block.addClassName('type');
		var label = this.quickbuy.createDiv();
		label.addClassName('label');
		label.update(this.quickbuy.getText('options') + ':');
		block.appendChild(label);
		if (parseInt(this.config.required_options)) {
			block.appendChild(this.createRequiredText());
		}
		for (var i = 0; i < this.config.super_attribute.length; i++) {
			var mainOption = !i;
			var option = this.createConfigurableOption(this.config.super_attribute[i], mainOption, block);
			block.appendChild(option);
		}
		return block;
	},
	createConfigurableOption : function(config, isMainOption, block) {
		var option = this.quickbuy.createDiv();
		option.addClassName('option');
		option.update(config.label);
		if (parseInt(config.required)) {
			option.appendChild(this.createRequireOptionText());
		}
		var dropdown = this.createOptionsDropdown(this.quickbuy.getText('chooseOption'));
		dropdown.disableDropdown = !isMainOption;
		var dropdownOptions = this.createDropdownOptions();
		var dropdownOption = null;
		for (var i = 0; i < config.options.length; i++) {
			var optionText = config.options[i].label;
			optionText += parseFloat(config.options[i].price)
						  ? ' +' + this.quickbuy.formatPrice(config.options[i].price + '.0000')
						  : '';
			dropdownOption = this.createDropdownOption(optionText);
			if (config.options[i].products.length) {
				var _temp = [];
				for (var j = 0; j < config.options[i].products.length; j++) {
					if (!(config.options[i].products[j] instanceof Array)) {
						var _tempNumber = '';
						for (var k = 0; k < 10; k++) {
							if (config.options[i].products[j][k]) {
								_tempNumber += config.options[i].products[j][k];
							}
						}
						_temp.push(_tempNumber);
					}
				}
				config.options[i].products = _temp;
			}

			dropdownOption.products = config.options[i].products;

			Event.observe(dropdownOption, 'click', this.selectConfigurableOption.bind(this, config.options[i], config.id, isMainOption, block, dropdown));
			if (this.superAttributes[config.id] && this.superAttributes[config.id] == config.options[i].id) {
				dropdown.select('.selected-option-text')[0].update(config.options[i].label);
				dropdown.disableDropdown = false;
			}
			Event.observe(dropdownOption, 'mouseover', function(){
				dropdownOptions.hoverDropdown = true;
			});
			Event.observe(dropdownOption, 'mouseout', function(){
				dropdownOptions.hoverDropdown = false;
			});
			dropdownOptions.appendChild(dropdownOption);
		}
		dropdown.appendChild(dropdownOptions);
		dropdownOptions.hide();
		option.appendChild(dropdown);
		Event.observe(dropdown, 'click', this.showDropdownOptions.bind(this, dropdownOptions, dropdown));
		var qb = this;
		Event.observe(dropdownOptions, 'mouseout', function(){
			setTimeout(qb.hideDropdownOptions.bind(qb, dropdownOptions), 1000);
		});
		return option;
	},
	selectConfigurableOption : function(config, attributeId, isMainOption, block, dropdown){
		dropdown.select('.selected-option-text')[0].update(config.label);
		this.superAttributes[attributeId] = config.id;
		if (isMainOption) {
			var dropdowns = block.select('.dropdown');
			for (var i = 1; i < dropdowns.length; i++) {
				var options = dropdowns[i].select('.option');
				dropdowns[i].select('.options')[0].show();
				for (var j = 0; j < options.length; j++) {
					for (var k = 0; k < options[j].products.length; k++) {
						var isProduct = (config.products.indexOf(options[j].products[k]) > -1);
						if (isProduct) {
							options[j].show();
							dropdowns[i].disableDropdown = false;
							break;
						} else {
							options[j].hide();
						}
					}
				}
				dropdowns[i].select('.options')[0].hide();
			}
		}
		var beforeValue = this.superAttributesPrices[attributeId] || 0;
		var afterValue = parseFloat(config.price);
		this.superAttributesPrices[attributeId] = afterValue;
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	showDropdownOptions : function(options, dropdown) {
		if (dropdown.disableDropdown) {
			return;
		}
		if (options.visible()) {
			options.hide();
		} else {
			options.show();
		}
	},
	hideDropdownOptions : function(options) {
		if (!options.hoverDropdown) {
			options.hide();
		}
	},
	createOptionsDropdown : function(defaultText) {
		var dropdown = this.quickbuy.createDiv();
		dropdown.addClassName('dropdown');
		dropdown.style.zIndex = this.zIndex--;
		var text = this.quickbuy.createDiv();
		text.addClassName('selected-option-text');
		text.update(defaultText);
		dropdown.appendChild(text);
		var arrow = this.quickbuy.createDiv();
		arrow.addClassName('arrow');
		dropdown.appendChild(arrow);
		return dropdown;
	},
	createDropdownOptions : function() {
		var options = this.quickbuy.createDiv();
		options.addClassName('options');
		return options;
	},
	createDropdownOption : function(title) {
		var option = this.quickbuy.createDiv();
		option.addClassName('option');
		var label = this.quickbuy.createDiv();
		label.addClassName('label');
		label.update(title);
		option.appendChild(label);
		return option;
	},
	createBundleOptions : function() {
		var block = this.quickbuy.createDiv();
		block.addClassName('type');
		block.addClassName('bundle');
		if (parseInt(this.config.required_options)) {
			block.appendChild(this.createRequiredText());
		}
		for (var i = 0; i < this.config.bundle_options.length; i++) {
			block.appendChild(this.createBundleOption(this.config.bundle_options[i]));
		}
		return block;
	},
	createBundleOption : function(option) {
		var optionBlock = this.quickbuy.createDiv();
		optionBlock.addClassName('option');
		optionBlock.update(option.label);
		if (parseInt(option.required)) {
			optionBlock.appendChild(this.createRequireOptionText());
		}
		var qtyInput = null;
		if (parseInt(option.can_change_qty)) {
			qtyInput = document.createElement('input');
		}
		for (var i = 0; i < option.products.length; i++) {
			optionBlock.appendChild(this.createBundleProduct(option.products[i], option.multiselection, option.id, qtyInput));
		}
		if (parseInt(option.can_change_qty)) {
			var value = this.quickbuy.createDiv();
			value.addClassName('value');
			var qty = this.quickbuy.createDiv();
			qty.addClassName('qty');
			qty.update(this.quickbuy.getText('qty'));
			value.appendChild(qty);
			Element.extend(qtyInput);
			qtyInput.addClassName('qty-input');
			qtyInput.value = this.bundleOptionsQty[option.id] || '0';
			value.appendChild(qtyInput);
			optionBlock.appendChild(value);
			Event.observe(qtyInput, 'change', this.addBundleQty.bind(this, qtyInput, option.id));
		}
		return optionBlock;
	},
	createBundleProduct : function(product, multiselection, optionId, qtyInput) {
		var block = this.quickbuy.createDiv();
		var radio = document.createElement('input');
		var type = '';
		var name = '';
		if (multiselection) {
			type = 'checkbox';
			Element.extend(radio);
			radio.type = type;
			if (this.bundleOptions[optionId] && this.bundleOptions[optionId].indexOf(product.id) != -1) {
				radio.checked = true;
			}
		} else {
			type = 'radio';
			name = this.productCount + '_' + this.config.product_id + '_b' + optionId;
			var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
			if (ie7) {
				radio = document.createElement('<input type="radio" name="'+ name +'" value="' + product.id + '"/>');
			} else {
				radio.type = type;
			}
			Element.extend(radio);
			if (this.bundleOptions[optionId] && this.bundleOptions[optionId] == product.id) {
				radio.checked = true;
			}
		}
		radio.addClassName('radio');
		radio.value = product.id;
		radio.name = name;
		//Event.observe(radio, 'click', this.addOption.bind(this, option, i));
		var title = document.createElement('span');
		Element.extend(title);
		title.update(product.name);
		block.appendChild(radio);
		block.appendChild(title);
		var additionalPrice = document.createElement('span');
		Element.extend(additionalPrice);
		additionalPrice.addClassName('add-price')
		additionalPrice.update('+' + this.quickbuy.formatPrice(product.price, 1, product.tax_class_id, this.quickbuy.canShowInclTaxPrice()));
		block.appendChild(additionalPrice);
		Event.observe(block, 'click', this.addBundlePrice.bind(this, optionId, product.id, this.quickbuy.formatPrice(product.price, 1, product.tax_class_id, this.quickbuy.canShowInclTaxPrice(), true), multiselection, qtyInput, radio));
		return block;
	},
	addBundlePrice : function(optionId, productId, price, multiselection, qtyInput, elm, ev) {
		var beforeValue = 0;
		var afterValue = 0;
		if (multiselection) {
			if (elm) {
				var targetElm = ev.target || ev.srcElement;
				if (elm != targetElm) {
					elm.checked = !elm.checked;
				}
			}
			if (!(this.bundleOptions[optionId] instanceof Array)) {
				this.bundleOptions[optionId] = [];
				this.bundleOptionsPrices[optionId] = [];
			}
			var wasInArray = false;
			if (this.bundleOptions[optionId].indexOf(productId) != -1) {
				this.bundleOptions[optionId] = this.bundleOptions[optionId].without(productId);
				beforeValue = this.bundleOptionsPrices[optionId][productId];
				delete this.bundleOptionsPrices[optionId][productId];
				wasInArray = true;
			}
			if (!wasInArray) {
				this.bundleOptions[optionId].push(productId);
				this.bundleOptionsPrices[optionId][productId] = price;
				afterValue = price;
			}
		} else {
			if (elm) {
				elm.checked = true;
			}
			this.bundleOptions[optionId] = productId;
			if (qtyInput) {
				if (!(this.bundleOptionsQty[optionId] && !parseInt(this.bundleOptionsQty[optionId])) || !productId) {
					var qty = productId ? 1 : 0;
					qtyInput.value = qty;
					this.bundleOptionsQty[optionId] = qty;
				}
			}
			if (!this.bundleOptionsQty[optionId]) {
				this.bundleOptionsQty[optionId] = 1;
			}
			if (this.bundleOptionsPrices[optionId]) {
				beforeValue = this.bundleOptionsPrices[optionId] * this.bundleOptionsQty[optionId];
			}
			this.bundleOptionsPrices[optionId] = price;
			afterValue = price * this.bundleOptionsQty[optionId];
		}
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	addBundleQty : function(input, optionId) {
		var beforeValue = this.bundleOptionsPrices[optionId] || 0;
		beforeValue *= this.bundleOptionsQty[optionId] || 0;
		var value = parseInt(input.value) || 0;
		this.bundleOptionsQty[optionId] = value;
		var afterValue = this.bundleOptionsPrices[optionId] || 0;
		afterValue *= value;
		this.calculateFinalPriceWithOptions(beforeValue, afterValue);
	},
	createAssociatedProducts : function() {
		var block = this.quickbuy.createDiv();
		block.addClassName('type');
		block.addClassName('type-grouped');
		var label = this.quickbuy.createDiv();
		label.addClassName('label');
		label.update(this.quickbuy.getText('associatedProducts') + ':');
		block.appendChild(label);
		var row = null;
		var title = null;
		var value = null;
		var price = null;
		var priceStr = 0;
		var qtyInput = null;
		var qty = null;
		var totalPrice = null;
		var selectedProduct = null;
		for (var i = 0; i < this.config.super_group.length; i++) {
			for (var j = 0; j < this.groupedProducts.length; j++) {
				if (this.groupedProducts[j].id == this.config.super_group[i].id) {
					selectedProduct = this.groupedProducts[j];
					break;
				}
			}
			if (!selectedProduct) {
				this.groupedProducts.push({
					'id'    : this.config.super_group[i].id,
					'qty'   : 0,
					'price' : parseFloat(this.quickbuy.formatPrice(this.config.super_group[i].price, 1, this.config.super_group[i].tax_class_id, this.quickbuy.canShowInclTaxPrice(), true)),
					'orig_price' :  parseFloat(this.quickbuy.formatPrice(this.config.super_group[i].price, 1, this.config.super_group[i].tax_class_id, this.quickbuy.canShowInclTaxPrice(), true)),
					'tier_prices' : this.config.super_group[i].tier_prices
				});
			}
			row = this.quickbuy.createDiv();
			row.addClassName('row');
			title = this.quickbuy.createDiv();
			title.addClassName('title');
			title.update(this.config.super_group[i].name + ' (' + this.quickbuy.translates.sku + ': ' + this.config.super_group[i].sku + '):');
			row.appendChild(title);
			value = this.quickbuy.createDiv();
			value.addClassName('value');
			qty = this.quickbuy.createDiv();
			qty.addClassName('qty');
			qty.update(this.quickbuy.getText('qty'));
			value.appendChild(qty);
			qtyInput = document.createElement('input');
			Element.extend(qtyInput);
			qtyInput.addClassName('qty-input');
			qtyInput.value = selectedProduct ? selectedProduct.qty : 0;
			value.appendChild(qtyInput);
			price = this.quickbuy.createDiv();
			price.addClassName('price');
			priceStr = this.getProductPriceInGroupedStr(i, qtyInput.value);
			price.update(priceStr);
			value.appendChild(price);
			totalPrice = this.quickbuy.createDiv();
			totalPrice.addClassName('price');
			var sumPrice = selectedProduct ? (parseInt(selectedProduct.qty) * parseFloat(selectedProduct.price))
										   : 0;
			totalPrice.update(this.quickbuy.currencySymbol + this.quickbuy.convertPrice(sumPrice));
			if (selectedProduct) {
				this.sumGroupedPrice();
			}
			value.appendChild(totalPrice);
			var minQty = document.createElement('div');
			Element.extend(minQty);
			minQty.addClassName('qb-min-sale-qty');
			var minQtyText = '';
			if (this.config.super_group[i].min_qty > 1) {
				minQtyText += this.quickbuy.translates.minimum.replace('%d', this.config.super_group[i].min_qty);
			}
			var incQty = this.quickbuy.getIncrementQty(this.config.super_group[i]);
			if (incQty) {
				minQtyText += (minQtyText.length ? ' ' : '') + this.quickbuy.translates.increments.replace('%d', incQty);
			}
			if (minQtyText.length) {
				minQty.update('(' + minQtyText + ')');
				value.appendChild(minQty);
				value.setStyle({width: '600px'});
			}

			Event.observe(qtyInput, 'change', this.calculateProductPrice.bind(this, qtyInput, totalPrice , i, price));
			row.appendChild(value);
			block.appendChild(row);
		}
		return block;
	},
	getProductPriceInGrouped : function(num, qty) {
		var data = this.groupedProducts[num];
		if (qty && data.tier_prices.length) {
			var maxQty = data.tier_prices[0].qty;
			var price = false;
			for (var i = 0; i < data.tier_prices.length; i++) {
				if (maxQty <= qty) {
					price = parseFloat(data.tier_prices[i].price);
				}
			}
			if (price) {
				return price;
			}
		}
		return data.orig_price;
	},
	getProductPriceInGroupedStr : function(productNum, qty) {
		return '* ' + this.quickbuy.currencySymbol + this.quickbuy.convertPrice(this.getProductPriceInGrouped(productNum, qty)) + ' =  ';
	},
	calculateProductPrice : function(input, totalPrice, productNum, onePriceStr) {
		var value = parseInt(input.value) ? input.value : 0;
		this.groupedProducts[productNum].qty = value;
		this.groupedProducts[productNum].price = this.getProductPriceInGrouped(productNum, value);
		onePriceStr.update(this.getProductPriceInGroupedStr(productNum, value));
		totalPrice.update(this.quickbuy.formatPrice(value * this.groupedProducts[productNum].price));
		this.sumGroupedPrice();
	},
	sumGroupedPrice : function() {
		var sumPrice = 0;
		for (var i = 0; i < this.groupedProducts.length; i++) {
			sumPrice += this.groupedProducts[i].price * this.groupedProducts[i].qty;
		}
		this.finalPriceBlock.update(this.quickbuy.getPriceStr(sumPrice, 1, this.taxClassId));
	},
	toogleOptions : function() {
		if (this.optionsBlock.visible()) {
			this.hideOptions();
		} else {
			this.showOptions();
		}
	},
	showOptions : function() {
		if (this.optionsBlock.visible()) {
			return;
		}
		var img = this.optionsBlock.select('img')[0];
		new Effect.Parallel([
			new Effect.Morph(this.block, {
				//20 = margin-top(30) + margin-bottom(10)
				style: 'height: ' + (this.defaultHeight + this.optionHeight + 40) + 'px'
			}),
				new Effect.Appear(this.optionsBlock),
				new Effect.Appear(img, {from: 0.0, to: 1.0})
			], {
				duration: 0.8
		});
		this.arrow.addClassName('arrow-down');
	},
	hideOptions : function() {
		if (!this.optionsBlock.visible()) {
			return;
		}
		var img = this.optionsBlock.select('img')[0];
		new Effect.Parallel([
			new Effect.Morph(this.block, {
				style: 'height: ' + this.defaultHeight + 'px'
			}),
			new Effect.Fade(this.optionsBlock),
			new Effect.Fade(img, {from: 1.0, to: 0.0})
			], {
			  duration: 0.8
		});
		this.arrow.removeClassName('arrow-down');
	},
	removeProduct : function() {
		this.block.remove();
		if (this.category.childElements().length <= 1) {
			var categoryId = this.category.id;
			this.category.remove();
			return categoryId;
		}
		return false;
	},
	validate : function() {
		var i = 0;
		this.error = false;
		this.dateError = false;
		this.dateNotValid = false;
		if (this.groupedProducts.length) {
			for (i = 0; i < this.groupedProducts.length; i++) {
				if (parseInt(this.groupedProducts[i].qty)) {
					break;
				}
				if (i == this.groupedProducts.length - 1) {
					this.error = true;
				}
			}
		} else {
			this.error = !this.qty;
		}
		if (!this.error && this.config.options) {
			for (i = 0; i < this.config.options.length; i++) {
				if (this.options[this.config.options[i].id]) {
					if (this.config.options[i].type == 'date' || this.config.options[i].type == 'date_time') {
						this.error = this.validateDate(this.config.options[i].id);
						if (!this.error && !this.validateDateValues(this.config.options[i].id)) {
							this.dateNotValid = this.quickbuy.translates.dateFieldIsNotValid + '. ';
							this.error = !!this.dateNotValid;
						}
					}
					if (!this.error && (this.config.options[i].type == 'time' || this.config.options[i].type == 'date_time')) {
						this.error = this.validateTime(this.config.options[i].id);
					}
					if (this.error) {
						this.dateError = this.dateNotValid || this.quickbuy.translates.dateFieldIsNotComplete + '. ';
						break;
					}
				}
			}
		}
		if (parseInt(this.config.required_options)) {
			if (!this.error && this.config.super_attribute) {
				for (i = 0; i < this.config.super_attribute.length; i++) {
					if (parseInt(this.config.super_attribute[i].required)) {
						if (!this.superAttributes[this.config.super_attribute[i].id]) {
							this.error = true;
							break;
						}
					}
				}
			}
			if (!this.error && this.config.bundle_options) {
				for (i = 0; i < this.config.bundle_options.length; i++) {
					if (parseInt(this.config.bundle_options[i].required)) {
						if (!this.bundleOptions[this.config.bundle_options[i].id]) {
							this.error = true;
							break;
						}
					}
					if (parseInt(this.config.bundle_options[i].can_change_qty)
						&& this.bundleOptions[this.config.bundle_options[i].id]
						&& !this.bundleOptionsQty[this.config.bundle_options[i].id]
					) {
						this.error = true;
						break;
					}
				}
			}
			if (!this.error && this.config.links) {
				if (!this.links.length) {
					this.error = true;
				}
			}
			if (!this.error && this.config.options) {
				for (i = 0; i < this.config.options.length; i++) {
					if (parseInt(this.config.options[i].required)) {
						if (!this.options[this.config.options[i].id]) {
							this.error = true;
							break;
						} else {
							if (this.config.options[i].type == 'date' || this.config.options[i].type == 'date_time') {
								this.error = this.validateDate(this.config.options[i].id);
								if (this.error) {
									break;
								}
							}
							if (this.config.options[i].type == 'time' || this.config.options[i].type == 'date_time') {
								this.error = this.validateTime(this.config.options[i].id);
								if (this.error) {
									break;
								}
							}
						}
					}
				}
			}
		}
		if (this.error) {
			this.quickbuy.alertValidateFail = true;
			if (this.dateError) {
				this.quickbuy.alertError += this.dateError;
			}
			if (!this.block.hasClassName('error')) {
				this.block.addClassName('error');
				this.showOptions();
			}
		} else {
			if (this.block.hasClassName('error')) {
				this.block.removeClassName('error');
			}
		}
		return this.error;
	},
	validateDate : function(optionId) {
		return !(this.options[optionId].month && this.options[optionId].day && this.options[optionId].year);
	},
	validateDateValues : function(optionId) {
		var d = new Date(parseNumber(this.options[optionId].year), parseNumber(this.options[optionId].month) - 1, parseNumber(this.options[optionId].day));
		return d.getFullYear() == parseNumber(this.options[optionId].year) && d.getMonth() == parseNumber(this.options[optionId].month) - 1 && d.getDate() == parseNumber(this.options[optionId].day);
	},
	validateTime : function(optionId) {
		return !(this.options[optionId].hour && this.options[optionId].minute && this.options[optionId].day_part);
	},
	toJSON : function() {
		
	}
}

Itoris.QuickBuy.CartList = Class.create();
Itoris.QuickBuy.CartList.prototype = {
	block : null,
	list : null,
	note : null,
	categories : [],
	products : [],
	quickbuy : null,
	expandIcon : null,
	expandText : null,
	preparedForCart : 0,
	zIndex : 100,
	initialize : function(quickbuy, products){
		this.block = $$('#qb .precart')[0];
		this.list = $$('#qb .cart-list')[0];
		this.note = $$('#qb .no-selection')[0];
		this.quickbuy = quickbuy;
		this.categories.size = 0;
		Event.observe($$('#qb .view-cart')[0], 'click', this.viewProduct.bind(this, quickbuy.urls.cart));
		Event.observe($$('#qb .add-to-cart')[0], 'click', this.addToCart.bind(this));
		this.list.appendChild(this.createExpandAll());
		var cartList = this;
		if (products && products.length) {
			products.each(function(item){
				if (item) {
					cartList.addProduct(item.config.evalJSON(), item);
				}
			});
		}
	},
	createExpandAll : function() {
		var block = document.createElement('div');
		Element.extend(block);
		block.addClassName('expand-all');
		var icon = document.createElement('div');
		Element.extend(icon);
		icon.addClassName('expand');
		Event.observe(icon, 'click', this.toogleProductsOptions.bind(this));
		block.appendChild(icon);
		this.expandIcon = icon;
		var text = document.createElement('div');
		Element.extend(text);
		text.addClassName('text');
		text.update(this.quickbuy.getText('expandAll'));
		block.appendChild(text);
		this.expandText = text;
		return block;
	},
	toogleProductsOptions : function() {
		var toogle = null;
		if (this.expandIcon.hasClassName('expand')) {
			toogle = function(element) {
				if (element) {
					element.showOptions();
				}
			}
			this.expandIcon.removeClassName('expand');
			this.expandIcon.addClassName('collapse');
			this.expandText.update(this.quickbuy.getText('collapseAll'));
		} else {
			toogle = function(element) {
				if (element) {
					element.hideOptions();
				}
			}
			this.expandIcon.removeClassName('collapse');
			this.expandIcon.addClassName('expand');
			this.expandText.update(this.quickbuy.getText('expandAll'));
		}
		this.products.each(toogle);
	},
	getCategoryList : function(name) {
		var idName = name.replace(/\s/g,'').escapeHTML();
		return this.categories['cat-' + idName] || this.createCategoryList(name);
	},
	createCategoryList : function(name) {
		var idName = name.replace(/\s/g,'').escapeHTML();
		var listId = 'cat-' + idName;
		var catList = document.createElement('div');
		Element.extend(catList);
		catList.id = listId;
		var title = document.createElement('div');
		Element.extend(title);
		title.addClassName('label');
		title.update(name);
		catList.appendChild(title);
		this.list.appendChild(catList);
		this.categories[listId] = catList;
		this.categories.size++;
		return catList;
	},
	addProduct : function(productConfig, preSelected) {
		if (!this.block.visible()) {
			this.block.show();
			this.note.hide();
		}
		var productRow = document.createElement('div');
		Element.extend(productRow);
		productRow.addClassName('product');
		var clickArea = document.createElement('div');
		Element.extend(clickArea);
		clickArea.addClassName('area');
		if (this.quickbuy.config.display_both_price) {
			clickArea.addClassName('incl-tax');
		}
		var productName = productConfig.product;
		if (productConfig.type == 'simple') {
			productName += ' (' + this.quickbuy.translates.sku + ': ' + productConfig.sku + ')';
		}
		var containsText = '';
		if (Number(productConfig.has_options)) {
			containsText = ' (' + this.quickbuy.getText('containsOptions') + ') ';
		}
		if (productConfig.type == 'grouped') {
			containsText = ' (' + this.quickbuy.getText('containsAssociatedProducts') + ') ';
		}
		productName += '<span class="contains-options">' + containsText + '</span>';
		clickArea.update(productName);
		var arrow = document.createElement('div');
		Element.extend(arrow);
		arrow.addClassName('arrow');
		clickArea.insert({'top': arrow});
		clickArea.insert({'top': arrow});
		productRow.appendChild(clickArea);
		var iconView = document.createElement('div');
		Element.extend(iconView);
		iconView.addClassName('icon-view');
		Event.observe(iconView, 'click', this.viewProduct.bind(this, productConfig.product_url));
		productRow.appendChild(iconView);
		var iconRemove = document.createElement('div');
		Element.extend(iconRemove);
		iconRemove.addClassName('icon-remove');
		productRow.appendChild(iconRemove);
		var price = document.createElement('div');
		Element.extend(price);
		price.addClassName('price');
		var priceStr = '';
		if (!parseFloat(productConfig.price)) {
			priceStr = productConfig.min_price;
		} else {
			priceStr = productConfig.price;
		}
		if (preSelected) {
			// it's string '[]'
			priceStr = (preSelected.groupedProducts.length != 2)
					   ? preSelected.finalPrice
					   : (parseInt(preSelected.qty) * parseFloat(preSelected.finalPrice));
		}
		if (productConfig.type == 'grouped' || (productConfig.type == 'bundle' && !parseFloat(productConfig.price))) {
			priceStr = 0;
		}
		if (this.quickbuy.config.display_both_price) {
			price.addClassName('incl-tax');
		}
		priceStr = this.quickbuy.getPriceStr(priceStr, 1, productConfig.tax_class_id);
		price.update(priceStr);
		productRow.appendChild(price);
		if (productConfig.type != 'grouped') {
			var qtyInput = document.createElement('input');
			Element.extend(qtyInput);
			qtyInput.addClassName('qty-input');
			qtyInput.value = preSelected ? preSelected.qty : 1;
			productRow.appendChild(qtyInput);
			var qty = document.createElement('div');
			Element.extend(qty);
			qty.addClassName('qty');
			qty.update(this.quickbuy.getText('qty'));
			var minQty = document.createElement('span');
			Element.extend(minQty);
			minQty.addClassName('qb-min-sale-qty');
			var minQtyText = '';
			if (productConfig.min_qty > 1) {
				minQtyText += this.quickbuy.translates.minimum.replace('%d', productConfig.min_qty);
			}
			var incQty = this.quickbuy.getIncrementQty(productConfig);
			if (incQty) {
				minQtyText += (minQtyText.length ? ' ' : '') + this.quickbuy.translates.increments.replace('%d', incQty);
			}
			if (minQtyText.length) {
				minQty.update('(' + minQtyText + ')');
				qty.appendChild(minQty);
			}
			productRow.appendChild(qty);
		}
		var category = this.getCategoryList(productConfig.category_path);
		productRow.style.zIndex = this.zIndex--;
		category.appendChild(productRow);
		var id = Number(productConfig.product_id);
		var product = new Itoris.QuickBuy.Product(productConfig, productRow, arrow, this.quickbuy, category, price, preSelected);
		this.products.push(product);
		Event.observe(iconRemove, 'click', this.removeProductConfirm.bind(this, product));
		Event.observe(clickArea, 'click', product.toogleOptions.bind(product));
		if (productConfig.type != 'grouped') {
			Event.observe(qtyInput, 'change', this.changeProductPrice.bind(this, qtyInput, product, price, productConfig.tax_class_id));
		}
	},
	changeProductPrice : function(qtyBlock, product, priceBlock, taxClassId) {
		var value = parseInt(qtyBlock.value) || 0;
		this.prepareProductPriceByQty(product, value);
		product.qty = value;
		priceBlock.update(this.quickbuy.getPriceStr(product.finalPrice, product.qty, taxClassId));
	},
	prepareProductPriceByQty : function(product, value) {
		var product = product;
		if (product.config.type && product.config.type == 'bundle') {
			this.prepareProductPriceByQtyAndPercent(product, value);
			return;
		}
		var priceBefore = this.getTierPrice(product.config.tier_prices, product.qty, (product.config.price || product.config.min_price)) || product.config.price || product.config.min_price;
		var tierPrice = this.getTierPrice(product.config.tier_prices, value, (product.config.price || product.config.min_price));
		if (tierPrice) {
			product.finalPrice = product.finalPrice - parseNumber(priceBefore) + parseNumber(tierPrice);
		} else {
			product.finalPrice = product.finalPrice - parseNumber(priceBefore) + parseNumber(product.config.price || product.config.min_price);
		}
	},
	prepareProductPriceByQtyAndPercent : function(product, value) {
		var priceBefore = this.getPercentTierPrice(product.config.tier_prices, product.qty, product.finalPrice, true);
		var priceAfter = this.getPercentTierPrice(product.config.tier_prices, value, priceBefore, false);
		product.finalPrice = priceAfter;
	},
	getTierPrice : function(prices, qty, finalPriceDefault) {
		for (var i = qty; i >= 1; i--) {
			if (prices[i]) {
				return parseNumber(prices[i]) < parseNumber(finalPriceDefault) ? prices[i] : finalPriceDefault;
			}
		}

		return null;
	},
	getPercentTierPrice : function(prices, qty, finalPrice, returnBeforeValue) {
		for (var i = qty; i >= 1; i--) {
			if (prices[i]) {
				if (returnBeforeValue) {
					return finalPrice / (1 - parseNumber(prices[i]) / 100);
				} else {
					return finalPrice * (1 - parseNumber(prices[i]) / 100);
				}
			}
		}

		return finalPrice;
	},
	viewProduct : function(url) {
		var newWindow = window.open(url, '_blank');
		newWindow.focus();
	},
	removeProductConfirm : function(product) {
		if (!confirm(this.quickbuy.getText('removeProduct'))) {
			return;
		}
		this.removeProduct(product, false);
	},
	removeProduct : function(product, addedToCart) {
		var deleteCategory = product.removeProduct();
		if (deleteCategory) {
			delete this.categories[deleteCategory];
			this.categories.size--;
			if (!this.categories.size) {
				this.block.hide();
				this.note.show();
			}
		}
		for (var i = 0; i < this.products.length; i++) {
			if (this.products[i] == product) {
				delete this.products[i];
				break;
			}
		}
		//this.quickbuy.selectedProducts = this.quickbuy.selectedProducts.without(id);
		if (addedToCart && !this.preparedForCart) {
			this.updateCartLink();
			this.quickbuy.hideLoader();
			this.quickbuy.setProducts(true);
			alert(this.quickbuy.getText('productAddedToCart'));
		}
	},
	addToCart : function() {
		var i = 0;
		var cartList = this;
		var preparedParams = {};
		var activeRequest = false;
		this.preparedForCart = 0;
		this.quickbuy.alertError = '';
		this.quickbuy.errorText = '';
		this.products.each( function(product, index) {
				if (product) {
					preparedParams = {
							'product' : product.config.product_id,
							'qty' : product.qty
					};
					for (i = 0; i < product.groupedProducts.length; i++) {
						if (product.groupedProducts[i].qty) {
							preparedParams['super_group[' + product.groupedProducts[i].id + ']'] = product.groupedProducts[i].qty;
						}
					}
					for (i = 0; i < product.links.length; i++) {
						preparedParams['links[' + i + ']'] = product.links[i];
					}
					product.options.forEach(function(valueId, optionId) {
						if (typeof(valueId) == 'object') {
							valueId.forEach(function(value, key) {
								preparedParams['options[' + optionId + ']['+ key +']'] = value;
							});
							if (valueId.month) {
								preparedParams['options[' + optionId + '][month]'] = valueId.month;
								preparedParams['options[' + optionId + '][day]'] = valueId.day;
								preparedParams['options[' + optionId + '][year]'] = valueId.year;
							}
							if (valueId.hour) {
								preparedParams['options[' + optionId + '][hour]'] = valueId.hour;
								preparedParams['options[' + optionId + '][minute]'] = valueId.minute;
								preparedParams['options[' + optionId + '][day_part]'] = valueId.day_part;
							}
						} else {
							preparedParams['options[' + optionId + ']'] = valueId;
						}
					});
					product.superAttributes.forEach(function(valueId, optionId) {
						preparedParams['super_attribute[' + optionId + ']'] = valueId;
					});
					product.bundleOptions.forEach(function(valueId, optionId){
						if (valueId instanceof Array) {
							for (i = 0; i < valueId.length; i++) {
								preparedParams['bundle_option[' + optionId + ']['+ i +']'] = valueId[i];
							}
						} else {
							preparedParams['bundle_option[' + optionId + ']'] = valueId;
						}
					});
					product.bundleOptionsQty.forEach(function(valueId, optionId) {
						preparedParams['bundle_option_qty[' + optionId + ']'] = valueId;
					});
					if (!product.validate()) {
						if (!activeRequest) {
							activeRequest = true;
							cartList.quickbuy.showLoader();
						}
						cartList.preparedForCart++;
						var iframeId = Math.random() + '_cart';
						var iframe = null;
						var ie7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
						if (ie7) {
							iframe = document.createElement('<iframe name="' + iframeId + '"></iframe>');
						} else {
							iframe = document.createElement('iframe');
						}
						Element.extend(iframe);
						iframe.id = iframeId;
						if (!ie7) {
							iframe.name = iframeId;
						}
						iframe.setStyle({'width':'0','height':'0','border':'0px solid #fff'});
						cartList.block.appendChild(iframe);
						if (!Prototype.Browser.IE) {
							var form = document.createElement('form');
							Element.extend(form);
							form.enctype = 'multipart/form-data';
							form.action = encodeURI(cartList.quickbuy.config.addToCartUrl);
							form.method = 'post';
							form.target = iframeId;
						} else {
							if (ie7) {
								var form = document.createElement('<form enctype="multipart/form-data" action="'+encodeURI(cartList.quickbuy.config.addToCartUrl) + '" method="post" target="'+iframeId+'">');
							} else {
								var form = document.createElement('form');
								form.setAttribute("enctype", "multipart/form-data");
								form.setAttribute("action", encodeURI(cartList.quickbuy.config.addToCartUrl));
								form.setAttribute("method", "post");
								form.setAttribute("target", iframeId);
							}
							Element.extend(form);
						}
						for (var key in preparedParams) {
							var input = document.createElement('input');
							Element.extend(input);
							input.type = 'hidden';
							input.name = key;
							input.value = preparedParams[key];
							form.appendChild(input);
						}
						var fileElements = [];
						product.files.forEach(function(fileField, optionId) {
							fileElements.push({
								parent : fileField.up(),
								field  : fileField
							});
							form.appendChild(fileField);
							var input = document.createElement('input');
							Element.extend(input);
							input.type = 'hidden';
							input.name = 'options_' + optionId + '_file_action';
							input.value = 'save_new';
							form.appendChild(input);
						});
						Event.observe(iframe, 'load', cartList.afterAddedToCart.bind(cartList, iframeId, product, index, iframe, form, fileElements));
						cartList.block.appendChild(form);
						form.submit();
						form.hide();
					}
				}
			}
		);
		if (this.quickbuy.alertValidateFail) {
			var alertText = this.quickbuy.alertError || this.quickbuy.getText('validateFail');
			alert(alertText);
			this.quickbuy.alertValidateFail = false;
			this.quickbuy.alertError = '';
		}

	},
	afterAddedToCart : function(iFrameId, product, productIndex, iFrame, form, fileElements) {
		var iFrame = $(iFrameId);
		var iFrameDocument = iFrame.contentDocument ? iFrame.contentDocument : iFrame.contentWindow.document;
		this.preparedForCart--;
		if (iFrameDocument.getElementById('success')) {
			this.removeProduct(product, true);
			this.products.without(productIndex);
		} else {
			if (iFrameDocument.getElementById('error')) {
				var errorText = iFrameDocument.getElementById('error').innerHTML;
				if (errorText.length) {
					this.quickbuy.errorText += errorText;
				}
			}
			if (!this.preparedForCart) {
				if (fileElements.length) {
					for (var i = 0; i < fileElements.length; i++) {
						fileElements[i].parent.appendChild(fileElements[i].field);
					}
				}
				this.updateCartLink();
				this.quickbuy.hideLoader();
				this.quickbuy.setProducts(true);
				alert(this.quickbuy.getText('products_not_added_to_cart') + '\n' + this.quickbuy.errorText);
			}
		}
	},
	updateCartLink : function() {
		var parameters = {
			type: 'cart_summary',
			sid : this.quickbuy.config.sid,
			base_path : this.quickbuy.config.base_path,
			store_id : this.quickbuy.config.store_id,
			callback : 'qbForm.cartList.updateCartLinkAfter'
		};
		this.quickbuy.sendJsonp(parameters, this.quickbuy.urls.cartSummaryUrl);
	},
	updateCartLinkAfter : function(data) {
		if (data.error) {
			console.log(data.error);
		} else {
			this.updateCartLinkByText(data.link_text, data.count);
		}
	},
	updateCartLinkByText : function(text, qty) {
		var cartHeader = $('cartHeader');
		if (cartHeader && cartHeader.select('span').length) {
			cartHeader.select('span')[0].update(qty);
		} else {
			var cartLink = $$('.header a[href|=' + this.quickbuy.config.checkoutLinkUrl + ']');
			if (cartLink.length) {
				if (cartLink[0].select('span').length) {
					cartLink[0].select('span')[0].update(qty);
				} else {
					cartLink[0].update(text);
				}
			}
		}
	}
}

if (!Array.prototype.forEach) {
  Array.prototype.forEach = function(callback, thisArg) {
    var T, k;
    if (this == null) {
      throw new TypeError( " this is null or not defined" );
    }
    var O = Object(this);
    var len = O.length >>> 0;
    if ({}.toString.call(callback) != "[object Function]") {
      throw new TypeError(callback + " is not a function");
    }
    if (thisArg) {
      T = thisArg;
    }
    k = 0;
    while(k < len) {
      var kValue;
      if (k in O) {
        kValue = O[ k ];
        callback.call( T, kValue, k, O );
      }
      k++;
    }
  };
}

if (!Array.prototype.toJSON) {
	Array.prototype.toJSON = function() {
		var results = [];
		this.each(function(object) {
		  var value = Object.toJSON(object);
		  if (!Object.isUndefined(value)) results.push(value);
		});
		return '[' + results.join(', ') + ']';
  	}
}

var JSONP_QB = function(global) {
	function JSONP(uri, callback) {
		function JSONPResponse() {
			try {
				delete global[src]
			} catch(e) {
				global[src] = null
			}
			documentElement.removeChild(script);
			callback.apply(this, arguments);
		}
		var src = prefix + id++;
		var script = document.createElement("script");
		global[src] = JSONPResponse;
		documentElement.insertBefore(
			script,
			documentElement.lastChild
		).src = uri + (uri.include('?') ? '&' : '?') + 'script_id=' + src;
	}
	var id = 0;
	var prefix = "__JSONP_QB__";
	var document = global.document;
	var documentElement = document.documentElement;
	return JSONP;
} (this);
