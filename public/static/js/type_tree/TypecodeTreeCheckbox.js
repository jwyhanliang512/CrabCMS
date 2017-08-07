
function beforeClickTypecodeTreeCheckbox(treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeTypecodeCheckbox");
        zTree.checkNode(treeNode, !treeNode.checked, null, true);
        return false;
}

function onCheckTypecodeTreeCheckbox(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeTypecodeCheckbox"),
        nodes = zTree.getCheckedNodes(true),
        v = "";
        cid = "";
        for (var i=0, l=nodes.length; i<l; i++) {
                v += nodes[i].name + ",";
                cid += nodes[i].id + ",";
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        if (cid.length > 0 ) cid = cid.substring(0, cid.length-1);
        var cityObj = $("#tree_typecode_checkbox");
        cityObj.attr("value", v);
        var cityObj = $("#tree_typecodeid_checkbox");
        cityObj.attr("value", cid);
}

function showMenuTypecodeTreeCheckbox() {
        var cityObj = $("#tree_typecode_checkbox");
        var cityOffset = $("#tree_typecode_checkbox").offset();
        $("#menuTypecodeCheckbox").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px","z-index":"4000","background-color":"white","overflow":"scroll"}).slideDown("fast");
        $("body").bind("mousedown", onBodyDownTypecodeTreeCheckbox);
}
function hideMenuTypecodeTreeCheckbox() {
        $("#menuTypecodeCheckbox").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDownTypecodeTreeCheckbox);
}
function onBodyDownTypecodeTreeCheckbox(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "tree_typecode_checkbox" || event.target.id == "menuTypecodeCheckbox" || $(event.target).parents("#menuTypecodeCheckbox").length>0)) {
                hideMenuTypecodeTreeCheckbox();
        }
}