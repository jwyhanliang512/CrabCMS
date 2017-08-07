

function beforeClickCompanyTree(treeId, treeNode) {
//        var check = (treeNode && !treeNode.isParent);
//        if (!check) alert("只能选择城市...");
//        return check;
}

function onClickCompanyTree(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
        nodes = zTree.getSelectedNodes(),
        v = "";
        cid = "";
        levelcode = "";
        nodes.sort(function compare(a,b){return a.id-b.id;});
        for (var i=0, l=nodes.length; i<l; i++) {
                v += nodes[i].name + ",";
                cid += nodes[i].id;
                levelcode += nodes[i].levelcode;
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        var cityObj = $("#tree_company");
        cityObj.attr("value", v);
        var cityObj = $("#tree_companyid");
        cityObj.attr("value", cid);
        var cityObj = $("#tree_companylevelcode");
        cityObj.attr("value", levelcode);
        hideMenuCompanyTree();
}

function showMenuCompanyTree() {
        var cityObj = $("#tree_company");
        var cityOffset = $("#tree_company").offset();
        $("#menuContent").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px","z-index":"4000","background-color":"white","overflow":"scroll"}).slideDown("fast");

        $("body").bind("mousedown", onBodyDownCompanyTree);
}
function hideMenuCompanyTree() {
        $("#menuContent").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDownCompanyTree);
}
function onBodyDownCompanyTree(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
                hideMenuCompanyTree();
        }
}