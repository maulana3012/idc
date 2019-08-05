// SpryTabbedPanels.js - version 0.5 - Spry Pre-Release 1.6
//
// Copyright (c) 2006. Adobe Systems Incorporated.
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//     this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//     this list of conditions and the following disclaimer in the documentation
//     and/or other materials provided with the distribution.
//   * Neither the name of Adobe Systems Incorporated nor the names of its
//     contributors may be used to endorse or promote products derived from this
//     software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
// LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
// CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
// SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
// INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
// CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
// POSSIBILITY OF SUCH DAMAGE.

var Spry;
if (!Spry) Spry = {};
if (!Spry.Widget) Spry.Widget = {};

Spry.BrowserSniff = function()
{
	var b = navigator.appName.toString();
	var up = navigator.platform.toString();
	var ua = navigator.userAgent.toString();

	this.mozilla = this.ie = this.opera = r = false;
	var re_opera = /Opera.([0-9\.]*)/i;
	var re_msie = /MSIE.([0-9\.]*)/i;
	var re_gecko = /gecko/i;
	var re_safari = /safari\/([\d\.]*)/i;

	if (ua.match(re_opera)) {
		r = ua.match(re_opera);
		this.opera = true;
		this.version = parseFloat(r[1]);
	} else if (ua.match(re_msie)) {
		r = ua.match(re_msie);
		this.ie = true;
		this.version = parseFloat(r[1]);
	} else if (ua.match(re_safari)) {
		this.safari = true;
		this.version = 1.4;
	} else if (ua.match(re_gecko)) {
		var re_gecko_version = /rv:\s*([0-9\.]+)/i;
		r = ua.match(re_gecko_version);
		this.mozilla = true;
		this.version = parseFloat(r[1]);
	}
	this.windows = this.mac = this.linux = false;

	this.Platform = ua.match(/windows/i) ? "windows" :
					(ua.match(/linux/i) ? "linux" :
					(ua.match(/mac/i) ? "mac" :
					ua.match(/unix/i)? "unix" : "unknown"));
	this[this.Platform] = true;
	this.v = this.version;

	if (this.safari && this.mac && this.mozilla) {
		this.mozilla = false;
	}
};

Spry.is = new Spry.BrowserSniff();

Spry.Widget.TabbedPanels = function(element, opts)
{
	this.element = this.getElement(element);
	this.defaultTab = 0; // Show the first panel by default.
	this.tabSelectedClass = "TabbedPanelsTabSelected";
	this.tabHoverClass = "TabbedPanelsTabHover";
	this.tabFocusedClass = "TabbedPanelsTabFocused";
	this.panelVisibleClass = "TabbedPanelsContentVisible";
	this.focusElement = null;
	this.hasFocus = false;
	this.currentTabIndex = 0;
	this.enableKeyboardNavigation = true;

	Spry.Widget.TabbedPanels.setOptions(this, opts);

	// If the defaultTab is expressed as a number/index, convert
	// it to an element.

	if (typeof (this.defaultTab) == "number")
	{
		if (this.defaultTab < 0)
			this.defaultTab = 0;
		else
		{
			var count = this.getTabbedPanelCount();
			if (this.defaultTab >= count)
				this.defaultTab = (count > 1) ? (count - 1) : 0;
		}

		this.defaultTab = this.getTabs()[this.defaultTab];
	}

	// The defaultTab property is supposed to be the tab element for the tab content
	// to show by default. The caller is allowed to pass in the element itself or the
	// element's id, so we need to convert the current value to an element if necessary.

	if (this.defaultTab)
		this.defaultTab = this.getElement(this.defaultTab);

	this.attachBehaviors();
};

Spry.Widget.TabbedPanels.prototype.getElement = function(ele)
{
	if (ele && typeof ele == "string")
		return document.getElementById(ele);
	return ele;
};

Spry.Widget.TabbedPanels.prototype.getElementChildren = function(element)
{
	var children = [];
	var child = element.firstChild;
	while (child)
	{
		if (child.nodeType == 1 /* Node.ELEMENT_NODE */)
			children.push(child);
		child = child.nextSibling;
	}
	return children;
};

