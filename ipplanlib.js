/* return an array of form values */
function getElements(frm) {
   var el=[];
   var tmp;
   var newcnt=document.forms[frm].elements.length;

   for (var i=0; i < newcnt; i++) {
      if (document.forms[frm].elements[i].type == "text" || 
          document.forms[frm].elements[i].type == "textarea" || 
          document.forms[frm].elements[i].type == "select-one") {
         tmp=document.forms[frm].elements[i].name;
         el[tmp]=document.forms[frm].elements[i].value;
      }
   }

   return el;
}

/* set an array of form values 
   this function is safe as we cannot set more elements than are in the form
   and only certain type of fields are set */
function setElements(el, frm) {
   var newcnt=document.forms[frm].elements.length;
   var tmp;
   var undefined;

   for (var i=0; i < newcnt; i++) {
      if (document.forms[frm].elements[i].type == "text" || 
          document.forms[frm].elements[i].type == "textarea" || 
          document.forms[frm].elements[i].type == "select-one") {
         tmp=document.forms[frm].elements[i].name;
         if (el[tmp] != undefined) {
            document.forms[frm].elements[i].value=el[tmp];
         }
      }
   }
}
