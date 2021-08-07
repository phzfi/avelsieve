function checkOther(id){
	for(var i=0;i<document.addrule.length;i++){
		if(document.addrule.elements[i].value == id){
			document.addrule.elements[i].checked = true;
		}
	}
}
function ToggleShowDiv(divname) {
  if(el(divname)) {
    if(el(divname).style.display == "none") {
      el(divname).style.display = "";
	} else {
      el(divname).style.display = "none";
	}
  }	
}
function ToggleShowDivWithImg(divname,scriptaculous) {
  if(el(divname)) {
    img_name = divname + '_img';
    if(el(divname).style.display == "none") {
      if(scriptaculous == 1) {
         Effect.toggle(divname, 'slide');
      } else {
         el(divname).style.display = "";
      }
	  if(document[img_name]) {
	  	document[img_name].src = "images/opentriangle.gif";
	  }	
	  if(el('divstate_' + divname )) {
	  	el('divstate_'+divname).value = 1;
	  }
	} else {
      if(scriptaculous == 1) {
         Effect.toggle(divname, 'slide');
      } else {
         el(divname).style.display = "none";
      }
	  if(document[img_name]) {
	  	document[img_name].src = "images/triangle.gif";
	  }	
	  if(el('divstate_'+divname)) {
	  	el('divstate_'+divname).value = 0;
	  }
	}
  }	
}
function alsoCheck(me,group) {
    var checked = me.checked; 
    if (checked) for (var i = 1; i < arguments.length; i++) { 
        var ck = document.getElementById(arguments[i]); 
        if (ck) ck.checked = true; 
    }
}
function alsoUnCheck(me,group) {
    var checked = me.checked; 
    if (checked == false) for (var i = 1; i < arguments.length; i++) { 
        var ck = document.getElementById(arguments[i]); 
        if (ck) ck.checked = false; 
    }
}
function radioCheck(me,group) {
    var checked = me.checked; 
    if (checked) for (var i = 1; i < arguments.length; i++) { 
        var ck = document.getElementById(arguments[i]); 
        if (ck) ck.checked = false; 
    } else {
        return;
    }
    me.checked = checked; // checkbox action 
    //me.checked = true; // radiobox action 
}
