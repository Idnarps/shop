function getFieldValue(rowId,fieldId)
{
	rowId = rowId.substring(3);
	var result;

	var cell=document.getElementById('cell_'+rowId+'_'+fieldId); 

	if(cell.value != undefined)
		result = cell.value;
	else result = cell.innerHTML;
	if (result == '&nbsp;') {
	  result = '';
	}
	return result;
}

function getColIndex(colName)
{
	var j=0;
	var arr = document.getElementsByTagName("td"); 
	var row = document.getElementById('tr_0');
	if(row!='undefined')
	{
		var childs = row.childNodes;
		for(var i=0;i<childs.length;i++)
		{
			var attr = childs[i].attributes;
			if(attr!=null)
			{
				if(attr.getNamedItem('name').value==colName) return j;
				else j++;
			}
		}
	}
	return -1;
}

function CloseWindow(windowId)
{
	document.getElementById(windowId).style.display = "none";
}
if(!window.GlobalBgColor) {
	var GlobalBgColor;
}
function ChangeColorOnOver(obj) {
	GlobalBgColor = obj.style.backgroundImage;
	obj.style.backgroundImage = "URL('/class/List/skins/Jira/images/hover_bg.png')";
	//$(obj).css("background-image", "URL('/class/List/skins/Jira/images/hover_bg.png')");
	//alert($(obj).css("background-image"));
}
function ChangeColorOnOut(obj) {
	obj.style.backgroundImage = GlobalBgColor;
}