Spry.Widget.TabbedPanels.prototype.addClassName = function(ele, className)
{
	if (!ele || !className || (ele.className && ele.className.search(new RegExp("\\b" + className + "\\b")) != -1))
		return;
	ele.className += (ele.className ? " " : "") + className;
};

Spry.Widget.TabbedPanels.prototype.removeClassName = function(ele, className)
{
	if (!ele || !className || (ele.className && ele.className.search(new RegExp("\\b" + className + "\\b")) == -1))
		return;
	ele.className = ele.className.replace(new RegExp("\\s*\\b" + className + "\\b", "g"), "");
};

Spry.Widget.TabbedPanels.setOptions = function(obj, optionsObj, ignoreUndefinedProps)
{
	if (!optionsObj)
		return;
	for (var optionName in optionsObj)
	{
		if (ignoreUndefinedProps && optionsObj[optionName] == undefined)
			continue;
		obj[optionName] = optionsObj[optionName];
	}
};

Spry.Widget.TabbedPanels.prototype.getTabGroup = function()
{
	if (this.element)
	{
		var children = this.getElementChildren(this.element);
		if (children.length)
			return children[0];
	}
	return null;
};

Spry.Widget.TabbedPanels.prototype.getTabs = function()
{
	var tabs = [];
	var tg = this.getTabGroup();
	if (tg)
		tabs = this.getElementChildren(tg);
	return tabs;
};

Spry.Widget.TabbedPanels.prototype.getContentPanelGroup = function()
{
	if (this.element)
	{
		var children = this.getElementChildren(this.element);
		if (children.length > 1)
			return children[1];
	}
	return null;
};

Spry.Widget.TabbedPanels.prototype.getContentPanels = function()
{
	var panels = [];
	var pg = this.getContentPanelGroup();
	if (pg)
		panels = this.getElementChildren(pg);
	return panels;
};

Spry.Widget.TabbedPanels.prototype.getIndex = function(ele, arr)
{
	ele = this.getElement(ele);
	if (ele && arr && arr.length)
	{
		for (var i = 0; i < arr.length; i++)
		{
			if (ele == arr[i])
				return i;
		}
	}
	return -1;
};

Spry.Widget.TabbedPanels.prototype.getTabIndex = function(ele)
{
	var i = this.getIndex(ele, this.getTabs());
	if (i < 0)
		i = this.getIndex(ele, this.getContentPanels());
	return i;
};

Spry.Widget.TabbedPanels.prototype.getCurrentTabIndex = function()
{
	return this.currentTabIndex;
};

Spry.Widget.TabbedPanels.prototype.getTabbedPanelCount = function(ele)
{
	return Math.min(this.getTabs().length, this.getContentPanels().length);
};

Spry.Widget.TabbedPanels.addEventListener = function(element, eventType, handler, capture)
{
	try
	{
		if (element.addEventListener)
			element.addEventListener(eventType, handler, capture);
		else if (element.attachEvent)
			element.attachEvent("on" + eventType, handler);
	}
	catch (e) {}
};

Spry.Widget.TabbedPanels.prototype.onTabClick = function(e, tab)
{
	this.showPanel(tab);

	if (e.preventDefault) e.preventDefault();
	else e.returnValue = false;
	if (e.stopPropagation) e.stopPropagation();
	else e.cancelBubble = true;

	return false;
};

Spry.Widget.TabbedPanels.prototype.onTabMouseOver = function(e, tab)
{
	this.addClassName(tab, this.tabHoverClass);
	return false;
};

Spry.Widget.TabbedPanels.prototype.onTabMouseOut = function(e, tab)
{
	this.removeClassName(tab, this.tabHoverClass);
	return false;
};

Spry.Widget.TabbedPanels.prototype.onTabFocus = function(e, tab)
{
	this.hasFocus = true;
	this.addClassName(tab, this.tabFocusedClass);
	return false;
};

Spry.Widget.TabbedPanels.prototype.onTabBlur = function(e, tab)
{
	this.hasFocus = false;
	this.removeClassName(tab, this.tabFocusedClass);
	return false;
};

Spry.Widget.TabbedPanels.ENTER_KEY = 13;
Spry.Widget.TabbedPanels.SPACE_KEY = 32;

