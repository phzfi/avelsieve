function avelsieveHandleOptionsSelect(selectElement, num) {
    var selectedOption;
    selectedOption =  selectElement.options[selectElement.selectedIndex].value;
    if(selectedOption) {
        if(selectedOption == 'mvposition') {
            var position; 
            position = prompt('Which Position?'); 
            if(position) {
                document.forms[0].position.value = position;
            } else {
                return false;
            }
        }
        document.forms[0].submit();
    }
    return true;
}
