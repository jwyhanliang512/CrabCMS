
function beforeClickCompanyTreeCheckbox(treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeCompanyCheckbox");
        zTree.checkNode(treeNode, !treeNode.checked, null, true);
        return false;
}

function onCheckCompanyTreeCheckbox(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeCompanyCheckbox"),
        nodes = zTree.getCheckedNodes(true),
        v = "";
        cid = "";
        levelcode = "";
        for (var i=0, l=nodes.length; i<l; i++) {
                v += nodes[i].name + ",";
                cid += nodes[i].id + ",";
                levelcode += nodes[i].levelcode + ",";
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        if (cid.length > 0 ) cid = cid.substring(0, cid.length-1);
        if (levelcode.length > 0 ) levelcode = levelcode.substring(0, levelcode.length-1);
        var cityObj = $("#tree_company_checkbox");
        cityObj.attr("value", v);
        var cityObj = $("#tree_companyid_checkbox");
        cityObj.attr("value", cid);
}

function showMenuCompanyTreeCheckbox() {
        var cityObj = $("#tree_company_checkbox");
        var cityOffset = $("#tree_company_checkbox").offset();
        $("#menuContentCheckbox").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px","z-index":"4000","background-color":"white","overflow":"scroll"}).slideDown("fast");
        $("body").bind("mousedown", onBodyDownCompanyTreeCheckbox);
}
function hideMenuCompanyTreeCheckbox() {
        $("#menuContentCheckbox").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDownCompanyTreeCheckbox);
}
function onBodyDownCompanyTreeCheckbox(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "tree_company_checkbox" || event.target.id == "menuContentCheckbox" || $(event.target).parents("#menuContentCheckbox").length>0)) {
                hideMenuCompanyTreeCheckbox();
        }
}