Spry.Widget.TabbedPanels.prototype.onTabKeyDown = function(e, tab)
{
	var key = e.keyCode;
	if (!this.hasFocus || (key != Spry.Widget.TabbedPanels.ENTER_KEY && key != Spry.Widget.TabbedPanels.SPACE_KEY))
		return true;

	this.showPanel(tab);

	if (e.preventDefault) e.preventDefault();
	else e.returnValue = false;
	if (e.stopPropagation) e.stopPropagation();
	else e.cancelBubble = true;

	return false;
};

Spry.Widget.TabbedPanels.prototype.preorderTraversal = function(root, func)
{
	var stopTraversal = false;
	if (root)
	{
		stopTraversal = func(root);
		if (root.hasChildNodes())
		{
			var child = root.firstChild;
			while (!stopTraversal && child)
			{
				stopTraversal = this.preorderTraversal(child, func);
				try { child = child.nextSibling; } catch (e) { child = null; }
			}
		}
	}
	return stopTraversal;
};

Spry.Widget.TabbedPanels.prototype.addPanelEventListeners = function(tab, panel)
{
	var self = this;
	Spry.Widget.TabbedPanels.addEventListener(tab, "click", function(e) { return self.onTabClick(e, tab); }, false);
	Spry.Widget.TabbedPanels.addEventListener(tab, "mouseover", function(e) { return self.onTabMouseOver(e, tab); }, false);
	Spry.Widget.TabbedPanels.addEventListener(tab, "mouseout", function(e) { return self.onTabMouseOut(e, tab); }, false);

	if (this.enableKeyboardNavigation)
	{
		// XXX: IE doesn't allow the setting of tabindex dynamically. This means we can't
		// rely on adding the tabindex attribute if it is missing to enable keyboard navigation
		// by default.

		// Find the first element within the tab container that has a tabindex or the first
		// anchor tag.
		
		var tabIndexEle = null;
		var tabAnchorEle = null;

		this.preorderTraversal(tab, function(node) {
			if (node.nodeType == 1 /* NODE.ELEMENT_NODE */)
			{
				var tabIndexAttr = tab.attributes.getNamedItem("tabindex");
				if (tabIndexAttr)
				{
					tabIndexEle = node;
					return true;
				}
				if (!tabAnchorEle && node.nodeName.toLowerCase() == "a")
					tabAnchorEle = node;
			}
			return false;
		});

		if (tabIndexEle)
			this.focusElement = tabIndexEle;
		else if (tabAnchorEle)
			this.focusElement = tabAnchorEle;

		if (this.focusElement)
		{
			Spry.Widget.TabbedPanels.addEventListener(this.focusElement, "focus", function(e) { return self.onTabFocus(e, tab); }, false);
			Spry.Widget.TabbedPanels.addEventListener(this.focusElement, "blur", function(e) { return self.onTabBlur(e, tab); }, false);
			Spry.Widget.TabbedPanels.addEventListener(this.focusElement, "keydown", function(e) { return self.onTabKeyDown(e, tab); }, false);
		}
	}
};

Spry.Widget.TabbedPanels.prototype.showPanel = function(elementOrIndex)
{
	var tpIndex = -1;
	
	if (typeof elementOrIndex == "number")
		tpIndex = elementOrIndex;
	else // Must be the element for the tab or content panel.
		tpIndex = this.getTabIndex(elementOrIndex);
	
	if (!tpIndex < 0 || tpIndex >= this.getTabbedPanelCount())
		return;

	var tabs = this.getTabs();
	var panels = this.getContentPanels();

	var numTabbedPanels = Math.max(tabs.length, panels.length);

	for (var i = 0; i < numTabbedPanels; i++)
	{
		if (i != tpIndex)
		{
			if (tabs[i])
				this.removeClassName(tabs[i], this.tabSelectedClass);
			if (panels[i])
			{
				this.removeClassName(panels[i], this.panelVisibleClass);
				panels[i].style.display = "none";
			}
		}
	}

	this.addClassName(tabs[tpIndex], this.tabSelectedClass);
	this.addClassName(panels[tpIndex], this.panelVisibleClass);
	panels[tpIndex].style.display = "block";

	this.currentTabIndex = tpIndex;
};

