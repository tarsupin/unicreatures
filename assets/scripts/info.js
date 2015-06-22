var autoRemove = setTimeout("removePopup('info-display')", 10000);
var keep = false;

function removePopup(display)
{
	if(popup = document.getElementById(display))
		popup.parentNode.removeChild(popup);
}

function removePopupClick(e, display)
{
	e = e ? e : window.event;
	if(e.stopPropagation)
		e.stopPropagation();
	else
		e.cancelBubble = true;
	removePopup(display);
}

function createPopup(display)
{
	popup = document.getElementById(display);
	if(!popup)
	{
		popup = document.createElement("div");
		popup.setAttribute("id", display);
		if (popup.addEventListener)
			popup.addEventListener("click", function(e) { removePopupClick(e, display); });
		else if (popup.attachEvent)
			popup.attachEvent("onclick", function(e) { removePopupClick(e, display); });
		document.body.appendChild(popup);
	}
	window.clearTimeout(autoRemove);
	if(!keep)
		autoRemove = setTimeout("removePopup('" + display + "')", 10000);
	
	return popup;
}

function openMysteryBox()
{
	keep = false;
	getAjax("", "open-mystery-box", "openMysteryBoxResponse");
}

function viewAchievements(family)
{
	keep = false;
	getAjax("", "view-achievements", "viewResponse", "family=" + family);
}

function viewExplore(zone)
{
	keep = true;
	getAjax("", "view-explore", "viewResponse", "zone=" + zone);
}

function viewInventory()
{
	getAjax("", "view-inventory", "viewResponse");
}

function openMysteryBoxResponse(response)
{
	if(!response)
		return;
	
	response = JSON.parse(response);	
	
	if(typeof response['count'] !== "undefined")
	{
		response['title'] = response['count'] + " " + response['title'];
		if(response['count'] > 1)
			if(response['type'] == "alchemy" || response['type'] == "coins" || response['type'] == "components")
				response['title'] += "s";
	}
	else if(typeof response['span'] !== "undefined")
		response['title'] = response['span'] + "h " + response['title'];
	
	if(typeof response['total'] !== "undefined" && document.getElementById(response['type'] + "-count"))
		document.getElementById(response['type'] + "-count").innerHTML = response['total'];
	
	if(response['remaining'] == 0)
	{
		var block = document.getElementById("mystery-block");
		block.parentNode.removeChild(block);
	}
	else if(document.getElementById("mystery-count"))
		document.getElementById("mystery-count").innerHTML = response['remaining'];
		
	var popup = createPopup("info-display");
	popup.innerHTML = '<img src="' + response['image'] + '" /><br/>' + response['title'];
}

function viewResponse(response)
{
	if(!response)
		return;
	
	var popup = createPopup("info-display" + (keep ? "-big" : ""));
	popup.innerHTML = (keep ? '<div class="uc-note" style="float:right;">Click anywhere on this popup to close.</div><br/>' : '') + response;
}