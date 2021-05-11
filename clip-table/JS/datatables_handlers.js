console.log("ClipTable : datatables_handlers.js loaded");

jQuery(document).ready(function($) {
 
    var jobtable = $('#main_table').DataTable({
      ajax: {
        url: datatablesajax.url + '?action=getitemsfordatatables'
      },
      columns: [
          { data: 'title' },
          { data: 'description' },
          { data: 'details' },
      ]
    });
  });