Spry.Widget.TabbedPanels.prototype.attachBehaviors = function(element)
{
	var tabs = this.getTabs();
	var panels = this.getContentPanels();
	var panelCount = this.getTabbedPanelCount();

	for (var i = 0; i < panelCount; i++)
		this.addPanelEventListeners(tabs[i], panels[i]);

	this.showPanel(this.defaultTab);
};

// Constructor for Menu Bar
// element should be an ID of an unordered list (<ul> tag)
// preloadImage1 and preloadImage2 are images for the rollover state of a menu
Spry.Widget.MenuBar = function(element, opts)
{
	this.init(element, opts);
};

Spry.Widget.MenuBar.prototype.init = function(element, opts)
{
	this.element = this.getElement(element);

	// represents the current (sub)menu we are operating on
	this.currMenu = null;
	this.showDelay = 250;
	this.hideDelay = 600;
	if(typeof document.getElementById == 'undefined' || (navigator.vendor == 'Apple Computer, Inc.' && typeof window.XMLHttpRequest == 'undefined') || (Spry.is.ie && typeof document.uniqueID == 'undefined'))
	{
		// bail on older unsupported browsers
		return;
	}

	// Fix IE6 CSS images flicker
	if (Spry.is.ie && Spry.is.version < 7){
		try {
			document.execCommand("BackgroundImageCache", false, true);
		} catch(err) {}
	}

	this.upKeyCode = Spry.Widget.MenuBar.KEY_UP;
	this.downKeyCode = Spry.Widget.MenuBar.KEY_DOWN;
	this.leftKeyCode = Spry.Widget.MenuBar.KEY_LEFT;
	this.rightKeyCode = Spry.Widget.MenuBar.KEY_RIGHT;
	this.escKeyCode = Spry.Widget.MenuBar.KEY_ESC;

	this.hoverClass = 'MenuBarItemHover';
	this.subHoverClass = 'MenuBarItemSubmenuHover';
	this.subVisibleClass ='MenuBarSubmenuVisible';
	this.hasSubClass = 'MenuBarItemSubmenu';
	this.activeClass = 'MenuBarActive';
	this.isieClass = 'MenuBarItemIE';
	this.verticalClass = 'MenuBarVertical';
	this.horizontalClass = 'MenuBarHorizontal';
	this.enableKeyboardNavigation = true;

	this.hasFocus = false;
	// load hover images now
	if(opts)
	{
		for(var k in opts)
		{
			if (typeof this[k] == 'undefined')
			{
				var rollover = new Image;
				rollover.src = opts[k];
			}
		}
		Spry.Widget.MenuBar.setOptions(this, opts);
	}

	// safari doesn't support tabindex
	if (Spry.is.safari)
		this.enableKeyboardNavigation = false;

	if(this.element)
	{
		this.currMenu = this.element;
		var items = this.element.getElementsByTagName('li');
		for(var i=0; i<items.length; i++)
		{
			if (i > 0 && this.enableKeyboardNavigation)
				items[i].getElementsByTagName('a')[0].tabIndex='-1';

			this.initialize(items[i], element);
			if(Spry.is.ie)
			{
				this.addClassName(items[i], this.isieClass);
				items[i].style.position = "static";
			}
		}
		if (this.enableKeyboardNavigation)
		{
			var self = this;
			this.addEventListener(document, 'keydown', function(e){self.keyDown(e); }, false);
		}

		if(Spry.is.ie)
		{
			if(this.hasClassName(this.element, this.verticalClass))
			{
				this.element.style.position = "relative";
			}
			var linkitems = this.element.getElementsByTagName('a');
			for(var i=0; i<linkitems.length; i++)
			{
				linkitems[i].style.position = "relative";
			}
		}
	}
};
Spry.Widget.MenuBar.KEY_ESC = 27;
Spry.Widget.MenuBar.KEY_UP = 38;
Spry.Widget.MenuBar.KEY_DOWN = 40;
Spry.Widget.MenuBar.KEY_LEFT = 37;
Spry.Widget.MenuBar.KEY_RIGHT = 39;

Spry.Widget.MenuBar.prototype.getElement = function(ele)
{
	if (ele && typeof ele == "string")
		return document.getElementById(ele);
	return ele;
};

