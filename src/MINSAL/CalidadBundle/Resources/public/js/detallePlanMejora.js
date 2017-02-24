$(document).ready(function () {
    // Grid de criterios
    $("#jqGrid").jqGrid({
        url: Routing.generate('calidad_planmejora_get_criterios'),
        datatype: "json",
        colModel: [
            {label: 'ID', name: 'CustomerID', key: true, width: 75},
            {label: 'Company Name', name: 'CompanyName', width: 150},
            {label: 'Contact Name', name: 'ContactName', width: 150},
            {label: 'Phone', name: 'Phone', width: 150},
            {label: 'City', name: 'City', width: 150}
        ],
        width: 780,
        height: 150,
        rowNum: 7,
        viewrecords: true,
        loadonce: true,
        caption: 'Criterios',
        onSelectRow: function (rowid, selected) {
            if (rowid != null) {
                jQuery("#jqGridDetails").jqGrid('setGridParam', {url: Routing.generate('calidad_planmejora_get_actividades', {criterio:rowid}), datatype: 'json'}); // the last setting is for demo only
                jQuery("#jqGridDetails").jqGrid('setCaption', 'Actividades de criterio::' + rowid);
                jQuery("#jqGridDetails").trigger("reloadGrid");
            }
        }, // use the onSelectRow that is triggered on row click to show a details grid
        onSortCol: clearSelection,
        onPaging: clearSelection,
        pager: "#jqGridPager"
    });

    // grid de actividades
    $("#jqGridDetails").jqGrid({
        url: Routing.generate('calidad_planmejora_get_actividades', {criterio:0}),
        mtype: "GET",
        datatype: "json",
        page: 1,
        colModel: [
            {label: 'Order ID', name: 'OrderID', key: true, width: 75},
            {label: 'Required Date', name: 'RequiredDate', width: 100},
            {label: 'Ship Name', name: 'ShipName', width: 100},
            {label: 'Ship City', name: 'ShipCity', width: 100},
            {label: 'Freight', name: 'Freight', width: 75}
        ],
        width: 780,
        rowNum: 5,
        loadonce: true,
        height: '100',
        viewrecords: true,
        caption: 'Actividades::',
        pager: "#jqGridDetailsPager"
    });
});

function clearSelection() {
    jQuery("#jqGridDetails").jqGrid('setGridParam', {url: Routing.generate('calidad_planmejora_get_actividades', {criterio:0}), datatype: 'json'}); // the last setting is for demo purpose only
    jQuery("#jqGridDetails").jqGrid('setCaption', 'Actividades:: ');
    jQuery("#jqGridDetails").trigger("reloadGrid");

}