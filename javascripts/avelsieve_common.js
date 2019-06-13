function el(id) {
  if (document.getElementById) {
    return document.getElementById(id);
  }
  return false;
}

function ShowDiv(divname) {
  if(el(divname)) {
    el(divname).style.display = "";
  }
  return false;
}
function HideDiv(divname) {
  if(el(divname)) {
    el(divname).style.display = "none";
  }
}