Spry.Widget.MenuBar.prototype.hasClassName = function(ele, className)
{
	if (!ele || !className || !ele.className || ele.className.search(new RegExp("\\b" + className + "\\b")) == -1)
	{
		return false;
	}
	return true;
};

Spry.Widget.MenuBar.prototype.addClassName = function(ele, className)
{
	if (!ele || !className || this.hasClassName(ele, className))
		return;
	ele.className += (ele.className ? " " : "") + className;
};

Spry.Widget.MenuBar.prototype.removeClassName = function(ele, className)
{
	if (!ele || !className || !this.hasClassName(ele, className))
		return;
	ele.className = ele.className.replace(new RegExp("\\s*\\b" + className + "\\b", "g"), "");
};

// addEventListener for Menu Bar
// attach an event to a tag without creating obtrusive HTML code
Spry.Widget.MenuBar.prototype.addEventListener = function(element, eventType, handler, capture)
{
	try
	{
		if (element.addEventListener)
		{
			element.addEventListener(eventType, handler, capture);
		}
		else if (element.attachEvent)
		{
			element.attachEvent('on' + eventType, handler);
		}
	}
	catch (e) {}
}

// createIframeLayer for Menu Bar
// creates an IFRAME underneath a menu so that it will show above form controls and ActiveX
Spry.Widget.MenuBar.prototype.createIframeLayer = function(menu)
{
	var layer = document.createElement('iframe');
	layer.tabIndex = '-1';
	layer.src = 'javascript:""';
	layer.frameBorder = '0';
	layer.scrolling = 'no';
	menu.parentNode.appendChild(layer);
	
	layer.style.left = menu.offsetLeft + 'px';
	layer.style.top = menu.offsetTop + 'px';
	layer.style.width = menu.offsetWidth + 'px';
	layer.style.height = menu.offsetHeight + 'px';
};

// removeIframeLayer for Menu Bar
// removes an IFRAME underneath a menu to reveal any form controls and ActiveX
Spry.Widget.MenuBar.prototype.removeIframeLayer =  function(menu)
{
	var layers = menu.parentNode.getElementsByTagName('iframe');
	while(layers.length > 0)
	{
		layers[0].parentNode.removeChild(layers[0]);
	}
};

// clearMenus for Menu Bar
// root is the top level unordered list (<ul> tag)
Spry.Widget.MenuBar.prototype.clearMenus = function(root)
{
	var menus = root.getElementsByTagName('ul');
	for(var i=0; i<menus.length; i++)
		this.hideSubmenu(menus[i]);

	this.removeClassName(this.element, this.activeClass);
};

// bubbledTextEvent for Menu Bar
// identify bubbled up text events in Safari so we can ignore them
Spry.Widget.MenuBar.prototype.bubbledTextEvent = function()
{
	return Spry.is.safari && (event.target == event.relatedTarget.parentNode || (event.eventPhase == 3 && event.target.parentNode == event.relatedTarget));
};

// showSubmenu for Menu Bar
// set the proper CSS class on this menu to show it
Spry.Widget.MenuBar.prototype.showSubmenu = function(menu)
{
	if(this.currMenu)
	{
		this.clearMenus(this.currMenu);
		this.currMenu = null;
	}
	
	if(menu)
	{
		this.addClassName(menu, this.subVisibleClass);
		if(typeof document.all != 'undefined' && !Spry.is.opera && navigator.vendor != 'KDE')
		{
			if(!this.hasClassName(this.element, this.horizontalClass) || menu.parentNode.parentNode != this.element)
			{
				menu.style.top = menu.parentNode.offsetTop + 'px';
			}
		}
		if(Spry.is.ie && Spry.is.version < 7)
		{
			this.createIframeLayer(menu);
		}
	}
	this.addClassName(this.element, this.activeClass);
};

// hideSubmenu for Menu Bar
// remove the proper CSS class on this menu to hide it
Spry.Widget.MenuBar.prototype.hideSubmenu = function(menu)
{
	if(menu)
	{
		this.removeClassName(menu, this.subVisibleClass);
		if(typeof document.all != 'undefined' && !Spry.is.opera && navigator.vendor != 'KDE')
		{
			menu.style.top = '';
			menu.style.left = '';
		}
		this.removeIframeLayer(menu);
	}
};

