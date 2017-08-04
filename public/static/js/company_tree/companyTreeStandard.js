

function beforeClick(treeId, treeNode) {
//        var check = (treeNode && !treeNode.isParent);
//        if (!check) alert("只能选择城市...");
//        return check;
}

function onClick(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
        nodes = zTree.getSelectedNodes(),
        v = "";
        sid = "";
        levelcode = "";
        nodes.sort(function compare(a,b){return a.id-b.id;});
        for (var i=0, l=nodes.length; i<l; i++) {
                v += nodes[i].name + ",";
                sid += nodes[i].id;
                levelcode += nodes[i].levelcode;
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        var cityObj = $("#tree_company");
        cityObj.attr("value", v);
        var cityObj = $("#tree_companyid");
        cityObj.attr("value", sid);
        var cityObj = $("#tree_companylevelcode");
        cityObj.attr("value", levelcode);
        hideMenu();
}

function showMenu() {
        var cityObj = $("#tree_company");
        var cityOffset = $("#tree_company").offset();
        $("#menuContent").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px","z-index":"4000","background-color":"white","overflow":"scroll"}).slideDown("fast");

        $("body").bind("mousedown", onBodyDown);
}
function hideMenu() {
        $("#menuContent").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDown);
}
function onBodyDown(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
                hideMenu();
        }
}