var statusDiv = document.getElementById('statusbar');
var computerDivPrefix = 'computer';

var classBase = 'computerbit';
var classNoInfo = 'computerbit-noinfo';
var classNoResponse = 'computerbit-noresponse';
var classAvailable = 'computerbit-available';
var classBusy = 'computerbit-busy';

var computersUpdateArray;


/*
// RECORD TYPES
define('AVAIBILITY_TYPE_OFFLINE',	0);
define('AVAIBILITY_TYPE_AVAILABLE',	1);
define('AVAIBILITY_TYPE_BUSY',		2);
*/

function updatePcStatus(regionId) {

	var sUrl = 'regionstats.php?id=' + regionId;
	
	statusDiv.innerHTML = "Updating..."; //SUCCESS
	
	var currentStatusClass = classNoResponse;
	
	var callback = {
	
	success: function(o) {
			//statusDiv.innerHTML = "AJAX Works"; //SUCCESS
			
			computersUpdateArray = eval("(" + o.responseText + ")");
			
			for (i=0; i < computersUpdateArray.length; i++)
			{
				var computerInfo = computersUpdateArray[i];
				//statusDiv.innerHTML = dump(computerInfo);
				computerDiv = document.getElementById(computerDivPrefix + computerInfo['id']);
				if(computerDiv) {
					//if (isNull(computerInfo['laststatus'])) {
					//	currentStatusClass = classNoResponse;
					//} else {
						switch (parseInt(computerInfo['laststatus'])) {
						case 0:
							currentStatusClass = classNoResponse;
							break;
						case 1:
							currentStatusClass = classAvailable;
							break;
						case 2:
							currentStatusClass = classBusy;
							break;
						default:
							currentStatusClass = classNoInfo;
							break;
						}
					//}
					computerDiv.className = classBase + ' ' + currentStatusClass;
				}
			} 
			statusDiv.innerHTML = "Updated Map to Live Status as of " + currentTime();
		},
failure: function(o) {
			statusDiv.innerHTML = "AJAX Time out..."; //FAIL
		}
	} 

	var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

	mapUpdateTimer=setTimeout('updatePcStatus(' + regionId + ')', 30 * 1000);
}

function editComputerDetails(ignitor, computerId, computerName, x, y) {
	//alert(computerName);
	var idDiv = document.getElementById('id');
	var computerNameDiv = document.getElementById('computer_name');
	var xDiv = document.getElementById('x');
	var yDiv = document.getElementById('y');
	var warningDiv = document.getElementById('editWarning');
	var addNewInsteadLinkDiv = document.getElementById('addNewInsteadLink');
	idDiv.value = computerId;
	computerNameDiv.value = computerName;
	xDiv.value = x;
	yDiv.value = y;
	warningDiv.style.display = '';
	addNewInsteadLinkDiv.style.display = '';
}

function createNewInstead() {
	var idDiv = document.getElementById('id');
	var warningDiv = document.getElementById('editWarning');
	warningDiv.style.display = 'none';
	idDiv.value = computerId;
	return false;
}

function getCursorXY(e) {
	document.getElementById('x').value = e.pageX;
	document.getElementById('y').value = e.pageY;
	//document.getElementById('x').value = (window.Event) ? e.pageX : event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	//document.getElementById('y').value = (window.Event) ? e.pageY : event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
}

function currentTime() {
	var d = new Date();

	var curr_hour = d.getHours();
	var curr_min = d.getMinutes();

	return (curr_hour + ":" + curr_min);
}


function findPos(obj){
	var posX = obj.offsetLeft;var posY = obj.offsetTop;
	while(obj.offsetParent){
		posX=posX+obj.offsetParent.offsetLeft;
		posY=posY+obj.offsetParent.offsetTop;
		if(obj==document.getElementsByTagName('body')[0]){break}
		else{obj=obj.offsetParent;}
	}
	return [posX,posY]
}



/**
* Function : dump()
* Arguments: The data - array,hash(associative array),object
*    The level - OPTIONAL
* Returns  : The textual representation of the array.
* This function was inspired by the print_r function of PHP.
* This will accept some data as the argument and return a
* text that will be a more readable version of the
* array/hash/object that is given.
*/
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
} 