// initialize for Menu Bar
// create event listeners for the Menu Bar widget so we can properly
// show and hide submenus
Spry.Widget.MenuBar.prototype.initialize = function(listitem, element)
{
	var opentime, closetime;
	var link = listitem.getElementsByTagName('a')[0];
	var submenus = listitem.getElementsByTagName('ul');
	var menu = (submenus.length > 0 ? submenus[0] : null);

	if(menu)
		this.addClassName(link, this.hasSubClass);

	if(!Spry.is.ie)
	{
		// define a simple function that comes standard in IE to determine
		// if a node is within another node
		listitem.contains = function(testNode)
		{
			// this refers to the list item
			if(testNode == null)
				return false;

			if(testNode == this)
				return true;
			else
				return this.contains(testNode.parentNode);
		};
	}

	// need to save this for scope further down
	var self = this;
	this.addEventListener(listitem, 'mouseover', function(e){self.mouseOver(listitem, e);}, false);
	this.addEventListener(listitem, 'mouseout', function(e){if (self.enableKeyboardNavigation) self.clearSelection(); self.mouseOut(listitem, e);}, false);

	if (this.enableKeyboardNavigation)
	{
		this.addEventListener(link, 'blur', function(e){self.onBlur(listitem);}, false);
		this.addEventListener(link, 'focus', function(e){self.keyFocus(listitem, e);}, false);
	}
};
Spry.Widget.MenuBar.prototype.keyFocus = function (listitem, e)
{
	this.lastOpen = listitem.getElementsByTagName('a')[0];
	this.addClassName(this.lastOpen, listitem.getElementsByTagName('ul').length > 0 ? this.subHoverClass : this.hoverClass);
	this.hasFocus = true;
};
Spry.Widget.MenuBar.prototype.onBlur = function (listitem)
{
	this.clearSelection(listitem);
};
Spry.Widget.MenuBar.prototype.clearSelection = function(el){
	//search any intersection with the current open element
	if (!this.lastOpen)
		return;

	if (el)
	{
		el = el.getElementsByTagName('a')[0];
		
		// check children
		var item = this.lastOpen;
		while (item != this.element)
		{
			var tmp = el;
			while (tmp != this.element)
			{
				if (tmp == item)
					return;
				try{
					tmp = tmp.parentNode;
				}catch(err){break;}
			}
			item = item.parentNode;
		}
	}
	var item = this.lastOpen;
	while (item != this.element)
	{
		this.hideSubmenu(item.parentNode);
		var link = item.getElementsByTagName('a')[0];
		this.removeClassName(link, this.hoverClass);
		this.removeClassName(link, this.subHoverClass);
		item = item.parentNode;
	}
	this.lastOpen = false;
};
Spry.Widget.MenuBar.prototype.keyDown = function (e)
{
	if (!this.hasFocus)
		return;

	if (!this.lastOpen)
	{
		this.hasFocus = false;
		return;
	}

	var e = e|| event;
	var listitem = this.lastOpen.parentNode;
	var link = this.lastOpen;
	var submenus = listitem.getElementsByTagName('ul');
	var menu = (submenus.length > 0 ? submenus[0] : null);
	var hasSubMenu = (menu) ? true : false;

	Spry.Widget.MenuBar.stopPropagation(e);

	var opts = [listitem, menu, null, this.getSibling(listitem, 'previousSibling'), this.getSibling(listitem, 'nextSibling')];

	if (!opts[3])
		opts[2] = (listitem.parentNode.parentNode.nodeName.toLowerCase() == 'li')?listitem.parentNode.parentNode:null;


	var found = 0;
	switch (e.keyCode){
		case this.upKeyCode:
			found = this.getElementForKey(opts, 'y', 1);
			break;
		case this.downKeyCode:
			found = this.getElementForKey(opts, 'y', -1);
			break;
		case this.leftKeyCode:
			found = this.getElementForKey(opts, 'x', 1);
			break;
		case this.rightKeyCode:
			found = this.getElementForKey(opts, 'x', -1);
			break;
		case this.escKeyCode:
		case 9:
			this.clearSelection();
			this.hasFocus = false;
		default: return;
	}
	switch (found)
	{
		case 0: return;
		case 1:
			//subopts
			this.mouseOver(listitem, e);
			break;
		case 2:
			//parent
			this.mouseOut(opts[2], e);
			break;
		case 3:
		case 4:
			// left - right
			this.removeClassName(link, hasSubMenu ? this.subHoverClass : this.hoverClass);
			break;
	}
	var link = opts[found].getElementsByTagName('a')[0];
	if (opts[found].nodeName.toLowerCase() == 'ul')
		opts[found] = opts[found].getElementsByTagName('li')[0];

	this.addClassName(link, opts[found].getElementsByTagName('ul').length > 0 ? this.subHoverClass : this.hoverClass);
	this.lastOpen = link;
	opts[found].getElementsByTagName('a')[0].focus();
};
Spry.Widget.MenuBar.prototype.mouseOver = function (listitem, e)
{
	var link = listitem.getElementsByTagName('a')[0];
	var submenus = listitem.getElementsByTagName('ul');
	var menu = (submenus.length > 0 ? submenus[0] : null);
	var hasSubMenu = (menu) ? true : false;
	if (this.enableKeyboardNavigation)
		this.clearSelection(listitem);

	if(this.bubbledTextEvent())
	{
		// ignore bubbled text events
		return;
	}

	if (listitem.closetime)
		clearTimeout(listitem.closetime);

	if(this.currMenu == listitem)
	{
		this.currMenu = null;
	}

	// move the focus too
	if (this.hasFocus)
		link.focus();

	// show menu highlighting
	this.addClassName(link, hasSubMenu ? this.subHoverClass : this.hoverClass);
	this.lastOpen = link;
	if(menu && !this.hasClassName(menu, this.subHoverClass))
	{
		var self = this;
		listitem.opentime = window.setTimeout(function(){self.showSubmenu(menu);}, this.showDelay);
	}
};
Spry.Widget.MenuBar.prototype.mouseOut = function (listitem, e)
{
	var link = listitem.getElementsByTagName('a')[0];
	var submenus = listitem.getElementsByTagName('ul');
	var menu = (submenus.length > 0 ? submenus[0] : null);
	var hasSubMenu = (menu) ? true : false;
	if(this.bubbledTextEvent())
	{
		// ignore bubbled text events
		return;
	}

	var related = (typeof e.relatedTarget != 'undefined' ? e.relatedTarget : e.toElement);
	if(!listitem.contains(related))
	{
		if (listitem.opentime) 
			clearTimeout(listitem.opentime);
		this.currMenu = listitem;

		// remove menu highlighting
		this.removeClassName(link, hasSubMenu ? this.subHoverClass : this.hoverClass);
		if(menu)
		{
			var self = this;
			listitem.closetime = window.setTimeout(function(){self.hideSubmenu(menu);}, this.hideDelay);
		}
		if (this.hasFocus)
			link.blur();
	}
};
Spry.Widget.MenuBar.prototype.getSibling = function(element, sibling)
{
	var child = element[sibling];
	while (child && child.nodeName.toLowerCase() !='li')
		child = child[sibling];

	return child;
};
Spry.Widget.MenuBar.prototype.getElementForKey = function(els, prop, dir)
{
	var found = 0;
	var rect = Spry.Widget.MenuBar.getPosition;
	var ref = rect(els[found]);

	var hideSubmenu = false;
	//make the subelement visible to compute the position
	if (els[1] && !this.hasClassName(els[1], this.MenuBarSubmenuVisible))
	{
		els[1].style.visibility = 'hidden';
		this.showSubmenu(els[1]);
		hideSubmenu = true;
	}

	for (var i = 0; i < els.length; i++)
		if (els[i])
		{
			var tmp = rect(els[i]); 
			if ( (dir * tmp[prop]) < (dir * ref[prop]))
			{
				ref = tmp;
				found = i;
			}
		}

	// hide back the submenu
	if (els[1] && hideSubmenu){
		this.hideSubmenu(els[1]);
		els[1].style.visibility =  '';
	}

	return found;
};
Spry.Widget.MenuBar.camelize = function(str)
{
	if (str.indexOf('-') == -1){
		return str;	
	}
	var oStringList = str.split('-');
	var isFirstEntry = true;
	var camelizedString = '';

	for(var i=0; i < oStringList.length; i++)
	{
		if(oStringList[i].length>0)
		{
			if(isFirstEntry)
			{
				camelizedString = oStringList[i];
				isFirstEntry = false;
			}
			else
			{
				var s = oStringList[i];
				camelizedString += s.charAt(0).toUpperCase() + s.substring(1);
			}
		}
	}

	return camelizedString;
};

