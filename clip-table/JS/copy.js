console.log("ClipTable : Copy.js loaded");

//Select All Copy Buttons
let btns = document.querySelectorAll('.copyBtn');

//Add Event Listener to each Copy Button (as ran after loading)
for (button of btns) {
  (function(button) {
    button.addEventListener('click', function() {  

      //Uses the ID set on the button from a static table, not Datatables. 
      var buttonID = button.id;
      copyButton(buttonID); 

    });
  })(button);
};

function copyButton(buttonID){  
  var buttonID = buttonID;
  //Select the Cell to copy based on ID name of cell , add button ID
  let element = document.querySelector('#copyItem-'+buttonID);
  
  //console.log("Selected Text to Copy: "+element.innerHTML);
  
  selectNode(element);
  document.execCommand('copy')
  alert("Copied: "+element.innerHTML)
}

function selectNode(node){
  let range  =  document.createRange();
  range.selectNodeContents(node)
  let select =  window.getSelection()
  select.removeAllRanges()
  select.addRange(range)
}
