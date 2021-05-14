console.log("ClipTable : datatables_handlers.js loaded");

jQuery(document).ready(function($) {

    var mainTable = $('#main_table').DataTable({
        ajax: {
        url: datatablesajax.url + '?action=getitemsfordatatables'
        },
        rowId: 'id',
        "autoWidth": false,
        columns: [
            // { data: 'id', visible: false},
            { data: 'title' },
            { data: 'description'},
            { data: 'details'},
        //   { data: 'actions' , className: '15px'},
            {
            sortable: false,
            "render": function ( data, type, full, meta ) {
                return "<button class='copyBtn btn-lg'P><i class='far fa-clipboard'></i></button>";
                }
            }
        ],
    });


    $('#main_table').on( 'click', '.copyBtn', function () {
      

        //Get Current Row Object
        var $row = $(this).closest('tr');
        var $rowData = mainTable.row($row).data();

        //Access the Details Column for the row
        var $detailsCell = $rowData.details;
        console.log( $detailsCell);
        textToClipboard($detailsCell);
        
        //This gets the row number, not the item ID
        // var id = $(this).closest('tr').index()
        // alert( 'Clicked row id '+id );
        // var $cell = $row.details;

        // console.log( "cell "+ $cell );

        // cant get element to work to be a 'node
        // var $row = $(this).closest('tr');
        // var element = $row.find(".copyItem");
        // console.log("element:"+element);

        // $row.find(".copyItem").focus();

        // element.focus();
        // element.select()
    

    } );

    function textToClipboard (text) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);

        alert("Copied: "+text);
    }

});