Spry.Widget.MenuBar.getStyleProp = function(element, prop)
{
	var value;
	try
	{
		if (element.style)
			value = element.style[Spry.Widget.MenuBar.camelize(prop)];

		if (!value)
			if (document.defaultView && document.defaultView.getComputedStyle)
			{
				var css = document.defaultView.getComputedStyle(element, null);
				value = css ? css.getPropertyValue(prop) : null;
			}
			else if (element.currentStyle) 
			{
					value = element.currentStyle[Spry.Widget.MenuBar.camelize(prop)];
			}
	}
	catch (e) {}

	return value == 'auto' ? null : value;
};
Spry.Widget.MenuBar.getIntProp = function(element, prop)
{
	var a = parseInt(Spry.Widget.MenuBar.getStyleProp(element, prop),10);
	if (isNaN(a))
		return 0;
	return a;
};

Spry.Widget.MenuBar.getPosition = function(el, doc)
{
	doc = doc || document;
	if (typeof(el) == 'string') {
		el = doc.getElementById(el);
	}

	if (!el) {
		return false;
	}

	if (el.parentNode === null || Spry.Widget.MenuBar.getStyleProp(el, 'display') == 'none') {
		//element must be visible to have a box
		return false;
	}

	var ret = {x:0, y:0};
	var parent = null;
	var box;

	if (el.getBoundingClientRect) { // IE
		box = el.getBoundingClientRect();
		var scrollTop = doc.documentElement.scrollTop || doc.body.scrollTop;
		var scrollLeft = doc.documentElement.scrollLeft || doc.body.scrollLeft;
		ret.x = box.left + scrollLeft;
		ret.y = box.top + scrollTop;
	} else if (doc.getBoxObjectFor) { // gecko
		box = doc.getBoxObjectFor(el);
		ret.x = box.x;
		ret.y = box.y;
	} else { // safari/opera
		ret.x = el.offsetLeft;
		ret.y = el.offsetTop;
		parent = el.offsetParent;
		if (parent != el) {
			while (parent) {
				ret.x += parent.offsetLeft;
				ret.y += parent.offsetTop;
				parent = parent.offsetParent;
			}
		}
		// opera & (safari absolute) incorrectly account for body offsetTop
		if (Spry.is.opera || Spry.is.safari && Spry.Widget.MenuBar.getStyleProp(el, 'position') == 'absolute')
			ret.y -= doc.body.offsetTop;
	}
	if (el.parentNode)
			parent = el.parentNode;
	else
		parent = null;
	if (parent.nodeName){
		var cas = parent.nodeName.toUpperCase();
		while (parent && cas != 'BODY' && cas != 'HTML') {
			cas = parent.nodeName.toUpperCase();
			ret.x -= parent.scrollLeft;
			ret.y -= parent.scrollTop;
			if (parent.parentNode)
				parent = parent.parentNode;
			else
				parent = null;
		}
	}
	// adjust the margin
	var gi = Spry.Widget.MenuBar.getIntProp;
	var btw = gi(el, "margin-top");
	var blw = gi(el, "margin-left");
	ret.x -= blw;
	ret.y -= btw;
	return ret;
};
Spry.Widget.MenuBar.stopPropagation = function(ev)
{
	if (ev.stopPropagation)
		ev.stopPropagation();
	else
		ev.cancelBubble = true;
};
Spry.Widget.MenuBar.setOptions = function(obj, optionsObj, ignoreUndefinedProps)
{
	if (!optionsObj)
		return;
	for (var optionName in optionsObj)
	{
		if (ignoreUndefinedProps && optionsObj[optionName] == undefined)
			continue;
		obj[optionName] = optionsObj[optionName];
	}
};